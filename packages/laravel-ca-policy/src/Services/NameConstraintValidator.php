<?php

declare(strict_types=1);

namespace CA\Policy\Services;

use CA\DTOs\DistinguishedName;
use CA\Policy\Contracts\NameConstraintInterface;
use CA\Models\NameType;

class NameConstraintValidator implements NameConstraintInterface
{
    /** @var array<string, array<int, string>> */
    private array $permitted = [];

    /** @var array<string, array<int, string>> */
    private array $excluded = [];

    public function isPermitted(string $name, string $type): bool
    {
        // If there are excluded subtrees and the name matches, deny it.
        if (isset($this->excluded[$type])) {
            foreach ($this->excluded[$type] as $subtree) {
                if ($this->matchesSubtree($name, $subtree, $type)) {
                    return false;
                }
            }
        }

        // If there are no permitted subtrees for this type, allow by default.
        if (!isset($this->permitted[$type]) || count($this->permitted[$type]) === 0) {
            return true;
        }

        // Must match at least one permitted subtree.
        foreach ($this->permitted[$type] as $subtree) {
            if ($this->matchesSubtree($name, $subtree, $type)) {
                return true;
            }
        }

        return false;
    }

    public function addPermitted(string $subtree, string $type): void
    {
        $this->permitted[$type][] = $subtree;
    }

    public function addExcluded(string $subtree, string $type): void
    {
        $this->excluded[$type][] = $subtree;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(DistinguishedName $subject, ?array $sans = null): array
    {
        $violations = [];

        // Validate email from DN.
        if ($subject->emailAddress !== null) {
            if (!$this->isPermitted($subject->emailAddress, NameType::EMAIL)) {
                $violations[] = "Email address '{$subject->emailAddress}' violates name constraints.";
            }
        }

        // Validate CN as DNS if it looks like a domain.
        if ($subject->commonName !== null && $this->looksLikeDomain($subject->commonName)) {
            if (!$this->isPermitted($subject->commonName, NameType::DNS)) {
                $violations[] = "Common name '{$subject->commonName}' violates DNS name constraints.";
            }
        }

        // Validate SANs.
        if ($sans !== null) {
            foreach ($sans as $san) {
                $sanType = $san['type'] ?? '';
                $sanValue = $san['value'] ?? '';

                if ($sanType === '' || $sanValue === '') {
                    continue;
                }

                if (!$this->isPermitted($sanValue, $sanType)) {
                    $violations[] = "SAN {$sanType}:{$sanValue} violates name constraints.";
                }
            }
        }

        return $violations;
    }

    /**
     * Load constraints from an array of NameConstraint models.
     *
     * @param iterable<\CA\Policy\Models\NameConstraint> $constraints
     */
    public function loadFromModels(iterable $constraints): void
    {
        foreach ($constraints as $constraint) {
            if (!$constraint->enabled) {
                continue;
            }

            if ($constraint->type === 'permitted') {
                $this->addPermitted($constraint->value, $constraint->name_type);
            } else {
                $this->addExcluded($constraint->value, $constraint->name_type);
            }
        }
    }

    private function matchesSubtree(string $name, string $subtree, string $type): bool
    {
        return match ($type) {
            NameType::DNS => $this->matchesDns($name, $subtree),
            NameType::EMAIL => $this->matchesEmail($name, $subtree),
            NameType::IP => $this->matchesIp($name, $subtree),
            NameType::URI => $this->matchesUri($name, $subtree),
            NameType::DIRECTORY_NAME => $this->matchesDirectoryName($name, $subtree),
            default => false,
        };
    }

    /**
     * DNS suffix matching: ".example.com" matches "host.example.com" and "sub.host.example.com".
     * Exact "example.com" matches only "example.com".
     */
    private function matchesDns(string $name, string $subtree): bool
    {
        $name = strtolower($name);
        $subtree = strtolower($subtree);

        if (str_starts_with($subtree, '.')) {
            // Suffix match: name must end with the subtree or equal the subtree without leading dot.
            return str_ends_with($name, $subtree)
                || $name === substr($subtree, 1);
        }

        return $name === $subtree;
    }

    /**
     * Email matching: domain-only subtree (e.g., "example.com") matches any address at that domain.
     * Full address match (e.g., "user@example.com") matches exactly.
     */
    private function matchesEmail(string $name, string $subtree): bool
    {
        $name = strtolower($name);
        $subtree = strtolower($subtree);

        if (!str_contains($subtree, '@')) {
            // Domain-only constraint: match the domain part.
            $parts = explode('@', $name);
            $domain = $parts[1] ?? '';

            return $domain === $subtree;
        }

        return $name === $subtree;
    }

    /**
     * IP CIDR matching: "10.0.0.0/8" matches any IP in that range.
     */
    private function matchesIp(string $name, string $subtree): bool
    {
        if (!str_contains($subtree, '/')) {
            return $name === $subtree;
        }

        [$subnet, $bits] = explode('/', $subtree, 2);
        $bits = (int) $bits;

        $ipLong = ip2long($name);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            // Try IPv6 comparison as string prefix.
            return str_starts_with(
                inet_pton($name) ?: '',
                substr(inet_pton($subnet) ?: '', 0, (int) ceil($bits / 8)),
            );
        }

        $mask = $bits === 0 ? 0 : (~0 << (32 - $bits));

        return ($ipLong & $mask) === ($subnetLong & $mask);
    }

    /**
     * URI host matching: extract host from URI and match against subtree.
     */
    private function matchesUri(string $name, string $subtree): bool
    {
        $host = parse_url($name, PHP_URL_HOST);

        if ($host === null || $host === false) {
            $host = $name;
        }

        return $this->matchesDns((string) $host, $subtree);
    }

    /**
     * Directory name prefix matching.
     */
    private function matchesDirectoryName(string $name, string $subtree): bool
    {
        return str_starts_with(strtolower($name), strtolower($subtree));
    }

    private function looksLikeDomain(string $value): bool
    {
        return (bool) preg_match('/^(\*\.)?[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?)+$/', $value);
    }
}
