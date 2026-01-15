<?php
namespace CryCMS\Builder;

use CryCMS\CUrl;
use CryCMS\DTO\CUrlConfigDTO;
use CryCMS\Part\ContentType;
use CryCMS\Part\CUrlHelper;
use CURLFile;

class CUrlBuilderBash extends CUrlBuilder
{
    protected array $content = [];

    public function __construct(CUrlConfigDTO $config)
    {
        $location = CUrlHelper::makeLocation($config);
        $this->content[] = "curl '" . $location . "'";

        parent::__construct($config);
    }

    public function execute(bool $full = false): string
    {
        $this->setInline($full);
        $this->setHeaders();
        $this->setPostData();

        return implode(" \\" . PHP_EOL . "  ", $this->content) . PHP_EOL;
    }

    protected function setInline(bool $full = false): void
    {
        if ($full) {
            $this->content[] = "--user-agent '" . $this->config->userAgent . "'";
        }

        if ($this->checkPropertyChange('followLocation') === false) {
            $this->content[] = "--location";
        }

        if ($this->checkPropertyChange('sslVerify')) {
            $this->content[] = "--insecure";
        }

        if ($this->checkPropertyChange('timeout')) {
            $this->content[] = "--max-time " . $this->config->timeout;
        }

        if ($this->checkPropertyChange('connectTimeout')) {
            $this->content[] = "--connect-timeout " . $this->config->connectTimeout;
        }

        if ($full || $this->checkPropertyChange('method')) {
            $this->content[] = "--request '" . $this->config->method . "'";
        }

        if ($this->checkPropertyChange('noBody')) {
            $this->content[] = "--silent --output /dev/null";
            $this->content[] = "--write-out '%{http_code}'";
        }

        if ($full) {
            $this->content[] = "--no-progress-meter";
        }
    }

    protected function setHeaders(): void
    {
        if (empty($this->config->headers)) {
            return;
        }

        foreach ($this->config->headers as $key => $value) {
            $this->content[] = "--header '" . $key . ": " . $value . "'";
        }
    }

    protected function setPostData(): void
    {
        if ($this->config->method !== CUrl::POST) {
            return;
        }

        if (empty($this->config->data)) {
            return;
        }

        if (
            !empty($this->config->headers['Content-Type']) &&
            $this->config->headers['Content-Type'] === ContentType::APPLICATION_JSON
        ) {
            $data = json_encode($this->config->data, JSON_UNESCAPED_UNICODE);
            $this->content[] = "--data '" . $data . "'";
            return;
        }

        if (
            !empty($this->config->headers['Content-Type']) &&
            $this->config->headers['Content-Type'] === ContentType::APPLICATION_X_WWW_FORM_URLENCODED
        ) {
            foreach ($this->config->data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $this->content[] = "--data-urlencode '" . urlencode($key) . "[" . $k . "]=" . $v . "'";
                    }

                    continue;
                }

                if ($value instanceof CURLFile) {
                    $this->content[] = "--data-urlencode '" . urlencode($key) . "@" . $value->getFilename() . "'";
                    continue;
                }

                $this->content[] = "--data-urlencode '" . urlencode($key) . "=" . $value . "'";
            }

            return;
        }

        foreach ($this->config->data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->content[] = "--form '" . $key . "[" . $k . "]=" . $v . "'";
                }

                continue;
            }

            if ($value instanceof CURLFile) {
                $parts = [];
                $parts[] = $key . "=@" . $value->getFilename();
                $parts[] = "type=" . $value->getMimeType();
                if (!empty($value->getPostFilename())) {
                    $parts[] = "filename=" . $value->getPostFilename();
                }
                $this->content[] = "--form '" . implode(';', $parts) . "'";
                continue;
            }

            $this->content[] = "--form '" . $key . "=" . $value . "'";
        }
    }

    protected function checkPropertyChange(string $property): bool
    {
        return array_key_exists($property, $this->config->defaultProperties) &&
            $this->config->defaultProperties[$property] !== $this->config->$property;
    }
}