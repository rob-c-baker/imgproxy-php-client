<?php declare(strict_types=1);

namespace Alanrogers\ImgproxyPhpClient;

use Alanrogers\ImgproxyPhpClient\exceptions\InvalidKindException;
use Alanrogers\ImgproxyPhpClient\exceptions\InvalidOptionException;
use Alanrogers\ImgproxyPhpClient\exceptions\URLException;
use Craft;
use craft\elements\Asset;
use Onliner\ImgProxy\UrlBuilder;
use yii\base\InvalidConfigException;

class ImageClient
{
    public const string DEFAULT_ENDPOINT = 'http://localhost:8080';
    private ?OptionBuilder $option_builder = null;

    /**
     * @throws URLException
     */
    public function __construct(
        private readonly string $key = '',
        private readonly string $salt = '',
        private readonly string $endpoint = self::DEFAULT_ENDPOINT,
        private readonly bool $dev_mode = false,
        private ?string $private_url_pattern = null
    ) {
        if ($this->private_url_pattern !== null && !$this->isPrivateURLPatternValid()) {
            throw new URLException('Private URL pattern must use at least 1 valid placeholder.');
        }
    }

    public function getBuilder(bool $signed=true): UrlBuilder
    {
        return $signed ? UrlBuilder::signed($this->key, $this->salt) : new UrlBuilder();
    }

    public function getOptionBuilder(): OptionBuilder
    {
        if ($this->option_builder === null) {
            $this->option_builder = new OptionBuilder();
        }
        return $this->option_builder;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @param string $src The source URL or path for the image
     * @param array|null $options The options for the URL builder
     * @param string|null $extension If supplied, will convert source image to the format the extension represents.
     * @param bool|null $encoded Whether to encode the URL. Defaults to true if `ImageClient::$dev_mode` is false.
     * @param bool|null $signed Whether to sign the URL. Defaults to true if `ImageClient::$dev_mode` is false.
     * @return Image
     * @throws InvalidOptionException
     */
    public function image(
        string $src,
        array $options = null,
        ?string $extension = null,
        ?bool $encoded = null,
        ?bool $signed = null
    ): Image
    {
        if ($encoded === null) {
            $encoded = !$this->dev_mode;
        }

        if ($signed === null) {
            $signed = !$this->dev_mode;
        }

        $option_builder = $this->getOptionBuilder();

        if ($options !== null) {
            $option_builder->addOptions($options);
        }

        if (!$option_builder->hasOptions()) {
            throw new InvalidOptionException('No options specified, at least 1 option must be supplied.');
        }

        return new Image($this, $option_builder, $src, $extension, $encoded, $signed);
    }

    /**
     * @param Asset $asset
     * @param array|null $options
     * @param string|null $extension
     * @param bool|null $encoded
     * @param bool|null $signed
     * @return Image|null
     * @throws InvalidOptionException
     * @throws InvalidKindException
     * @throws URLException
     */
    public function imageFromAsset(
        Asset $asset,
        array $options = null,
        ?string $extension = null,
        ?bool $encoded = null,
        ?bool $signed = null
    ): ?Image
    {
        // Note: cannot process PDFs until we have an imgproxy paid licence
        if ($asset->kind !== Asset::KIND_IMAGE/* && $asset->kind !== Asset::KIND_PDF*/) {
            throw new InvalidKindException(
                sprintf('Asset with id "%d" is not an image, it has type "%s".', $asset->id, $asset->kind)
            );
        }

        try {
            $src = $asset->getUrl();
        } catch (InvalidConfigException $e) {
            Craft::error($e->getMessage(), 'ImageClient');
            return null;
        }

        if ($src === null) {
            if ($this->private_url_pattern) {
                // use a special URL for retrieval of assets that have no public URL
                $src = str_replace([ '{id}', '{uid}', '{filename}' ], [ $asset->id, $asset->uid, $asset->filename ], $this->private_url_pattern);
            } else {
                throw new URLException(sprintf('Asset with id "%d" has no public URL and private URLs are disabled.', $asset->id));
            }
        }

        return $this->image($src, $options, $extension, $encoded, $signed);
    }

    /**
     * Produces a number of image URLs defined by multiple arrays of options passed in to `$option_sets`.
     * This number of image URLs are then returned in a `Srcset` object that can render a `srcset` attribute.
     * @param Asset|string $src
     * @param array $option_sets
     * @param string|null $extension
     * @param bool|null $encoded
     * @param bool|null $signed
     * @return Srcset
     * @throws InvalidKindException
     * @throws InvalidOptionException
     * @throws URLException
     */
    public function srcset(
        Asset|string $src,
        array $option_sets,
        ?string $extension = null,
        ?bool $encoded = null,
        ?bool $signed = null
    ): Srcset {

        $images = [];

        foreach ($option_sets as $options) {
            if ($src instanceof Asset) {
                $images[] = $this->imageFromAsset($src, $options, $extension, $encoded, $signed);
            } else {
                $images[] = $this->image($src, $options, $extension, $encoded, $signed);
            }
        }

        return new Srcset($images);
    }

    /**
     * Sets options for the private URL which will be used when an `Asset` has no public URLs so that imgproxy can
     * download the image.
     *
     * @param string $pattern Must be a valid private URL pattern.
     *
     * Example: "https://host/actions/private-image/fetch?id={id}"
     *
     * Can contain:
     * - {id} - which will be replaced with the asset ID
     * - {uid} - which will be replaced with the asset UID
     * - {filename} - which will be replaced with the asset filename (including extension).
     *
     * @return void
     */
    public function setPrivateURLOptions(string $pattern): void
    {
        $this->private_url_pattern = $pattern;
    }

    /**
     * Prevents use of private URLs to access otherwise inaccessible assets.
     * @return void
     */
    public function disablePrivateURLs(): void
    {
        $this->private_url_pattern = null;
    }

    private function isPrivateURLPatternValid(): bool
    {
        return preg_match('/{id}|{uid}|{filename}/', $this->private_url_pattern) > 0;
    }
}