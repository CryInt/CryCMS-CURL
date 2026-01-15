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
        $query = CUrl::get('https://postman-echo.com/headers')
            ->authorizationBearer('TOKEN-123')
            ->userAgent('Just Agent');

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        try {
            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertIsArray($data['headers']);

        $this->assertArrayHasKey('authorization', $data['headers']);
        $this->assertEquals('Bearer TOKEN-123', $data['headers']['authorization']);

        $this->assertArrayHasKey('user-agent', $data['headers']);
        $this->assertEquals('Just Agent', $data['headers']['user-agent']);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($response->body, $responseBash);
    }

    public function testCode(): void
    {
        foreach (self::CODE_TEST_LIST as $code) {
            $query = CUrl::code('https://postman-echo.com/status/' . $code, 10);
            $response = $query->send();

            $this->assertIsBool($response->isSuccess);
            $this->assertEquals($code, $response->httpCode);
            $this->assertEquals(HTTPCode::LIST[$code] ?? '', $response->httpCodeText);

            $bash = $query->bash(true);
            $responseBash = shell_exec($bash);
            $this->assertNotEmpty($responseBash);
            $this->assertEquals($response->httpCode, $responseBash);
        }
    }

    public function testGet(): void
    {
        $query = CUrl::get('https://postman-echo.com/get')
            ->data('test', '123')
            ->data('array', [1, 2, 3]);

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['args']);

        $this->assertArrayHasKey('test', $data['args']);
        $this->assertEquals('123', $data['args']['test']);

        $this->assertArrayHasKey('array', $data['args']);
        $this->assertEquals([1, 2, 3], $data['args']['array']);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($response->body, $responseBash);
    }

    public function testPost(): void
    {
        $query = CUrl::post('https://postman-echo.com/post')
            ->data('test', '123');

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['form']);
        $this->assertArrayHasKey('test', $data['form']);
        $this->assertEquals('123', $data['form']['test']);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($this->removeDynamicResponse($response->body), $this->removeDynamicResponse($responseBash));
    }

    public function testPostFormUrlencoded(): void
    {
        $query = CUrl::post('https://postman-echo.com/post')
            ->data('test', '123')
            ->header('Content-Type', ContentType::APPLICATION_X_WWW_FORM_URLENCODED);

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['form']);
        $this->assertArrayHasKey('test', $data['form']);
        $this->assertEquals('123', $data['form']['test']);
        $this->assertEquals(ContentType::APPLICATION_X_WWW_FORM_URLENCODED, $data['headers']['content-type']);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($response->body, $responseBash);
    }

    public function testJson(): void
    {
        $query = CUrl::json('https://postman-echo.com/post')
            ->data('test', '123');

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['data']);
        $this->assertEquals(['test' => '123'], $data['data']);
        $this->assertEquals(ContentType::APPLICATION_JSON, $data['headers']['content-type']);
        $this->assertStringContainsString(ContentType::APPLICATION_JSON, $response->contentType);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($response->body, $responseBash);
    }

    public function testFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'TestFile_') . '.txt';
        file_put_contents($tmpFile, '1234');

        $query = CUrl::post('https://postman-echo.com/post')
            ->data([
                'field1' => 'V1',
                'field2' => 'V2',
            ])
            ->file('file', $tmpFile);

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['files']);
        $this->assertArrayHasKey(basename($tmpFile), $data['files']);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($this->removeDynamicResponse($response->body), $this->removeDynamicResponse($responseBash));
    }

    public function testSSL(): void
    {
        $query = CUrl::code('https://expired-rsa-dv.ssl.com', 10);
        $response = $query->send();
        $this->assertFalse($response->isSuccess);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($response->httpCode, $responseBash);

        $query = CUrl::code('https://expired-rsa-dv.ssl.com', 10)->ssl(false);
        $response = $query->send();
        $this->assertTrue($response->isSuccess);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($response->httpCode, $responseBash);
    }

    protected function removeDynamicResponse(string $content): string
    {
        $content = preg_replace('/"content-length":"\d+"/', '"content-length":"~"', $content);
        return preg_replace('/=-{4,}[a-z0-9]+/', '', $content);
    }
}