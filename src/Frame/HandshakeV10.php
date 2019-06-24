<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol\Frame;


class HandshakeV10
{
    public $serverVersion;

    public $connectionId;

    public $authData;

    public $capabilities;

    public $charset;

    public $status;

    public $authPlugin;

    public function __construct(
        string $serverVersion,
        int $connectionId,
        string $authData,
        int $capabilities,
        int $charset = 0,
        int $status = 0,
        string $authPlugin = ''
    )
    {
        $this->serverVersion = $serverVersion;
        $this->connectionId = $connectionId;
        $this->authData = $authData;
        $this->capabilities = $capabilities;
        $this->charset = $charset;
        $this->status = $status;
        $this->authPlugin = $authPlugin;
    }


}
