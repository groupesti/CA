<?php

declare(strict_types=1);

namespace CA\Services;

use CA\Contracts\EncryptionStrategyInterface;
use CA\DTOs\DistinguishedName;
use CA\Models\CertificateStatus;
use CA\Models\CertificateType;
use CA\Models\KeyAlgorithm;
use CA\Events\CaCreated;
use CA\Events\CaSuspended;
use CA\Exceptions\CaException;
use CA\Models\CertificateAuthority;
use CA\Storage\StorageManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class CaManager
{
    private ?string $tenantId = null;

    public function __construct(
        private readonly StorageManager $storageManager,
        private readonly SerialNumberGenerator $serialNumberGenerator,
        private readonly EncryptionStrategyInterface $encryptionStrategy,
    ) {}

    public function createRootCA(
        DistinguishedName $dn,
        KeyAlgorithm $algorithm,
        array $keyParams = [],
        int $validityDays = 3650,
        ?string $tenantId = null,
        ?int $pathLength = null,
    ): CertificateAuthority {
        $effectiveTenantId = $tenantId ?? $this->tenantId;

        $serialNumber = $this->serialNumberGenerator->generate(
            (int) config('ca.serial_number.bytes', 20),
        );

        $ca = new CertificateAuthority();
        $ca->id = (string) Str::uuid();
        $ca->tenant_id = $effectiveTenantId;
        $ca->parent_id = null;
        $ca->type = CertificateType::ROOT_CA;
        $ca->status = CertificateStatus::ACTIVE;
        $ca->subject_dn = $dn->toArray();
        $ca->serial_number = $serialNumber;
        $ca->key_algorithm = $algorithm->slug;
        $ca->hash_algorithm = config('ca.defaults.hash_algorithm', 'sha256');
        $ca->path_length = $pathLength;
        $ca->not_before = now();
        $ca->not_after = now()->addDays($validityDays);
        $ca->metadata = array_filter([
            'key_params' => $keyParams,
            'validity_days' => $validityDays,
        ]);

        $ca->save();

        event(new CaCreated($ca));

        return $ca;
    }

    public function createIntermediateCA(
        CertificateAuthority $parent,
        DistinguishedName $dn,
        KeyAlgorithm $algorithm,
        array $keyParams = [],
        int $validityDays = 1825,
        ?int $pathLength = null,
    ): CertificateAuthority {
        if (!$parent->status->isValid()) {
            throw new CaException("Parent CA [{$parent->id}] is not in a valid state.");
        }

        if ($parent->path_length !== null && $parent->path_length <= 0) {
            throw new CaException("Parent CA [{$parent->id}] does not allow further subordinate CAs.");
        }

        $parentNotAfter = $parent->not_after;
        $requestedNotAfter = now()->addDays($validityDays);

        if ($requestedNotAfter->greaterThan($parentNotAfter)) {
            throw new CaException(
                'Intermediate CA validity cannot exceed parent CA validity.',
            );
        }

        $serialNumber = $this->serialNumberGenerator->generate(
            (int) config('ca.serial_number.bytes', 20),
        );

        $effectivePathLength = null;
        if ($pathLength !== null) {
            $effectivePathLength = $pathLength;
        } elseif ($parent->path_length !== null) {
            $effectivePathLength = $parent->path_length - 1;
        }

        $ca = new CertificateAuthority();
        $ca->id = (string) Str::uuid();
        $ca->tenant_id = $parent->tenant_id;
        $ca->parent_id = $parent->id;
        $ca->type = CertificateType::INTERMEDIATE_CA;
        $ca->status = CertificateStatus::ACTIVE;
        $ca->subject_dn = $dn->toArray();
        $ca->serial_number = $serialNumber;
        $ca->key_algorithm = $algorithm->slug;
        $ca->hash_algorithm = config('ca.defaults.hash_algorithm', 'sha256');
        $ca->path_length = $effectivePathLength;
        $ca->not_before = now();
        $ca->not_after = now()->addDays($validityDays);
        $ca->metadata = array_filter([
            'key_params' => $keyParams,
            'validity_days' => $validityDays,
        ]);

        $ca->save();

        event(new CaCreated($ca));

        return $ca;
    }

    public function getHierarchy(CertificateAuthority $ca): array
    {
        $root = $ca;

        while ($root->parent_id !== null) {
            $parent = $root->parent;
            if ($parent === null) {
                break;
            }
            $root = $parent;
        }

        return $this->buildHierarchyTree($root);
    }

    public function suspend(CertificateAuthority $ca): void
    {
        if ($ca->status === CertificateStatus::REVOKED) {
            throw new CaException("Cannot suspend a revoked CA [{$ca->id}].");
        }

        $ca->status = CertificateStatus::SUSPENDED;
        $ca->save();

        $ca->children()->each(function (CertificateAuthority $child): void {
            if ($child->status === CertificateStatus::ACTIVE) {
                $this->suspend($child);
            }
        });

        event(new CaSuspended($ca));
    }

    public function activate(CertificateAuthority $ca): void
    {
        if ($ca->status === CertificateStatus::REVOKED) {
            throw new CaException("Cannot activate a revoked CA [{$ca->id}].");
        }

        if ($ca->status === CertificateStatus::EXPIRED) {
            throw new CaException("Cannot activate an expired CA [{$ca->id}].");
        }

        if ($ca->parent_id !== null) {
            $parent = $ca->parent;
            if ($parent !== null && !$parent->status->isValid()) {
                throw new CaException(
                    "Cannot activate CA [{$ca->id}] because parent CA [{$parent->id}] is not active.",
                );
            }
        }

        $ca->status = CertificateStatus::ACTIVE;
        $ca->save();
    }

    public function forTenant(string $tenantId): self
    {
        $clone = clone $this;
        $clone->tenantId = $tenantId;

        return $clone;
    }

    public function find(string $uuid): ?CertificateAuthority
    {
        $query = CertificateAuthority::query();

        if ($this->tenantId !== null) {
            $query->where('tenant_id', $this->tenantId);
        }

        return $query->find($uuid);
    }

    public function all(): Collection
    {
        $query = CertificateAuthority::query();

        if ($this->tenantId !== null) {
            $query->where('tenant_id', $this->tenantId);
        }

        return $query->orderBy('created_at')->get();
    }

    private function buildHierarchyTree(CertificateAuthority $ca): array
    {
        $children = $ca->children->map(
            fn(CertificateAuthority $child): array => $this->buildHierarchyTree($child),
        )->toArray();

        return [
            'id' => $ca->id,
            'subject_dn' => $ca->subject_dn,
            'type' => $ca->type->slug,
            'status' => $ca->status->slug,
            'serial_number' => $ca->serial_number,
            'not_before' => $ca->not_before?->toIso8601String(),
            'not_after' => $ca->not_after?->toIso8601String(),
            'children' => $children,
        ];
    }
}
