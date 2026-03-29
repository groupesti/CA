<?php

declare(strict_types=1);

namespace CA\Crt\Services;

use CA\Crt\Models\Certificate;
use CA\Models\ExportFormat;
use CA\Exceptions\CertificateException;
use phpseclib3\File\ASN1;
use phpseclib3\File\X509;

final class CertificateExporter
{
    /**
     * Export a certificate in the specified format.
     */
    public function export(Certificate $certificate, ExportFormat $format): string
    {
        return match ($format) {
            ExportFormat::PEM => $this->exportPem($certificate),
            ExportFormat::DER => $this->exportDer($certificate),
            ExportFormat::PKCS7 => $this->exportPkcs7($certificate),
            ExportFormat::PKCS12 => throw new CertificateException(
                'PKCS#12 export requires a private key and is not supported from certificate-only export.',
            ),
        };
    }

    /**
     * Export a certificate chain as concatenated PEM.
     *
     * @param array<int, Certificate> $chain
     */
    public function exportChainPem(array $chain): string
    {
        $pems = [];

        foreach ($chain as $cert) {
            $pems[] = trim($cert->certificate_pem);
        }

        return implode("\n", $pems) . "\n";
    }

    private function exportPem(Certificate $certificate): string
    {
        return $certificate->certificate_pem;
    }

    private function exportDer(Certificate $certificate): string
    {
        if ($certificate->certificate_der !== null && $certificate->certificate_der !== '') {
            return $certificate->certificate_der;
        }

        // Convert PEM to DER by stripping headers and base64 decoding
        $pem = $certificate->certificate_pem;
        $pem = preg_replace('/-----BEGIN CERTIFICATE-----/', '', $pem);
        $pem = preg_replace('/-----END CERTIFICATE-----/', '', $pem);
        $pem = preg_replace('/\s+/', '', $pem);

        $der = base64_decode($pem, true);

        if ($der === false) {
            throw new CertificateException('Failed to decode PEM to DER format.');
        }

        return $der;
    }

    private function exportPkcs7(Certificate $certificate): string
    {
        // Build a degenerate PKCS#7 (SignedData with no signerInfos) containing the certificate chain
        $chain = [];
        $current = $certificate;

        while ($current !== null) {
            $chain[] = $current;
            $current = $current->issuer_certificate_id !== null
                ? $current->issuerCertificate
                : null;
        }

        return $this->buildDegeneratePkcs7($chain);
    }

    /**
     * Build a degenerate PKCS#7 SignedData structure containing only certificates.
     *
     * @param array<int, Certificate> $certificates
     */
    private function buildDegeneratePkcs7(array $certificates): string
    {
        $certDerBlobs = [];

        foreach ($certificates as $cert) {
            $certDerBlobs[] = $this->exportDer($cert);
        }

        // Build ASN.1 structure for degenerate PKCS#7 SignedData
        // ContentType: signedData (1.2.840.113549.1.7.2)
        $contentTypeOid = "\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x07\x02";

        // Build the inner SignedData SEQUENCE
        // version: 1
        $version = "\x02\x01\x01";

        // digestAlgorithms: empty SET
        $digestAlgorithms = "\x31\x00";

        // contentInfo: data (1.2.840.113549.1.7.1)
        $dataOid = "\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x07\x01";
        $contentInfo = $this->asn1Sequence($dataOid);

        // certificates: implicit [0] SET OF Certificate
        $certsConcat = '';
        foreach ($certDerBlobs as $derBlob) {
            $certsConcat .= $derBlob;
        }
        $certificatesField = $this->asn1ContextTag(0, $certsConcat);

        // signerInfos: empty SET
        $signerInfos = "\x31\x00";

        $signedData = $this->asn1Sequence(
            $version . $digestAlgorithms . $contentInfo . $certificatesField . $signerInfos,
        );

        // Wrap in ContentInfo
        $content = $this->asn1ContextTag(0, $signedData);
        $pkcs7 = $this->asn1Sequence($contentTypeOid . $content);

        // Encode as PEM-style PKCS#7
        $b64 = base64_encode($pkcs7);
        $lines = str_split($b64, 64);

        return "-----BEGIN PKCS7-----\n" . implode("\n", $lines) . "\n-----END PKCS7-----\n";
    }

    private function asn1Sequence(string $content): string
    {
        return "\x30" . $this->asn1Length(strlen($content)) . $content;
    }

    private function asn1ContextTag(int $tag, string $content): string
    {
        $tagByte = chr(0xA0 | $tag);

        return $tagByte . $this->asn1Length(strlen($content)) . $content;
    }

    private function asn1Length(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }

        $bytes = '';
        $temp = $length;

        while ($temp > 0) {
            $bytes = chr($temp & 0xFF) . $bytes;
            $temp >>= 8;
        }

        return chr(0x80 | strlen($bytes)) . $bytes;
    }
}
