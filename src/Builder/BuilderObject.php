<?php
namespace CryCMS\CURL\Builder;

use CryCMS\CURL\CURL;
use CryCMS\CURL\DTO\ConfigDTO;
use CryCMS\CURL\DTO\ResponseDTO;
use CryCMS\CURL\Part\ContentType;
use CryCMS\CURL\Part\CUrlHelper;
use CryCMS\CURL\Part\HTTPCode;

class BuilderObject extends Builder
{
    protected $curlHandle;

    public function __construct(ConfigDTO $config)
    {
        $this->curlHandle = curl_init();
        parent::__construct($config);
    }

    public function execute(): ResponseDTO
    {
        $this->setInline();

        $location = CUrlHelper::makeLocation($this->config);
        curl_setopt($this->curlHandle, CURLOPT_URL, $location);

        $this->setHeaders();
        $this->setPostData();

        $body = curl_exec($this->curlHandle);
        if ($body === false) {
            $body = null;
        }

        $httpCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($this->curlHandle, CURLINFO_CONTENT_TYPE);

        curl_close($this->curlHandle);

        return new ResponseDTO([
            'location' => $location,
            'method' => $this->config->method,
            'isSuccess' => $httpCode >= 200 && $httpCode < 300,
            'httpCode' => $httpCode,
            'httpCodeText' => HTTPCode::LIST[$httpCode] ?? '',
            'contentType' => $contentType,
            'body' => !$this->config->noBody ? $body : null,
        ]);
    }

    protected function setInline(): void
    {
        curl_setopt($this->curlHandle, CURLOPT_HEADER, false);
        curl_setopt($this->curlHandle, CURLOPT_USERAGENT, $this->config->userAgent);
        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, $this->config->returnTransfer);
        curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION, $this->config->followLocation);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYHOST, $this->config->sslVerify ? 2 : 0);
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, $this->config->sslVerify);
        curl_setopt($this->curlHandle, CURLOPT_TIMEOUT, $this->config->timeout);
        curl_setopt($this->curlHandle, CURLOPT_CONNECTTIMEOUT, $this->config->connectTimeout);
        curl_setopt($this->curlHandle, CURLOPT_CUSTOMREQUEST, $this->config->method);
        curl_setopt($this->curlHandle, CURLOPT_NOBODY, $this->config->noBody);
    }

    protected function setHeaders(): void
    {
        if (empty($this->config->headers)) {
            return;
        }

        $headers = [];
        foreach ($this->config->headers as $key => $value) {
            $headers[] = $key . ': ' . $value;
        }

        curl_setopt($this->curlHandle, CURLOPT_HTTPHEADER, $headers);
    }

    protected function setPostData(): void
    {
        if ($this->config->method !== CUrl::POST) {
            return;
        }

        curl_setopt($this->curlHandle, CURLOPT_POST, true);

        if (!empty($this->config->headers['Content-Type']) && $this->config->headers['Content-Type'] === ContentType::APPLICATION_JSON) {
            $data = json_encode($this->config->data, JSON_UNESCAPED_UNICODE);
            curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $data);
            return;
        }

        if (!empty($this->config->headers['Content-Type']) && $this->config->headers['Content-Type'] === ContentType::APPLICATION_X_WWW_FORM_URLENCODED) {
            curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, http_build_query($this->config->data));
            return;
        }

        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $this->config->data);
    }
}