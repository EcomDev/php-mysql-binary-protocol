<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class BufferPayloadReader implements PayloadReader
{
    private const LENGTH_MARKERS = [
        0xfc => 2,
        0xfd => 3,
        0xfe => 8
    ];

    private const NULL_MARKER = 0xfb;

    /** @var ReadBuffer */
    private $buffer;

    /**
     * @var BinaryIntegerReader
     */
    private $integerReader;

    /** @var int[] */
    private $unreadPacketLength;


    public function __construct(ReadBuffer $buffer, array $unreadPacketLength, BinaryIntegerReader $integerReader)
    {
        $this->buffer = $buffer;
        $this->integerReader = $integerReader;
        $this->unreadPacketLength = $unreadPacketLength;
    }

    /**
     * {@inheritDoc}
     */
    public function readFixedInteger(int $bytes)
    {
        return $this->integerReader->readFixed(
            $this->buffer->read($bytes),
            $bytes
        );
    }

    /**
     * {@inheritDoc}
     */
    public function readLengthEncodedIntegerOrNull()
    {
        $firstByte = $this->readFixedInteger(1);

        if ($firstByte < 251) {
            return $firstByte;
        }

        if ($firstByte === self::NULL_MARKER) {
            return null;
        }

        if (isset(self::LENGTH_MARKERS[$firstByte])) {
            return $this->readFixedInteger(self::LENGTH_MARKERS[$firstByte]);
        }

        throw new InvalidBinaryDataException();
    }

    /**
     * Reads string of specified length from buffer
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readFixedString(int $length): string
    {
        return $this->buffer->read($length);
    }

    /**
     * Reads string that is has length as the first part of the fragment
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readLengthEncodedStringOrNull(): ?string
    {
        $length = $this->readLengthEncodedIntegerOrNull();

        if ($length === null) {
            return null;
        }

        return $this->buffer->read($length);
    }

    /**
     * Reads string till x00 character
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readNullTerminatedString(): string
    {
        $nullPosition = $this->buffer->scan("\x00");

        if ($nullPosition === -1) {
            throw new IncompleteBufferException();
        }

        $string = $this->buffer->read($nullPosition - 1);
        $this->buffer->read(1);

        return $string;
    }

    /**
     * Reads string that is rest of payload
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readRestOfPacketString(): string
    {
        return $this->buffer->read(
            $this->remainingPacketLengthToRead()
        );
    }

    private function remainingPacketLengthToRead(): int
    {
        $currentBufferPosition = $this->buffer->currentPosition();

        $currentPacketIndex = 0;
        while ($this->unreadPacketLength[$currentPacketIndex] <= $currentBufferPosition) {
            $currentBufferPosition -= $this->unreadPacketLength[$currentPacketIndex];
            $currentPacketIndex++;
        }

        return $this->unreadPacketLength[$currentPacketIndex] - $currentBufferPosition;
    }

}
