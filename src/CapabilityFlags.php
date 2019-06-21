<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

/**
 * Collection of capability flags for MySQL communication
 *
 * @see https://dev.mysql.com/doc/internals/en/capability-flags.html#packet-Protocol::CapabilityFlags
 */
class CapabilityFlags
{
    public const CLIENT_LONG_PASSWORD = 0x01;
    public const CLIENT_FOUND_ROWS = 0x02;
    public const CLIENT_LONG_FLAG = 0x04;
    public const CLIENT_CONNECT_WITH_DB = 0x08;
    public const CLIENT_NO_SCHEMA = 0x10;
    public const CLIENT_COMPRESS = 0x20;
    public const CLIENT_IGNORE_SPACE = 0x0100;
    public const CLIENT_PROTOCOL_41 = 0x0200;
    public const CLIENT_INTERACTIVE = 0x0400;
    public const CLIENT_SSL = 0x0800;
    public const CLIENT_TRANSACTIONS = 0x2000;
    public const CLIENT_SECURE_CONNECTION = 0x8000;
    public const CLIENT_MULTI_STATEMENTS = 0x010000;
    public const CLIENT_MULTI_RESULTS = 0x020000;
    public const CLIENT_PS_MULTI_RESULTS = 0x040000;
    public const CLIENT_PLUGIN_AUTH = 0x080000;
    public const CLIENT_CONNECT_ATTRS = 0x100000;
    public const CLIENT_PLUGIN_AUTH_LENENC_CLIENT_DATA = 0x200000;
    public const CLIENT_SESSION_TRACK = 0x800000;
    public const CLIENT_DEPRECATE_EOF = 0x01000000;
}
