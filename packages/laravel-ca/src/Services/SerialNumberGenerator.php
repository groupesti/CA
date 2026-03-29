<?php

declare(strict_types=1);

namespace CA\Services;

final class SerialNumberGenerator
{
    public function generate(int $bytes = 20): string
    {
        $randomBytes = random_bytes($bytes);

        // Ensure the most significant bit is 0 (positive integer per RFC 5280)
        $randomBytes[0] = chr(ord($randomBytes[0]) & 0x7F);

        // Ensure serial is not zero
        if ($randomBytes === str_repeat("\0", $bytes)) {
            $randomBytes[$bytes - 1] = "\x01";
        }

        return strtoupper(bin2hex($randomBytes));
    }
}
