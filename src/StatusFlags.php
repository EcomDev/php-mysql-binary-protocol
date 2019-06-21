<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

/**
 * Collection of server status flags sent in OK packet
 *
 * @see https://dev.mysql.com/doc/internals/en/status-flags.html
 */
class StatusFlags
{
    public const SERVER_STATUS_IN_TRANS = 0x01;
    public const SERVER_STATUS_AUTOCOMMIT = 0x02;
    public const SERVER_MORE_RESULTS_EXISTS = 0x08;
    public const SERVER_STATUS_NO_GOOD_INDEX_USED = 0x10;
    public const SERVER_STATUS_NO_INDEX_USED = 0x20;
    public const SERVER_STATUS_CURSOR_EXISTS = 0x40;
    public const SERVER_STATUS_LAST_ROW_SENT = 0x80;
    public const SERVER_STATUS_DB_DROPPED = 0x0100;
    public const SERVER_STATUS_NO_BACKSLASH_ESCAPES = 0x0200;
    public const SERVER_STATUS_METADATA_CHANGED = 0x0400;
    public const SERVER_QUERY_WAS_SLOW = 0x0800;
    public const SERVER_PS_OUT_PARAMS = 0x1000;
    public const SERVER_STATUS_IN_TRANS_READONLY = 0x2000;
    public const SERVER_SESSION_STATE_CHANGED = 0x4000;
}
