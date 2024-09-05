<?php declare(strict_types=1);

namespace Alanrogers\ImgproxyPhpClient;

use Alanrogers\ImgproxyPhpClient\exceptions\InvalidParameters;

class ImageClientFactory
{
    private static ?ImageClient $instance = null;

    public static function getInstance(?string $key=null, ?string $salt=null): ImageClient
    {
        if (self::$instance === null) {
            if ($key === null || $salt === null) {
                throw new InvalidParameters('`ImageClientFactory::getInstance()` or `ImageClientFactory::settInstance()` must be first called with `key` and `salt` parameters.');
            }
            self::setInstance($key, $salt);
        }
        return self::$instance;
    }

    public static function setInstance(string $key, string $salt): void
    {
        self::$instance = new ImageClient($key, $salt);
    }
}