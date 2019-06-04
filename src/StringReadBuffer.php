<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class StringReadBuffer implements ReadBuffer
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
        // TODO: Implement readFragment() method.
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
            $this->binaryIntegerReader->readFixed($this->buffer, 3)
        ];
    }


}
