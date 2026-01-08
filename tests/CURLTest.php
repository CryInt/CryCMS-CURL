<?php
declare(strict_types=1);

use CryCMS\ContentType;
use CryCMS\CURL;
use CryCMS\HTTPCode;
use PHPUnit\Framework\TestCase;

final class CURLTest extends TestCase
{
    protected const CODE_TEST_LIST = [
        HTTPCode::OK,
        HTTPCode::BAD_REQUEST,
        HTTPCode::FORBIDDEN,
        HTTPCode::NOT_FOUND,
    ];

    public function testCode(): void
    {
        foreach (self::CODE_TEST_LIST as $code) {
            $response = CURL::code('https://postman-echo.com/status/' . $code, 10)->send();
            $this->assertEquals($code, $response->httpCode);
        }
    }

    public function testGet(): void
    {
        $response = CURL::get('https://postman-echo.com/get')
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
        $response = CURL::post('https://postman-echo.com/post')
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
        $response = CURL::post('https://postman-echo.com/post')
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
        $response = CURL::json('https://postman-echo.com/post')
            ->data('test', '123')
            ->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['data']);
        $this->assertEquals(['test' => '123'], $data['data']);
        $this->assertEquals(ContentType::APPLICATION_JSON, $data['headers']['content-type']);
    }

    public function testFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'TestFile_') . '.txt';
        file_put_contents($tmpFile, '1234');

        $response = CURL::post('https://postman-echo.com/post')
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
}