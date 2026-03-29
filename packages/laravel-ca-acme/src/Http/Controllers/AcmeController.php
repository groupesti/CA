<?php

declare(strict_types=1);

namespace CA\Acme\Http\Controllers;

use CA\Acme\Contracts\AcmeServerInterface;
use CA\Models\ChallengeStatus;
use CA\Models\ChallengeType;
use CA\Models\OrderStatus;
use CA\Acme\Events\AcmeChallengeCompleted;
use CA\Acme\Models\AcmeAccount;
use CA\Acme\Models\AcmeAuthorization;
use CA\Acme\Models\AcmeChallenge;
use CA\Acme\Models\AcmeOrder;
use CA\Acme\Order\OrderProcessor;
use CA\Acme\Services\AcmeServer;
use CA\Acme\Services\JwsVerifier;
use CA\Acme\Services\NonceManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AcmeController extends Controller
{
    public function __construct(
        private readonly AcmeServerInterface $acmeServer,
        private readonly NonceManager $nonceManager,
        private readonly OrderProcessor $orderProcessor,
    ) {}

    /**
     * GET /directory - Return the ACME directory object.
     */
    public function directory(): JsonResponse
    {
        return response()->json($this->acmeServer->directory());
    }

    /**
     * HEAD|GET /new-nonce - Return a fresh nonce.
     */
    public function newNonce(Request $request): Response
    {
        $nonce = $this->acmeServer->newNonce();

        $statusCode = $request->isMethod('HEAD') ? 200 : 204;

        return response('', $statusCode)
            ->header('Replay-Nonce', $nonce)
            ->header('Cache-Control', 'no-store')
            ->header('Link', '<' . url(config('ca-acme.route_prefix', 'acme') . '/directory') . '>;rel="index"');
    }

    /**
     * POST /new-account - Create or find an account.
     */
    public function newAccount(Request $request): JsonResponse
    {
        $payload = $request->attributes->get('acme_payload', []);
        $jwk = $request->attributes->get('acme_jwk');

        if ($jwk === null) {
            return $this->problemResponse(
                'malformed',
                'Missing JWK in request.',
                400,
            );
        }

        try {
            $account = $this->acmeServer->newAccount($payload, json_encode($jwk));

            $prefix = config('ca-acme.route_prefix', 'acme');
            $statusCode = $account->wasRecentlyCreated ? 201 : 200;

            return response()->json([
                'status' => $account->status,
                'contact' => $account->contact,
                'termsOfServiceAgreed' => $account->terms_agreed,
                'orders' => url("{$prefix}/account/{$account->id}/orders"),
            ], $statusCode)
                ->header('Location', url("{$prefix}/account/{$account->id}"))
                ->header('Replay-Nonce', $this->nonceManager->generate());
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'does not exist')) {
                return $this->problemResponse('accountDoesNotExist', $e->getMessage(), 400);
            }

            return $this->problemResponse('malformed', $e->getMessage(), 400);
        }
    }

    /**
     * POST /new-order - Create a new order.
     */
    public function newOrder(Request $request): JsonResponse
    {
        $account = $request->attributes->get('acme_account');

        if (!$account instanceof AcmeAccount) {
            return $this->problemResponse('unauthorized', 'Account not found.', 401);
        }

        $payload = $request->attributes->get('acme_payload', []);
        $identifiers = $payload['identifiers'] ?? [];

        if (empty($identifiers)) {
            return $this->problemResponse(
                'malformed',
                'At least one identifier is required.',
                400,
            );
        }

        // Validate identifiers
        foreach ($identifiers as $identifier) {
            if (!isset($identifier['type'], $identifier['value'])) {
                return $this->problemResponse(
                    'malformed',
                    'Each identifier must have a type and value.',
                    400,
                );
            }

            if ($identifier['type'] !== 'dns') {
                return $this->problemResponse(
                    'unsupportedIdentifier',
                    "Identifier type '{$identifier['type']}' is not supported.",
                    400,
                );
            }
        }

        try {
            $order = $this->acmeServer->newOrder($account, $identifiers);
            $prefix = config('ca-acme.route_prefix', 'acme');

            return response()->json($this->formatOrder($order), 201)
                ->header('Location', url("{$prefix}/order/{$order->id}"))
                ->header('Replay-Nonce', $this->nonceManager->generate());
        } catch (\RuntimeException $e) {
            return $this->problemResponse('serverInternal', $e->getMessage(), 500);
        }
    }

    /**
     * POST /order/{uuid} - Get order details (POST-as-GET).
     */
    public function getOrder(Request $request, string $uuid): JsonResponse
    {
        try {
            $order = $this->acmeServer->getOrder($uuid);

            // Check for expiry
            if ($order->expires_at?->isPast() && $order->status === OrderStatus::PENDING) {
                $this->orderProcessor->checkAndTransition($order);
                $order->refresh();
            }

            return response()->json($this->formatOrder($order))
                ->header('Replay-Nonce', $this->nonceManager->generate());
        } catch (\RuntimeException $e) {
            return $this->problemResponse('malformed', $e->getMessage(), 404);
        }
    }

    /**
     * POST /authorization/{uuid} - Get authorization details (POST-as-GET).
     */
    public function getAuthorization(Request $request, string $uuid): JsonResponse
    {
        $authorization = AcmeAuthorization::with('challenges')->find($uuid);

        if ($authorization === null) {
            return $this->problemResponse('malformed', 'Authorization not found.', 404);
        }

        $prefix = config('ca-acme.route_prefix', 'acme');

        $challenges = $authorization->challenges->map(fn(AcmeChallenge $ch): array => [
            'type' => $ch->type,
            'url' => url("{$prefix}/challenge/{$ch->id}"),
            'token' => $ch->token,
            'status' => $ch->status,
            'validated' => $ch->validated_at?->toIso8601String(),
            'error' => $ch->error,
        ])->toArray();

        $response = [
            'identifier' => [
                'type' => $authorization->identifier_type,
                'value' => $authorization->identifier_value,
            ],
            'status' => $authorization->status,
            'expires' => $authorization->expires_at?->toIso8601String(),
            'challenges' => $challenges,
        ];

        if ($authorization->wildcard) {
            $response['wildcard'] = true;
        }

        return response()->json($response)
            ->header('Replay-Nonce', $this->nonceManager->generate());
    }

    /**
     * POST /challenge/{uuid} - Respond to a challenge.
     */
    public function getChallenge(Request $request, string $uuid): JsonResponse
    {
        $challenge = AcmeChallenge::with('authorization.order.account')->find($uuid);

        if ($challenge === null) {
            return $this->problemResponse('malformed', 'Challenge not found.', 404);
        }

        $prefix = config('ca-acme.route_prefix', 'acme');

        // If the client is POSTing to respond to the challenge
        $payload = $request->attributes->get('acme_payload', []);

        if ($challenge->status === ChallengeStatus::PENDING && !$this->isPostAsGet($payload)) {
            $account = $challenge->authorization->order->account;

            // Set the key authorization
            $thumbprint = $account->account_key_thumbprint;
            $keyAuth = $challenge->token . '.' . $thumbprint;

            $challenge->update([
                'status' => ChallengeStatus::PROCESSING,
                'key_authorization' => $keyAuth,
            ]);

            // Validate the challenge
            $validator = app(AcmeServer::class)->getChallengeValidator($challenge->type);

            if ($validator->validate($challenge)) {
                $challenge->update([
                    'status' => ChallengeStatus::VALID,
                    'validated_at' => now(),
                ]);

                event(new AcmeChallengeCompleted($challenge));
                $this->orderProcessor->processChallenge($challenge);
            } else {
                $challenge->update([
                    'status' => ChallengeStatus::INVALID,
                    'error' => [
                        'type' => 'urn:ietf:params:acme:error:incorrectResponse',
                        'detail' => 'Challenge validation failed.',
                        'status' => 403,
                    ],
                ]);
            }

            $challenge->refresh();
        }

        $response = [
            'type' => $challenge->type,
            'url' => url("{$prefix}/challenge/{$challenge->id}"),
            'token' => $challenge->token,
            'status' => $challenge->status,
        ];

        if ($challenge->validated_at !== null) {
            $response['validated'] = $challenge->validated_at->toIso8601String();
        }

        if ($challenge->error !== null) {
            $response['error'] = $challenge->error;
        }

        $authUrl = url("{$prefix}/authorization/{$challenge->authorization_id}");

        return response()->json($response)
            ->header('Link', "<{$authUrl}>;rel=\"up\"")
            ->header('Replay-Nonce', $this->nonceManager->generate());
    }

    /**
     * POST /order/{uuid}/finalize - Submit CSR to finalize order.
     */
    public function finalizeOrder(Request $request, string $uuid): JsonResponse
    {
        $order = AcmeOrder::find($uuid);

        if ($order === null) {
            return $this->problemResponse('malformed', 'Order not found.', 404);
        }

        $payload = $request->attributes->get('acme_payload', []);
        $csr = $payload['csr'] ?? null;

        if ($csr === null) {
            return $this->problemResponse('malformed', 'CSR is required.', 400);
        }

        try {
            $order = $this->acmeServer->finalizeOrder($order, $csr);
            $prefix = config('ca-acme.route_prefix', 'acme');

            return response()->json($this->formatOrder($order))
                ->header('Location', url("{$prefix}/order/{$order->id}"))
                ->header('Replay-Nonce', $this->nonceManager->generate());
        } catch (\RuntimeException $e) {
            return $this->problemResponse('orderNotReady', $e->getMessage(), 403);
        }
    }

    /**
     * POST /certificate/{uuid} - Download certificate (POST-as-GET).
     */
    public function getCertificate(Request $request, string $uuid): Response|JsonResponse
    {
        $order = AcmeOrder::find($uuid);

        if ($order === null) {
            return $this->problemResponse('malformed', 'Order not found.', 404);
        }

        try {
            $pem = $this->acmeServer->getCertificate($order);

            return response($pem)
                ->header('Content-Type', 'application/pem-certificate-chain')
                ->header('Replay-Nonce', $this->nonceManager->generate());
        } catch (\RuntimeException $e) {
            return $this->problemResponse('malformed', $e->getMessage(), 404);
        }
    }

    /**
     * POST /revoke-cert - Revoke a certificate.
     */
    public function revokeCertificate(Request $request): JsonResponse
    {
        $payload = $request->attributes->get('acme_payload', []);
        $certificate = $payload['certificate'] ?? null;
        $reason = $payload['reason'] ?? null;

        if ($certificate === null) {
            return $this->problemResponse('malformed', 'Certificate is required.', 400);
        }

        try {
            // Decode the base64url-encoded DER certificate
            $certDer = JwsVerifier::base64UrlDecode($certificate);
            $certPem = "-----BEGIN CERTIFICATE-----\n"
                . chunk_split(base64_encode($certDer), 64)
                . "-----END CERTIFICATE-----";

            $this->acmeServer->revokeCertificate($certPem, $reason);

            return response()->json(null, 200)
                ->header('Replay-Nonce', $this->nonceManager->generate());
        } catch (\RuntimeException $e) {
            return $this->problemResponse('malformed', $e->getMessage(), 400);
        }
    }

    /**
     * Check if the request is a POST-as-GET (empty payload).
     */
    private function isPostAsGet(mixed $payload): bool
    {
        return $payload === '' || $payload === null || $payload === [];
    }

    /**
     * Format an order for the ACME response.
     *
     * @return array<string, mixed>
     */
    private function formatOrder(AcmeOrder $order): array
    {
        $prefix = config('ca-acme.route_prefix', 'acme');
        $order->load('authorizations');

        $response = [
            'status' => $order->status,
            'expires' => $order->expires_at?->toIso8601String(),
            'identifiers' => $order->identifiers,
            'authorizations' => $order->authorizations->map(
                fn(AcmeAuthorization $auth): string => url("{$prefix}/authorization/{$auth->id}"),
            )->toArray(),
            'finalize' => url("{$prefix}/order/{$order->id}/finalize"),
        ];

        if ($order->not_before !== null) {
            $response['notBefore'] = $order->not_before->toIso8601String();
        }

        if ($order->not_after !== null) {
            $response['notAfter'] = $order->not_after->toIso8601String();
        }

        if ($order->status === OrderStatus::VALID && $order->certificate_id !== null) {
            $response['certificate'] = url("{$prefix}/certificate/{$order->id}");
        }

        return $response;
    }

    /**
     * Return an RFC 7807 problem response.
     */
    private function problemResponse(string $type, string $detail, int $status): JsonResponse
    {
        return response()->json([
            'type' => "urn:ietf:params:acme:error:{$type}",
            'detail' => $detail,
            'status' => $status,
        ], $status)
            ->header('Content-Type', 'application/problem+json')
            ->header('Replay-Nonce', $this->nonceManager->generate());
    }
}
