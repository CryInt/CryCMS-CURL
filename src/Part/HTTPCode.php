<?php
namespace CryCMS\CURL\Part;

class HTTPCode
{
    public const LIST = [
        self::PROCESSING => "Processing",

        self::OK => "OK",
        self::CREATED => "Created",
        self::ACCEPTED => "Accepted",
        self::PARTIAL_CONTENT => "Partial Content",

        self::MOVED_PERMANENTLY => "Moved Permanently",
        self::FOUND => "Found",
        self::NOT_MODIFIED => "Not Modified",
        self::TEMPORARY_REDIRECT => "Temporary Redirect",
        self::PERMANENT_REDIRECT => "Permanent Redirect",

        self::BAD_REQUEST => "Bad Request",
        self::UNAUTHORIZED => "Unauthorized",
        self::FORBIDDEN => "Forbidden",
        self::NOT_FOUND => "Not Found",
        self::METHOD_NOT_ALLOWED => "Method Not Allowed",
        self::NOT_ACCEPTABLE => "Not Acceptable",
        self::REQUEST_TIMEOUT => "Request Timeout",
        self::CONFLICT => "Conflict",
        self::REQUEST_ENTITY_TOO_LARGE => "Request Entity Too Large",
        self::UNSUPPORTED_MEDIA_TYPE => "Unsupported Media Type",

        self::INTERNAL_SERVER_ERROR => "Internal Server Error",
        self::NOT_IMPLEMENTED => "Not Implemented",
        self::BAD_GATEWAY => "Bad Gateway",
        self::SERVICE_UNAVAILABLE => "Service Unavailable",
    ];

    public const PROCESSING = 102;

    public const OK = 200;
    public const CREATED = 201;
    public const ACCEPTED = 202;
    public const PARTIAL_CONTENT = 206;

    public const MOVED_PERMANENTLY = 301;
    public const FOUND = 302;
    public const NOT_MODIFIED = 304;
    public const TEMPORARY_REDIRECT = 307;
    public const PERMANENT_REDIRECT = 308;

    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const NOT_ACCEPTABLE = 406;
    public const REQUEST_TIMEOUT = 408;
    public const CONFLICT = 409;
    public const REQUEST_ENTITY_TOO_LARGE = 413;
    public const UNSUPPORTED_MEDIA_TYPE = 415;

    public const INTERNAL_SERVER_ERROR = 500;
    public const NOT_IMPLEMENTED = 501;
    public const BAD_GATEWAY = 502;
    public const SERVICE_UNAVAILABLE = 503;
}