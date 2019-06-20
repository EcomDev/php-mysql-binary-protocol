<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

use function strlen;
use function substr;
use function strpos;

class ReadBuffer
{
    const ONE_MEGABYTE = 1024*1024;

    /**
     * @var string
     */
    private $buffer = '';

    /** @var int */
    private $currentBufferOffset = 0;

    /** @var int */
    private $readBufferOffset = 0;

    /**
     * @var int
     */
    private $bufferSize;

    public function __construct(int $bufferSize = self::ONE_MEGABYTE)
    {
        $this->bufferSize = $bufferSize;
    }

    public function append(string $data): void
    {
        $this->buffer .= $data;
    }

    public function read(int $length): string
    {
        if (!$this->isReadable($length)) {
            $this->currentBufferOffset = $this->readBufferOffset;
            throw new IncompleteBufferException();
        }

        $data = substr($this->buffer, $this->currentBufferOffset, $length);

        $this->currentBufferOffset += $length;
        return $data;
    }

    public function isReadable(int $length): bool
    {
        return strlen($this->buffer) - $this->currentBufferOffset >= $length;
    }

    public function flush(): int
    {
        $bytesRead = $this->currentPosition();

        $this->readBufferOffset = $this->currentBufferOffset;

        if ($this->readBufferOffset >= $this->bufferSize) {
            $this->buffer = substr($this->buffer, $this->readBufferOffset);
            $this->readBufferOffset = 0;
            $this->currentBufferOffset = 0;
        }

        return $bytesRead;
    }

    public function scan(string $pattern): int
    {
        $position = strpos($this->buffer, $pattern, $this->currentBufferOffset);

        return $position === false ? -1 : ($position - $this->currentBufferOffset) + 1;
    }

    public function advance(int $length): void
    {
        if (!$this->isReadable($length)) {
            throw new IncompleteBufferException();
        }

        $this->currentBufferOffset += $length;
    }

    public function currentPosition(): int
    {
        return $this->currentBufferOffset - $this->readBufferOffset;
    }
}
