<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class StringPacketReader implements PacketReader, PacketFragmentReader
{
    const INTEGER_LENGTH_MARKER = [
        0xfc => 2,
        0xfd => 3,
        0xfe => 8
    ];

    /**
     * @var int
     */
    private $currentPacketLength;

    /**
     * @var int
     */
    private $currentPacketSequence;

    /** @var BinaryIntegerReader */
    private $binaryIntegerReader;

    /**
     * @var ReadBuffer
     */
    private $readBuffer;

    public function __construct(BinaryIntegerReader $binaryIntegerReader, ReadBuffer $readBuffer)
    {
        $this->binaryIntegerReader = $binaryIntegerReader;
        $this->readBuffer = $readBuffer;
    }

    /**
     * {@inheritDoc}
     */
    public function append(string $data): void
    {
        $this->readBuffer->append($data);

        if ($this->currentPacketLength === null) {
            $this->initPacket();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function readFragment(callable $reader): bool
    {
        try {
            $reader($this);
            $this->currentPacketLength -= $this->readBuffer->flush();
        } catch (IncompleteBufferException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function isFullPacket(): bool
    {
        return $this->readBuffer->isReadable($this->currentPacketLength);
    }

    /**
     * {@inheritDoc}
     */
    public function nextPacket(): void
    {
        $this->initPacket();
    }

    private function initPacket(): void
    {
        $this->currentPacketLength = $this->binaryIntegerReader->readFixed(
            $this->readBuffer->read(3),
            3
        );
        $this->currentPacketSequence = $this->binaryIntegerReader->readFixed(
            $this->readBuffer->read(1),
            1
        );
    }

    /**
     * {@inheritDoc}
     */
    public function readFixedInteger(int $bytes)
    {
        return $this->binaryIntegerReader->readFixed(
            $this->readBuffer->read($bytes),
            $bytes
        );
    }

    /**
     * Reads length encoded integer
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_integers.html
     */
    public function readLengthEncodedIntegerOrNull()
    {
        $value = $this->readFixedInteger(1);

        if ($value === 0xfb) {
            return null;
        }

        if ($value < 251) {
            return $value;
        }

        if (!isset(self::INTEGER_LENGTH_MARKER[$value])) {
            throw new InvalidBinaryDataException();
        }

        return $this->readFixedInteger(self::INTEGER_LENGTH_MARKER[$value]);
    }

    /**
     * Reads string of specified length from buffer
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readFixedString(int $length): string
    {
        return $this->readBuffer->read($length);
    }

    /**
     * Reads string that is has length as the first part of the fragment
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readLengthEncodedStringOrNull(): ?string
    {
        $stringLength = $this->readLengthEncodedIntegerOrNull();

        if ($stringLength === null) {
            return null;
        }

        return $this->readFixedString($stringLength);
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
