<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class UncompressedPacketReader implements PacketReader
{
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
     *   [lengthOfPacket, sequence],
     *   [lengthOfPacket, sequence],
     *   [lengthOfPacket, sequence],
     * ]
     * @var array
     */
    private $packets = [];

    /** @var int[] */
    private $remainingPacketLength = [];

    /** @var BinaryIntegerReader */
    private $binaryIntegerReader;

    /**
     * @var ReadBuffer
     */
    private $readBuffer;
    /**
     * @var BufferPayloadReaderFactory
     */
    private $payloadReaderFactory;

    public function __construct(
        BinaryIntegerReader $binaryIntegerReader,
        ReadBuffer $readBuffer,
        BufferPayloadReaderFactory $payloadReaderFactory
    )
    {
        $this->binaryIntegerReader = $binaryIntegerReader;
        $this->readBuffer = $readBuffer;
        $this->payloadReaderFactory = $payloadReaderFactory;
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
            $reader(
                $this->payloadReaderFactory->createFromBuffer($this->readBuffer, $this->remainingPacketLength),
                $this->packets[0][self::LENGTH],
                $this->packets[0][self::SEQUENCE]
            );

            $this->advancePacketLength($this->readBuffer->flush());
        } catch (IncompleteBufferException $exception) {
            return false;
        }

        return true;
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
            self::SEQUENCE => $this->binaryIntegerReader->readFixed($dataToParse[3], 1)
        ];

        $this->remainingPacketLength[] = $this->awaitedPacketLength;

        return substr($dataToParse, 4);
    }


    private function advancePacketLength(int $readLength): void
    {
        while ($this->remainingPacketLength[0] <= $readLength) {
            $readLength -= $this->remainingPacketLength[0];
            array_shift($this->packets);
            array_shift($this->remainingPacketLength);

            if (!$this->packets) {
                return;
            }
        }

        $this->remainingPacketLength[0] -= $readLength;
    }
}
