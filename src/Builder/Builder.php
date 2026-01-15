<?php
namespace CryCMS\CURL\Builder;

use CryCMS\CURL\DTO\ConfigDTO;

abstract class Builder implements BuilderInterface
{
    protected ConfigDTO $config;

    public function __construct(ConfigDTO $config)
    {
        $this->config = $config;
    }
}