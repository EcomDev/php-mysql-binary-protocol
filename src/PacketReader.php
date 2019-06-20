<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

interface PacketReader
{
    /**
     * Appends data into buffer
     */
    public function append(string $data): void;

    /**
     * Executes callable to read a packed payload by using payload reader
     *
     * In case of IncompleteBufferException is thrown during reading,
     * method returns false and same data will be returned on the next read.
     *
     * The code of $reader MUST NOT catch this exception
     */
    public function readPayload(callable $reader): bool;
}
