<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

interface ReadBuffer
{
    /**
     * Appends data into buffer
     */
    public function append(string $data): void;

    /**
     * Executes callable to read a fragment of the buffer
     *
     * In case of IncompleteBufferException is thrown during reading,
     * method returns false and same data will be returned on the next read.
     *
     * The code of $reader MUST NOT catch this exception
     */
    public function readFragment(callable $reader): bool;

    /**
     * Checks if current packet readable till the end
     */
    public function isFullPacket(): bool;

    /**
     * Moves internal pointer to start reading next packet,
     * ignoring any left data in current one
     */
    public function nextPacket(): void;
}
