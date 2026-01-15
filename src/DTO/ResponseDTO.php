<?php
namespace CryCMS\CURL\DTO;

class ResponseDTO extends DTO
{
    public string $location;
    public string $method;
    public bool $isSuccess;
    public int $httpCode;
    public string $httpCodeText = '';
    public string $contentType;
    public ?string $body = null;
}