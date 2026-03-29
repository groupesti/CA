<?php

declare(strict_types=1);

namespace CA\Tsa\Services;

/**
 * Generate unique serial numbers for timestamp tokens using cryptographic randomness.
 */
class TsaSerialGenerator
{
    /**
     * Generate a random serial number as a hexadecimal string.
     *
     * @param int $bits Number of random bits (default from config).
     */
    public function generate(?int $bits = null): string
    {
        $bits = $bits ?? (int) config('ca-tsa.serial_number_bits', 64);
        $bytes = (int) ceil($bits / 8);

        $random = random_bytes($bytes);

        // Ensure the high bit is set to guarantee positive integer representation
        // and consistent length.
        $random[0] = chr(ord($random[0]) | 0x80);

        return bin2hex($random);
    }
}
