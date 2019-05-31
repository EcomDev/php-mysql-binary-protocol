<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


class InMemoryReadBuffer implements ReadBuffer
{
    /** @var string */
    private $buffer = '';

    /** @var int */
    private $bufferSize = 0;

    /**
     * {@inheritDoc}
     */
    public function append(string $data): void
    {
        $this->buffer = $data;
        $this->bufferSize = 0;
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
}
