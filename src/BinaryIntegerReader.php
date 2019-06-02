<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

/**
 * @internal
 */
class BinaryIntegerReader
{
    public function readFixed(string $binary, int $size)
    {
        $result = ord($binary);

        if ($size === 1) {
            return $result;
        }

        if ($size > 8) {
            throw new \RuntimeException('Cannot read integers above 8 bytes');
        }


        if ($size === 8) {
            // This is a workaround of the bug in unpack for values
            // above signed int value for little endian
            return hexdec(bin2hex(strrev(substr($binary, 0, 8))));
        }

        for ($i = 1; $i < $size; $i ++) {
            $result += ord($binary[$i]) << (8*$i);
        }

        return $result;
    }
}
