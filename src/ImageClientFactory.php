<?php declare(strict_types=1);

namespace Alanrogers\ImgproxyPhpClient;

use Alanrogers\ImgproxyPhpClient\exceptions\InvalidOptionException;
use Alanrogers\ImgproxyPhpClient\exceptions\InvalidParameters;
use Craft;
use Onliner\ImgProxy\Options\AbstractOption;

class ImageClientFactory
{
    private static ?ImageClient $instance = null;

    /**
     * @param string|null $key
     * @param string|null $salt
     * @param array<AbstractOption>|array $defaults
     * @param bool $register_twig_ext
     * @return ImageClient
     * @throws InvalidOptionException
     */
    public static function getInstance(
        ?string $key=null,
        ?string $salt=null,
        array $defaults=[],
        bool $register_twig_ext=false
    ): ImageClient {
        if (self::$instance === null) {
            if ($key === null || $salt === null) {
                throw new InvalidParameters('`ImageClientFactory::getInstance()` or `ImageClientFactory::settInstance()` must be first called with `key` and `salt` parameters.');
            }
            self::setInstance($key, $salt, $defaults, $register_twig_ext);
        }
        return self::$instance;
    }

    /**
     * @param string $key
     * @param string $salt
     * @param array<AbstractOption>|array $defaults
     * @param bool $register_twig_ext
     * @return void
     * @throws InvalidOptionException
     */
    public static function setInstance(
        string $key,
        string $salt,
        array $defaults=[],
        bool $register_twig_ext=false
    ): void {
        self::$instance = new ImageClient($key, $salt);
        self::$instance->getOptionBuilder()::setDefaults($defaults);
        if ($register_twig_ext) {
            Craft::$app->getView()->registerTwigExtension(new TwigExtension(self::$instance));
        }
    }
}