<?php

declare(strict_types=1);

namespace CA\Csr\Services;

use CA\Csr\Contracts\CsrManagerInterface;
use CA\Csr\Models\Csr;
use CA\DTOs\DistinguishedName;
use CA\Exceptions\CsrException;
use CA\Key\Models\Key;
use CA\Models\CertificateTemplate;

final class CsrBuilder
{
    private ?DistinguishedName $subject = null;

    private ?Key $key = null;

    private ?CertificateTemplate $template = null;

    /** @var array<int, array{type: string, value: string}> */
    private array $sanEntries = [];

    /** @var array<int, array{oid: string, value: mixed, critical: bool}> */
    private array $extensions = [];

    public function __construct(
        private readonly CsrManagerInterface $manager,
    ) {}

    public function subject(DistinguishedName $dn): self
    {
        $this->subject = $dn;

        return $this;
    }

    public function key(Key $key): self
    {
        $this->key = $key;

        return $this;
    }

    public function template(CertificateTemplate $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function addDnsName(string $name): self
    {
        $this->sanEntries[] = ['type' => 'dns', 'value' => $name];

        return $this;
    }

    public function addIpAddress(string $ip): self
    {
        $this->sanEntries[] = ['type' => 'ip', 'value' => $ip];

        return $this;
    }

    public function addEmail(string $email): self
    {
        $this->sanEntries[] = ['type' => 'email', 'value' => $email];

        return $this;
    }

    public function addUri(string $uri): self
    {
        $this->sanEntries[] = ['type' => 'uri', 'value' => $uri];

        return $this;
    }

    public function extension(string $oid, mixed $value, bool $critical = false): self
    {
        $this->extensions[] = [
            'oid' => $oid,
            'value' => $value,
            'critical' => $critical,
        ];

        return $this;
    }

    public function build(): Csr
    {
        if ($this->subject === null) {
            throw new CsrException('Subject DN is required to build a CSR.');
        }

        if ($this->key === null) {
            throw new CsrException('Key is required to build a CSR.');
        }

        $allExtensions = array_merge($this->sanEntries, $this->extensions);

        return $this->manager->create(
            dn: $this->subject,
            key: $this->key,
            extensions: $allExtensions,
            template: $this->template,
        );
    }
}
