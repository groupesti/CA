<?php

declare(strict_types=1);

namespace CA\Acme\Services;

use InvalidArgumentException;
use phpseclib3\Crypt\EC;
use phpseclib3\Crypt\EC\PublicKey as ECPublicKey;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PublicKey as RSAPublicKey;
use phpseclib3\Math\BigInteger;
use RuntimeException;

class JwsVerifier
{
    /**
     * Verify a JWS (JSON Web Signature) from an ACME client.
     *
     * @param  array<string, mixed>|null  $jwk  The JWK to verify against (if null, extracted from protected header)
     */
    public function verify(string $protectedHeader, string $payload, string $signature, ?array $jwk = null): bool
    {
        $headerData = json_decode(self::base64UrlDecode($protectedHeader), true);

        if (!is_array($headerData)) {
            return false;
        }

        $alg = $headerData['alg'] ?? null;

        if ($jwk === null) {
            $jwk = $headerData['jwk'] ?? null;
        }

        if ($jwk === null || $alg === null) {
            return false;
        }

        $signingInput = $protectedHeader . '.' . $payload;
        $signatureBytes = self::base64UrlDecode($signature);

        return match ($alg) {
            'RS256' => $this->verifyRsa($jwk, $signingInput, $signatureBytes, 'sha256'),
            'ES256' => $this->verifyEc($jwk, $signingInput, $signatureBytes, 'sha256', 32),
            'ES384' => $this->verifyEc($jwk, $signingInput, $signatureBytes, 'sha384', 48),
            'ES512' => $this->verifyEc($jwk, $signingInput, $signatureBytes, 'sha512', 66),
            default => false,
        };
    }

    /**
     * Extract JWK from the protected header.
     *
     * @return array<string, mixed>
     */
    public function extractJwk(string $protectedHeader): array
    {
        $headerData = json_decode(self::base64UrlDecode($protectedHeader), true);

        if (!is_array($headerData) || !isset($headerData['jwk'])) {
            throw new InvalidArgumentException('Protected header does not contain a JWK.');
        }

        return $headerData['jwk'];
    }

    /**
     * Compute JWK Thumbprint per RFC 7638.
     *
     * @param  array<string, mixed>  $jwk
     */
    public function computeThumbprint(array $jwk): string
    {
        $kty = $jwk['kty'] ?? '';

        $canonicalJwk = match ($kty) {
            'RSA' => json_encode([
                'e' => $jwk['e'],
                'kty' => $jwk['kty'],
                'n' => $jwk['n'],
            ], JSON_UNESCAPED_SLASHES),
            'EC' => json_encode([
                'crv' => $jwk['crv'],
                'kty' => $jwk['kty'],
                'x' => $jwk['x'],
                'y' => $jwk['y'],
            ], JSON_UNESCAPED_SLASHES),
            default => throw new InvalidArgumentException("Unsupported key type: {$kty}"),
        };

        return self::base64UrlEncode(hash('sha256', $canonicalJwk, true));
    }

    /**
     * Verify an RSA signature using phpseclib3.
     *
     * @param  array<string, mixed>  $jwk
     */
    private function verifyRsa(array $jwk, string $message, string $signature, string $hash): bool
    {
        try {
            $n = new BigInteger(self::base64UrlDecode($jwk['n']), 256);
            $e = new BigInteger(self::base64UrlDecode($jwk['e']), 256);

            /** @var RSAPublicKey $publicKey */
            $publicKey = RSA::loadPublicKey([
                'n' => $n,
                'e' => $e,
            ])
                ->withHash($hash)
                ->withPadding(RSA::SIGNATURE_PKCS1);

            return $publicKey->verify($message, $signature);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Verify an EC signature using phpseclib3.
     *
     * @param  array<string, mixed>  $jwk
     */
    private function verifyEc(array $jwk, string $message, string $signature, string $hash, int $componentLength): bool
    {
        try {
            $crv = $jwk['crv'] ?? 'P-256';
            $curve = match ($crv) {
                'P-256' => 'secp256r1',
                'P-384' => 'secp384r1',
                'P-521' => 'secp521r1',
                default => throw new InvalidArgumentException("Unsupported curve: {$crv}"),
            };

            // Build the public key from JWK components using loadPublicKey
            $publicKey = EC::loadFormat('JWK', json_encode([
                'kty' => 'EC',
                'crv' => $crv,
                'x' => $jwk['x'],
                'y' => $jwk['y'],
            ]));

            $publicKey = $publicKey->withHash($hash);

            // Convert fixed-length r||s signature to ASN.1 DER format for phpseclib
            if (strlen($signature) === $componentLength * 2) {
                $r = new BigInteger(substr($signature, 0, $componentLength), 256);
                $s = new BigInteger(substr($signature, $componentLength), 256);

                $derSignature = self::encodeEcDerSignature($r, $s);

                return $publicKey->verify($message, $derSignature);
            }

            return $publicKey->verify($message, $signature);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Encode EC signature components as DER.
     */
    private static function encodeEcDerSignature(BigInteger $r, BigInteger $s): string
    {
        $rBytes = self::encodeAsn1Integer($r);
        $sBytes = self::encodeAsn1Integer($s);

        $content = $rBytes . $sBytes;

        return "\x30" . self::encodeAsn1Length(strlen($content)) . $content;
    }

    /**
     * Encode a BigInteger as an ASN.1 INTEGER.
     */
    private static function encodeAsn1Integer(BigInteger $value): string
    {
        $bytes = $value->toBytes();

        // Ensure positive encoding
        if (ord($bytes[0]) & 0x80) {
            $bytes = "\x00" . $bytes;
        }

        return "\x02" . self::encodeAsn1Length(strlen($bytes)) . $bytes;
    }

    /**
     * Encode ASN.1 length.
     */
    private static function encodeAsn1Length(int $length): string
    {
        if ($length < 0x80) {
            return chr($length);
        }

        $temp = ltrim(pack('N', $length), "\x00");

        return chr(0x80 | strlen($temp)) . $temp;
    }

    /**
     * Base64url decode.
     */
    public static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        if ($decoded === false) {
            throw new RuntimeException('Invalid base64url data.');
        }

        return $decoded;
    }

    /**
     * Base64url encode.
     */
    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
