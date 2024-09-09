<?php declare(strict_types=1);

namespace Alanrogers\ImgproxyPhpClient;

use Alanrogers\ImgproxyPhpClient\exceptions\InvalidOptionException;
use Craft;
use craft\elements\Asset;
use Onliner\ImgProxy\UrlBuilder;
use yii\base\InvalidConfigException;

class ImageClient
{
    /**
     * Can be set externally depending on environment
     * @var bool
     */
    public static bool $dev_mode = false;

    private ?OptionBuilder $option_builder = null;

    public function __construct(
        private readonly string $key='',
        private readonly string $salt=''
    ) {}

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

    // @todo default options?
    // @todo support making a `srcset` attribute
    // @todo support passing a Craft asset to the `src` param or a different method

    /**
     * @param string $src The source URL or path for the image
     * @param array $options The options for the URL builder
     * @param string|null $extension If supplied, will convert source image to the format the extension represents.
     * @param bool|null $encoded Whether to encode the URL. Defaults to true if `ImageClient::$dev_mode` is false.
     * @param bool|null $signed Whether to sign the URL. Defaults to true if `ImageClient::$dev_mode` is false.
     * @return string
     * @throws InvalidOptionException
     */
    public static function imageURL(
        string $src,
        array $options,
        ?string $extension = null,
        ?bool $encoded=null,
        ?bool $signed=null
    ): string
    {
        if ($signed === null) {
            $signed = !self::$dev_mode;
        }
        if ($encoded === null) {
            $encoded = !self::$dev_mode;
        }

        $image_client = ImageClientFactory::getInstance();
        $option_builder = $image_client->getOptionBuilder();

        $url_builder = $image_client->getBuilder($signed)
            ->with(...$option_builder->makeOptions($options));

        if (!$encoded) {
            $url_builder->encoded(false)->url($src, $extension);
        }

        return $url_builder->url($src, $extension);
    }

    /**
     * @param Asset $asset
     * @param string|null $extension
     * @param bool|null $encoded
     * @param bool|null $signed
     * @return string|null
     * @throws InvalidOptionException
     */
    public static function imageURLFromAsset(
        Asset $asset,
        array $options,
        ?string $extension = null,
        ?bool $encoded=null,
        ?bool $signed=null
    ): ?string
    {
        if ($asset->kind !== Asset::KIND_IMAGE && $asset->kind !== Asset::KIND_PDF) {
            Craft::warning(sprintf('Asset %s is not an image', $asset->id), 'ImageClient');
            return null; // can't handle non-image assets
        }

        try {
            $src = $asset->getUrl();
        } catch (InvalidConfigException $e) {
            Craft::error($e->getMessage(), 'ImageClient');
            return null;
        }

        if ($src === null) {
            // @todo use a special URL for retrieval of assets that have no public URL
        }

        return self::imageURL($src, $options, $extension, $encoded, $signed);
    }
}