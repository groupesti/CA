<?php

declare(strict_types=1);

namespace CA\Tsa\Services;

use CA\Tsa\Asn1\Maps\PKIStatusInfo;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\ASN1;
use phpseclib3\File\ASN1\Element;
use phpseclib3\Math\BigInteger;

/**
 * Build RFC 3161 TimeStampResp DER structures including CMS SignedData wrapping.
 */
class TsaResponseBuilder
{
    // OIDs
    private const OID_SIGNED_DATA = '1.2.840.113549.1.7.2';
    private const OID_TST_INFO = '1.2.840.113549.1.9.16.1.4';
    private const OID_CONTENT_TYPE = '1.2.840.113549.1.9.3';
    private const OID_SIGNING_TIME = '1.2.840.113549.1.9.5';
    private const OID_MESSAGE_DIGEST = '1.2.840.113549.1.9.4';
    private const OID_SHA256 = '2.16.840.1.101.3.4.2.1';
    private const OID_SHA384 = '2.16.840.1.101.3.4.2.2';
    private const OID_SHA512 = '2.16.840.1.101.3.4.2.3';
    private const OID_SHA224 = '2.16.840.1.101.3.4.2.4';
    private const OID_SHA1 = '1.3.14.3.2.26';
    private const OID_RSA_ENCRYPTION = '1.2.840.113549.1.1.1';
    private const OID_RSA_SHA256 = '1.2.840.113549.1.1.11';
    private const OID_RSA_SHA384 = '1.2.840.113549.1.1.12';
    private const OID_RSA_SHA512 = '1.2.840.113549.1.1.13';
    private const OID_ECDSA_SHA256 = '1.2.840.10045.4.3.2';
    private const OID_ECDSA_SHA384 = '1.2.840.10045.4.3.3';
    private const OID_ECDSA_SHA512 = '1.2.840.10045.4.3.4';
    private const OID_EC_PUBLIC_KEY = '1.2.840.10045.2.1';

    private const HASH_OIDS = [
        'sha1' => self::OID_SHA1,
        'sha224' => self::OID_SHA224,
        'sha256' => self::OID_SHA256,
        'sha384' => self::OID_SHA384,
        'sha512' => self::OID_SHA512,
    ];

    /**
     * Build a TSTInfo DER structure.
     */
    public function buildTstInfo(
        string $policyOid,
        string $hashAlgorithmOid,
        string $hashedMessageRaw,
        string $serialNumberHex,
        \DateTimeInterface $genTime,
        ?array $accuracy,
        bool $ordering,
        ?string $nonce,
    ): string {
        $asn1 = new ASN1();

        // version INTEGER (1)
        $tstInfo = chr(ASN1::TYPE_INTEGER) . chr(1) . chr(1);

        // policy OID
        $tstInfo .= $this->encodeOid($policyOid);

        // messageImprint SEQUENCE
        $algIdDer = $this->encodeAlgorithmIdentifier($hashAlgorithmOid);
        $hashedMsgDer = $this->encodeOctetString($hashedMessageRaw);
        $tstInfo .= $this->encodeSequence($algIdDer . $hashedMsgDer);

        // serialNumber INTEGER
        $serialBytes = hex2bin($serialNumberHex);
        $tstInfo .= $this->encodeInteger($serialBytes);

        // genTime GeneralizedTime
        $tstInfo .= $this->encodeGeneralizedTime($genTime);

        // accuracy OPTIONAL
        if ($accuracy !== null) {
            $tstInfo .= $this->encodeAccuracy($accuracy);
        }

        // ordering BOOLEAN DEFAULT FALSE — only encode if true
        if ($ordering) {
            $tstInfo .= chr(ASN1::TYPE_BOOLEAN) . chr(1) . chr(0xFF);
        }

        // nonce INTEGER OPTIONAL
        if ($nonce !== null) {
            $nonceBI = new BigInteger($nonce, 10);
            $nonceBytes = $nonceBI->toBytes(true);
            $tstInfo .= $this->encodeInteger($nonceBytes);
        }

        return $this->encodeSequence($tstInfo);
    }

    /**
     * Wrap TSTInfo in CMS SignedData and build a complete TimeStampResp with status=granted.
     */
    public function buildGrantedResponse(
        string $tstInfoDer,
        PrivateKey $privateKey,
        string $certificateDer,
        bool $includeCert,
        string $hashAlgorithm = 'sha256',
    ): string {
        // Build the SignedData
        $signedDataDer = $this->wrapInSignedData(
            $tstInfoDer,
            $privateKey,
            $certificateDer,
            $includeCert,
            $hashAlgorithm,
        );

        // ContentInfo wrapping: SEQUENCE { contentType, [0] EXPLICIT content }
        $contentInfoDer = $this->encodeOidRaw(self::OID_SIGNED_DATA)
            . $this->encodeContextExplicit(0, $signedDataDer);
        $contentInfoDer = $this->encodeSequence($contentInfoDer);

        // Build PKIStatusInfo with status=granted
        $statusDer = $this->encodePkiStatusInfo(PKIStatusInfo::STATUS_GRANTED);

        // TimeStampResp = SEQUENCE { status, timeStampToken }
        return $this->encodeSequence($statusDer . $contentInfoDer);
    }

    /**
     * Build an error/rejection TimeStampResp.
     */
    public function buildErrorResponse(
        int $status,
        ?string $statusString = null,
        ?int $failInfo = null,
    ): string {
        $statusDer = $this->encodePkiStatusInfo($status, $statusString, $failInfo);

        return $this->encodeSequence($statusDer);
    }

    /**
     * Wrap TSTInfo content into CMS SignedData structure.
     *
     * SignedData ::= SEQUENCE {
     *   version CMSVersion,
     *   digestAlgorithms DigestAlgorithmIdentifiers,
     *   encapContentInfo EncapsulatedContentInfo,
     *   certificates [0] IMPLICIT CertificateSet OPTIONAL,
     *   signerInfos SignerInfos
     * }
     */
    public function wrapInSignedData(
        string $tstInfoDer,
        PrivateKey $privateKey,
        string $certificateDer,
        bool $includeCert,
        string $hashAlgorithm = 'sha256',
    ): string {
        $hashOid = self::HASH_OIDS[$hashAlgorithm] ?? self::OID_SHA256;

        // ---- digestAlgorithms SET OF AlgorithmIdentifier ----
        $digestAlgDer = $this->encodeAlgorithmIdentifier($hashOid);
        $digestAlgSetDer = $this->encodeSet($digestAlgDer);

        // ---- encapContentInfo ----
        // EncapsulatedContentInfo ::= SEQUENCE {
        //   eContentType ContentType (id-smime-ct-TSTInfo),
        //   eContent [0] EXPLICIT OCTET STRING OPTIONAL
        // }
        $eContentOctet = $this->encodeOctetString($tstInfoDer);
        $encapContentInfoDer = $this->encodeOidRaw(self::OID_TST_INFO)
            . $this->encodeContextExplicit(0, $eContentOctet);
        $encapContentInfoDer = $this->encodeSequence($encapContentInfoDer);

        // ---- certificates [0] IMPLICIT ----
        $certsDer = '';
        if ($includeCert) {
            $certsDer = $this->encodeContextImplicit(0, $certificateDer);
        }

        // ---- signerInfos ----
        $signerInfoDer = $this->buildSignerInfo(
            $tstInfoDer,
            $privateKey,
            $certificateDer,
            $hashAlgorithm,
            $hashOid,
        );
        $signerInfoSetDer = $this->encodeSet($signerInfoDer);

        // ---- SignedData SEQUENCE ----
        // version = 3 (for encapContentInfo with eContentType != id-data)
        $versionDer = chr(ASN1::TYPE_INTEGER) . chr(1) . chr(3);

        $signedData = $versionDer
            . $digestAlgSetDer
            . $encapContentInfoDer
            . $certsDer
            . $signerInfoSetDer;

        return $this->encodeSequence($signedData);
    }

    /**
     * Build SignerInfo structure.
     *
     * SignerInfo ::= SEQUENCE {
     *   version CMSVersion,
     *   sid SignerIdentifier,
     *   digestAlgorithm DigestAlgorithmIdentifier,
     *   signedAttrs [0] IMPLICIT SignedAttributes OPTIONAL,
     *   signatureAlgorithm SignatureAlgorithmIdentifier,
     *   signature SignatureValue
     * }
     */
    private function buildSignerInfo(
        string $tstInfoDer,
        PrivateKey $privateKey,
        string $certificateDer,
        string $hashAlgorithm,
        string $hashOid,
    ): string {
        // version = 1 (issuerAndSerialNumber)
        $versionDer = chr(ASN1::TYPE_INTEGER) . chr(1) . chr(1);

        // sid = IssuerAndSerialNumber (extract from certificate)
        $sidDer = $this->extractIssuerAndSerial($certificateDer);

        // digestAlgorithm
        $digestAlgDer = $this->encodeAlgorithmIdentifier($hashOid);

        // signedAttrs: content-type, signing-time, message-digest
        $contentTypeAttr = $this->encodeAttribute(
            self::OID_CONTENT_TYPE,
            $this->encodeOidRaw(self::OID_TST_INFO),
        );

        $signingTimeAttr = $this->encodeAttribute(
            self::OID_SIGNING_TIME,
            $this->encodeUtcTime(new \DateTimeImmutable('now', new \DateTimeZone('UTC'))),
        );

        // message-digest = hash of the eContent (TSTInfo DER)
        $digest = hash($hashAlgorithm, $tstInfoDer, true);
        $messageDigestAttr = $this->encodeAttribute(
            self::OID_MESSAGE_DIGEST,
            $this->encodeOctetString($digest),
        );

        $signedAttrsDer = $contentTypeAttr . $signingTimeAttr . $messageDigestAttr;

        // For signing, signedAttrs must be encoded as SET OF (tag 0x31)
        $signedAttrsForSigning = $this->encodeSet($signedAttrsDer);
        $signedAttrsImplicit = $this->encodeContextImplicit(0, $signedAttrsDer);

        // signatureAlgorithm
        $sigAlgOid = $this->getSignatureAlgorithmOid($privateKey, $hashAlgorithm);
        $sigAlgDer = $this->encodeAlgorithmIdentifier($sigAlgOid);

        // signature: sign the DER-encoded signedAttrs (with SET tag, not implicit)
        $signature = $this->sign($privateKey, $signedAttrsForSigning, $hashAlgorithm);
        $signatureDer = $this->encodeOctetString($signature);

        return $this->encodeSequence(
            $versionDer
            . $sidDer
            . $digestAlgDer
            . $signedAttrsImplicit
            . $sigAlgDer
            . $signatureDer,
        );
    }

    /**
     * Sign data with the private key using phpseclib.
     */
    private function sign(PrivateKey $privateKey, string $data, string $hashAlgorithm): string
    {
        if ($privateKey instanceof RSA\PrivateKey) {
            /** @var RSA\PrivateKey $signer */
            $signer = $privateKey->withPadding(RSA::SIGNATURE_PKCS1);
            $signer = $signer->withHash($hashAlgorithm);

            return $signer->sign($data);
        }

        if ($privateKey instanceof EC\PrivateKey) {
            $signer = $privateKey->withHash($hashAlgorithm);

            return $signer->sign($data);
        }

        // Fallback for Ed25519 or other key types
        return $privateKey->sign($data);
    }

    /**
     * Extract IssuerAndSerialNumber from a DER certificate.
     */
    private function extractIssuerAndSerial(string $certificateDer): string
    {
        $asn1 = new ASN1();
        $decoded = $asn1->decodeBER($certificateDer);

        if ($decoded === null || !isset($decoded[0])) {
            throw new \RuntimeException('Failed to decode certificate DER for signer info.');
        }

        // Certificate -> tbsCertificate -> issuer (index 3), serialNumber (index 1)
        $tbsCert = $decoded[0]['content'][0];

        // Determine offset: if first element is context-specific (version), offset = 1
        $offset = 0;
        if (isset($tbsCert['content'][0]['type']) && $tbsCert['content'][0]['type'] === (ASN1::TYPE_CONSTRUCTED | 0x80)) {
            $offset = 1;
        }

        // serialNumber is at index $offset + 0 (after version if present)
        // For v3 certs, version is [0] EXPLICIT, then serialNumber, then signature, then issuer
        // Standard: version(optional), serialNumber, signature, issuer, validity, subject, ...
        $serialNumberElement = $tbsCert['content'][$offset];
        $issuerElement = $tbsCert['content'][$offset + 2];

        $serialNumberDer = substr(
            $certificateDer,
            $serialNumberElement['start'],
            $serialNumberElement['length'] + $serialNumberElement['headerlength'],
        );

        $issuerDer = substr(
            $certificateDer,
            $issuerElement['start'],
            $issuerElement['length'] + $issuerElement['headerlength'],
        );

        return $this->encodeSequence($issuerDer . $serialNumberDer);
    }

    /**
     * Get the signature algorithm OID for the given key and hash.
     */
    private function getSignatureAlgorithmOid(PrivateKey $privateKey, string $hashAlgorithm): string
    {
        if ($privateKey instanceof RSA\PrivateKey) {
            return match ($hashAlgorithm) {
                'sha384' => self::OID_RSA_SHA384,
                'sha512' => self::OID_RSA_SHA512,
                default => self::OID_RSA_SHA256,
            };
        }

        if ($privateKey instanceof EC\PrivateKey) {
            return match ($hashAlgorithm) {
                'sha384' => self::OID_ECDSA_SHA384,
                'sha512' => self::OID_ECDSA_SHA512,
                default => self::OID_ECDSA_SHA256,
            };
        }

        // Ed25519: 1.3.101.112
        return '1.3.101.112';
    }

    // ---- Low-level DER encoding helpers ----

    private function encodeLength(int $length): string
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

    private function encodeSequence(string $content): string
    {
        return chr(0x30) . $this->encodeLength(strlen($content)) . $content;
    }

    private function encodeSet(string $content): string
    {
        return chr(0x31) . $this->encodeLength(strlen($content)) . $content;
    }

    private function encodeOctetString(string $content): string
    {
        return chr(0x04) . $this->encodeLength(strlen($content)) . $content;
    }

    private function encodeInteger(string $bytes): string
    {
        // Ensure positive encoding: prepend 0x00 if high bit is set
        if (strlen($bytes) > 0 && (ord($bytes[0]) & 0x80)) {
            $bytes = "\x00" . $bytes;
        }

        if ($bytes === '') {
            $bytes = "\x00";
        }

        return chr(ASN1::TYPE_INTEGER) . $this->encodeLength(strlen($bytes)) . $bytes;
    }

    private function encodeOid(string $oid): string
    {
        return $this->encodeOidRaw($oid);
    }

    private function encodeOidRaw(string $oid): string
    {
        $parts = array_map('intval', explode('.', $oid));

        if (count($parts) < 2) {
            throw new \RuntimeException("Invalid OID: {$oid}");
        }

        $encoded = chr($parts[0] * 40 + $parts[1]);

        for ($i = 2; $i < count($parts); $i++) {
            $value = $parts[$i];
            if ($value < 128) {
                $encoded .= chr($value);
            } else {
                $temp = '';
                $temp = chr($value & 0x7F) . $temp;
                $value >>= 7;
                while ($value > 0) {
                    $temp = chr(($value & 0x7F) | 0x80) . $temp;
                    $value >>= 7;
                }
                $encoded .= $temp;
            }
        }

        return chr(0x06) . $this->encodeLength(strlen($encoded)) . $encoded;
    }

    private function encodeAlgorithmIdentifier(string $oid): string
    {
        $oidDer = $this->encodeOidRaw($oid);
        // NULL parameters
        $nullDer = chr(0x05) . chr(0x00);

        return $this->encodeSequence($oidDer . $nullDer);
    }

    private function encodeGeneralizedTime(\DateTimeInterface $time): string
    {
        $formatted = $time->format('YmdHis') . 'Z';

        return chr(0x18) . $this->encodeLength(strlen($formatted)) . $formatted;
    }

    private function encodeUtcTime(\DateTimeInterface $time): string
    {
        $formatted = $time->format('ymdHis') . 'Z';

        return chr(0x17) . $this->encodeLength(strlen($formatted)) . $formatted;
    }

    private function encodeAccuracy(array $accuracy): string
    {
        $content = '';

        if (isset($accuracy['seconds']) && $accuracy['seconds'] > 0) {
            $secBytes = (new BigInteger($accuracy['seconds']))->toBytes(true);
            $content .= $this->encodeInteger($secBytes);
        }

        if (isset($accuracy['millis']) && $accuracy['millis'] > 0) {
            $millisBytes = (new BigInteger($accuracy['millis']))->toBytes(true);
            // [0] IMPLICIT INTEGER
            if (strlen($millisBytes) > 0 && (ord($millisBytes[0]) & 0x80)) {
                $millisBytes = "\x00" . $millisBytes;
            }
            $content .= chr(0x80) . $this->encodeLength(strlen($millisBytes)) . $millisBytes;
        }

        if (isset($accuracy['micros']) && $accuracy['micros'] > 0) {
            $microsBytes = (new BigInteger($accuracy['micros']))->toBytes(true);
            if (strlen($microsBytes) > 0 && (ord($microsBytes[0]) & 0x80)) {
                $microsBytes = "\x00" . $microsBytes;
            }
            $content .= chr(0x81) . $this->encodeLength(strlen($microsBytes)) . $microsBytes;
        }

        if ($content === '') {
            return '';
        }

        return $this->encodeSequence($content);
    }

    private function encodeAttribute(string $oid, string $valueDer): string
    {
        $oidDer = $this->encodeOidRaw($oid);
        $valueSetDer = $this->encodeSet($valueDer);

        return $this->encodeSequence($oidDer . $valueSetDer);
    }

    private function encodeContextExplicit(int $tag, string $content): string
    {
        $tagByte = 0xA0 | $tag;

        return chr($tagByte) . $this->encodeLength(strlen($content)) . $content;
    }

    private function encodeContextImplicit(int $tag, string $content): string
    {
        // IMPLICIT tagging: replace the original tag byte of the inner TLV
        // with a context-specific constructed tag, keeping the original
        // length and value bytes intact.
        $tagByte = chr(0xA0 | $tag); // constructed, context-specific

        // Strip the original tag byte from the content (first byte is the tag)
        // and replace it with the context-specific tag.
        return $tagByte . substr($content, 1);
    }

    private function encodeBitString(string $content): string
    {
        // Prepend unused bits count (0)
        $data = chr(0x00) . $content;

        return chr(0x03) . $this->encodeLength(strlen($data)) . $data;
    }

    private function encodeUtf8String(string $content): string
    {
        return chr(0x0C) . $this->encodeLength(strlen($content)) . $content;
    }

    /**
     * Encode PKIStatusInfo.
     */
    private function encodePkiStatusInfo(int $status, ?string $statusString = null, ?int $failInfo = null): string
    {
        // status INTEGER
        $statusBytes = (new BigInteger($status))->toBytes(true);
        if ($statusBytes === '') {
            $statusBytes = "\x00";
        }
        $content = $this->encodeInteger($statusBytes);

        // statusString SEQUENCE OF UTF8String OPTIONAL
        if ($statusString !== null) {
            $utf8 = $this->encodeUtf8String($statusString);
            $content .= $this->encodeSequence($utf8);
        }

        // failInfo BIT STRING OPTIONAL
        if ($failInfo !== null) {
            // PKIFailureInfo is a BIT STRING; encode the bit position
            $byteCount = (int) ceil(($failInfo + 1) / 8);
            $byteCount = max($byteCount, 4); // At least 4 bytes for the BIT STRING
            $bitString = str_repeat("\x00", $byteCount);
            $byteIndex = (int) floor($failInfo / 8);
            $bitIndex = 7 - ($failInfo % 8);
            $bitString[$byteIndex] = chr(ord($bitString[$byteIndex]) | (1 << $bitIndex));
            $unusedBits = ($byteCount * 8) - ($failInfo + 1);
            $content .= chr(0x03) . $this->encodeLength(strlen($bitString) + 1)
                . chr($unusedBits) . $bitString;
        }

        return $this->encodeSequence($content);
    }
}
