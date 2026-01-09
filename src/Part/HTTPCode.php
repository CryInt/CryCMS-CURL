<?php
namespace CryCMS\Part;

class HTTPCode
{
    const LIST = [
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

    const PROCESSING = 102;

    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const PARTIAL_CONTENT = 206;

    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const NOT_MODIFIED = 304;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENT_REDIRECT = 308;

    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 409;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const UNSUPPORTED_MEDIA_TYPE = 415;

    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
}