<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol;


use EcomDev\MySQLBinaryProtocol\Frame\HandshakeV10Builder;

class HandshakeParser
{

    /**
     * @var HandshakeV10Builder
     */
    private $frameBuilder;
    /**
     * @var callable
     */
    private $frameReceiver;

    public function __construct(HandshakeV10Builder $frameBuilder, callable $frameReceiver)
    {

        $this->frameBuilder = $frameBuilder;
        $this->frameReceiver = $frameReceiver;
    }

    public function __invoke(PayloadReader $reader)
    {
        $reader->readFixedInteger(1);

        $frameBuilder = $this->frameBuilder->withServerInfo(
            $reader->readNullTerminatedString(),
            $reader->readFixedInteger(4)
        );

        $authData = $reader->readFixedString(8);

        $reader->readFixedString(1);

        $capabilities = $reader->readFixedInteger(2);
        $frameBuilder = $frameBuilder->withCharset($reader->readFixedInteger(1))
            ->withStatus($reader->readFixedInteger(2));

        $capabilities += $reader->readFixedInteger(2) << 16;

        $frameBuilder = $frameBuilder->withCapabilities($capabilities);

        $totalAuthDataLenght = $reader->readFixedInteger(1);

        $reader->readFixedString(10);

        $authData .= $reader->readFixedString($totalAuthDataLenght - 8);

        $frameBuilder = $frameBuilder->withAuthData($authData);

        $authPlugin = $reader->readNullTerminatedString();

        ($this->frameReceiver)($frameBuilder->withAuthPlugin($authPlugin)->build());
    }
}
