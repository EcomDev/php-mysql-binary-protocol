<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


use PHPUnit\Framework\TestCase;

class ReadBufferTest extends TestCase
{
    /** @var ReadBuffer */
    private $readBuffer;

    protected function setUp(): void
    {
        $this->readBuffer = new ReadBuffer();
    }


    /** @test */
    public function readsBufferByLength()
    {
        $this->readBuffer->append('Some string');

        $this->assertEquals('Some', $this->readBuffer->read(4));
    }
    
    /** @test */
    public function readsBufferByMovingPositionForward()
    {
        $this->readBuffer->append('TDD is awesome');

        $this->readBuffer->read(3);

        $this->assertEquals(' is awesome', $this->readBuffer->read(11));
    }

    /** @test */
    public function throwsIncompleteBufferExceptionWhenNotBufferIsSmallerThenReadSize()
    {
        $this->readBuffer->append('TDD is');

        $this->expectException(IncompleteBufferException::class);

        $this->readBuffer->read(11);
    }
    
    /** @test */
    public function throwIncompleteBufferExceptionWhenNotEnoughDataIsLeftToRead()
    {
        $this->readBuffer->append('TDD is great');

        $this->readBuffer->read(7);

        $this->expectException(IncompleteBufferException::class);

        $this->readBuffer->read(7);
    }
    
    /** @test */
    public function allowsToReadAllAddedPiecesToBuffer()
    {
        $this->readBuffer->append('TDD is');

        $this->readBuffer->read(4);

        $this->readBuffer->append(' great');

        $this->assertEquals('is great', $this->readBuffer->read(8));
    }

    /** @test */
    public function isReadableWhenAskedBytesAreBelowBufferLength()
    {
        $this->readBuffer->append('Some data');

        $this->assertEquals(true, $this->readBuffer->isReadable(4));
    }

    /** @test */
    public function isNotReadableWhenBytesAreLongerThenBufferLength()
    {
        $this->readBuffer->append('Some');

        $this->assertEquals(false, $this->readBuffer->isReadable(5));
    }
    
    /** @test */
    public function isNotReadableWhenAskedLengthIsLowerThenRemainingBytesToRead()
    {
        $this->readBuffer->append('Some data');
        $this->readBuffer->read(5);

        $this->assertEquals(false, $this->readBuffer->isReadable(5));
    }

    /** @test */
    public function isReadableWhenExactAmountOfBytesAvailableToRead()
    {
        $this->readBuffer->append('Data in buffer');

        $this->readBuffer->read(7);

        $this->assertEquals(true, $this->readBuffer->isReadable(7));
    }

    /** @test */
    public function allowsToReadDataAgainIfPreviousSessionWasNotReadCompletely()
    {
        $this->readBuffer->append('Data in buffer');
        $this->readBuffer->read(4);
        $this->readBuffer->read(4);

        try {
            $this->readBuffer->read(7);
        } catch (IncompleteBufferException $exception) { }

        $this->assertEquals('Data in ', $this->readBuffer->read(8));
    }

    /** @test */
    public function allowsToMoveReadBufferPointerAfterRead()
    {
        $this->readBuffer->append('Data in buffer');

        $this->readBuffer->read(5);
        $this->readBuffer->flush();

        try {
            $this->readBuffer->read(10);
        } catch (IncompleteBufferException $e) {

        }

        $this->assertEquals('in buffer', $this->readBuffer->read(9));
    }
    
    /** @test */
    public function clearsBufferWhenReadLimitIsReached()
    {
        $limitedReadBuffer = new ReadBuffer(20);

        $limitedReadBuffer->append('Some data to read 2 remainder of buffer');
        $limitedReadBuffer->read(10);
        $limitedReadBuffer->read(10);
        $limitedReadBuffer->flush();

        $expectedReadBuffer = new ReadBuffer(20);
        $expectedReadBuffer->append('remainder of buffer');

        $this->assertEquals($expectedReadBuffer, $limitedReadBuffer);
    }

    /** @test */
    public function clearsBufferLimitIsReachedALongTimeAgo()
    {
        $limitedReadBuffer = new ReadBuffer(20);

        $limitedReadBuffer->append('Some data to read 2 very long string to read remainder of buffer');
        $limitedReadBuffer->read(10);
        $limitedReadBuffer->flush();
        $limitedReadBuffer->read(20);
        $limitedReadBuffer->read(15);
        $limitedReadBuffer->flush();

        $expectedReadBuffer = new ReadBuffer(20);
        $expectedReadBuffer->append('remainder of buffer');

        $this->assertEquals($expectedReadBuffer, $limitedReadBuffer);
    }

    /** @test */
    public function flushReturnsNumberOfReadBytes()
    {
        $this->readBuffer->append('Some data');
        $this->readBuffer->read(4);
        $this->readBuffer->read(2);
        $this->assertEquals(6, $this->readBuffer->flush());
    }
    
    /** @test */
    public function flushReturnsZeroWhenNoBytesRead()
    {
        $this->readBuffer->append('Some data');

        $this->assertEquals(0, $this->readBuffer->flush());
    }
    
    /** @test */
    public function returnsLengthOfReadInOrderToReadDataUpToThisCharacter()
    {
        $this->readBuffer->append('some:data');

        $this->assertEquals(5, $this->readBuffer->scan(':'));
    }
    
    /** @test */
    public function returnsNegativeIndexWhenNoMatchFoundForScan()
    {
        $this->readBuffer->append('some data without character');

        $this->assertEquals(-1, $this->readBuffer->scan(':'));
    }

    /** @test */
    public function returnsLengthOfReadEvenIfCharacterForSearchIsAFirstOneInBuffer()
    {
        $this->readBuffer->append(':some other data');

        $this->assertEquals(1, $this->readBuffer->scan(':'));
    }
    
    /** @test */
    public function returnsLengthOfRequiredReadForTheNextCharacterOccurrence()
    {
        $this->readBuffer->append('some:other:data');
        $this->readBuffer->read(5);

        $this->assertEquals(6, $this->readBuffer->scan(':'));
    }
    
    /** @test */
    public function advancesBufferPositionFromBeginningOfBuffer()
    {
        $this->readBuffer->append('some very long data');

        $this->readBuffer->advance(10);

        $this->assertEquals('long data', $this->readBuffer->read(9));
    }
    
    /** @test */
    public function advancesBufferPositionAfterRead()
    {
        $this->readBuffer->append('some other nice data after advance');
        $this->readBuffer->read(10);
        $this->readBuffer->advance(11);

        $this->assertEquals('after advance', $this->readBuffer->read(13));
    }

    /** @test */
    public function reportsIncompleteBufferWhenAdvanceIsLargerThanCurrentData()
    {
        $this->readBuffer->append('some data');

        $this->readBuffer->read(5);

        $this->expectException(IncompleteBufferException::class);

        $this->readBuffer->advance(5);
    }


    /** @test */
    public function defaultReadPositionInBufferIsZero()
    {
        $this->assertEquals(0, $this->readBuffer->currentPosition());
    }
    
    /** @test */
    public function currentPositionIsMovedWithNumberOfReadBytes()
    {
        $this->readBuffer->append('Some very long string data');

        $this->readBuffer->read(4);
        $this->readBuffer->read(6);

        $this->assertEquals(10, $this->readBuffer->currentPosition());
    }
    
    /** @test */
    public function currentPositionIsRelativeToFlushedReadData()
    {
        $this->readBuffer->append('Some very long string data');

        $this->readBuffer->read(10);

        $this->readBuffer->flush();

        $this->readBuffer->read(3);

        $this->assertEquals(3, $this->readBuffer->currentPosition());

    }
}
