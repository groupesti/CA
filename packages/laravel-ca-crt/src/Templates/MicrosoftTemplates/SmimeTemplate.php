<?php

declare(strict_types=1);

namespace CA\Crt\Templates\MicrosoftTemplates;

use CA\Crt\Contracts\MicrosoftTemplateInterface;
use CA\DTOs\ExtensionCollection;

final class SmimeTemplate implements MicrosoftTemplateInterface
{
    public function getName(): string
    {
        return 'SMIME';
    }

    public function getOid(): string
    {
        return '1.3.6.1.4.1.311.21.8.10159878.12294508.5765498.7568526.1376567.85.1.12';
    }

    public function getKeyUsage(): array
    {
        return ['digitalSignature', 'keyEncipherment', 'dataEncipherment'];
    }

    public function getExtendedKeyUsage(): array
    {
        return ['emailProtection'];
    }

    public function getBasicConstraints(): array
    {
        return ['ca' => false];
    }

    public function getValidityDays(): int
    {
        return 365; // 1 year
    }

    public function toExtensionCollection(): ExtensionCollection
    {
        $extensions = new ExtensionCollection();

        $extensions->add('2.5.29.19', true, $this->getBasicConstraints());
        $extensions->add('2.5.29.15', true, $this->getKeyUsage());
        $extensions->add('2.5.29.37', false, $this->getExtendedKeyUsage());

        $extensions->add('1.3.6.1.4.1.311.21.7', false, [
            'templateID' => $this->getOid(),
            'templateMajorVersion' => 1,
            'templateMinorVersion' => 0,
        ]);

        $extensions->add('1.3.6.1.4.1.311.20.2', false, $this->getName());
        $extensions->add('1.3.6.1.4.1.311.21.10', false, $this->getExtendedKeyUsage());

        return $extensions;
    }
}
