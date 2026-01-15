<?php
namespace CryCMS\CURL;

use CryCMS\CURL\Builder\BuilderBash;
use CryCMS\CURL\Builder\BuilderObject;
use CryCMS\CURL\DTO\ConfigDTO;
use CryCMS\CURL\DTO\ResponseDTO;
use CryCMS\CURL\Part\ContentType;
use CURLFile;

class CURL
{
    public const GET = 'GET';
    public const POST = 'POST';

    protected ConfigDTO $config;

    protected function __construct(string $location, string $method = self::GET)
    {
        $this->config = new ConfigDTO();
        $this->config->setLocation($location);
        $this->config->setMethod($method);
    }

    public static function get(string $location): self
    {
        return new self($location);
    }

    public static function post(string $location): self
    {
        return new self($location, self::POST);
    }

    public static function json(string $location): self
    {
        $instance = new self($location, self::POST);
        $instance->header('Content-Type', ContentType::APPLICATION_JSON);
        return $instance;
    }

    public static function code(string $location, ?int $timeoutSeconds = null): self
    {
        $instance = new self($location, self::GET);
        $instance->config->flushHeaders();
        $instance->config->setNoBody(true);
        $instance->config->setFollowLocation(false);
        $instance->config->setReturnTransfer(false);

        if ($timeoutSeconds !== null) {
            $instance->connectTimeout($timeoutSeconds);
            $instance->timeout($timeoutSeconds);
        }

        return $instance;
    }

    public function data($key, $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->config->setData($k, $v);
            }
            return $this;
        }

        $this->config->setData($key, $value);
        return $this;
    }

    public function file(string $field, string $filePath, ?string $fileName = null): self
    {
        $cFile = new CURLFile($filePath, mime_content_type($filePath), $fileName);
        $this->config->setData($field, $cFile);
        $this->config->setHeader('Content-Type', ContentType::MULTIPART_FORM_DATA);
        return $this;
    }

    public function header(string $key, string $value): self
    {
        $this->config->setHeader($key, $value);
        return $this;
    }

    public function timeout(int $seconds): self
    {
        $this->config->setTimeout($seconds);
        return $this;
    }

    public function connectTimeout(int $seconds): self
    {
        $this->config->setConnectTimeout($seconds);
        return $this;
    }

    public function ssl(bool $ssl): self
    {
        $this->config->setSSLVerify($ssl);
        return $this;
    }

    public function authorizationBearer(string $token): self
    {
        $this->config->setHeader('Authorization', 'Bearer ' . $token);
        return $this;
    }

    public function userAgent(string $userAgent): self
    {
        $this->config->setUserAgent($userAgent);
        return $this;
    }

    public function send(): ResponseDTO
    {
        return (new BuilderObject($this->config))->execute();
    }

    public function bash(bool $full = false): string
    {
        return (new BuilderBash($this->config))->execute($full);
    }
}