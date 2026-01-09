<?php
namespace CryCMS\Builder;

use CryCMS\DTO\CUrlConfigDTO;
use CryCMS\Part\CUrlHelper;

class CUrlBuilderBash extends CUrlBuilder
{
    protected array $content = [];

    public function __construct(CUrlConfigDTO $config)
    {
        $this->content[] = "curl";
        parent::__construct($config);
    }

    public function execute(): string
    {
        $location = CUrlHelper::makeLocation($this->config);
        $this->content[] = "--location '" . $location . "'";

        $this->content[] = "--request '" . $this->config->method . "'";

        return implode(" \\" . PHP_EOL . "  ", $this->content) . PHP_EOL;
    }
}