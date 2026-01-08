<?php
namespace CryCMS\DTO;

class CURLResponseDTO extends DTO
{
    public string $location;
    public string $method;
    public bool $isSuccess;
    public int $httpCode;
    public string $httpCodeText = '';
    public string $contentType;
    public ?string $body = null;
}