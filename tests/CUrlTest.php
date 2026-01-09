<?php
declare(strict_types=1);

use CryCMS\CUrl;
use CryCMS\Part\ContentType;
use CryCMS\Part\HTTPCode;
use PHPUnit\Framework\TestCase;

final class CUrlTest extends TestCase
{
    protected const CODE_TEST_LIST = [
        HTTPCode::OK,
        HTTPCode::BAD_REQUEST,
        HTTPCode::FORBIDDEN,
        HTTPCode::NOT_FOUND,
    ];

    public function testHeaders(): void
    {
        $response = CUrl::get('https://postman-echo.com/headers')
            ->authorizationBearer('TOKEN-123')
            ->userAgent('Just Agent')
            ->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['headers']);

        $this->assertArrayHasKey('authorization', $data['headers']);
        $this->assertEquals('Bearer TOKEN-123', $data['headers']['authorization']);

        $this->assertArrayHasKey('user-agent', $data['headers']);
        $this->assertEquals('Just Agent', $data['headers']['user-agent']);
    }

    public function testCode(): void
    {
        foreach (self::CODE_TEST_LIST as $code) {
            $response = CUrl::code('https://postman-echo.com/status/' . $code, 10)->send();
            $this->assertIsBool($response->isSuccess);
            $this->assertEquals($code, $response->httpCode);
            $this->assertEquals(HTTPCode::LIST[$code] ?? '', $response->httpCodeText);
        }
    }

    public function testGet(): void
    {
        $response = CUrl::get('https://postman-echo.com/get')
            ->data('test', '123')
            ->data('array', [1, 2, 3])
            ->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['args']);

        $this->assertArrayHasKey('test', $data['args']);
        $this->assertEquals('123', $data['args']['test']);

        $this->assertArrayHasKey('array', $data['args']);
        $this->assertEquals([1, 2, 3], $data['args']['array']);
    }

    public function testPost(): void
    {
        $response = CUrl::post('https://postman-echo.com/post')
            ->data('test', '123')
            ->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['form']);
        $this->assertArrayHasKey('test', $data['form']);
        $this->assertEquals('123', $data['form']['test']);
    }

    public function testPostFormUrlencoded(): void
    {
        $response = CUrl::post('https://postman-echo.com/post')
            ->data('test', '123')
            ->header('Content-Type', ContentType::APPLICATION_X_WWW_FORM_URLENCODED)
            ->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['form']);
        $this->assertArrayHasKey('test', $data['form']);
        $this->assertEquals('123', $data['form']['test']);
        $this->assertEquals(ContentType::APPLICATION_X_WWW_FORM_URLENCODED, $data['headers']['content-type']);
    }

    public function testJson(): void
    {
        $response = CUrl::json('https://postman-echo.com/post')
            ->data('test', '123')
            ->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['data']);
        $this->assertEquals(['test' => '123'], $data['data']);
        $this->assertEquals(ContentType::APPLICATION_JSON, $data['headers']['content-type']);
        $this->assertStringContainsString(ContentType::APPLICATION_JSON, $response->contentType);
    }

    public function testFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'TestFile_') . '.txt';
        file_put_contents($tmpFile, '1234');

        $response = CUrl::post('https://postman-echo.com/post')
            ->data([
                'field1' => 'V1',
                'field2' => 'V2',
            ])
            ->file('file', $tmpFile)
            ->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['files']);
        $this->assertArrayHasKey(basename($tmpFile), $data['files']);
    }

    public function testSSL(): void
    {
        $response = CUrl::code('https://expired-rsa-dv.ssl.com', 10)->send();
        $this->assertFalse($response->isSuccess);

        $response = CUrl::code('https://expired-rsa-dv.ssl.com', 10)->ssl(false)->send();
        $this->assertTrue($response->isSuccess);
    }
}