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
     *
     * @return void
     */
    public function append(string $data): void;

    /**
     * Executes callable to read a fragment of the buffer
     *
     * In case of IncompleteBufferException is thrown during reading,
     * method returns false, otherwise buffer is advanced
     *
     * @param callable
     * @return boolean
     */
    public function readFragment(callable $reader): bool;

    /**
     * Checks if complete packed has been added to the buffer.
     */
    public function isCompletePacket(): bool;
}
