<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class StringReadBufferFactory
{
    public function createWithDefaultSettings(): StringReadBuffer
    {
        return new StringReadBuffer(new BinaryIntegerReader());
    }
}
