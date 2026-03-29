<?php

declare(strict_types=1);

namespace CA\Est\Services;

use phpseclib3\File\ASN1;

/**
 * Builds EST response formats per RFC 7030.
 *
 * Constructs PKCS#7 degenerate SignedData, CSR attributes, and
 * server key generation multipart responses using phpseclib3 ASN1.
 */
class EstResponseBuilder
{
    /**
     * Build a degenerate PKCS#7 certs-only SignedData containing the given certificates.
     *
     * Per RFC 7030, the /cacerts and enrollment responses use a degenerate
     * SignedData (no signerInfos) with certificates in the certificates field.
     *
     * @param  array<int, string>  $certPems  Array of PEM-encoded certificates
     * @return string Base64-encoded DER of the PKCS#7 structure
     */
    public function buildCertsOnlyPkcs7(array $certPems): string
    {
        $certificates = [];

        foreach ($certPems as $pem) {
            $derCert = $this->pemToDer($pem);
            if ($derCert !== null) {
                $certificates[] = $derCert;
            }
        }

        $derSignedData = $this->buildDegenerateSignedData($certificates);

        // Wrap in ContentInfo: SEQUENCE { contentType, [0] EXPLICIT content }
        $contentInfo = $this->buildContentInfo(
            '1.2.840.113549.1.7.2', // id-signedData
            $derSignedData,
        );

        return base64_encode($contentInfo);
    }

    /**
     * Build server key generation response parts.
     *
     * @return array{private_key: string, certificate: string, content_type: string}
     */
    public function buildServerKeyGenResponse(string $privateKeyPem, string $certPkcs7Base64): array
    {
        // The private key is returned as application/pkcs8 (base64 DER)
        $privateKeyDer = $this->pemToDer($privateKeyPem);
        $privateKeyBase64 = base64_encode($privateKeyDer ?? $privateKeyPem);

        return [
            'private_key' => $privateKeyBase64,
            'certificate' => $certPkcs7Base64,
            'content_type' => 'multipart/mixed',
        ];
    }

    /**
     * Build CSR attributes response per RFC 7030 Section 4.5.2.
     *
     * Returns base64-encoded ASN.1 SEQUENCE OF AttrOrOID.
     *
     * @param  array<int, string>  $oids  Array of OID strings
     * @return string Base64-encoded ASN.1
     */
    public function buildCsrAttrs(array $oids): string
    {
        $elements = '';

        foreach ($oids as $oid) {
            // Each AttrOrOID is just an OID in the simple case
            $elements .= $this->encodeOid($oid);
        }

        // SEQUENCE OF AttrOrOID
        $sequence = $this->encodeSequence($elements);

        return base64_encode($sequence);
    }

    /**
     * Convert PEM to DER format.
     */
    private function pemToDer(string $pem): ?string
    {
        // Remove PEM headers/footers and decode base64
        $pem = trim($pem);

        // Handle multiple possible PEM header formats
        $patterns = [
            '/-----BEGIN [A-Z0-9 ]+-----/',
            '/-----END [A-Z0-9 ]+-----/',
        ];

        $base64 = preg_replace($patterns, '', $pem);
        $base64 = preg_replace('/\s+/', '', $base64);

        if ($base64 === null || $base64 === '') {
            return null;
        }

        $der = base64_decode($base64, true);

        return $der !== false ? $der : null;
    }

    /**
     * Build a degenerate SignedData structure (no signerInfos, just certificates).
     *
     * SignedData ::= SEQUENCE {
     *   version          CMSVersion,
     *   digestAlgorithms SET OF DigestAlgorithmIdentifier,
     *   encapContentInfo EncapsulatedContentInfo,
     *   certificates     [0] IMPLICIT CertificateSet OPTIONAL,
     *   crls             [1] IMPLICIT RevocationInfoChoices OPTIONAL,
     *   signerInfos      SET OF SignerInfo
     * }
     *
     * @param  array<int, string>  $derCertificates  Array of DER-encoded certificates
     */
    private function buildDegenerateSignedData(array $derCertificates): string
    {
        // version: 1 (for degenerate certs-only)
        $version = $this->encodeInteger(1);

        // digestAlgorithms: empty SET
        $digestAlgorithms = $this->encodeSet('');

        // encapContentInfo: SEQUENCE { contentType id-data }
        $encapContentInfo = $this->encodeSequence(
            $this->encodeOid('1.2.840.113549.1.7.1'), // id-data
        );

        // certificates [0] IMPLICIT: concatenated DER certificates
        $certsContent = implode('', $derCertificates);
        $certificates = $this->encodeTaggedImplicit(0, $certsContent);

        // signerInfos: empty SET
        $signerInfos = $this->encodeSet('');

        // SignedData SEQUENCE
        return $this->encodeSequence(
            $version . $digestAlgorithms . $encapContentInfo . $certificates . $signerInfos,
        );
    }

    /**
     * Build a ContentInfo structure.
     *
     * ContentInfo ::= SEQUENCE {
     *   contentType ContentType,
     *   content     [0] EXPLICIT ANY DEFINED BY contentType
     * }
     */
    private function buildContentInfo(string $oid, string $content): string
    {
        return $this->encodeSequence(
            $this->encodeOid($oid) . $this->encodeTaggedExplicit(0, $content),
        );
    }

    /**
     * ASN.1 DER encode an INTEGER.
     */
    private function encodeInteger(int $value): string
    {
        $bytes = '';
        if ($value === 0) {
            $bytes = "\x00";
        } else {
            $temp = $value;
            while ($temp > 0) {
                $bytes = chr($temp & 0xFF) . $bytes;
                $temp >>= 8;
            }
            // Add leading zero if high bit is set (positive integer)
            if (ord($bytes[0]) & 0x80) {
                $bytes = "\x00" . $bytes;
            }
        }

        return "\x02" . $this->encodeLength(strlen($bytes)) . $bytes;
    }

    /**
     * ASN.1 DER encode an OID.
     */
    private function encodeOid(string $oid): string
    {
        $parts = array_map('intval', explode('.', $oid));

        if (count($parts) < 2) {
            return '';
        }

        // First two components combined
        $encoded = chr($parts[0] * 40 + $parts[1]);

        for ($i = 2; $i < count($parts); $i++) {
            $encoded .= $this->encodeOidComponent($parts[$i]);
        }

        return "\x06" . $this->encodeLength(strlen($encoded)) . $encoded;
    }

    /**
     * Encode a single OID component using base-128 encoding.
     */
    private function encodeOidComponent(int $value): string
    {
        if ($value < 128) {
            return chr($value);
        }

        $bytes = [];
        $bytes[] = $value & 0x7F;
        $value >>= 7;

        while ($value > 0) {
            $bytes[] = ($value & 0x7F) | 0x80;
            $value >>= 7;
        }

        $result = '';
        for ($i = count($bytes) - 1; $i >= 0; $i--) {
            $result .= chr($bytes[$i]);
        }

        return $result;
    }

    /**
     * ASN.1 DER encode a SEQUENCE.
     */
    private function encodeSequence(string $content): string
    {
        return "\x30" . $this->encodeLength(strlen($content)) . $content;
    }

    /**
     * ASN.1 DER encode a SET.
     */
    private function encodeSet(string $content): string
    {
        return "\x31" . $this->encodeLength(strlen($content)) . $content;
    }

    /**
     * ASN.1 DER encode a context-specific IMPLICIT tag.
     *
     * IMPLICIT replaces the original tag byte of the inner TLV with
     * a context-specific constructed tag, keeping the original length
     * and value bytes intact.
     */
    private function encodeTaggedImplicit(int $tag, string $content): string
    {
        $tagByte = chr(0xA0 | $tag);

        // Replace the original tag byte (first byte) with the context-specific tag
        return $tagByte . substr($content, 1);
    }

    /**
     * ASN.1 DER encode a context-specific EXPLICIT tag.
     *
     * EXPLICIT wraps the entire inner TLV with an outer context-specific
     * constructed tag.
     */
    private function encodeTaggedExplicit(int $tag, string $content): string
    {
        $tagByte = chr(0xA0 | $tag);

        return $tagByte . $this->encodeLength(strlen($content)) . $content;
    }

    /**
     * ASN.1 DER encode length.
     */
    private function encodeLength(int $length): string
    {
        if ($length < 128) {
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
