<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;

use PHPUnit\Framework\TestCase;

class BufferPayloadReaderTest extends TestCase
{
    /** @var BufferPayloadReaderFactory */
    private $payloadReaderFactory;

    /** @var ReadBuffer */
    private $buffer;

    protected function setUp(): void
    {
        $this->payloadReaderFactory = new BufferPayloadReaderFactory();
        $this->buffer = new ReadBuffer();
    }


    /** @test */
    public function readsOneByteFixedInteger()
    {
        $payloadReader = $this->createPayloadReader("\x01\x02\x03");

        $this->assertEquals(
            [
                1,
                2,
                3
            ],
            [
                $payloadReader->readFixedInteger(1),
                $payloadReader->readFixedInteger(1),
                $payloadReader->readFixedInteger(1),
            ]
        );
    }

    /** @test */
    public function readsMultipleBytesOfFixedInteger()
    {
        $payloadReader = $this->createPayloadReader(
            "\x00\x02\x02\x00\x00\x00\x00\x00\x00\x00\x00\xF0\x00"
        );

        $this->assertEquals(
            [
                512,
                2,
                67553994410557440
            ],
            [
                $payloadReader->readFixedInteger(2),
                $payloadReader->readFixedInteger(3),
                $payloadReader->readFixedInteger(8),
            ]
        );
    }

    /** @test */
    public function readsOneByteLengthEncodedInteger()
    {
        $payloadReader = $this->createPayloadReader("\x00\xfa\xf9\xa0");

        $this->assertEquals(
            [
                0,
                250,
                249,
                160
            ],
            [
                $payloadReader->readLengthEncodedIntegerOrNull(),
                $payloadReader->readLengthEncodedIntegerOrNull(),
                $payloadReader->readLengthEncodedIntegerOrNull(),
                $payloadReader->readLengthEncodedIntegerOrNull(),
            ]
        );
    }

    /** @test */
    public function readsTwoByteLengthEncodedInteger()
    {
        $payloadReader = $this->createPayloadReader("\xfc\xfb\x00\xfc\xfc\x00\xfc\xff\xf0");

        $this->assertEquals(
            [
                251,
                252,
                61695
            ],
            [
                $payloadReader->readLengthEncodedIntegerOrNull(),
                $payloadReader->readLengthEncodedIntegerOrNull(),
                $payloadReader->readLengthEncodedIntegerOrNull(),
            ]
        );
    }

    /** @test */
    public function readsThreeByteLengthEncodedInteger()
    {
        $payloadReader = $this->createPayloadReader("\xfd\xff\xf0\x00\xfd\xa9\xff\xf0");

        $this->assertEquals(
            [
                61695,
                15794089
            ],
            [
                $payloadReader->readLengthEncodedIntegerOrNull(),
                $payloadReader->readLengthEncodedIntegerOrNull(),
            ]
        );
    }

    /** @test */
    public function readsEightByteLengthEncodedInteger()
    {
        $payloadReader = $this->createPayloadReader(
            "\xfe\xa9\xff\xf0\x00\x00\x00\x00\x00\xfe\x09\xea\xca\xff\x0a\xff\xff\x0a"
        );

        $this->assertEquals(
            [
                15794089,
                792632482146740745
            ],
            [
                $payloadReader->readLengthEncodedIntegerOrNull(),
                $payloadReader->readLengthEncodedIntegerOrNull(),
            ]
        );
    }

    /** @test */
    public function readsNullValueFromLengthEncodedIntegerSpec()
    {
        $payloadReader = $this->createPayloadReader("\xfb");

        $this->assertEquals(
            null,
            $payloadReader->readLengthEncodedIntegerOrNull()
        );
    }

    /** @test */
    public function reportsIncorrectLengthEncodedIntegerGivenFirstByteIsOutOfBounds()
    {
        $payloadReader = $this->createPayloadReader("\xff");

        $this->expectException(InvalidBinaryDataException::class);
        $payloadReader->readLengthEncodedIntegerOrNull();
    }

    /** @test */
    public function readsFixedLengthString()
    {
        $payloadReader = $this->createPayloadReader('onetwothree');

        $this->assertEquals(
            [
                'one',
                'two',
                'three'
            ],
            [
                $payloadReader->readFixedString(3),
                $payloadReader->readFixedString(3),
                $payloadReader->readFixedString(5),
            ]
        );
    }
    
    /** @test */
    public function readsLengthEncodedString()
    {
        $payloadReader = $this->createPayloadReader(
            "\x01a\x03one\x05three"
        );

        $this->assertEquals(
            [
                'a',
                'one',
                'three'
            ],
            [
                $payloadReader->readLengthEncodedStringOrNull(),
                $payloadReader->readLengthEncodedStringOrNull(),
                $payloadReader->readLengthEncodedStringOrNull(),
            ]
        );
    }

    /** @test */
    public function readsLongLengthEncodedString()
    {
        $payloadReader = $this->createPayloadReader("\xfc\xe8\x03" . str_repeat('a', 1000));

        $this->assertEquals(
            str_repeat('a', 1000),
            $payloadReader->readLengthEncodedStringOrNull()
        );
    }
    
    
    /** @test */
    public function readsNullStringValue()
    {
        $payloadReader = $this->createPayloadReader("\xfb");

        $this->assertEquals(
            null,
            $payloadReader->readLengthEncodedStringOrNull()
        );
    }

    /** @test */
    public function readsNullTerminatedString()
    {
        $payloadReader = $this->createPayloadReader("null_terminated_string\x00other_data");

        $this->assertEquals('null_terminated_string', $payloadReader->readNullTerminatedString());
    }
    
    /** @test */
    public function readsMultipleNullTerminatedStrings()
    {
        $payloadReader = $this->createPayloadReader("null_terminated_string\x00other_null_terminated_string\x00");

        $this->assertEquals(
            [
                'null_terminated_string',
                'other_null_terminated_string'
            ],
            [
                $payloadReader->readNullTerminatedString(),
                $payloadReader->readNullTerminatedString(),
            ]
        );
    }
    
    /** @test */
    public function throwsIncompleteBufferExceptionWhenNullCharacterIsNotPresent()
    {
        $payloadReader = $this->createPayloadReader('some string without null character');

        $this->expectException(IncompleteBufferException::class);

        $payloadReader->readNullTerminatedString();
    }

    /** @test */
    public function readsStringTillEndOfTheBuffer()
    {
        $payloadReader = $this->createPayloadReader('some string till end of buffer');

        $this->assertEquals('some string till end of buffer', $payloadReader->readRestOfPacketString());
    }

    /** @test */
    public function readsMultipleStringsThatRepresentCompletePacket()
    {
        $payloadReader = $this->createPayloadReader(
            'packet1packet2packet3last packet',
            7, 7, 7, 11
        );

        $this->assertEquals(
            [
                'packet1',
                'packet2',
                'packet3',
                'last packet'
            ],
            [
                $payloadReader->readRestOfPacketString(),
                $payloadReader->readRestOfPacketString(),
                $payloadReader->readRestOfPacketString(),
                $payloadReader->readRestOfPacketString(),
            ]
        );
    }

    /** @test */
    public function readsRestOfPacketStringStartingFromCurrentBufferPosition()
    {
        $payloadReader = $this->createPayloadReader(
           'onerest of packetstring that should not be read',
            17, 30
        );

        $payloadReader->readFixedString(3);

        $this->assertEquals(
            'rest of packet',
            $payloadReader->readRestOfPacketString()
        );
    }
    
    private function createPayloadReader(string $payload, int ...$packetLength): PayloadReader
    {
        $buffer = new ReadBuffer();
        $buffer->append($payload);
        $packetLength = $packetLength ?: [strlen($payload)];
        return $this->payloadReaderFactory->createFromBuffer($buffer, $packetLength);
    }
}
