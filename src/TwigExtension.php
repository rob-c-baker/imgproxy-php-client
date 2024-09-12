<?php declare(strict_types=1);

namespace Alanrogers\ImgproxyPhpClient;

use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(
        private ImageClient $image_client
    ) {}


    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('imageUrl', [ $this->image_client, 'imageUrl' ]),
        ];
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('imageUrl', [ $this->image_client, 'imageUrl' ]),
        ];
    }
}