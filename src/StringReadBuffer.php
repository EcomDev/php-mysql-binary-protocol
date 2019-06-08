<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class StringReadBuffer implements ReadBuffer, ReadBufferFragment
{

    /** @var int */
    private $bufferSize = 0;

    /** @var int */
    private $currentPosition = 0;

    /**
     * @var int[]
     */
    private $currentPacket = [];

    /**
     * @var string
     */
    private $buffer = '';

    /** @var BinaryIntegerReader */
    private $binaryIntegerReader;

    public function __construct(BinaryIntegerReader $binaryIntegerReader)
    {
        $this->binaryIntegerReader = $binaryIntegerReader;
    }

    /**
     * {@inheritDoc}
     */
    public function append(string $data): void
    {
        $this->buffer .= $data;
        $this->bufferSize += strlen($data);

        if (!$this->currentPacket) {
            $this->initPacket();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function readFragment(callable $reader): bool
    {
        $reader($this);

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function isFullPacket(): bool
    {
        return strlen($this->buffer) > $this->currentPacket[0];
    }

    /**
     * {@inheritDoc}
     */
    public function nextPacket(): void
    {
        // TODO: Implement nextPacket() method.
    }

    private function initPacket(): void
    {
        $this->currentPacket = [
            $this->binaryIntegerReader->readFixed($this->read(3), 3),
            $this->binaryIntegerReader->readFixed($this->read(1), 1)
        ];
    }

    private function read(int $length): string
    {
        $value = substr($this->buffer, $this->currentPosition, $length);
        $this->currentPosition += $length;
        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function readFixedInteger(int $bytes): int
    {
        return $this->binaryIntegerReader->readFixed($this->read($bytes), $bytes);
    }

    /**
     * Reads length encoded integer
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_integers.html
     */
    public function readLengthEncodedInteger(): int
    {
        // TODO: Implement readLengthEncodedInteger() method.
    }

    /**
     * Reads string of specified length from buffer
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readFixedString(int $length): string
    {
        // TODO: Implement readFixedString() method.
    }

    /**
     * Reads string that is has length as the first part of the fragment
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readLengthEncodedStringOrNull(): ?string
    {
        // TODO: Implement readLengthEncodedStringOrNull() method.
    }

    /**
     * Reads string till x00 character
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readNullTerminatedString(): string
    {
        // TODO: Implement readNullTerminatedString() method.
    }

    /**
     * Reads string that is rest of payload
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readRestOfPacketString(): string
    {
        // TODO: Implement readRestOfPacketString() method.
    }
}
