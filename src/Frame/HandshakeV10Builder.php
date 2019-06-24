<?php
/**
 * Copyright © EcomDev B.V. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace EcomDev\MySQLBinaryProtocol\Frame;


class HandshakeV10Builder
{

    /**
     * @var string
     */
    private $serverVersion;
    /**
     * @var int
     */
    private $clientId;

    /**
     * @var string
     */
    private $authData;

    /**
     * @var int
     */
    private $capabilities = 0;

    /**
     * @var int
     */
    private $charset = 0;

    /**
     * @var int
     */
    private $status = 0;

    /**
     * @var string
     */
    private $authPlugin = '';

    public function withServerInfo(string $serverVersion, int $clientId): self
    {
        $builder = clone $this;
        $builder->serverVersion = $serverVersion;
        $builder->clientId = $clientId;
        return $builder;
    }

    public function withAuthData(string $authData): self
    {
        $builder = clone $this;
        $builder->authData = $authData;
        return $builder;
    }

    public function withCapabilities(int $flags): self
    {
        $builder = clone $this;
        $builder->capabilities = $flags;
        return $builder;
    }

    public function withCharset(int $charsetId): self
    {
        $builder = clone $this;

        $builder->charset = $charsetId;
        return $builder;
    }


    public function withStatus(int $status): self
    {
        $builder = clone $this;

        $builder->status = $status;
        return $builder;
    }

    public function withAuthPlugin(string $authPlugin): self
    {
        $builder = clone $this;

        $builder->authPlugin = $authPlugin;

        return $builder;
    }

    public function build(): HandshakeV10
    {
        return new HandshakeV10(
            $this->serverVersion,
            $this->clientId,
            $this->authData,
            $this->capabilities,
            $this->charset,
            $this->status,
            $this->authPlugin
        );
    }



}
