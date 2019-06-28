<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

/**
 * Reader for buffer fragments
 *
 */
interface PayloadReader
{
    /**
     * Reads fixed value integer
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_integers.html
     * @return int|float
     *
     * @throws IncompleteBufferException
     */
    public function readFixedInteger(int $bytes);

    /**
     * Reads length encoded integer
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_integers.html
     * @return int|float
     *
     * @throws IncompleteBufferException
     */
    public function readLengthEncodedIntegerOrNull();

    /**
     * Reads string of specified length from buffer
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     *
     *
     * @throws IncompleteBufferException
     */
    public function readFixedString(int $length): string;

    /**
     * Reads string that is has length as the first part of the fragment
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     *
     * @throws IncompleteBufferException
     */
    public function readLengthEncodedStringOrNull(): ?string;

    /**
     * Reads string till x00 character
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     *
     * @throws IncompleteBufferException
     */
    public function readNullTerminatedString(): string;


    /**
     * Reads string that is rest of payload
     *
     * @see https://dev.mysql.com/doc/dev/mysql-server/latest/page_protocol_basic_dt_strings.html
     *
     * @throws IncompleteBufferException
     */
    public function readRestOfPacketString(): string;
}
