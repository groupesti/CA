<?php

declare(strict_types=1);

namespace CA\Crt\Templates;

use CA\Crt\Contracts\MicrosoftTemplateInterface;
use CA\Crt\Templates\MicrosoftTemplates\CodeSigningTemplate;
use CA\Crt\Templates\MicrosoftTemplates\ComputerTemplate;
use CA\Crt\Templates\MicrosoftTemplates\DomainControllerTemplate;
use CA\Crt\Templates\MicrosoftTemplates\SmimeTemplate;
use CA\Crt\Templates\MicrosoftTemplates\UserTemplate;
use CA\Crt\Templates\MicrosoftTemplates\WebServerTemplate;

final class TemplateResolver
{
    /** @var array<string, MicrosoftTemplateInterface> */
    private static array $templatesByName = [];

    /** @var array<string, MicrosoftTemplateInterface> */
    private static array $templatesByOid = [];

    private static bool $initialized = false;

    /**
     * Resolve a template by name or OID.
     */
    public static function resolve(string $nameOrOid): ?MicrosoftTemplateInterface
    {
        self::ensureInitialized();

        return self::$templatesByName[strtolower($nameOrOid)]
            ?? self::$templatesByOid[$nameOrOid]
            ?? null;
    }

    /**
     * Register a custom template.
     */
    public static function register(MicrosoftTemplateInterface $template): void
    {
        self::ensureInitialized();

        self::$templatesByName[strtolower($template->getName())] = $template;
        self::$templatesByOid[$template->getOid()] = $template;
    }

    /**
     * Get all registered templates.
     *
     * @return array<string, MicrosoftTemplateInterface>
     */
    public static function all(): array
    {
        self::ensureInitialized();

        return self::$templatesByName;
    }

    /**
     * Check if a template exists by name or OID.
     */
    public static function has(string $nameOrOid): bool
    {
        return self::resolve($nameOrOid) !== null;
    }

    private static function ensureInitialized(): void
    {
        if (self::$initialized) {
            return;
        }

        $builtIn = [
            new WebServerTemplate(),
            new UserTemplate(),
            new CodeSigningTemplate(),
            new SmimeTemplate(),
            new DomainControllerTemplate(),
            new ComputerTemplate(),
        ];

        foreach ($builtIn as $template) {
            self::$templatesByName[strtolower($template->getName())] = $template;
            self::$templatesByOid[$template->getOid()] = $template;
        }

        self::$initialized = true;
    }
}
