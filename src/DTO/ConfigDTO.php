<?php
namespace CryCMS\CURL\DTO;

use CryCMS\CURL\CURL;

/**
 * @property string $location
 * @property string $method
 * @property array $headers
 * @property ?string $cookieFile
 * @property string $userAgent
 * @property string $encoding
 * @property array $data
 * @property int $connectTimeout
 * @property int $timeout
 * @property bool $followLocation
 * @property bool $sslVerify
 * @property bool $returnTransfer
 * @property bool $noBody
 */
class ConfigDTO extends DTO
{
    protected string $location;
    protected string $method = CURL::GET;
    protected array $headers = [];
    protected ?string $cookieFile = null;
    protected string $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322; CryCMS cURL Facade)';
    protected string $encoding = '';
    protected array $data = [];
    protected int $connectTimeout = 300;
    protected int $timeout = 300;
    protected bool $followLocation = true;
    protected bool $sslVerify = true;
    protected bool $returnTransfer = true;
    protected bool $noBody = false;

    public function __construct(array $attributes = [])
    {
        $properties = get_object_vars($this);
        foreach ($properties as $property => $value) {
            if ($property !== 'defaultProperties') {
                $this->defaultProperties[$property] = $value;
            }
        }

        parent::__construct($attributes);
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    public function setHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    public function flushHeaders(): void
    {
        $this->headers = [];
    }

    public function setCookieFile(string $filePath): void
    {
        $this->cookieFile = $filePath;
    }

    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    public function setEncoding(string $encoding): void
    {
        $this->encoding = $encoding;
    }

    public function setData($key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function setConnectTimeout(int $connectTimeout): void
    {
        $this->connectTimeout = $connectTimeout;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function setFollowLocation(bool $followLocation): void
    {
        $this->followLocation = $followLocation;
    }

    public function setSSLVerify(bool $sslVerify): void
    {
        $this->sslVerify = $sslVerify;
    }

    public function setReturnTransfer(bool $returnTransfer): void
    {
        $this->returnTransfer = $returnTransfer;
    }

    public function setNoBody(bool $noBody): void
    {
        $this->noBody = $noBody;
    }
}