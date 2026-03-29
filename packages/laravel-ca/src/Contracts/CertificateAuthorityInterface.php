<?php

declare(strict_types=1);

namespace CA\Contracts;

use Illuminate\Support\Collection;

interface CertificateAuthorityInterface
{
    public function getId(): string;

    public function getTenantId(): ?string;

    public function getSubjectDN(): array;

    public function isRoot(): bool;

    public function getParent(): ?self;

    public function getChildren(): Collection;

    public function getStatus(): string;

    public function getChainDepth(): int;
}
