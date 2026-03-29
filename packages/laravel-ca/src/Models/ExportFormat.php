<?php

declare(strict_types=1);

namespace CA\Models;

class ExportFormat extends Lookup
{
    protected static string $lookupType = 'export_format';

    public const PEM = 'pem';
    public const DER = 'der';
    public const PKCS12 = 'pkcs12';
    public const PKCS7 = 'pkcs7';

    public function getMimeType(): string
    {
        return $this->meta('mime_type', 'application/octet-stream');
    }

    public function getExtension(): string
    {
        return $this->meta('extension', '');
    }
}
