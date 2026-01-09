<?php
namespace CryCMS\DTO;

class CUrlResponseDTO extends CUrlDTO
{
    public string $location;
    public string $method;
    public bool $isSuccess;
    public int $httpCode;
    public string $httpCodeText = '';
    public string $contentType;
    public ?string $body = null;
}