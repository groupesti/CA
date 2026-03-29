<?php

declare(strict_types=1);

namespace CA\Scep\Http\Controllers;

use CA\Models\CertificateAuthority;
use CA\Scep\Contracts\ScepServerInterface;
use CA\Models\ScepMessageType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use RuntimeException;

final class ScepController extends Controller
{
    public function __construct(
        private readonly ScepServerInterface $scepServer,
    ) {}

    /**
     * Handle SCEP GET requests (GetCACert, GetCACaps, GetNextCACert).
     */
    public function get(Request $request, string $caUuid): Response
    {
        if (!config('ca-scep.enabled', true)) {
            return new Response('SCEP service is disabled.', 503);
        }

        $ca = CertificateAuthority::findOrFail($caUuid);

        $operation = $request->query('operation', '');

        return match ($operation) {
            'GetCACert' => $this->handleGetCACert($ca),
            'GetCACaps' => $this->handleGetCACaps($ca),
            'GetNextCACert' => $this->handleGetNextCACert($ca),
            'PKIOperation' => $this->handlePKIOperationGet($request, $ca),
            default => new Response('Invalid operation: ' . $operation, 400),
        };
    }

    /**
     * Handle SCEP POST requests (PKIOperation).
     */
    public function post(Request $request, string $caUuid): Response
    {
        if (!config('ca-scep.enabled', true)) {
            return new Response('SCEP service is disabled.', 503);
        }

        $ca = CertificateAuthority::findOrFail($caUuid);

        $pkiMessage = $request->getContent();
        if ($pkiMessage === '' || $pkiMessage === false) {
            return new Response('Empty PKI message body.', 400);
        }

        return $this->handlePKIOperation($ca, $pkiMessage);
    }

    /**
     * Handle GetCACert operation.
     */
    private function handleGetCACert(CertificateAuthority $ca): Response
    {
        try {
            $response = $this->scepServer->handleGetCACert($ca);

            // Determine content type: single cert or PKCS#7 (chain)
            $contentType = $this->isRawCertificate($response)
                ? 'application/x-x509-ca-cert'
                : 'application/x-x509-ca-ra-cert';

            return new Response($response, 200, [
                'Content-Type' => $contentType,
            ]);
        } catch (\Throwable $e) {
            return new Response('GetCACert failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle GetCACaps operation.
     */
    private function handleGetCACaps(CertificateAuthority $ca): Response
    {
        $capabilities = $this->scepServer->handleGetCACaps($ca);

        return new Response($capabilities, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Handle GetNextCACert operation.
     */
    private function handleGetNextCACert(CertificateAuthority $ca): Response
    {
        try {
            $response = $this->scepServer->handleGetNextCACert($ca);

            return new Response($response, 200, [
                'Content-Type' => 'application/x-x509-ca-ra-cert',
            ]);
        } catch (\Throwable $e) {
            return new Response('GetNextCACert failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle PKIOperation from GET request (base64-encoded message query param).
     */
    private function handlePKIOperationGet(Request $request, CertificateAuthority $ca): Response
    {
        $message = $request->query('message', '');
        if ($message === '') {
            return new Response('Missing message parameter for PKIOperation.', 400);
        }

        $derMessage = base64_decode($message, strict: true);
        if ($derMessage === false) {
            return new Response('Invalid base64 message encoding.', 400);
        }

        return $this->handlePKIOperation($ca, $derMessage);
    }

    /**
     * Route a PKI operation to the appropriate handler based on message type.
     */
    private function handlePKIOperation(CertificateAuthority $ca, string $pkiMessage): Response
    {
        try {
            // Peek at the message type to route correctly
            $messageType = $this->peekMessageType($pkiMessage);

            $response = match ($messageType->slug) {
                ScepMessageType::PKCS_REQ => $this->scepServer->handlePKCSReq($ca, $pkiMessage),
                ScepMessageType::GET_CERT_INITIAL => $this->scepServer->handleCertPoll($ca, $pkiMessage),
                ScepMessageType::GET_CERT => $this->scepServer->handleGetCert($ca, $pkiMessage),
                ScepMessageType::GET_CRL => $this->scepServer->handleGetCRL($ca, $pkiMessage),
                default => throw new RuntimeException('Unsupported SCEP message type: ' . $messageType->slug),
            };

            return new Response($response, 200, [
                'Content-Type' => 'application/x-pki-message',
            ]);
        } catch (\Throwable $e) {
            return new Response('PKIOperation failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Peek at the message type in a SCEP PKI message without full parsing.
     *
     * Attempts a lightweight scan of the SignedData authenticated attributes
     * to find the messageType OID and its value.
     */
    private function peekMessageType(string $derMessage): ScepMessageType
    {
        // Use the full parser to determine message type
        $parser = app(ScepServerInterface::class);

        // We need a lightweight way to get message type. Use the parser.
        try {
            $messageParserClass = \CA\Scep\Contracts\ScepMessageParserInterface::class;
            $parser = app($messageParserClass);
            $scepMessage = $parser->parse($derMessage);

            return $scepMessage->messageType;
        } catch (\Throwable) {
            // Default to PKCSReq if we can't determine the type
            return ScepMessageType::fromSlug(ScepMessageType::PKCS_REQ);
        }
    }

    /**
     * Check if the response is a raw DER certificate (not PKCS#7).
     * A raw certificate starts with SEQUENCE tag (0x30) and contains
     * a tbsCertificate starting with another SEQUENCE.
     */
    private function isRawCertificate(string $data): bool
    {
        if (strlen($data) < 4) {
            return false;
        }

        // PKCS#7 SignedData ContentInfo starts with SEQUENCE { OID(1.2.840.113549.1.7.2) ... }
        // A raw certificate also starts with SEQUENCE but its first child is also SEQUENCE (tbsCertificate)
        // For PKCS#7, the first child is an OID

        // Simple heuristic: check if the data contains the SignedData OID
        $signedDataOidDer = "\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x07\x02";

        return !str_contains($data, $signedDataOidDer);
    }
}
