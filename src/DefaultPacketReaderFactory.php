<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class DefaultPacketReaderFactory
{
    public function createWithDefaultSettings(): StringPacketReader
    {
        return new StringPacketReader(new BinaryIntegerReader(), new ReadBuffer());
    }
}
