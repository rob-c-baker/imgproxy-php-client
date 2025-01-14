<?php declare(strict_types=1);

namespace ImgproxyPhpClient;

use ImgproxyPhpClient\exceptions\InvalidOptionException;
use ImgproxyPhpClient\exceptions\InvalidParametersException;
use ImgproxyPhpClient\exceptions\URLException;
use Onliner\ImgProxy\Options\AbstractOption;

class ImageClientFactory
{
    private static ?ImageClient $instance = null;

    /**
     * @param string|null $key
     * @param string|null $salt
     * @param array<AbstractOption>|array $defaults
     * @param string $endpoint
     * @param bool $dev_mode
     * @param string|null $private_url_pattern
     * @return ImageClient
     * @throws InvalidOptionException
     * @throws URLException
     * @throws InvalidParametersException
     */
    public static function getInstance(
        ?string $key = null,
        ?string $salt = null,
        array $defaults = [],
        string $endpoint = ImageClient::DEFAULT_ENDPOINT,
        bool $dev_mode = false,
        ?string $private_url_pattern = null
    ): ImageClient {
        if (self::$instance === null) {
            if ($key === null || $salt === null) {
                throw new InvalidParametersException('`ImageClientFactory::getInstance()` or `ImageClientFactory::settInstance()` must be first called with `key` and `salt` parameters.');
            }
            self::setInstance($key, $salt, $defaults, $endpoint, $dev_mode, $private_url_pattern);
        }
        return self::$instance;
    }

    /**
     * @param string $key
     * @param string $salt
     * @param array<AbstractOption>|array $defaults
     * @param string $endpoint
     * @param bool $dev_mode
     * @param string|null $private_url_pattern
     * @return void
     * @throws InvalidOptionException
     * @throws URLException
     */
    public static function setInstance(
        string $key,
        string $salt,
        array $defaults = [],
        string $endpoint = ImageClient::DEFAULT_ENDPOINT,
        bool $dev_mode = false,
        ?string $private_url_pattern = null
    ): void {

        self::$instance = new ImageClient($key, $salt, $endpoint, $dev_mode, $private_url_pattern);
        $defaults ?: self::$instance->getOptionBuilder()->setDefaults($defaults);
    }
}