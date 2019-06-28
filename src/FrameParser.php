<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


interface FrameParser
{
    /**
     * Parses frames from payload reader
     *
     * @throws InvalidBinaryDataException
     * @throws IncompleteBufferException
     */
    public function parse(PayloadReader $payload, int $length, int $sequenceNumber): Frame;
}
