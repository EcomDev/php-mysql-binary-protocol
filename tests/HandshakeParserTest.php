<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


use EcomDev\MySQLBinaryProtocol\Frame\HandshakeV10;
use EcomDev\MySQLBinaryProtocol\Frame\HandshakeV10Builder;
use PHPUnit\Framework\TestCase;

class HandshakeParserTest extends TestCase
{
    /** @var HandshakeV10Builder */
    private $frameBuilder;

    /**
     * @var PacketReader
     */
    private $packetReader;

    /**
     * @var HandshakeParser
     */
    private $parser;

    /**
     * @var HandshakeV10[]
     */
    private $frames = [];

    protected function setUp(): void
    {
        $this->frameBuilder = new Frame\HandshakeV10Builder();
        $this->packetReader = (new DefaultPacketReaderFactory())->createWithDefaultSettings();

        $this->parser = new HandshakeParser(
            $this->frameBuilder,
            function (HandshakeV10 $handshake) {
                $this->frames[] = $handshake;
            }
        );
    }

    /** @test */
    public function parsesMySQL8HandshakeInitMessage()
    {
        $this->packetReader->append(
        "\x4a\x00\x00\x00\x0a8.0.16\x00\x0d\x00\x00\x00\x10\x4a\x12\x05"
            . "\x71\x5d\x78\x63\x00\xff\xff\xff\x02\x00\xff\xc3\x15\x00\x00\x00"
            . "\x00\x00\x00\x00\x00\x00\x00\x6e\x48\x49\x48\x56\x78\x42\x33\x76"
            . "\x39\x3d\x5c\x00\x63\x61\x63\x68\x69\x6e\x67\x5f\x73\x68\x61\x32"
            . "\x5f\x70\x61\x73\x73\x77\x6f\x72\x64\x00"
        );

        $this->packetReader->readPayload($this->parser);

        $this->assertEquals(
            $this->frameBuilder
                ->withServerInfo(
                    '8.0.16',
                    13
                )
                ->withStatus(
                    StatusFlags::SERVER_STATUS_AUTOCOMMIT
                )
                ->withCharset(
                    CharsetIdentifiers::UTF8MB4
                )
                ->withCapabilities(0xc3ffffff)
                ->withAuthData(
                    "\x10\x4a\x12\x05\x71\x5d\x78\x63\x6e\x48\x49\x48\x56\x78\x42\x33\x76\x39\x3d\x5c\x00"
                )
                ->withAuthPlugin(
                    "caching_sha2_password"
                )
                ->build(),
            current($this->frames)
        );

    }


}
