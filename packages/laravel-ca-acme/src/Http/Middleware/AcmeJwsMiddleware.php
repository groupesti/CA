<?php

declare(strict_types=1);

namespace CA\Acme\Http\Middleware;

use CA\Acme\Models\AcmeAccount;
use CA\Acme\Services\JwsVerifier;
use CA\Acme\Services\NonceManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcmeJwsMiddleware
{
    public function __construct(
        private readonly JwsVerifier $jwsVerifier,
        private readonly NonceManager $nonceManager,
    ) {}

    /**
     * Verify JWS signature on all ACME POST requests.
     * Extracts account from JWK/kid. Validates and consumes nonce.
     * Adds fresh nonce to response Replay-Nonce header.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethod('POST')) {
            return $next($request);
        }

        $body = $request->getContent();
        $jws = json_decode($body, true);

        if (!is_array($jws) || !isset($jws['protected'], $jws['signature'])) {
            return $this->problemResponse('malformed', 'Invalid JWS format.', 400);
        }

        $protected = $jws['protected'];
        $payload = $jws['payload'] ?? '';
        $signature = $jws['signature'];

        // Decode the protected header
        $headerData = json_decode(JwsVerifier::base64UrlDecode($protected), true);

        if (!is_array($headerData)) {
            return $this->problemResponse('malformed', 'Invalid protected header.', 400);
        }

        // Validate and consume nonce
        $nonce = $headerData['nonce'] ?? null;

        if ($nonce === null) {
            return $this->problemResponse('badNonce', 'Missing nonce.', 400);
        }

        if (!$this->nonceManager->validate($nonce)) {
            return $this->problemResponse('badNonce', 'Invalid or expired nonce.', 400);
        }

        // Validate URL matches the request URL
        $headerUrl = $headerData['url'] ?? null;

        if ($headerUrl !== null && $headerUrl !== $request->url()) {
            return $this->problemResponse('malformed', 'URL mismatch in protected header.', 400);
        }

        // Extract JWK or resolve account by kid
        $jwk = $headerData['jwk'] ?? null;
        $kid = $headerData['kid'] ?? null;
        $account = null;

        if ($jwk !== null && $kid !== null) {
            return $this->problemResponse('malformed', 'JWS must use either jwk or kid, not both.', 400);
        }

        if ($kid !== null) {
            // Resolve account by kid (account URL)
            $accountId = basename($kid);
            $account = AcmeAccount::find($accountId);

            if ($account === null) {
                return $this->problemResponse('accountDoesNotExist', 'Account not found.', 400);
            }

            if ($account->status !== 'valid') {
                return $this->problemResponse('unauthorized', 'Account is not valid.', 403);
            }

            $jwk = $account->public_key_jwk;
        }

        if ($jwk === null) {
            return $this->problemResponse('malformed', 'Missing JWK or kid in protected header.', 400);
        }

        // Verify the signature
        if (!$this->jwsVerifier->verify($protected, $payload, $signature, $jwk)) {
            return $this->problemResponse('malformed', 'JWS signature verification failed.', 403);
        }

        // Decode the payload (empty string means POST-as-GET)
        $decodedPayload = $payload !== ''
            ? json_decode(JwsVerifier::base64UrlDecode($payload), true) ?? []
            : '';

        // Attach data to the request
        $request->attributes->set('acme_payload', $decodedPayload);
        $request->attributes->set('acme_jwk', $jwk);
        $request->attributes->set('acme_account', $account);
        $request->attributes->set('acme_protected_header', $headerData);

        /** @var Response $response */
        $response = $next($request);

        // Ensure a fresh nonce on every response
        if (!$response->headers->has('Replay-Nonce')) {
            $response->headers->set('Replay-Nonce', $this->nonceManager->generate());
        }

        return $response;
    }

    /**
     * Return an RFC 7807 problem response.
     */
    private function problemResponse(string $type, string $detail, int $status): Response
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
