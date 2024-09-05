<?php declare(strict_types=1);

namespace Alanrogers\ImgproxyPhpClient;

use Alanrogers\ImgproxyPhpClient\exceptions\InvalidOptionException;
use Onliner\ImgProxy\Options\AbstractOption;
use Onliner\ImgProxy\Options\AutoRotate;
use Onliner\ImgProxy\Options\Background;
use Onliner\ImgProxy\Options\Blur;
use Onliner\ImgProxy\Options\CacheBuster;
use Onliner\ImgProxy\Options\Crop;
use Onliner\ImgProxy\Options\Dpr;
use Onliner\ImgProxy\Options\EnforceThumbnail;
use Onliner\ImgProxy\Options\Enlarge;
use Onliner\ImgProxy\Options\Expires;
use Onliner\ImgProxy\Options\Extend;
use Onliner\ImgProxy\Options\ExtendAspectRatio;
use Onliner\ImgProxy\Options\Filename;
use Onliner\ImgProxy\Options\Format;
use Onliner\ImgProxy\Options\FormatQuality;
use Onliner\ImgProxy\Options\Gravity;
use Onliner\ImgProxy\Options\Height;
use Onliner\ImgProxy\Options\KeepCopyright;
use Onliner\ImgProxy\Options\MaxBytes;
use Onliner\ImgProxy\Options\MinHeight;
use Onliner\ImgProxy\Options\MinWidth;
use Onliner\ImgProxy\Options\Padding;
use Onliner\ImgProxy\Options\Preset;
use Onliner\ImgProxy\Options\Quality;
use Onliner\ImgProxy\Options\Raw;
use Onliner\ImgProxy\Options\Resize;
use Onliner\ImgProxy\Options\ResizingType;
use Onliner\ImgProxy\Options\ReturnAttachment;
use Onliner\ImgProxy\Options\Rotate;
use Onliner\ImgProxy\Options\Sharpen;
use Onliner\ImgProxy\Options\Size;
use Onliner\ImgProxy\Options\SkipProcessing;
use Onliner\ImgProxy\Options\StripColorProfile;
use Onliner\ImgProxy\Options\StripMetadata;
use Onliner\ImgProxy\Options\Trim;
use Onliner\ImgProxy\Options\Watermark;
use Onliner\ImgProxy\Options\Width;
use Onliner\ImgProxy\Options\Zoom;

class OptionBuilder
{
    private const array OPTION_MAP = [
        'auto-rotate' => AutoRotate::class,
        'background' => Background::class,
        'blur' => Blur::class,
        'cache-buster' => CacheBuster::class,
        'crop' => Crop::class,
        'dpr' => Dpr::class,
        'enforce-thumbnail' => EnforceThumbnail::class,
        'enlarge' => Enlarge::class,
        'expires' => Expires::class,
        'extend' => Extend::class,
        'extend-aspect-ratio' => ExtendAspectRatio::class,
        'filename' => Filename::class,
        'format' => Format::class,
        'format-quality' => FormatQuality::class,
        'gravity' => Gravity::class,
        'height' => Height::class,
        'keep-copyright' => KeepCopyright::class,
        'max-bytes' => MaxBytes::class,
        'min-height' => MinHeight::class,
        'min-width' => MinWidth::class,
        'padding' => Padding::class,
        'preset' => Preset::class,
        'quality' => Quality::class,
        'raw' => Raw::class,
        'resize' => Resize::class,
        'resizing-type' => ResizingType::class,
        'return-attachment' => ReturnAttachment::class,
        'rotate' => Rotate::class,
        'sharpen' => Sharpen::class,
        'size' => Size::class,
        'skip-processing' => SkipProcessing::class,
        'strip-color-profile' => StripColorProfile::class,
        'strip-metadata' => StripMetadata::class,
        'trim' => Trim::class,
        'watermark' => Watermark::class,
        'width' => Width::class,
        'zoom' => Zoom::class,
    ];

    public function isValid(string $option): bool
    {
        return isset(self::OPTION_MAP[$option]);
    }

    /**
     * Makes an array of `AbstractOption` classes from an array of option names and values
     * @param array $options
     * @return array
     * @throws InvalidOptionException
     */
    public function makeOptions(array $options): array
    {
        $built_options = [];
        foreach ($options as $key => $data) {
            $built_options[] = $this->$key(...$data);
        }
        return $built_options;
    }

    /**
     * @throws InvalidOptionException
     */
    public function make(string $option, array $data=[]): AbstractOption
    {
        if (!$this->isValid($option)) {
            throw new InvalidOptionException(sprintf('Invalid option: %s', $option));
        }
        return new self::OPTION_MAP[$option](...$data);
    }

    public function autoRotate(bool $rotate = true): AutoRotate
    {
        /** @var AutoRotate $auto_rotate */
        /** @noinspection PhpUnhandledExceptionInspection */
        $auto_rotate = $this->make('auto-rotate', [ 'rotate' => $rotate ]);
        return $auto_rotate;
    }

    public function background(string $color): Background
    {
        /** @var Background $background */
        /** @noinspection PhpUnhandledExceptionInspection */
        $background = $this->make('background', [ 'color' => $color ]);
        return $background;
    }

    public function blur(int $radius): Blur
    {
        /** @var Blur $blur */
        /** @noinspection PhpUnhandledExceptionInspection */
        $blur = $this->make('blur', [ 'radius' => $radius ]);
        return $blur;
    }

    public function cacheBuster(string $value): CacheBuster
    {
        /** @var CacheBuster $cache_buster */
        /** @noinspection PhpUnhandledExceptionInspection */
        $cache_buster = $this->make('cache-buster', [ 'value' => $value ]);
        return $cache_buster;
    }

    public function crop(int $width, int $height, ?string $gravity = null): Crop
    {
        /** @var Crop $crop */
        /** @noinspection PhpUnhandledExceptionInspection */
        $crop = $this->make('crop', [ 'width' => $width, 'height' => $height, 'gravity' => $gravity ]);
        return $crop;
    }

    public function dpr(int $value): Dpr
    {
        /** @var Dpr $dpr */
        /** @noinspection PhpUnhandledExceptionInspection */
        $dpr = $this->make('dpr', [ 'value' => $value ]);
        return $dpr;
    }

    public function enforceThumbnail(?string $format = null): EnforceThumbnail
    {
        /** @var EnforceThumbnail $enforce_thumbnail */
        /** @noinspection PhpUnhandledExceptionInspection */
        $enforce_thumbnail = $this->make('enforce-thumbnail', [ 'format' => $format ]);
        return $enforce_thumbnail;
    }

    public function enlarge(bool $enlarge = true): Enlarge
    {
        /** @var Enlarge $enlarge */
        /** @noinspection PhpUnhandledExceptionInspection */
        $enlarge = $this->make('enlarge', [ 'enlarge' => $enlarge ]);
        return $enlarge;
    }

    public function extendAspectRatio(bool $extend = true, ?string $gravity = null): ExtendAspectRatio
    {
        /** @var ExtendAspectRatio $extend_aspect_ratio */
        /** @noinspection PhpUnhandledExceptionInspection */
        $extend_aspect_ratio = $this->make('extend-aspect-ratio', [ 'extend' => $extend, 'gravity' => $gravity ]);
        return $extend_aspect_ratio;
    }

    public function filename(string $name): Filename
    {
        /** @var Filename $filename */
        /** @noinspection PhpUnhandledExceptionInspection */
        $filename = $this->make('height', [ 'name' => $name ]);
        return $filename;
    }

    public function format(string $format): Format
    {
        /** @var Format $format */
        /** @noinspection PhpUnhandledExceptionInspection */
        $format = $this->make('format', [ 'format' => $format ]);
        return $format;
    }

    public function formatQuality(array $options): FormatQuality
    {
        /** @var FormatQuality $format_quality */
        /** @noinspection PhpUnhandledExceptionInspection */
        $format_quality = $this->make('format-quality', [ 'options' => $options ]);
        return $format_quality;
    }

    public function gravity(string $type, ?float $x = null, ?float $y = null): Gravity
    {
        /** @var Gravity $gravity */
        /** @noinspection PhpUnhandledExceptionInspection */
        $gravity = $this->make('gravity', [ 'type' => $type, 'x' => $x, 'y' => $y ]);
        return $gravity;
    }

    public function height(int $height): Height
    {
        /** @var Height $height */
        /** @noinspection PhpUnhandledExceptionInspection */
        $height = $this->make('height', [ 'height' => $height ]);
        return $height;
    }

    public function keepCopyright(bool $keep = true): KeepCopyright
    {
        /** @var KeepCopyright $keep_copyright */
        /** @noinspection PhpUnhandledExceptionInspection */
        $keep_copyright = $this->make('keep-copyright', [ 'keep' => $keep ]);
        return $keep_copyright;
    }

    public function maxBytes(int $bytes): MaxBytes
    {
        /** @var MaxBytes $max_bytes */
        /** @noinspection PhpUnhandledExceptionInspection */
        $max_bytes = $this->make('max-bytes', [ 'bytes' => $bytes ]);
        return $max_bytes;
    }

    public function minHeight(int $height): MinHeight
    {
        /** @var MinHeight $min_height */
        /** @noinspection PhpUnhandledExceptionInspection */
        $min_height = $this->make('min-height', [ 'height' => $height ]);
        return $min_height;
    }

    public function minWidth(int $width): MinWidth
    {
        /** @var MinWidth $min_width */
        /** @noinspection PhpUnhandledExceptionInspection */
        $min_width = $this->make('min-width', [ 'width' => $width ]);
        return $min_width;
    }

    public function padding(?int $top = null, ?int $right = null, ?int $bottom = null, ?int $left = null): Padding
    {
        /** @var Padding $padding */
        /** @noinspection PhpUnhandledExceptionInspection */
        $padding = $this->make('padding', [ 'top' => $top, 'right' => $right, 'bottom' => $bottom, 'left' => $left ]);
        return $padding;
    }

    public function preset(string ...$presets): Preset
    {
        /** @var Preset $preset */
        /** @noinspection PhpUnhandledExceptionInspection */
        $preset = $this->make('padding', [ 'presets' => $presets ]);
        return $preset;
    }

    public function quality(int $quality): Quality
    {
        /** @var Quality $q */
        /** @noinspection PhpUnhandledExceptionInspection */
        $q = $this->make('quality', [ 'quality' => $quality ]);
        return $q;
    }

    public function raw(bool $raw = true): Raw
    {
        /** @var Raw $raw */
        /** @noinspection PhpUnhandledExceptionInspection */
        $raw = $this->make('raw', [ 'raw' => $raw ]);
        return $raw;
    }

    public function resize(int $width, int $height): Resize
    {
        /** @var Resize $resize */
        /** @noinspection PhpUnhandledExceptionInspection */
        $resize = $this->make('resize', [ 'width' => $width, 'height' => $height ]);
        return $resize;
    }

    public function resizingType(string $type): ResizingType
    {
        /** @var ResizingType $resizing_type */
        /** @noinspection PhpUnhandledExceptionInspection */
        $resizing_type = $this->make('resizing-type', [ 'type' => $type ]);
        return $resizing_type;
    }

    public function returnAttachment(bool $value = true): ReturnAttachment
    {
        /** @var ReturnAttachment $return_attachment */
        /** @noinspection PhpUnhandledExceptionInspection */
        $return_attachment = $this->make('return-attachment', [ 'value' => $value ]);
        return $return_attachment;
    }

    public function rotate(int $angle): Rotate
    {
        /** @var Rotate $rotate */
        /** @noinspection PhpUnhandledExceptionInspection */
        $rotate = $this->make('rotate', [ 'angle' => $angle ]);
        return $rotate;
    }

    public function sharpen(float $sigma): Sharpen
    {
        /** @var Sharpen $sharpen */
        /** @noinspection PhpUnhandledExceptionInspection */
        $sharpen = $this->make('sharpen', [ 'sigma' => $sigma ]);
        return $sharpen;
    }

    public function size(?int $width = null, ?int $height = null, ?bool $enlarge = null, ?bool $extend = null): Size
    {
        /** @var Size $size */
        /** @noinspection PhpUnhandledExceptionInspection */
        $size = $this->make('size', [ 'width' => $width, 'height' => $height, 'enlarge' => $enlarge, 'extend' => $extend ]);
        return $size;
    }

    public function skipProcessing(string ...$extensions): SkipProcessing
    {
        /** @var SkipProcessing $skip_processing */
        /** @noinspection PhpUnhandledExceptionInspection */
        $skip_processing = $this->make('skip-processing', [ 'extensions' => $extensions ]);
        return $skip_processing;
    }

    public function stripColorProfile(bool $strip = true): StripColorProfile
    {
        /** @var StripColorProfile $strip_color_profile */
        /** @noinspection PhpUnhandledExceptionInspection */
        $strip_color_profile = $this->make('strip-color-profile', [ 'strip' => $strip ]);
        return $strip_color_profile;
    }

    public function stripMetadata(bool $value = true): StripMetadata
    {
        /** @var StripMetadata $strip_metadata */
        /** @noinspection PhpUnhandledExceptionInspection */
        $strip_metadata = $this->make('strip-metadata', [ 'value' => $value ]);
        return $strip_metadata;
    }

    public function trim(string $threshold, ?string $color = null): Trim
    {
        /** @var Trim $trim */
        /** @noinspection PhpUnhandledExceptionInspection */
        $trim = $this->make('trim', [ 'threshold' => $threshold, 'color' => $color ]);
        return $trim;
    }

    public function watermark(string $url, ?int $opacity = null, ?int $position = null): Watermark
    {
        /** @var Watermark $watermark */
        /** @noinspection PhpUnhandledExceptionInspection */
        $watermark = $this->make('watermark', [ 'url' => $url, 'opacity' => $opacity, 'position' => $position ]);
        return $watermark;
    }

    public function width(int $width): Width
    {
        /** @var Width $w */
        /** @noinspection PhpUnhandledExceptionInspection */
        $w = $this->make('width', [ 'value' => $width ]);
        return $w;
    }

    public function zoom(float $x, ?float $y = null): Zoom
    {
        /** @var Zoom $zoom */
        /** @noinspection PhpUnhandledExceptionInspection */
        $zoom = $this->make('zoom', [ 'x' => $x, 'y' => $y ]);
        return $zoom;
    }
}