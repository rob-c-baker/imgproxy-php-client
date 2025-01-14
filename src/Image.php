<?php declare(strict_types=1);

namespace ImgproxyPhpClient;

use ImgproxyPhpClient\exceptions\URLException;
use craft\elements\Asset;
use Onliner\ImgProxy\Options\Height;
use Onliner\ImgProxy\Options\Width;
use yii\base\InvalidConfigException;

/**
 * Represents an image.
 */
readonly class Image
{
    public function __construct(
        private ImageClient   $image_client,
        private OptionBuilder $option_builder,
        private string|Asset  $src,
        private ?string       $extension = null,
        private bool          $encoded = true,
        private bool          $signed = true
    ) {}

    public function getURL(): ?string
    {
        $url_builder = $this->image_client->getBuilder($this->signed);

        if ($this->encoded !== null) {
            $url_builder = $url_builder->encoded($this->encoded);
        }

        $url_builder = $url_builder->with(...$this->option_builder->getOptions());

        try {
            $url = $url_builder->url(
                $this->src instanceof Asset ? $this->src->getUrl() : $this->src,
                $this->extension
            );
        } catch (InvalidConfigException $e) {
            throw new URLException('Could not get URL from source.', (int) $e->getCode(), $e);
        }

        $endpoint = $this->image_client->getEndpoint();

        return $endpoint ? $endpoint . $url : $url;
    }

    public function getSrc(): string|Asset
    {
        return $this->src;
    }

    /**
     * Gets the width of the image requested from imgproxy.
     * Will return null if there is no width option set.
     * @return int|null
     */
    public function getWidth(): ?int
    {
        $width = $this->option_builder->getOption(Width::class);
        if ($width) {
            return (int) $width->value();
        }
        return null;
    }

    /**
     * Gets the height of the image requested from imgproxy.
     * Will return null if there is no height option set.
     * @return int|null
     */
    public function getHeight(): ?int
    {
        $height = $this->option_builder->getOption(Height::class);
        if ($height) {
            return (int) $height->value();
        }
        return null;
    }
}