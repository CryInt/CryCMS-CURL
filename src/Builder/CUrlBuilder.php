<?php
namespace CryCMS\Builder;

use CryCMS\DTO\CUrlConfigDTO;

abstract class CUrlBuilder implements CUrlBuilderInterface
{
    protected CUrlConfigDTO $config;

    public function __construct(CUrlConfigDTO $config)
    {
        $this->config = $config;
    }
}