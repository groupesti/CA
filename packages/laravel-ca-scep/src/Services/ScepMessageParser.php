<?php

declare(strict_types=1);

namespace CA\Scep\Services;

use CA\Key\Contracts\KeyManagerInterface;
use CA\Scep\Asn1\Maps\ScepPkiMessage;
use CA\Scep\Contracts\ScepMessageParserInterface;
use CA\Models\ScepMessageType;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\TripleDES;
use phpseclib3\Crypt\RSA\PrivateKey as RSAPrivateKey;
use phpseclib3\File\ASN1;
use phpseclib3\File\ASN1\Element;
use phpseclib3\File\X509;
use RuntimeException;

final class ScepMessageParser implements ScepMessageParserInterface
{
    public function __construct(
        private readonly KeyManagerInterface $keyManager,
    ) {}

    /**
     * Parse a DER-encoded PKCS#7 SignedData SCEP message.
     *
     * Structure: ContentInfo -> SignedData -> SignerInfo (with SCEP attributes)
     *                                     -> EncapsulatedContentInfo (EnvelopedData)
     */
    public function parse(string $derMessage): ScepMessage
    {
        $asn1 = new ASN1();

        // Decode the raw DER into ASN.1 tree
        $decoded = $asn1->decodeBER($derMessage);
        if ($decoded === false || empty($decoded)) {
            throw new RuntimeException('Failed to decode SCEP message: invalid DER encoding.');
        }

        $contentInfo = $decoded[0];

        // ContentInfo is SEQUENCE { contentType OID, content [0] EXPLICIT }
        if (!isset($contentInfo['content'])) {
            throw new RuntimeException('Invalid ContentInfo structure.');
        }

        $contentTypeOid = $this->extractOid($contentInfo['content'][0]);
        if ($contentTypeOid !== ScepPkiMessage::OID_SIGNED_DATA) {
            throw new RuntimeException('Expected SignedData content type, got: ' . $contentTypeOid);
        }

        // The SignedData is in the [0] EXPLICIT context tag
        $signedDataRaw = $contentInfo['content'][1]['content'][0] ?? null;
        if ($signedDataRaw === null) {
            throw new RuntimeException('Could not extract SignedData from ContentInfo.');
        }

        // Parse SignedData components
        $signedDataContent = $signedDataRaw['content'] ?? [];

        // version
        $versionIdx = 0;

        // digestAlgorithms (SET OF AlgorithmIdentifier)
        $digestAlgIdx = 1;

        // encapContentInfo (SEQUENCE { eContentType, eContent })
        $encapIdx = 2;
        $encapContent = $signedDataContent[$encapIdx] ?? null;

        // Extract the enveloped data (inner content of encapContentInfo)
        $envelopedDataDer = '';
        if ($encapContent !== null && isset($encapContent['content'])) {
            $encapChildren = $encapContent['content'];
            // eContentType OID
            // eContent is [0] EXPLICIT OCTET STRING
            if (isset($encapChildren[1])) {
                $eContent = $encapChildren[1];
                // It might be tagged [0], extract inner content
                if (isset($eContent['content'][0]['content'])) {
                    $envelopedDataDer = $eContent['content'][0]['content'];
                } elseif (isset($eContent['content'])) {
                    $envelopedDataDer = is_string($eContent['content']) ? $eContent['content'] : '';
                }
            }
        }

        // Extract certificates from [0] IMPLICIT
        $signerCertDer = '';
        $certsIdx = null;
        $signerInfosIdx = null;

        // Walk through remaining elements to find certificates and signerInfos
        for ($i = 3; $i < count($signedDataContent); $i++) {
            $element = $signedDataContent[$i];
            // Certificates are context [0] implicit
            if (isset($element['tag']) && $element['tag'] === 0 && isset($element['class']) && $element['class'] === ASN1::CLASS_CONTEXT_SPECIFIC) {
                $certsIdx = $i;
                // Extract the first certificate DER
                if (isset($element['content'][0])) {
                    $signerCertDer = $element['content'][0]['content_raw'] ?? ASN1::encodeDER(
                        $element['content'][0],
                        ['type' => ASN1::TYPE_SEQUENCE],
                    );
                }
            }
            // SignerInfos is a SET
            if (isset($element['type']) && $element['type'] === ASN1::TYPE_SET) {
                $signerInfosIdx = $i;
            }
        }

        // If we couldn't locate signerInfos by type, it's the last element
        if ($signerInfosIdx === null) {
            $signerInfosIdx = count($signedDataContent) - 1;
        }

        // Parse SignerInfo to extract authenticated attributes
        $signerInfoSet = $signedDataContent[$signerInfosIdx] ?? null;
        $messageType = null;
        $transactionId = '';
        $senderNonce = '';
        $digestAlgorithm = 'sha256';
        $signature = '';
        $rawSignedAttrs = [];

        if ($signerInfoSet !== null && isset($signerInfoSet['content'][0])) {
            $signerInfo = $signerInfoSet['content'][0];
            $siContent = $signerInfo['content'] ?? [];

            // Extract signed attributes and signature from SignerInfo
            foreach ($siContent as $siElement) {
                // Signed attributes are context [0] IMPLICIT SET-like
                if (isset($siElement['tag']) && $siElement['tag'] === 0 && isset($siElement['class']) && $siElement['class'] === ASN1::CLASS_CONTEXT_SPECIFIC) {
                    $rawSignedAttrs = $this->parseSignedAttributes($siElement);
                }
                // Extract signature (last OCTET STRING)
                if (isset($siElement['type']) && $siElement['type'] === ASN1::TYPE_OCTET_STRING && isset($siElement['content'])) {
                    $signature = $siElement['content'];
                }
            }
        }

        // Extract SCEP attributes from signed attributes
        $messageType = isset($rawSignedAttrs[ScepPkiMessage::OID_MESSAGE_TYPE])
            ? ScepMessageType::fromNumericValue((int) $rawSignedAttrs[ScepPkiMessage::OID_MESSAGE_TYPE])
            : throw new RuntimeException('SCEP messageType attribute not found.');

        $transactionId = $rawSignedAttrs[ScepPkiMessage::OID_TRANSACTION_ID]
            ?? throw new RuntimeException('SCEP transactionID attribute not found.');

        $senderNonce = $rawSignedAttrs[ScepPkiMessage::OID_SENDER_NONCE]
            ?? throw new RuntimeException('SCEP senderNonce attribute not found.');

        // Try to get signer certificate DER from the raw data if we don't have it yet
        if ($signerCertDer === '' || $signerCertDer === null) {
            // Attempt to extract from the raw certificates section
            $signerCertDer = $this->extractCertificateFromRawContent($derMessage);
        }

        return new ScepMessage(
            messageType: $messageType,
            transactionId: $transactionId,
            senderNonce: $senderNonce,
            signerCertificateDer: $signerCertDer,
            encryptedContent: $envelopedDataDer,
            digestAlgorithm: $digestAlgorithm,
            rawSignedAttributes: $rawSignedAttrs,
            signature: $signature,
        );
    }

    /**
     * Extract the PKCS#10 CSR from the decrypted SCEP message content.
     */
    public function extractCsr(ScepMessage $message): string
    {
        $content = $message->decryptedContent;
        if ($content === null || $content === '') {
            throw new RuntimeException('No decrypted content available. Decrypt the envelope first.');
        }

        // The decrypted content is a DER-encoded PKCS#10 CSR
        // Convert to PEM
        $pem = "-----BEGIN CERTIFICATE REQUEST-----\n"
            . chunk_split(base64_encode($content), 64, "\n")
            . "-----END CERTIFICATE REQUEST-----\n";

        return $pem;
    }

    /**
     * Extract the challenge password from a parsed SCEP message's CSR.
     */
    public function extractChallenge(ScepMessage $message): ?string
    {
        $content = $message->decryptedContent;
        if ($content === null || $content === '') {
            return null;
        }

        // Parse the PKCS#10 CSR to find the challengePassword attribute
        $asn1 = new ASN1();
        $decoded = $asn1->decodeBER($content);
        if ($decoded === false || empty($decoded)) {
            return null;
        }

        // OID for challengePassword: 1.2.840.113549.1.9.7
        $challengePasswordOid = '1.2.840.113549.1.9.7';

        return $this->findAttributeInCsr($decoded[0], $challengePasswordOid);
    }

    /**
     * Decrypt the EnvelopedData content using the CA's private key.
     */
    public function decryptEnvelopedData(ScepMessage $message, RSAPrivateKey $caPrivateKey): ScepMessage
    {
        $envelopedDer = $message->encryptedContent;
        if ($envelopedDer === '' || $envelopedDer === null) {
            throw new RuntimeException('No enveloped data content to decrypt.');
        }

        $asn1 = new ASN1();

        // The enveloped data might be wrapped in a ContentInfo or be raw EnvelopedData
        $decoded = $asn1->decodeBER($envelopedDer);
        if ($decoded === false || empty($decoded)) {
            throw new RuntimeException('Failed to decode EnvelopedData.');
        }

        $envData = $decoded[0];
        $envContent = $envData['content'] ?? [];

        // Check if this is a ContentInfo wrapper
        if (isset($envContent[0]['type']) && $envContent[0]['type'] === ASN1::TYPE_OBJECT_IDENTIFIER) {
            // ContentInfo { contentType, content [0] }
            $innerContent = $envContent[1]['content'][0] ?? null;
            if ($innerContent !== null) {
                $envContent = $innerContent['content'] ?? [];
            }
        }

        // EnvelopedData: version, recipientInfos (SET), encryptedContentInfo
        $recipientInfosSet = null;
        $encryptedContentInfo = null;
        $contentIdx = 0;

        foreach ($envContent as $idx => $element) {
            if (isset($element['type'])) {
                if ($element['type'] === ASN1::TYPE_INTEGER) {
                    // version
                    $contentIdx++;
                } elseif ($element['type'] === ASN1::TYPE_SET) {
                    $recipientInfosSet = $element;
                    $contentIdx++;
                } elseif ($element['type'] === ASN1::TYPE_SEQUENCE && $recipientInfosSet !== null) {
                    $encryptedContentInfo = $element;
                }
            }
        }

        if ($recipientInfosSet === null || $encryptedContentInfo === null) {
            throw new RuntimeException('Invalid EnvelopedData structure: missing required components.');
        }

        // Extract encrypted symmetric key from RecipientInfo
        $encryptedKey = $this->extractEncryptedKeyFromRecipientInfos($recipientInfosSet);

        // Decrypt the symmetric key using CA's RSA private key
        $decryptedKey = $caPrivateKey->withPadding(RSA::ENCRYPTION_PKCS1)->decrypt($encryptedKey);
        if ($decryptedKey === false) {
            throw new RuntimeException('Failed to decrypt symmetric key with CA private key.');
        }

        // Extract encryption algorithm and encrypted content
        $encContentChildren = $encryptedContentInfo['content'] ?? [];
        $encAlgOid = '';
        $encAlgIv = '';
        $encryptedData = '';

        foreach ($encContentChildren as $child) {
            if (isset($child['type']) && $child['type'] === ASN1::TYPE_OBJECT_IDENTIFIER) {
                // contentType OID (data)
                continue;
            }
            if (isset($child['type']) && $child['type'] === ASN1::TYPE_SEQUENCE) {
                // contentEncryptionAlgorithm
                $algChildren = $child['content'] ?? [];
                foreach ($algChildren as $algChild) {
                    if (isset($algChild['type']) && $algChild['type'] === ASN1::TYPE_OBJECT_IDENTIFIER) {
                        $encAlgOid = $algChild['content'];
                    } elseif (isset($algChild['type']) && $algChild['type'] === ASN1::TYPE_OCTET_STRING) {
                        $encAlgIv = $algChild['content'];
                    }
                }
            }
            // Encrypted content is [0] IMPLICIT OCTET STRING
            if (isset($child['tag']) && $child['tag'] === 0 && isset($child['class']) && $child['class'] === ASN1::CLASS_CONTEXT_SPECIFIC) {
                $encryptedData = $child['content'] ?? '';
                if (is_array($encryptedData) && isset($encryptedData[0])) {
                    $encryptedData = $encryptedData[0]['content'] ?? '';
                }
            }
        }

        // Decrypt the content using the symmetric key
        $decryptedContent = $this->symmetricDecrypt($encAlgOid, $decryptedKey, $encAlgIv, $encryptedData);

        return $message->withDecryptedContent($decryptedContent);
    }

    /**
     * Parse the signed attributes from a SignerInfo structure.
     */
    private function parseSignedAttributes(array $attrsElement): array
    {
        $attributes = [];
        $children = $attrsElement['content'] ?? [];

        foreach ($children as $attr) {
            if (!isset($attr['content']) || !is_array($attr['content'])) {
                continue;
            }

            $attrContent = $attr['content'];
            $oid = null;
            $value = null;

            foreach ($attrContent as $part) {
                if (isset($part['type']) && $part['type'] === ASN1::TYPE_OBJECT_IDENTIFIER) {
                    $oid = $part['content'];
                } elseif (isset($part['type']) && $part['type'] === ASN1::TYPE_SET) {
                    // The value is inside the SET
                    $setParts = $part['content'] ?? [];
                    if (!empty($setParts)) {
                        $valuePart = $setParts[0];
                        $value = $valuePart['content'] ?? null;
                    }
                }
            }

            if ($oid !== null && $value !== null) {
                $attributes[$oid] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Extract the encrypted symmetric key from RecipientInfos.
     */
    private function extractEncryptedKeyFromRecipientInfos(array $recipientInfosSet): string
    {
        $recipientInfos = $recipientInfosSet['content'] ?? [];
        foreach ($recipientInfos as $ri) {
            $riContent = $ri['content'] ?? [];
            // KeyTransRecipientInfo: version, rid, keyEncryptionAlgorithm, encryptedKey
            foreach ($riContent as $element) {
                if (isset($element['type']) && $element['type'] === ASN1::TYPE_OCTET_STRING) {
                    return $element['content'];
                }
            }
        }

        throw new RuntimeException('Could not extract encrypted key from RecipientInfos.');
    }

    /**
     * Decrypt content using symmetric algorithm.
     */
    private function symmetricDecrypt(string $algOid, string $key, string $iv, string $encryptedData): string
    {
        $cipher = match ($algOid) {
            ScepPkiMessage::OID_AES_256_CBC => $this->createAesCipher(256, $key, $iv),
            ScepPkiMessage::OID_AES_128_CBC => $this->createAesCipher(128, $key, $iv),
            ScepPkiMessage::OID_DES_EDE3_CBC => $this->createTripleDesCipher($key, $iv),
            default => throw new RuntimeException('Unsupported content encryption algorithm: ' . $algOid),
        };

        $decrypted = $cipher->decrypt($encryptedData);
        if ($decrypted === false) {
            throw new RuntimeException('Symmetric decryption failed.');
        }

        return $decrypted;
    }

    /**
     * Create an AES cipher instance.
     */
    private function createAesCipher(int $keyLength, string $key, string $iv): AES
    {
        $cipher = new AES('cbc');
        $cipher->setKeyLength($keyLength);
        $cipher->setKey($key);
        $cipher->setIV($iv);
        $cipher->disablePadding();

        return $cipher;
    }

    /**
     * Create a Triple DES cipher instance.
     */
    private function createTripleDesCipher(string $key, string $iv): TripleDES
    {
        $cipher = new TripleDES('cbc');
        $cipher->setKey($key);
        $cipher->setIV($iv);
        $cipher->disablePadding();

        return $cipher;
    }

    /**
     * Search for a specific attribute OID value within a CSR's ASN.1 structure.
     */
    private function findAttributeInCsr(array $csrElement, string $targetOid): ?string
    {
        // Walk through the CSR structure to find the attribute
        if (isset($csrElement['content']) && is_array($csrElement['content'])) {
            foreach ($csrElement['content'] as $child) {
                // Check if this is an attribute with matching OID
                if (isset($child['type']) && $child['type'] === ASN1::TYPE_OBJECT_IDENTIFIER) {
                    if ($child['content'] === $targetOid) {
                        return null; // OID found but need value from sibling
                    }
                }

                // Check SEQUENCE that might be { OID, SET { value } }
                if (isset($child['content']) && is_array($child['content'])) {
                    $foundOid = false;
                    $foundValue = null;

                    foreach ($child['content'] as $part) {
                        if (isset($part['type']) && $part['type'] === ASN1::TYPE_OBJECT_IDENTIFIER && $part['content'] === $targetOid) {
                            $foundOid = true;
                        }
                        if ($foundOid && isset($part['type']) && $part['type'] === ASN1::TYPE_SET) {
                            $setParts = $part['content'] ?? [];
                            if (!empty($setParts)) {
                                return $setParts[0]['content'] ?? null;
                            }
                        }
                    }

                    // Recurse
                    $result = $this->findAttributeInCsr($child, $targetOid);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract the first certificate from raw PKCS#7 DER content.
     */
    private function extractCertificateFromRawContent(string $derMessage): string
    {
        // Look for certificate structure within the SignedData certificates field
        // This is a fallback that scans the raw DER for a certificate SEQUENCE
        $asn1 = new ASN1();
        $decoded = $asn1->decodeBER($derMessage);

        if ($decoded === false || empty($decoded)) {
            return '';
        }

        return $this->findCertificateInTree($decoded[0]);
    }

    /**
     * Recursively search for a certificate in an ASN.1 tree.
     */
    private function findCertificateInTree(array $element): string
    {
        // A certificate is identified by its structure: SEQUENCE containing a
        // SEQUENCE (tbsCertificate) starting with a [0] EXPLICIT version tag
        if (isset($element['content']) && is_array($element['content'])) {
            foreach ($element['content'] as $child) {
                if (isset($child['tag']) && $child['tag'] === 0 && isset($child['class']) && $child['class'] === ASN1::CLASS_CONTEXT_SPECIFIC) {
                    // This could be the certificates [0] field in SignedData
                    if (isset($child['content'][0])) {
                        $certCandidate = $child['content'][0];
                        if (isset($certCandidate['type']) && $certCandidate['type'] === ASN1::TYPE_SEQUENCE) {
                            // Re-encode this as DER
                            return ASN1::encodeDER($certCandidate, ['type' => ASN1::TYPE_SEQUENCE]);
                        }
                        if (isset($certCandidate['content_raw'])) {
                            return $certCandidate['content_raw'];
                        }
                    }
                }

                $result = $this->findCertificateInTree($child);
                if ($result !== '') {
                    return $result;
                }
            }
        }

        return '';
    }

    /**
     * Extract an OID value from an ASN.1 element.
     */
    private function extractOid(array $element): string
    {
        if (isset($element['type']) && $element['type'] === ASN1::TYPE_OBJECT_IDENTIFIER) {
            return $element['content'];
        }

        if (isset($element['content']) && is_string($element['content'])) {
            return $element['content'];
        }

        return '';
    }
}
