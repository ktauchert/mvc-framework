<?php

namespace Framework\Core;

class Config
{
    private static array $config = [];

    public static function Load(array $config): void
    {
        self::$config = $config;
    }

    public static function Get(?string $key = null, $default = null)
    {
        // If no key is provided, return the entire configuration array
        if ($key === null) {
            return self::$config;
        }

        // Split the key by dots to support nested configuration values
        $value = self::$config;
        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }
}
