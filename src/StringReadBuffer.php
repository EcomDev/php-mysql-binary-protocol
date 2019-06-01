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
        return false;
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
        $totalLength = ord($this->buffer[0])
            + (ord($this->buffer[1]) << 8)
            + (ord($this->buffer[1]) << 16)
        ;
        $this->currentPacket = [

        ];
    }



}
