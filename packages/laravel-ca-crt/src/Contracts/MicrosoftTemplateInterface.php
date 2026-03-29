<?php

declare(strict_types=1);

namespace CA\Crt\Contracts;

use CA\DTOs\ExtensionCollection;

interface MicrosoftTemplateInterface
{
    /**
     * Get the template display name.
     */
    public function getName(): string;

    /**
     * Get the template OID.
     */
    public function getOid(): string;

    /**
     * Get the key usage flags for this template.
     *
     * @return array<int, string>
     */
    public function getKeyUsage(): array;

    /**
     * Get the extended key usage OIDs for this template.
     *
     * @return array<int, string>
     */
    public function getExtendedKeyUsage(): array;

    /**
     * Get the basic constraints configuration.
     *
     * @return array{ca: bool, pathLength?: int}
     */
    public function getBasicConstraints(): array;

    /**
     * Get the default validity period in days.
     */
    public function getValidityDays(): int;

    /**
     * Convert this template to an ExtensionCollection.
     */
    public function toExtensionCollection(): ExtensionCollection;
}
