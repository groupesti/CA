<?php

declare(strict_types=1);

namespace CA\Crt\Services;

use CA\Crt\Contracts\CertificateSignerInterface;
use CA\DTOs\CertificateOptions;
use CA\Models\HashAlgorithm;
use CA\Exceptions\CertificateException;
use CA\Services\SerialNumberGenerator;
use phpseclib3\Crypt\Common\PrivateKey;
use phpseclib3\File\X509;

final class CertificateSigner implements CertificateSignerInterface
{
    public function __construct(
        private readonly SerialNumberGenerator $serialGenerator,
    ) {}

    public function sign(
        X509 $issuerX509,
        X509 $subjectX509,
        PrivateKey $issuerKey,
        CertificateOptions $options,
    ): string {
        $x509 = new X509();

        $x509->setSerialNumber($this->serialGenerator->generate(), 16);

        $notBefore = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $notAfter = $notBefore->modify('+' . $options->validityDays . ' days');

        $x509->setStartDate($notBefore->format('Y-m-d H:i:s'));
        $x509->setEndDate($notAfter->format('Y-m-d H:i:s'));

        $this->applyExtensions($x509, $options);

        $hashAlgorithm = $options->hashAlgorithm ?? HashAlgorithm::from(
            (string) config('ca-crt.default_hash', 'sha256'),
        );
        $x509->setHash($hashAlgorithm->slug);

        $signed = $x509->sign($issuerX509, $subjectX509, $issuerKey);

        if ($signed === false) {
            throw new CertificateException('Failed to sign certificate.');
        }

        $pem = $x509->saveX509($signed);

        if ($pem === false) {
            throw new CertificateException('Failed to encode signed certificate to PEM.');
        }

        return $pem;
    }

    private function applyExtensions(X509 $x509, CertificateOptions $options): void
    {
        // Basic Constraints
        if ($options->isCa !== null) {
            $bcValue = ['cA' => $options->isCa];
            if ($options->pathLength !== null) {
                $bcValue['pathLenConstraint'] = $options->pathLength;
            }
            $x509->setExtension('id-ce-basicConstraints', $bcValue, true);
        }

        // Key Usage
        if ($options->keyUsage !== []) {
            $keyUsageMap = [];
            foreach ($options->keyUsage as $usage) {
                $keyUsageMap[$usage] = true;
            }
            $x509->setExtension('id-ce-keyUsage', $keyUsageMap, true);
        }

        // Extended Key Usage
        if ($options->extendedKeyUsage !== []) {
            $ekuOids = array_map(
                fn(string $eku): string => $this->resolveExtendedKeyUsageOid($eku),
                $options->extendedKeyUsage,
            );
            $x509->setExtension('id-ce-extKeyUsage', $ekuOids);
        }

        // Subject Alternative Names
        if ($options->subjectAlternativeNames !== null && $options->subjectAlternativeNames !== []) {
            $sanValues = [];
            foreach ($options->subjectAlternativeNames as $san) {
                if (isset($san['dNSName'])) {
                    $sanValues[] = ['dNSName' => $san['dNSName']];
                } elseif (isset($san['iPAddress'])) {
                    $sanValues[] = ['iPAddress' => $san['iPAddress']];
                } elseif (isset($san['rfc822Name'])) {
                    $sanValues[] = ['rfc822Name' => $san['rfc822Name']];
                } elseif (isset($san['uniformResourceIdentifier'])) {
                    $sanValues[] = ['uniformResourceIdentifier' => $san['uniformResourceIdentifier']];
                } elseif (is_string($san)) {
                    // Treat plain strings as DNS names
                    $sanValues[] = ['dNSName' => $san];
                }
            }
            if ($sanValues !== []) {
                $x509->setExtension('id-ce-subjectAltName', $sanValues);
            }
        }

        // Custom extensions (including Microsoft-specific)
        foreach ($options->customExtensions as $ext) {
            $oid = $ext['oid'] ?? null;
            $value = $ext['value'] ?? null;
            $critical = $ext['critical'] ?? false;

            if ($oid !== null && $value !== null) {
                $x509->setExtension($oid, $value, $critical);
            }
        }
    }

    private function resolveExtendedKeyUsageOid(string $eku): string
    {
        return match ($eku) {
            'serverAuth' => 'id-kp-serverAuth',
            'clientAuth' => 'id-kp-clientAuth',
            'codeSigning' => 'id-kp-codeSigning',
            'emailProtection' => 'id-kp-emailProtection',
            'timeStamping' => 'id-kp-timeStamping',
            'OCSPSigning' => 'id-kp-OCSPSigning',
            'smartcardLogon' => '1.3.6.1.4.1.311.20.2.2',
            'EFS' => '1.3.6.1.4.1.311.10.3.4',
            default => $eku, // Pass through raw OIDs
        };
    }
}
