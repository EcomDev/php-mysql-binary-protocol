<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class BufferPayloadReaderFactory
{
    /**
     * @var BinaryIntegerReader
     */
    private $binaryIntegerReader;

    public function __construct(BinaryIntegerReader $binaryIntegerReader = null)
    {
        $this->binaryIntegerReader = $binaryIntegerReader ?? new BinaryIntegerReader();
    }

    public function createFromBuffer(ReadBuffer $buffer, array $unreadPacketLength): BufferPayloadReader
    {
        return new BufferPayloadReader($buffer, $unreadPacketLength, $this->binaryIntegerReader);
    }

    public function createFromString(string $data): BufferPayloadReader
    {
        $buffer = new ReadBuffer();
        $buffer->append($data);

        return $this->createFromBuffer($buffer, [strlen($data)]);
    }
}
