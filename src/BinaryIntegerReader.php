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
            return $this->readUnsigned1ByteInteger($binary);
        }

        if ($size === 2) {
            return $this->readUnsigned2ByteInteger($binary);
        }

        if ($size === 3) {
            return $this->readUnsigned4ByteInteger($binary."\x00");
        }

        if ($size === 4) {
            return $this->readUnsigned4ByteInteger($binary);
        }

        if ($size === 8) {
            return $this->readUnsigned8ByteInteger($binary);
        }

        if ($size > 8) {
            throw new \InvalidArgumentException('Cannot read integers above 8 bytes');
        }

        return unpack('P', str_pad($binary, 8, "\x00"))[1];
    }

    private function readUnsigned8ByteInteger(string $binary)
    {
        // Unpack does not support unsigned 64 bit integers,
        // so we have to improvise here
        if (strlen($binary) > 8) {
            $binary = substr($binary, 0, 8);
        }

        return \hexdec(\bin2hex(\strrev($binary)));
    }

    private function readUnsigned4ByteInteger(string $binary): int
    {
        return unpack('V', $binary)[1];
    }

    private function readUnsigned2ByteInteger(string $binary)
    {
        return unpack('v', $binary)[1];
    }

    private function readUnsigned1ByteInteger(string $binary)
    {
        return unpack('C', $binary)[1];
    }
}
