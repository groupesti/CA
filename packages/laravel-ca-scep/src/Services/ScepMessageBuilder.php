<?php

declare(strict_types=1);

namespace CA\Scep\Services;

use CA\Scep\Asn1\Maps\ScepPkiMessage;
use CA\Models\ScepFailInfo;
use CA\Models\ScepMessageType;
use CA\Models\ScepPkiStatus;
use CA\Scep\Models\ScepTransaction;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey as RSAPrivateKey;
use phpseclib3\Crypt\RSA\PublicKey as RSAPublicKey;
use phpseclib3\Crypt\TripleDES;
use phpseclib3\File\ASN1;
use phpseclib3\File\X509;
use RuntimeException;

/**
 * Builds SCEP response messages (CertRep) as PKCS#7 SignedData structures.
 *
 * Response structure:
 *   ContentInfo {
 *     SignedData {
 *       certificates: [CA cert],
 *       signerInfos: [{
 *         signedAttrs: [messageType=CertRep, pkiStatus, transactionID, recipientNonce, senderNonce, ...],
 *         signature: RSA signature by CA
 *       }],
 *       encapContentInfo: {
 *         EnvelopedData { ... encrypted certificate ... }  (only for SUCCESS)
 *       }
 *     }
 *   }
 */
final class ScepMessageBuilder
{
    /**
     * Build a SUCCESS CertRep response containing the issued certificate.
     *
     * The certificate is wrapped in EnvelopedData (encrypted to the client's
     * public key), then wrapped in SignedData (signed by the CA).
     */
    public function buildCertRep(
        ScepTransaction $transaction,
        string $issuedCertDer,
        string $caCertDer,
        RSAPrivateKey $caPrivateKey,
        string $clientCertDer,
        string $encryptionAlgorithm = 'aes-256-cbc',
        string $hashAlgorithm = 'sha256',
    ): string {
        // Step 1: Build degenerate PKCS#7 containing the issued certificate
        $degeneratePkcs7 = $this->buildDegenerateCertOnly($issuedCertDer);

        // Step 2: Encrypt the degenerate PKCS#7 in EnvelopedData to the client
        $envelopedData = $this->buildEnvelopedData(
            $degeneratePkcs7,
            $clientCertDer,
            $encryptionAlgorithm,
        );

        // Step 3: Wrap in SignedData with SCEP attributes
        return $this->buildSignedData(
            encapContent: $envelopedData,
            encapContentType: ScepPkiMessage::OID_ENVELOPED_DATA,
            caCertDer: $caCertDer,
            caPrivateKey: $caPrivateKey,
            transaction: $transaction,
            pkiStatus: ScepPkiStatus::fromSlug(ScepPkiStatus::SUCCESS),
            hashAlgorithm: $hashAlgorithm,
        );
    }

    /**
     * Build a PENDING CertRep response.
     */
    public function buildPending(
        ScepTransaction $transaction,
        string $caCertDer,
        RSAPrivateKey $caPrivateKey,
        string $hashAlgorithm = 'sha256',
    ): string {
        return $this->buildSignedData(
            encapContent: '',
            encapContentType: ScepPkiMessage::OID_DATA,
            caCertDer: $caCertDer,
            caPrivateKey: $caPrivateKey,
            transaction: $transaction,
            pkiStatus: ScepPkiStatus::fromSlug(ScepPkiStatus::PENDING),
            hashAlgorithm: $hashAlgorithm,
        );
    }

    /**
     * Build a FAILURE CertRep response.
     */
    public function buildFailure(
        ScepTransaction $transaction,
        ScepFailInfo $failInfo,
        string $caCertDer,
        RSAPrivateKey $caPrivateKey,
        string $hashAlgorithm = 'sha256',
    ): string {
        return $this->buildSignedData(
            encapContent: '',
            encapContentType: ScepPkiMessage::OID_DATA,
            caCertDer: $caCertDer,
            caPrivateKey: $caPrivateKey,
            transaction: $transaction,
            pkiStatus: ScepPkiStatus::fromSlug(ScepPkiStatus::FAILURE),
            failInfo: $failInfo,
            hashAlgorithm: $hashAlgorithm,
        );
    }

    /**
     * Build a degenerate PKCS#7 SignedData containing only certificates (no signers).
     * Used for GetCACert responses and for wrapping issued certificates.
     */
    public function buildDegenerateCertOnly(string ...$certsDer): string
    {
        $asn1 = new ASN1();

        // Build the certificates SET
        $certsContent = '';
        foreach ($certsDer as $certDer) {
            $certsContent .= $certDer;
        }

        // version INTEGER (1)
        $version = $asn1->encodeDER(
            ['content' => '1'],
            ['type' => ASN1::TYPE_INTEGER],
        );

        // digestAlgorithms SET OF (empty)
        $digestAlgorithms = $asn1->encodeDER(
            ['content' => []],
            ['type' => ASN1::TYPE_SET, 'children' => []],
        );

        // encapContentInfo SEQUENCE { contentType data }
        $encapContentType = $asn1->encodeDER(
            ['content' => ScepPkiMessage::OID_DATA],
            ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
        );
        $encapContentInfo = $this->buildSequence($encapContentType);

        // certificates [0] IMPLICIT
        $certificates = $this->buildContextTag(0, $certsContent, constructed: true);

        // signerInfos SET OF (empty)
        $signerInfos = $asn1->encodeDER(
            ['content' => []],
            ['type' => ASN1::TYPE_SET, 'children' => []],
        );

        // SignedData SEQUENCE
        $signedData = $this->buildSequence(
            $version . $digestAlgorithms . $encapContentInfo . $certificates . $signerInfos,
        );

        // Wrap in ContentInfo
        $contentTypeOid = $asn1->encodeDER(
            ['content' => ScepPkiMessage::OID_SIGNED_DATA],
            ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
        );
        $contentExplicit = $this->buildContextTag(0, $signedData, constructed: true);

        return $this->buildSequence($contentTypeOid . $contentExplicit);
    }

    /**
     * Build an EnvelopedData structure encrypting content to the recipient.
     */
    private function buildEnvelopedData(
        string $content,
        string $recipientCertDer,
        string $algorithm = 'aes-256-cbc',
    ): string {
        $asn1 = new ASN1();

        // Extract the recipient's public key from their certificate
        $x509 = new X509();
        $certData = $x509->loadX509($recipientCertDer);
        $publicKey = $x509->getPublicKey();
        if (!$publicKey instanceof RSAPublicKey) {
            throw new RuntimeException('Recipient certificate does not contain an RSA public key.');
        }

        // Generate symmetric key and IV
        [$keyLength, $ivLength, $algOid] = match ($algorithm) {
            'aes-256-cbc' => [32, 16, ScepPkiMessage::OID_AES_256_CBC],
            'aes-128-cbc' => [16, 16, ScepPkiMessage::OID_AES_128_CBC],
            '3des' => [24, 8, ScepPkiMessage::OID_DES_EDE3_CBC],
            default => [32, 16, ScepPkiMessage::OID_AES_256_CBC],
        };

        $symmetricKey = Random::string($keyLength);
        $iv = Random::string($ivLength);

        // Encrypt the content with the symmetric key
        $encryptedContent = $this->symmetricEncrypt($algorithm, $symmetricKey, $iv, $content);

        // Encrypt the symmetric key with the recipient's RSA public key
        $encryptedKey = $publicKey->withPadding(RSA::ENCRYPTION_PKCS1)->encrypt($symmetricKey);

        // Build RecipientInfo
        $recipientInfo = $this->buildKeyTransRecipientInfo(
            $recipientCertDer,
            $encryptedKey,
            $asn1,
        );

        // Build EncryptedContentInfo
        $encContentInfo = $this->buildEncryptedContentInfo(
            $algOid,
            $iv,
            $encryptedContent,
            $asn1,
        );

        // version INTEGER (0)
        $version = $asn1->encodeDER(
            ['content' => '0'],
            ['type' => ASN1::TYPE_INTEGER],
        );

        // recipientInfos SET OF
        $recipientInfosSet = $this->buildSet($recipientInfo);

        // EnvelopedData SEQUENCE
        return $this->buildSequence($version . $recipientInfosSet . $encContentInfo);
    }

    /**
     * Build the full SignedData response with SCEP attributes.
     */
    private function buildSignedData(
        string $encapContent,
        string $encapContentType,
        string $caCertDer,
        RSAPrivateKey $caPrivateKey,
        ScepTransaction $transaction,
        ScepPkiStatus $pkiStatus,
        string $hashAlgorithm = 'sha256',
        ?ScepFailInfo $failInfo = null,
    ): string {
        $asn1 = new ASN1();

        $hashOid = ScepPkiMessage::getHashAlgorithmOid($hashAlgorithm);
        $sigOid = ScepPkiMessage::getSignatureAlgorithmOid($hashAlgorithm);

        // version INTEGER (1)
        $version = $asn1->encodeDER(
            ['content' => '1'],
            ['type' => ASN1::TYPE_INTEGER],
        );

        // digestAlgorithms SET OF AlgorithmIdentifier
        $hashAlgId = $this->buildAlgorithmIdentifier($hashOid, $asn1);
        $digestAlgorithms = $this->buildSet($hashAlgId);

        // encapContentInfo
        $encapContentTypeOid = $asn1->encodeDER(
            ['content' => $encapContentType],
            ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
        );

        if ($encapContent !== '') {
            $eContentOctet = $asn1->encodeDER(
                ['content' => $encapContent],
                ['type' => ASN1::TYPE_OCTET_STRING],
            );
            $eContentExplicit = $this->buildContextTag(0, $eContentOctet, constructed: true);
            $encapContentInfo = $this->buildSequence($encapContentTypeOid . $eContentExplicit);
        } else {
            $encapContentInfo = $this->buildSequence($encapContentTypeOid);
        }

        // certificates [0] IMPLICIT (CA certificate)
        $certificates = $this->buildContextTag(0, $caCertDer, constructed: true);

        // Build signed attributes
        $signedAttrs = $this->buildScepSignedAttributes(
            $transaction,
            $pkiStatus,
            $hashAlgorithm,
            $encapContentType,
            $encapContent,
            $failInfo,
            $asn1,
        );

        // Sign the attributes
        $signedAttrsForSigning = $this->buildSet(implode('', $signedAttrs));
        $hashFn = match ($hashAlgorithm) {
            'sha512' => 'sha512',
            default => 'sha256',
        };
        $signature = $caPrivateKey
            ->withHash($hashFn)
            ->withPadding(RSA::SIGNATURE_PKCS1)
            ->sign($signedAttrsForSigning);

        // Build SignerInfo
        $signerInfo = $this->buildSignerInfo(
            $caCertDer,
            $hashOid,
            $sigOid,
            $signedAttrs,
            $signature,
            $asn1,
        );

        // signerInfos SET OF
        $signerInfosSet = $this->buildSet($signerInfo);

        // SignedData SEQUENCE
        $signedData = $this->buildSequence(
            $version . $digestAlgorithms . $encapContentInfo . $certificates . $signerInfosSet,
        );

        // ContentInfo
        $contentTypeOid = $asn1->encodeDER(
            ['content' => ScepPkiMessage::OID_SIGNED_DATA],
            ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
        );
        $contentExplicit = $this->buildContextTag(0, $signedData, constructed: true);

        return $this->buildSequence($contentTypeOid . $contentExplicit);
    }

    /**
     * Build SCEP-specific signed attributes.
     */
    private function buildScepSignedAttributes(
        ScepTransaction $transaction,
        ScepPkiStatus $pkiStatus,
        string $hashAlgorithm,
        string $contentType,
        string $content,
        ?ScepFailInfo $failInfo,
        ASN1 $asn1,
    ): array {
        $attrs = [];

        // contentType attribute
        $contentTypeValue = $asn1->encodeDER(
            ['content' => $contentType],
            ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
        );
        $attrs[] = $this->buildAttribute(ScepPkiMessage::OID_CONTENT_TYPE, $contentTypeValue, $asn1);

        // messageDigest attribute
        $hashFn = match ($hashAlgorithm) {
            'sha512' => 'sha512',
            default => 'sha256',
        };
        $digest = hash($hashFn, $content, binary: true);
        $digestValue = $asn1->encodeDER(
            ['content' => $digest],
            ['type' => ASN1::TYPE_OCTET_STRING],
        );
        $attrs[] = $this->buildAttribute(ScepPkiMessage::OID_MESSAGE_DIGEST, $digestValue, $asn1);

        // messageType attribute (CertRep = 3)
        $messageTypeValue = $asn1->encodeDER(
            ['content' => ScepMessageType::CERT_REP],
            ['type' => ASN1::TYPE_PRINTABLE_STRING],
        );
        $attrs[] = $this->buildAttribute(ScepPkiMessage::OID_MESSAGE_TYPE, $messageTypeValue, $asn1);

        // pkiStatus attribute
        $pkiStatusValue = $asn1->encodeDER(
            ['content' => (string) $pkiStatus->slug],
            ['type' => ASN1::TYPE_PRINTABLE_STRING],
        );
        $attrs[] = $this->buildAttribute(ScepPkiMessage::OID_PKI_STATUS, $pkiStatusValue, $asn1);

        // transactionID attribute
        $transactionIdValue = $asn1->encodeDER(
            ['content' => $transaction->transaction_id],
            ['type' => ASN1::TYPE_PRINTABLE_STRING],
        );
        $attrs[] = $this->buildAttribute(ScepPkiMessage::OID_TRANSACTION_ID, $transactionIdValue, $asn1);

        // recipientNonce attribute (set to sender's nonce)
        if ($transaction->sender_nonce !== null && $transaction->sender_nonce !== '') {
            $recipientNonceValue = $asn1->encodeDER(
                ['content' => $transaction->sender_nonce],
                ['type' => ASN1::TYPE_OCTET_STRING],
            );
            $attrs[] = $this->buildAttribute(ScepPkiMessage::OID_RECIPIENT_NONCE, $recipientNonceValue, $asn1);
        }

        // senderNonce attribute (CA generates its own nonce)
        $caNonce = $transaction->recipient_nonce ?? bin2hex(Random::string(16));
        $senderNonceValue = $asn1->encodeDER(
            ['content' => $caNonce],
            ['type' => ASN1::TYPE_OCTET_STRING],
        );
        $attrs[] = $this->buildAttribute(ScepPkiMessage::OID_SENDER_NONCE, $senderNonceValue, $asn1);

        // failInfo attribute (only for FAILURE)
        if ($pkiStatus->is(ScepPkiStatus::FAILURE) && $failInfo !== null) {
            $failInfoValue = $asn1->encodeDER(
                ['content' => (string) $failInfo->slug],
                ['type' => ASN1::TYPE_PRINTABLE_STRING],
            );
            $attrs[] = $this->buildAttribute(ScepPkiMessage::OID_FAIL_INFO, $failInfoValue, $asn1);
        }

        return $attrs;
    }

    /**
     * Build a single Attribute SEQUENCE { OID, SET { value } }.
     */
    private function buildAttribute(string $oid, string $valueDer, ASN1 $asn1): string
    {
        $oidDer = $asn1->encodeDER(
            ['content' => $oid],
            ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
        );
        $valueSet = $this->buildSet($valueDer);

        return $this->buildSequence($oidDer . $valueSet);
    }

    /**
     * Build a SignerInfo structure.
     */
    private function buildSignerInfo(
        string $signerCertDer,
        string $digestAlgOid,
        string $sigAlgOid,
        array $signedAttrs,
        string $signature,
        ASN1 $asn1,
    ): string {
        // version INTEGER (1)
        $version = $asn1->encodeDER(
            ['content' => '1'],
            ['type' => ASN1::TYPE_INTEGER],
        );

        // sid (IssuerAndSerialNumber)
        $sid = $this->extractIssuerAndSerialFromCert($signerCertDer);

        // digestAlgorithm
        $digestAlg = $this->buildAlgorithmIdentifier($digestAlgOid, $asn1);

        // signedAttrs [0] IMPLICIT
        $signedAttrsContent = implode('', $signedAttrs);
        $signedAttrsDer = $this->buildContextTag(0, $signedAttrsContent, constructed: true);

        // signatureAlgorithm
        $sigAlg = $this->buildAlgorithmIdentifier($sigAlgOid, $asn1);

        // signature OCTET STRING
        $signatureDer = $asn1->encodeDER(
            ['content' => $signature],
            ['type' => ASN1::TYPE_OCTET_STRING],
        );

        return $this->buildSequence(
            $version . $sid . $digestAlg . $signedAttrsDer . $sigAlg . $signatureDer,
        );
    }

    /**
     * Build a KeyTransRecipientInfo for EnvelopedData.
     */
    private function buildKeyTransRecipientInfo(
        string $recipientCertDer,
        string $encryptedKey,
        ASN1 $asn1,
    ): string {
        // version INTEGER (0)
        $version = $asn1->encodeDER(
            ['content' => '0'],
            ['type' => ASN1::TYPE_INTEGER],
        );

        // rid (IssuerAndSerialNumber)
        $rid = $this->extractIssuerAndSerialFromCert($recipientCertDer);

        // keyEncryptionAlgorithm (rsaEncryption)
        $keyEncAlg = $this->buildAlgorithmIdentifier(ScepPkiMessage::OID_RSA_ENCRYPTION, $asn1);

        // encryptedKey OCTET STRING
        $encKeyDer = $asn1->encodeDER(
            ['content' => $encryptedKey],
            ['type' => ASN1::TYPE_OCTET_STRING],
        );

        return $this->buildSequence($version . $rid . $keyEncAlg . $encKeyDer);
    }

    /**
     * Build an EncryptedContentInfo for EnvelopedData.
     */
    private function buildEncryptedContentInfo(
        string $algOid,
        string $iv,
        string $encryptedContent,
        ASN1 $asn1,
    ): string {
        // contentType (data)
        $contentType = $asn1->encodeDER(
            ['content' => ScepPkiMessage::OID_DATA],
            ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
        );

        // contentEncryptionAlgorithm { OID, IV }
        $algOidDer = $asn1->encodeDER(
            ['content' => $algOid],
            ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
        );
        $ivDer = $asn1->encodeDER(
            ['content' => $iv],
            ['type' => ASN1::TYPE_OCTET_STRING],
        );
        $contentEncAlg = $this->buildSequence($algOidDer . $ivDer);

        // encryptedContent [0] IMPLICIT OCTET STRING
        $encContent = $this->buildContextTag(0, $encryptedContent, constructed: false);

        return $this->buildSequence($contentType . $contentEncAlg . $encContent);
    }

    /**
     * Extract IssuerAndSerialNumber from a DER certificate.
     */
    private function extractIssuerAndSerialFromCert(string $certDer): string
    {
        $x509 = new X509();
        $cert = $x509->loadX509($certDer);

        // Get issuer DN as DER
        $asn1 = new ASN1();

        // Re-encode issuer from the parsed certificate
        $issuerDn = $cert['tbsCertificate']['issuer'];
        $issuerDer = '';
        if (isset($issuerDn['rdnSequence'])) {
            $issuerDer = $this->encodeRdnSequence($issuerDn['rdnSequence'], $asn1);
        }

        // Get serial number
        $serialNumber = $cert['tbsCertificate']['serialNumber']->toString();
        $serialDer = $asn1->encodeDER(
            ['content' => $serialNumber],
            ['type' => ASN1::TYPE_INTEGER],
        );

        return $this->buildSequence($issuerDer . $serialDer);
    }

    /**
     * Encode an RDN Sequence to DER.
     */
    private function encodeRdnSequence(array $rdnSequence, ASN1 $asn1): string
    {
        $rdnSets = '';
        foreach ($rdnSequence as $rdn) {
            $attrTypeAndValues = '';
            foreach ($rdn as $atv) {
                $oid = $atv['type'] ?? '';
                $value = $atv['value'] ?? [];

                $oidDer = $asn1->encodeDER(
                    ['content' => $oid],
                    ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
                );

                // Encode the value - it may be various string types
                $valueDer = '';
                if (is_array($value)) {
                    foreach ($value as $encoding => $val) {
                        $type = match ($encoding) {
                            'utf8String' => ASN1::TYPE_UTF8_STRING,
                            'printableString' => ASN1::TYPE_PRINTABLE_STRING,
                            'ia5String' => ASN1::TYPE_IA5_STRING,
                            default => ASN1::TYPE_UTF8_STRING,
                        };
                        $valueDer = $asn1->encodeDER(
                            ['content' => $val],
                            ['type' => $type],
                        );
                        break;
                    }
                } elseif (is_string($value)) {
                    $valueDer = $asn1->encodeDER(
                        ['content' => $value],
                        ['type' => ASN1::TYPE_UTF8_STRING],
                    );
                }

                $attrTypeAndValues .= $this->buildSequence($oidDer . $valueDer);
            }
            $rdnSets .= $this->buildSet($attrTypeAndValues);
        }

        return $this->buildSequence($rdnSets);
    }

    /**
     * Build an AlgorithmIdentifier SEQUENCE { OID, NULL }.
     */
    private function buildAlgorithmIdentifier(string $oid, ASN1 $asn1): string
    {
        $oidDer = $asn1->encodeDER(
            ['content' => $oid],
            ['type' => ASN1::TYPE_OBJECT_IDENTIFIER],
        );
        $nullDer = $asn1->encodeDER(
            [],
            ['type' => ASN1::TYPE_NULL],
        );

        return $this->buildSequence($oidDer . $nullDer);
    }

    /**
     * Perform symmetric encryption.
     */
    private function symmetricEncrypt(string $algorithm, string $key, string $iv, string $data): string
    {
        // Apply PKCS#7 padding
        $data = $this->pkcs7Pad($data, match ($algorithm) {
            'aes-256-cbc', 'aes-128-cbc' => 16,
            '3des' => 8,
            default => 16,
        });

        return match ($algorithm) {
            'aes-256-cbc' => $this->aesEncrypt(256, $key, $iv, $data),
            'aes-128-cbc' => $this->aesEncrypt(128, $key, $iv, $data),
            '3des' => $this->tripleDesEncrypt($key, $iv, $data),
            default => $this->aesEncrypt(256, $key, $iv, $data),
        };
    }

    private function aesEncrypt(int $keyLength, string $key, string $iv, string $data): string
    {
        $cipher = new AES('cbc');
        $cipher->setKeyLength($keyLength);
        $cipher->setKey($key);
        $cipher->setIV($iv);
        $cipher->disablePadding();

        return $cipher->encrypt($data);
    }

    private function tripleDesEncrypt(string $key, string $iv, string $data): string
    {
        $cipher = new TripleDES('cbc');
        $cipher->setKey($key);
        $cipher->setIV($iv);
        $cipher->disablePadding();

        return $cipher->encrypt($data);
    }

    /**
     * PKCS#7 padding.
     */
    private function pkcs7Pad(string $data, int $blockSize): string
    {
        $pad = $blockSize - (strlen($data) % $blockSize);

        return $data . str_repeat(chr($pad), $pad);
    }

    /**
     * Build an ASN.1 SEQUENCE (tag 0x30).
     */
    private function buildSequence(string $content): string
    {
        return "\x30" . $this->buildLength(strlen($content)) . $content;
    }

    /**
     * Build an ASN.1 SET (tag 0x31).
     */
    private function buildSet(string $content): string
    {
        return "\x31" . $this->buildLength(strlen($content)) . $content;
    }

    /**
     * Build an ASN.1 context-specific tag.
     */
    private function buildContextTag(int $tagNumber, string $content, bool $constructed = true): string
    {
        $tag = 0x80 | $tagNumber;
        if ($constructed) {
            $tag |= 0x20;
        }

        return chr($tag) . $this->buildLength(strlen($content)) . $content;
    }

    /**
     * Build ASN.1 length encoding (DER).
     */
    private function buildLength(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }

        $temp = '';
        $remaining = $length;
        while ($remaining > 0) {
            $temp = chr($remaining & 0xFF) . $temp;
            $remaining >>= 8;
        }

        return chr(0x80 | strlen($temp)) . $temp;
    }
}
