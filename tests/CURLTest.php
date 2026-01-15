<?php
declare(strict_types=1);

use CryCMS\CURL\CURL;
use CryCMS\CURL\Part\ContentType;
use CryCMS\CURL\Part\HTTPCode;
use PHPUnit\Framework\TestCase;

final class CURLTest extends TestCase
{
    protected static string $cookieFile;
    protected static string $cookieFileBash;

    protected const CODE_TEST_LIST = [
        HTTPCode::OK,
        HTTPCode::BAD_REQUEST,
        HTTPCode::FORBIDDEN,
        HTTPCode::NOT_FOUND,
    ];

    public static function setUpBeforeClass(): void
    {
        self::$cookieFile = tempnam(sys_get_temp_dir(), 'TestCookie_') . '.txt';
        self::$cookieFileBash = tempnam(sys_get_temp_dir(), 'TestCookie_') . '.txt';
        parent::setUpBeforeClass();
    }

    public function testCookieFileSet(): void
    {
        $query = CURL::get('https://postman-echo.com/cookies/set')
            ->cookieFile(self::$cookieFile)
            ->data(['foo1' => 'bar1', 'foo2' => 'bar2']);

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);
        $this->assertFileExists(self::$cookieFile);

        try {
            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertIsArray($data['cookies']);
        $this->assertArrayHasKey('foo1', $data['cookies']);
        $this->assertEquals('bar1', $data['cookies']['foo1']);
        $this->assertArrayHasKey('foo2', $data['cookies']);
        $this->assertEquals('bar2', $data['cookies']['foo2']);

        $bash = $query->cookieFile(self::$cookieFileBash)->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertFileExists(self::$cookieFileBash);
        $this->assertEquals($this->removeDynamicResponse($response->body), $this->removeDynamicResponse($responseBash));
    }

    public function testCookieFileGet(): void
    {
        $query = CURL::get('https://postman-echo.com/cookies')
            ->cookieFile(self::$cookieFile);

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        try {
            $data = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertIsArray($data['cookies']);
        $this->assertArrayHasKey('foo1', $data['cookies']);
        $this->assertEquals('bar1', $data['cookies']['foo1']);
        $this->assertArrayHasKey('foo2', $data['cookies']);
        $this->assertEquals('bar2', $data['cookies']['foo2']);

        $bash = $query->cookieFile(self::$cookieFileBash)->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertFileExists(self::$cookieFileBash);
        $this->assertEquals($this->removeDynamicResponse($response->body), $this->removeDynamicResponse($responseBash));
    }

    public function testHeaders(): void
    {
        $query = CURL::get('https://postman-echo.com/headers')
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
            $query = CURL::code('https://postman-echo.com/status/' . $code, 10);
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
        $query = CURL::get('https://postman-echo.com/get')
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
        $query = CURL::post('https://postman-echo.com/post')
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

    public function testPut(): void
    {
        $query = CURL::put('https://postman-echo.com/put')
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

    public function testPatch(): void
    {
        $query = CURL::patch('https://postman-echo.com/patch?id=69')
            ->data('test', '123');

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['args']);

        $this->assertArrayHasKey('id', $data['args']);
        $this->assertEquals('69', $data['args']['id']);

        $this->assertIsArray($data['form']);
        $this->assertArrayHasKey('test', $data['form']);
        $this->assertEquals('123', $data['form']['test']);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($this->removeDynamicResponse($response->body), $this->removeDynamicResponse($responseBash));
    }

    public function testDelete(): void
    {
        $query = CURL::delete('https://postman-echo.com/delete?id=1')
            ->data('test', '444');

        $response = $query->send();

        $this->assertEquals(HTTPCode::OK, $response->httpCode);
        $this->assertNotEmpty($response->body);
        $this->assertIsString($response->body);

        $data = json_decode($response->body, true);

        $this->assertIsArray($data['args']);

        $this->assertArrayHasKey('id', $data['args']);
        $this->assertEquals('1', $data['args']['id']);

        $this->assertIsArray($data['form']);
        $this->assertArrayHasKey('test', $data['form']);
        $this->assertEquals('444', $data['form']['test']);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($this->removeDynamicResponse($response->body), $this->removeDynamicResponse($responseBash));
    }

    public function testPostFormUrlencoded(): void
    {
        $query = CURL::post('https://postman-echo.com/post')
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
        $query = CURL::json('https://postman-echo.com/post')
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

        $query = CURL::post('https://postman-echo.com/post')
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
        $query = CURL::code('https://expired-rsa-dv.ssl.com', 10);
        $response = $query->send();
        $this->assertFalse($response->isSuccess);

        $bash = $query->bash(true);
        $responseBash = shell_exec($bash);
        $this->assertNotEmpty($responseBash);
        $this->assertEquals($response->httpCode, $responseBash);

        $query = CURL::code('https://expired-rsa-dv.ssl.com', 10)->ssl(false);
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
        $content = preg_replace('/"_cfuvid":"[0-9a-zA-Z.\\-_]+"/', '"_cfuvid":"~"', $content);
        $content = preg_replace('/"__cf_bm":"[0-9a-zA-Z.\\-_]+"/', '"__cf_bm":"~"', $content);
        return preg_replace('/=-{4,}[a-z0-9]+/', '', $content);
    }
}