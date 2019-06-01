<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

namespace EcomDev\MySQLBinaryProtocol;

use PHPUnit\Framework\TestCase;

class BinaryIntegerReaderTest extends TestCase
{
    /**
     * @var BinaryIntegerReader
     */
    private $reader;

    protected function setUp(): void
    {
        $this->reader = new BinaryIntegerReader();
    }

    /**
     * @test
     * @dataProvider oneByteIntegers
     */
    public function readsFixedOneByteInteger(string $binary, int $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->reader->readFixed($binary, 1));
    }

    /**
     * @test
     * @dataProvider twoByteIntegers
     */
    public function readsFixedTwoByteInteger(string $binary, int $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->reader->readFixed($binary, 2));
    }

    /**
     * @test
     * @dataProvider threeByteIntegers
     */
    public function readsFixedThreeByteInteger(string $binary, int $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->reader->readFixed($binary, 3));
    }

    /**
     * @test
     */
    public function readsSevenByteInteger()
    {
        $this->assertEquals(72057594037927935, $this->reader->readFixed("\xFF\xFF\xFF\xFF\xFF\xFF\xFF", 7));
    }

    /**
     * @test
     * @dataProvider eightByteIntegers
     */
    public function readsEightByteIntegers(string $binary, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->reader->readFixed($binary, 8));
    }

    /** @test */
    public function doesNotSupportValuesHigherThan8Bytes()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot read integers above 8 bytes');

        $this->reader->readFixed("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF", 9);
    }

    public function oneByteIntegers()
    {
        return [
            'zero' => ["\x00", 0],
            'one' => ["\x01", 1],
            'ten' => ["\x0A", 10],
            'hundred' => ["\x64", 100],
            'max' => ["\xFF", 255],
        ];
    }

    public function twoByteIntegers()
    {
        return [
            'zero' => ["\x00\x00", 0],
            'ten' => ["\x0A\x00", 10],
            'thousand' => ["\xE8\x03", 1000],
            'max' => ["\xFF\xFF", 65535],
        ];
    }

    public function threeByteIntegers()
    {
        return [
            'zero' => ["\x00\x00\x00", 0],
            'two_byte_max' => ["\xFF\xFF\x00", 65535],
            'two_byte_max+1' => ["\x00\x00\x01", 65536],
            'max' => ["\xFF\xFF\xFF", 16777215],
        ];
    }

    public function eightByteIntegers()
    {
        return [
            'seven_byte_max' => ["\xFF\xFF\xFF\xFF\xFF\xFF\xFF\x00", 72057594037927935],
            'eight_byte_max' => ["\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFF", 18446744073709551615],
        ];
    }
}
