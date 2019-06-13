<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

use PHPUnit\Framework\TestCase;

class PacketReaderTest extends TestCase
{
    /**
     * @var PacketReader
     */
    private $reader;

    protected function setUp(): void
    {
        $this->reader = (new DefaultPacketReaderFactory())->createWithDefaultSettings();
    }

    /**
     * @test
     *
     */
    public function reportsIncompleteBufferWhenNotFullLengthProvided()
    {
        $this->reader->append("\xFF\xFF\xFF\x00Some Payload");

        $this->assertFalse($this->reader->isFullPacket());
    }

    /** @test */
    public function reportsCompleteBufferWhenAllDataIsProvidedAtOnce()
    {
        $this->reader->append("\x01\x00\x00\x00\x01");

        $this->assertTrue($this->reader->isFullPacket());
    }

    /** @test */
    public function allowsToReadSingleByteIntegerFromPayload()
    {
        $this->reader->append("\x01\x00\x00\x00\xF1");

        $data = [];
        $this->reader->readFragment(function (PacketFragmentReader $fragment) use (&$data) {
            $data[] = $fragment->readFixedInteger(1);
        });

        $this->assertEquals([241], $data);
    }

    /** @test */
    public function allowsToReadMultiplePackets()
    {
        $this->reader->append("\x01\x00\x00\x00\xF1");
        $this->reader->append("\x01\x00\x00\x00\xF2");

        $data = [];
        $readOneByte = function (PacketFragmentReader $fragment) use (&$data) {
            $data[] = $fragment->readFixedInteger(1);
        };

        $this->reader->readFragment($readOneByte);
        $this->reader->nextPacket();
        $this->reader->readFragment($readOneByte);

        $this->assertEquals([241, 242], $data);
    }

    /** @test */
    public function reportsCompletePacketAvailableEvenAfterFragmentIsRead()
    {
        $this->reader->append("\x02\x00\x00\x00\xF1\xF2");

        $this->reader->readFragment(
            function (PacketFragmentReader $fragment) {
                $fragment->readFixedInteger(1);
            }
        );

        $this->assertEquals(true, $this->reader->isFullPacket());
    }
    
    /** @test */
    public function reportsFragmentIsRead()
    {
        $this->reader->append("\x01\x00\x00\x00\x01");

        $this->assertEquals(
            true,
            $this->reader->readFragment(function (PacketFragmentReader $reader) {
                $reader->readFixedInteger(1);
            })
        );
    }

    /** @test */
    public function reportsFragmentIsNotRead()
    {
        $this->reader->append("\x02\x00\x00\x00\x01");

        $this->assertEquals(
            false,
            $this->reader->readFragment(function (PacketFragmentReader $reader) {
                $reader->readFixedInteger(1);
                $reader->readFixedInteger(1);
            })
        );
    }

    /** @test */
    public function allowsReadingVariousFixedIntegers()
    {
        $this->reader->append("\x0D\x00\x00\x00\x00\x02\x02\x00\x00\x00\x00\x00\x00\x00\x00\xF0\x00");

        $data = [];
        $this->reader->readFragment(function (PacketFragmentReader $fragment) use (&$data) {
            $data[] = $fragment->readFixedInteger(2); // 512
            $data[] = $fragment->readFixedInteger(3); // 2
            $data[] = $fragment->readFixedInteger(8); // 67553994410557440
        });

        $this->assertEquals(
            [
                512,
                2,
                67553994410557440
            ],
            $data
        );
    }


}
