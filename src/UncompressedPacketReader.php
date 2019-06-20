<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class UncompressedPacketReader implements PacketReader, PacketPayloadReader
{
    private const INTEGER_LENGTH_MARKER = [
        0xfc => 2,
        0xfd => 3,
        0xfe => 8
    ];

    private const UNREAD_LENGTH = 2;
    private const LENGTH = 0;
    private const SEQUENCE = 1;

    /**
     * @var int
     */
    private $awaitedPacketLength = 0;

    /**
     * Registry of packets
     *
     * [
     *   [lengthOfPacket, sequence, remainingToReadLength],
     *   [lengthOfPacket, sequence, remainingToReadLength],
     *   [lengthOfPacket, sequence, remainingToReadLength],
     * ]
     * @var array
     */
    private $packets = [];

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
        do {
            $data = $this->registerPacket($data);
        } while ($data !== '');
    }

    /**
     * {@inheritDoc}
     */
    public function readPayload(callable $reader): bool
    {
        try {
            $reader($this, $this->packets[0][self::LENGTH], $this->packets[0][self::SEQUENCE]);
            $this->advancePacketLength($this->readBuffer->flush());
        } catch (IncompleteBufferException $exception) {
            return false;
        }

        return true;
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
        $nullPosition = $this->readBuffer->scan("\x00");

        if ($nullPosition === -1) {
            throw new IncompleteBufferException();
        }

        $string = $this->readBuffer->read($nullPosition - 1);
        $this->readBuffer->advance(1);
        return $string;
    }

    /**
     * Reads string that is rest of payload
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     */
    public function readRestOfPacketString(): string
    {
        return $this->readBuffer->read(
            $this->remainingPacketLengthToRead()
        );
    }

    private function registerPacket(string $dataToParse): string
    {
        if ($this->awaitedPacketLength) {
            $trimLength = min(strlen($dataToParse), $this->awaitedPacketLength);
            $this->readBuffer->append(substr($dataToParse, 0, $trimLength));
            $this->awaitedPacketLength -= $trimLength;
            return substr($dataToParse, $trimLength);
        }

        $this->awaitedPacketLength = $this->binaryIntegerReader->readFixed(
            substr($dataToParse, 0, 3),
            3
        );

        $this->packets[] = [
            self::LENGTH => $this->awaitedPacketLength,
            self::SEQUENCE => $this->binaryIntegerReader->readFixed($dataToParse[3], 1),
            self::UNREAD_LENGTH => $this->awaitedPacketLength
        ];

        return substr($dataToParse, 4);
    }


    private function remainingPacketLengthToRead(): int
    {
        $currentBufferPosition = $this->readBuffer->currentPosition();

        $currentPacketIndex = 0;
        while ($this->packets[$currentPacketIndex][self::UNREAD_LENGTH] <= $currentBufferPosition) {
            $currentBufferPosition -= $this->packets[$currentPacketIndex][self::UNREAD_LENGTH];
            $currentPacketIndex++;
        }

        return $this->packets[$currentPacketIndex][self::UNREAD_LENGTH] - $currentBufferPosition;
    }

    private function advancePacketLength(int $readLength): void
    {

        while ($this->packets[0][self::UNREAD_LENGTH] <= $readLength) {
            $readLength -= $this->packets[0][self::UNREAD_LENGTH];
            array_shift($this->packets);

            if (!$this->packets) {
                return;
            }
        }


        $this->packets[0][self::UNREAD_LENGTH] -= $readLength;
    }
}
