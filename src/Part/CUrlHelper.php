<?php
namespace CryCMS\Part;

use CryCMS\CUrl;
use CryCMS\DTO\CUrlConfigDTO;

class CUrlHelper
{
    public static function makeLocation(CUrlConfigDTO $config): string
    {
        if ($config->method === CUrl::GET) {
            $urlParts = parse_url($config->location);
            if (isset($urlParts['query'])) {
                parse_str($urlParts['query'], $params);
            } else {
                $params = [];
            }

            $params = array_merge($params, $config->data);

            $urlParts['query'] = http_build_query($params);

            return self::makeUrl($urlParts);
        }

        return $config->location;
    }

    protected static function makeUrl(array $parts): string
    {
        $result = [];

        $result[] = $parts['scheme'] . '://' . $parts['host'];

        if (!empty($parts['port']) && $parts['port'] !== 80 && $parts['port'] !== 443) {
            $result[] = ':' . $parts['port'];
        }

        if (!empty($parts['path'])) {
            $result[] = $parts['path'];
        }

        if (!empty($parts['query'])) {
            $result[] = '?' . $parts['query'];
        }

        return implode('', $result);
    }
}