<?php declare(strict_types=1);

namespace Alanrogers\ImgproxyPhpClient;

use Alanrogers\ImgproxyPhpClient\exceptions\InvalidOptionException;
use Alanrogers\ImgproxyPhpClient\exceptions\URLException;
use craft\elements\Asset;

/**
 * Represents a set of images for use in srcset.
 */
class Srcset
{
    public function __construct(
        /**
         * @var Image[]
         */
        private readonly array $images
    ) {}

    // @todo use https://docs.imgproxy.net/usage/processing#dpr for srcset that use x1, x2, x3 descriptors

    /**
     * @throws URLException
     * @throws InvalidOptionException
     */
    public function getAttribute(): string
    {
        $attr = [];

        foreach ($this->images as $image) {

            $width = $image->getWidth();
            $height = $image->getHeight();

            if (!$width && !$height) {
                if ($image->getSrc() instanceof Asset) {
                    $identifier = $image->getSrc()->id;
                    $type = 'asset';
                } else {
                    $identifier = $image->getSrc();
                    $type = 'url';
                }
                throw new InvalidOptionException(
                    sprintf(
                        'Width or height must be set to create a srcset on %s "%s"',
                        $type,
                        $identifier
                    )
                );
            } elseif ($width > 0) {
                $attr[] = "{$image->getURL()} {$width}w";
            } elseif ($height > 0) {
                $attr[] = "{$image->getURL()} {$height}h";
            }
        }

        return implode(", ", $attr);
    }
}