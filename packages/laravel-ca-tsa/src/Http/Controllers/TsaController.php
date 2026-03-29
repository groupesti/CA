<?php

declare(strict_types=1);

namespace CA\Tsa\Http\Controllers;

use CA\Tsa\Contracts\TsaServerInterface;
use CA\Tsa\Models\TsaCertificate;
use CA\Tsa\Services\TsaRequestParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * RFC 3161 Time-Stamp Authority HTTP endpoint.
 */
class TsaController extends Controller
{
    public function __construct(
        private readonly TsaServerInterface $tsaServer,
        private readonly TsaRequestParser $requestParser,
    ) {}

    /**
     * Handle an RFC 3161 timestamp request.
     *
     * POST / — Accept Content-Type: application/timestamp-query
     *          Return Content-Type: application/timestamp-reply
     */
    public function timestamp(Request $request): Response
    {
        $tsqDer = $request->getContent();

        if ($tsqDer === '' || $tsqDer === false) {
            return new Response('Empty request body.', 400, [
                'Content-Type' => 'text/plain',
            ]);
        }

        $tsrDer = $this->tsaServer->handleRequest($tsqDer);

        return new Response($tsrDer, 200, [
            'Content-Type' => 'application/timestamp-reply',
        ]);
    }

    /**
     * Return TSA information.
     *
     * GET /info
     */
    public function info(): JsonResponse
    {
        $caId = config('ca-tsa.ca_id');

        $activeCert = null;
        if ($caId !== null) {
            $activeCert = TsaCertificate::query()
                ->where('ca_id', $caId)
                ->where('is_active', true)
                ->with('certificate')
                ->first();
        }

        return response()->json([
            'enabled' => (bool) config('ca-tsa.enabled', true),
            'policy_oid' => config('ca-tsa.policy_oid', '1.2.3.4.1'),
            'supported_algorithms' => $this->requestParser->supportedAlgorithms(),
            'ordering' => (bool) config('ca-tsa.ordering', false),
            'nonce_required' => (bool) config('ca-tsa.nonce_required', false),
            'accuracy' => [
                'seconds' => (int) config('ca-tsa.accuracy_seconds', 1),
                'millis' => (int) config('ca-tsa.accuracy_millis', 0),
                'micros' => (int) config('ca-tsa.accuracy_micros', 0),
            ],
            'certificate' => $activeCert?->certificate ? [
                'subject' => $activeCert->certificate->subject_dn,
                'serial_number' => $activeCert->certificate->serial_number,
                'not_before' => $activeCert->certificate->not_before?->toIso8601String(),
                'not_after' => $activeCert->certificate->not_after?->toIso8601String(),
                'fingerprint_sha256' => $activeCert->certificate->fingerprint_sha256,
            ] : null,
        ]);
    }
}
