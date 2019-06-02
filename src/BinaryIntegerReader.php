<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

use function unpack;

/**
 * @internal
 */
class BinaryIntegerReader
{
    public function readFixed(string $binary, int $size)
    {
        if ($size === 1) {
            return unpack('C', $binary)[1];
        }

        if ($size === 2) {
            return unpack('v', $binary)[1];
        }

        if ($size === 3) {
            return unpack('V', $binary."\x00")[1];
        }

        if ($size === 4) {
            return unpack('V', $binary)[1];
        }

        if ($size === 8) {
            if (strlen($binary) > $size) {
                $binary = substr($binary, 0, $size);
            }

            return \hexdec(\bin2hex(\strrev($binary)));
        }

        if ($size > 8) {
            throw new \RuntimeException('Cannot read integers above 8 bytes');
        }

        return unpack('P', str_pad($binary, 8, "\x00"))[1];
    }
}
