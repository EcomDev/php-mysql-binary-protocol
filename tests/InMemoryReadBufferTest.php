<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

use PHPUnit\Framework\TestCase;

class InMemoryReadBufferTest extends TestCase
{
    /**
     * @var ReadBuffer
     */
    private $buffer;

    protected function setUp(): void
    {
        $this->buffer = new InMemoryReadBuffer();
    }

    /** @test */
    public function reportsIncompleteBufferWhenNotFullLengthProvided()
    {
        $this->buffer->append("\x00\x00\x01\x00");

        $this->assertFalse($this->buffer->isFullPacket());
    }

    /** @test */
    public function reportsCompleteBufferWhenAllDataIsProvidedAtOnce()
    {
        $this->buffer->append("\x01\x00\x00\x00\x01");

        $this->assertTrue($this->buffer->isFullPacket());
    }
}
