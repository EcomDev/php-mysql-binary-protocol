<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol\Frame;


use EcomDev\MySQLBinaryProtocol\CapabilityFlags;
use EcomDev\MySQLBinaryProtocol\CharsetIdentifiers;
use EcomDev\MySQLBinaryProtocol\StatusFlags;
use PHPUnit\Framework\TestCase;

class HandshakeBuilderV10Test extends TestCase
{
    /** @var HandshakeV10Builder */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new HandshakeV10Builder();
    }

    /** @test */
    public function createHandshakeWithMinimalData()
    {
        $this->assertEquals(
            new HandshakeV10(
                '5.5.1',
                10,
                "\x01\x02\x03\x04\x05\x06\x07\x08",
                CapabilityFlags::CLIENT_PROTOCOL_41
            ),
            $this->builder->withServerInfo('5.5.1', 10)
                ->withAuthData("\x01\x02\x03\x04\x05\x06\x07\x08")
                ->withCapabilities(CapabilityFlags::CLIENT_PROTOCOL_41)
                ->build()
        );
    }

    /** @test */
    public function createsHandshakeWithServerStatusAndCharset()
    {
        $this->assertEquals(
            new HandshakeV10(
                '1.0.0',
                1,
                'thisisstringdata',
                0,
                CharsetIdentifiers::UTF8,
                StatusFlags::SERVER_STATUS_AUTOCOMMIT
            ),
            $this->builder->withServerInfo('1.0.0', 1)
                ->withAuthData('thisisstringdata')
                ->withCharset(CharsetIdentifiers::UTF8)
                ->withStatus(StatusFlags::SERVER_STATUS_AUTOCOMMIT)
                ->build()
        );
    }
    
    /** @test */
    public function createsHandshakeWithAuthPluginSpecified()
    {
        $this->assertEquals(
            new HandshakeV10(
                '1.0.0',
                1,
                'thisisstringdata',
                0,
                0,
                0,
                'mysql_native_password'
            ),
            $this->builder->withServerInfo('1.0.0', 1)
                ->withAuthData('thisisstringdata')
                ->withAuthPlugin('mysql_native_password')
                ->build()
        );
    }
}
