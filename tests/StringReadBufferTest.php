<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

use PHPUnit\Framework\TestCase;

class StringReadBufferTest extends TestCase
{
    /**
     * @var ReadBuffer
     */
    private $buffer;

    protected function setUp(): void
    {
        $this->buffer = (new StringReadBufferFactory())->createWithDefaultSettings();
    }

    /**
     * @test
     *
     */
    public function reportsIncompleteBufferWhenNotFullLengthProvided()
    {
        $this->buffer->append("\xFF\xFF\xFF\x00Some Payload");

        $this->assertFalse($this->buffer->isFullPacket());
    }

    /** @test */
    public function reportsCompleteBufferWhenAllDataIsProvidedAtOnce()
    {
        $this->buffer->append("\x01\x00\x00\x00\x01");

        $this->assertTrue($this->buffer->isFullPacket());
    }
}
