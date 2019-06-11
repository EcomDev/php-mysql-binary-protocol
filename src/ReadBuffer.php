<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

use function strlen;
use function substr;

class ReadBuffer
{
    const ONE_MEGABYTE = 1024*1024;

    /**
     * @var string
     */
    private $buffer = '';

    /** @var int */
    private $currentPosition = 0;

    /** @var int */
    private $readPosition = 0;

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
            $this->currentPosition = $this->readPosition;
            throw new IncompleteBufferException();
        }

        $data = substr($this->buffer, $this->currentPosition, $length);

        $this->currentPosition += $length;
        return $data;
    }

    public function isReadable(int $length): bool
    {
        return strlen($this->buffer) - $this->currentPosition >= $length;
    }

    public function flush(): int
    {
        $bytesRead = $this->currentPosition - $this->readPosition;
        $this->readPosition = $this->currentPosition;

        if ($this->readPosition >= $this->bufferSize) {
            $this->buffer = substr($this->buffer, $this->readPosition);
            $this->readPosition = 0;
            $this->currentPosition = 0;
        }

        return $bytesRead;
    }
}
