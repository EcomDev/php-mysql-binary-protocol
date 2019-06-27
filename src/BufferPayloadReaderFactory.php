<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class BufferPayloadReaderFactory
{
    public function create(ReadBuffer $buffer, array $unreadPacketLength): BufferPayloadReader
    {
        return new BufferPayloadReader($buffer, $unreadPacketLength, new BinaryIntegerReader());
    }
}
