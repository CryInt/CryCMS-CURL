<?php
namespace CryCMS;

use CryCMS\DTO\CURLResponseDTO;
use CURLFile;

class CURL
{
    protected string $location;
    protected string $method = self::GET;
    protected array $headers = [];
    protected string $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322; CryCMS cURL Facade)';
    protected array $data = [];
    protected int $connectTimeout = 30;
    protected int $timeout = 30;
    protected bool $followLocation = true;
    protected bool $sslVerify = true;
    protected bool $returnTransfer = true;
    protected bool $noBody = false;

    public const GET = 'GET';
    public const POST = 'POST';

    protected function __construct(string $location, string $method = self::GET)
    {
        $this->location = $location;
        $this->method = $method;
    }

    /** @noinspection PhpUnused */
    public static function get(string $location): self
    {
        return new self($location);
    }

    /** @noinspection PhpUnused */
    public static function post(string $location): self
    {
        return new self($location, self::POST);
    }

    /** @noinspection PhpUnused */
    public static function json(string $location): self
    {
        $instance = new self($location, self::POST);
        $instance->header('Content-Type', ContentType::APPLICATION_JSON);
        return $instance;
    }

    /** @noinspection PhpUnused */
    public static function code(string $location, ?int $timeoutSeconds = null): self
    {
        $instance = new self($location, self::GET);
        $instance->headers = [];
        $instance->noBody = true;
        $instance->followLocation = false;
        $instance->returnTransfer = false;

        if ($timeoutSeconds !== null) {
            $instance->connectTimeout = $timeoutSeconds;
            $instance->timeout($timeoutSeconds);
        }

        return $instance;
    }

    /** @noinspection PhpUnused */
    public function data($key, $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
            return $this;
        }

        $this->data[$key] = $value;
        return $this;
    }

    public function file(string $field, string $filePath, ?string $fileName = null): self
    {
        $cFile = new CURLFile($filePath, mime_content_type($filePath), $fileName);
        $this->data[$field] = $cFile;
        $this->header('Content-Type', ContentType::MULTIPART_FORM_DATA);

        return $this;
    }

    /** @noinspection PhpUnused */
    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /** @noinspection PhpUnused */
    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /** @noinspection PhpUnused */
    public function connectTimeout(int $seconds): self
    {
        $this->connectTimeout = $seconds;
        return $this;
    }

    /** @noinspection PhpUnused */
    public function authorizationBearer(string $token): self
    {
        $this->header('Authorization', 'Bearer ' . $token);
        return $this;
    }

    public function send(): CURLResponseDTO
    {
        $ch = curl_init();

        $location = $this->makeLocation();

        curl_setopt($ch, CURLOPT_URL, $location);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->returnTransfer);

        if (!empty($this->headers)) {
            $headers = [];
            foreach ($this->headers as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($this->followLocation) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }

        if ($this->sslVerify === false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);

        if ($this->method === self::POST) {
            curl_setopt($ch, CURLOPT_POST, true);

            $data = $this->data;
            if (!empty($this->headers['Content-Type']) && $this->headers['Content-Type'] === ContentType::APPLICATION_JSON) {
                $data = json_encode($data, JSON_UNESCAPED_UNICODE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            elseif(!empty($this->headers['Content-Type']) && $this->headers['Content-Type'] === ContentType::APPLICATION_X_WWW_FORM_URLENCODED) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
            else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        if ($this->noBody) {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }

        $body = curl_exec($ch);
        if ($body === false) {
            $body = null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        curl_close($ch);

        return new CURLResponseDTO([
            'location' => $location,
            'method' => $this->method,
            'isSuccess' => $httpCode >= 200 && $httpCode < 300,
            'httpCode' => $httpCode,
            'httpCodeText' => HTTPCode::LIST[$httpCode] ?? '',
            'contentType' => $contentType,
            'body' => !$this->noBody ? $body : null,
        ]);
    }

    protected function makeLocation(): string
    {
        if ($this->method === self::GET) {
            $urlParts = parse_url($this->location);
            if (isset($urlParts['query'])) {
                parse_str($urlParts['query'], $params);
            } else {
                $params = [];
            }

            $params = array_merge($params, $this->data);

            $urlParts['query'] = http_build_query($params);

            return $this->makeUrl($urlParts);
        }

        return $this->location;
    }

    protected function makeUrl(array $parts): string
    {
        $result = [];

        $result[] = $parts['scheme'] . '://' . $parts['host'];

        if (!empty($parts['port']) && $parts['port'] !== 80 && $parts['port'] !== 443) {
            $result[] = ':' . $parts['port'];
        }

        if (!empty($parts['path'])) {
            $result[] = $parts['path'];
        }

        if (!empty($parts['query'])) {
            $result[] = '?' . $parts['query'];
        }

        return implode('', $result);
    }
}