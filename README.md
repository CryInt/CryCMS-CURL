# Facade for PHP cURL

### Page status code 
```php
$response = CURL::code('https://postman-echo.com/status/404', 10)->send();
```

### GET query

```php
$response = CURL::get('https://postman-echo.com/get')
    ->data('test', '123')
    ->data('array', [1, 2, 3])
    ->send();
```

### POST query

```php
$response = CURL::post('https://postman-echo.com/post')
    ->data('test', '123')
    ->send();
```

### POST query as x-www-form-urlencoded

```php
$response = CURL::post('https://postman-echo.com/post')
    ->data('test', '123')
    ->header('Content-Type', ContentType::APPLICATION_X_WWW_FORM_URLENCODED)
    ->send();
```

### POST with file

```php
$tmpFile = tempnam(sys_get_temp_dir(), 'File_') . '.txt';
file_put_contents($tmpFile, 'File content');

$response = CURL::post('https://postman-echo.com/post')
    ->data([
        'field1' => 'V1',
        'field2' => 'V2',
    ])
    ->file('file', $tmpFile)
    ->send();
```

### JSON query

```php
$response = CURL::json('https://postman-echo.com/post')
    ->data('test', '123')
    ->send();
```

### Add Authorization Bearer

```php
$response = CURL::get('https://postman-echo.com/get')
    ->authorizationBearer('token')
    ->send();
```

### Method Send() return always response as an DTO object

```php
CURLResponseDTO Object
(
    [location] => <query location>
    [method] => <used method>
    [isSuccess] => <true or false>
    [httpCode] => <answer http code> 
    [httpCodeText] => <text representation of the response code>
    [contentType] => <answer content type>
    [body] => <response body>
)
```

### Method Bash() return string for use in CLI
```php
$bash = CUrl::post('https://postman-echo.com/post')
    ->data([
        'field1' => 'V1',
        'field2' => 'V2',
    ])
    ->file('file', $tmpFile)
    ->bash();
```

```bash
curl 'https://postman-echo.com/post' \
  --location \
  --request 'POST' \
  --header 'Content-Type: multipart/form-data' \
  --form 'field1=V1' \
  --form 'field2=V2' \
  --form 'file=@/tmp/TestFile_YYj8rW.txt;type=text/plain'
```

### UnitTest

```bash
$ ./vendor/bin/phpunit
```