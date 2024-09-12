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
    public const array OPTION_MAP = [
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

    /**
     * @var array<AbstractOption>
     */
    private array $options = [];

    /**
     * @var array<AbstractOption>|array
     */
    private static array $default_options = [];

    /**
     * @throws InvalidOptionException
     */
    public function __construct()
    {
        $this->addOptions(self::$default_options);
    }

    /**
     * @param array $defaults
     * @return void
     */
    public static function setDefaults(array $defaults): void
    {
        self::$default_options = $defaults;
    }

    /**
     * @param string $option
     * @return bool
     */
    public function isValid(string $option): bool
    {
        return isset(self::OPTION_MAP[$option]);
    }

    /**
     * Makes an array of `AbstractOption` classes from an array of option names and values
     * or an array of `AbstractOption` objects.
     * @param array<AbstractOption>|array $options
     * @return OptionBuilder
     * @throws InvalidOptionException
     */
    public function addOptions(array $options): self
    {
        foreach ($options as $key => $data) {
            if (!$this->isValid($key)) {
                throw new InvalidOptionException(sprintf('Invalid option: %s', $key));
            }
            if ($data instanceof AbstractOption) {
                $this->options[] = $data;
                continue;
            }
            $this->options[] = $this->make($key, $data);
        }
        return $this;
    }

    /**
     * @return array<AbstractOption>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @throws InvalidOptionException
     */
    public function make(string $option, array $data=[]): AbstractOption
    {
        if (!$this->isValid($option)) {
            throw new InvalidOptionException(sprintf('Invalid option: %s', $option));
        }
        return new (self::OPTION_MAP[$option])(...$data);
    }

    /**
     * When set to 1, t or true, imgproxy will automatically rotate images based on the EXIF Orientation parameter (if
     * available in the image meta data). The orientation tag will be removed from the image in all cases. Normally
     * this is controlled by the IMGPROXY_AUTO_ROTATE configuration but this processing option allows the configuration
     * to be set for each request.
     * @param bool $rotate
     * @return AutoRotate
     * @throws InvalidOptionException
     */
    public function autoRotate(bool $rotate = true): AutoRotate
    {
        /** @var AutoRotate $auto_rotate */
        /** @noinspection PhpUnhandledExceptionInspection */
        $auto_rotate = $this->make('auto-rotate', [ 'rotate' => $rotate ]);
        return $auto_rotate;
    }

    /**
     * When set, imgproxy will fill the resulting image background with the specified color. R, G, and B are the red,
     * green and blue channel values of the background color (0-255). hex_color is a hex-coded value of the color.
     * Useful when you convert an image with alpha-channel to JPEG.
     *
     * @param string $color
     *
     * - %R:%G:%B
     * - %hex_color
     *
     * @return Background
     * @throws InvalidOptionException
     */
    public function background(string $color): Background
    {
        /** @var Background $background */
        /** @noinspection PhpUnhandledExceptionInspection */
        $background = $this->make('background', [ 'color' => $color ]);
        return $background;
    }

    /**
     * When set, imgproxy will apply a gaussian blur filter to the resulting image. The value of sigma defines the size
     * of the mask imgproxy will use.
     * @param float $sigma
     * @return Blur
     * @throws InvalidOptionException
     */
    public function blur(float $sigma): Blur
    {
        /** @var Blur $blur */
        /** @noinspection PhpUnhandledExceptionInspection */
        $blur = $this->make('blur', [ 'sigma' => $sigma ]);
        return $blur;
    }

    /**
     * Cache buster doesn't affect image processing but its changing allows for bypassing the CDN, proxy server and
     * browser cache. Useful when you have changed some things that are not reflected in the URL, like image quality
     * settings, presets, or watermark data.
     *
     * @param string $value
     * @return CacheBuster
     * @throws InvalidOptionException
     */
    public function cacheBuster(string $value): CacheBuster
    {
        /** @var CacheBuster $cache_buster */
        /** @noinspection PhpUnhandledExceptionInspection */
        $cache_buster = $this->make('cache-buster', [ 'value' => $value ]);
        return $cache_buster;
    }

    /**
     * Defines an area of the image to be processed (crop before resize).
     * - width and height define the size of the area:
     *   - When width or height is greater than or equal to 1, imgproxy treats it as an absolute value.
     *   - When width or height is less than 1, imgproxy treats it as a relative value.
     *   - When width or height is set to 0, imgproxy will use the full width/height of the source image.
     * - gravity (optional) accepts the same values as the gravity option. When gravity is not set, imgproxy will use the value of the gravity option.
     *
     * @param int $width
     * @param int $height
     * @param string|null $gravity "type:x_offset:y_offset"
     *
     * Gravity Types:
     * - no: north (top edge)
     * - so: south (bottom edge)
     * - ea: east (right edge)
     * - we: west (left edge)
     * - noea: north-east (top-right corner)
     * - nowe: north-west (top-left corner)
     * - soea: south-east (bottom-right corner)
     * - sowe: south-west (bottom-left corner)
     * - ce: center
     *
     * @return Crop
     * @throws InvalidOptionException
     */
    public function crop(int $width, int $height, ?string $gravity = null): Crop
    {
        /** @var Crop $crop */
        /** @noinspection PhpUnhandledExceptionInspection */
        $crop = $this->make('crop', [ 'width' => $width, 'height' => $height, 'gravity' => $gravity ]);
        return $crop;
    }

    /**
     * When set, imgproxy will multiply the image dimensions according to this factor for HiDPI (Retina) devices.
     * The value must be greater than 0.
     * @param int $dpr
     * @return Dpr
     * @throws InvalidOptionException
     */
    public function dpr(int $dpr): Dpr
    {
        /** @var Dpr $dpr */
        /** @noinspection PhpUnhandledExceptionInspection */
        $dpr = $this->make('dpr', [ 'dpr' => $dpr ]);
        return $dpr;
    }

    /**
     * When set to 1, t or true and the source image has an embedded thumbnail, imgproxy will always use the embedded
     * thumbnail instead of the main image. Currently, only thumbnails embedded in heic and avif are supported. This is
     * normally controlled by the IMGPROXY_ENFORCE_THUMBNAIL configuration but this processing option allows the
     * configuration to be set for each request.
     * @param string|null $format
     * @return EnforceThumbnail
     * @throws InvalidOptionException
     */
    public function enforceThumbnail(?string $format = null): EnforceThumbnail
    {
        /** @var EnforceThumbnail $enforce_thumbnail */
        /** @noinspection PhpUnhandledExceptionInspection */
        $enforce_thumbnail = $this->make('enforce-thumbnail', [ 'format' => $format ]);
        return $enforce_thumbnail;
    }

    /**
     * When set to true, imgproxy will enlarge the image if it is smaller than the given size.
     * @param bool $enlarge
     * @return Enlarge
     * @throws InvalidOptionException
     */
    public function enlarge(bool $enlarge = true): Enlarge
    {
        /** @var Enlarge $enlarge */
        /** @noinspection PhpUnhandledExceptionInspection */
        $enlarge = $this->make('enlarge', [ 'enlarge' => $enlarge ]);
        return $enlarge;
    }

    /**
     * When extend is set to true, imgproxy will extend the image to the requested aspect ratio.
     * gravity (optional) accepts the same values as the gravity option, except sm. When gravity is not set, imgproxy
     * will use ce gravity without offsets.
     * @param bool $extend
     * @param string|null $gravity
     * @return ExtendAspectRatio
     * @throws InvalidOptionException
     */
    public function extendAspectRatio(bool $extend = true, ?string $gravity = null): ExtendAspectRatio
    {
        /** @var ExtendAspectRatio $extend_aspect_ratio */
        /** @noinspection PhpUnhandledExceptionInspection */
        $extend_aspect_ratio = $this->make('extend-aspect-ratio', [ 'extend' => $extend, 'gravity' => $gravity ]);
        return $extend_aspect_ratio;
    }

    /**
     * Defines a filename for the Content-Disposition header. When not specified, imgproxy will get the filename from
     * the source URL.
     *
     * - filename: escaped or URL-safe Base64-encoded filename to be used in the Content-Disposition header
     * - encoded: (optional) identifies if filename is Base64-encoded. Set it to 1, t, or true if you encoded
     * the filename value with URL-safe Base64 encoding.
     * @param string $name
     * @return Filename
     * @throws InvalidOptionException
     */
    public function filename(string $name): Filename
    {
        /** @var Filename $filename */
        /** @noinspection PhpUnhandledExceptionInspection */
        $filename = $this->make('height', [ 'name' => $name ]);
        return $filename;
    }

    /**
     * Specifies the resulting image format. Alias for the extension part of the URL.
     * @param string $format
     * @return Format
     * @throws InvalidOptionException
     */
    public function format(string $format): Format
    {
        /** @var Format $format */
        /** @noinspection PhpUnhandledExceptionInspection */
        $format = $this->make('format', [ 'format' => $format ]);
        return $format;
    }

    /**
     * Adds or redefines IMGPROXY_FORMAT_QUALITY values.
     *
     * @param array $options
     *
     * ```
     * [
     *    [ 'jpg' => 80 ],
     *    ...
     * ]
     * ```
     *
     * @return FormatQuality
     * @throws InvalidOptionException
     */
    public function formatQuality(array $options): FormatQuality
    {
        /** @var FormatQuality $format_quality */
        /** @noinspection PhpUnhandledExceptionInspection */
        $format_quality = $this->make('format-quality', [ 'options' => $options ]);
        return $format_quality;
    }

    /**
     * When imgproxy needs to cut some parts of the image, it is guided by the gravity option.
     *
     * - type - specifies the gravity type. Available values:
     *   - no: north (top edge)
     *   - so: south (bottom edge)
     *   - ea: east (right edge)
     *   - we: west (left edge)
     *   - noea: north-east (top-right corner)
     *   - nowe: north-west (top-left corner)
     *   - soea: south-east (bottom-right corner)
     *   - sowe: south-west (bottom-left corner)
     *   - ce: center
     * - x_offset, y_offset - (optional) specifies the gravity offset along the X and Y axes:
     *   - When x_offset or y_offset is greater than or equal to 1, imgproxy treats it as an absolute value.
     *   - When x_offset or y_offset is less than 1, imgproxy treats it as a relative value.
     *
     * Default: ce:0:0
     *
     * ## Special gravities:
     *
     * - gravity:sm: smart gravity. libvips detects the most "interesting" section of the image and considers it as the
     * center of the resulting image. Offsets are not applicable here.
     * - gravity:obj:%class_name1:%class_name2:...:%class_nameN: pro object-oriented gravity. imgproxy detects objects
     * of provided classes on the image and calculates the resulting image center using their positions. If class names
     * are omited, imgproxy will use all the detected objects.
     * - gravity:fp:%x:%y: the gravity focus point . x and y are floating point numbers between 0 and 1 that define the
     * coordinates of the center of the resulting image. Treat 0 and 1 as right/left for x and top/bottom for y.
     *
     * @param string $type
     * @param float|null $x
     * @param float|null $y
     * @return Gravity
     * @throws InvalidOptionException
     */
    public function gravity(string $type, ?float $x = null, ?float $y = null): Gravity
    {
        /** @var Gravity $gravity */
        /** @noinspection PhpUnhandledExceptionInspection */
        $gravity = $this->make('gravity', [ 'type' => $type, 'x' => $x, 'y' => $y ]);
        return $gravity;
    }

    /**
     * Defines the height of the resulting image. When set to 0, imgproxy will calculate resulting height using the
     * defined width and source aspect ratio. When set to 0 and resizing type is force, imgproxy will keep the original
     * height.
     *
     * Default: 0
     *
     * @param int $height
     * @return Height
     * @throws InvalidOptionException
     */
    public function height(int $height): Height
    {
        /** @var Height $height */
        /** @noinspection PhpUnhandledExceptionInspection */
        $height = $this->make('height', [ 'height' => $height ]);
        return $height;
    }

    /**
     * When set to true, imgproxy will not remove copyright info while stripping metadata. This is normally controlled
     * by the IMGPROXY_KEEP_COPYRIGHT configuration but this processing option allows the configuration to be set for
     * each request.
     * @param bool $keep
     * @return KeepCopyright
     * @throws InvalidOptionException
     */
    public function keepCopyright(bool $keep = true): KeepCopyright
    {
        /** @var KeepCopyright $keep_copyright */
        /** @noinspection PhpUnhandledExceptionInspection */
        $keep_copyright = $this->make('keep-copyright', [ 'keep' => $keep ]);
        return $keep_copyright;
    }

    /**
     * When set, imgproxy automatically degrades the quality of the image until the image size is under the specified
     * amount of bytes.
     * @param int $bytes
     * @return MaxBytes
     * @throws InvalidOptionException
     */
    public function maxBytes(int $bytes): MaxBytes
    {
        /** @var MaxBytes $max_bytes */
        /** @noinspection PhpUnhandledExceptionInspection */
        $max_bytes = $this->make('max-bytes', [ 'bytes' => $bytes ]);
        return $max_bytes;
    }

    /**
     * Defines the minimum height of the resulting image.
     *
     * Warning:
     *
     * When both height and min-height are set, the final image will be cropped according to height, so use this
     * combination with care.
     *
     * @param int $height
     * @return MinHeight
     * @throws InvalidOptionException
     */
    public function minHeight(int $height): MinHeight
    {
        /** @var MinHeight $min_height */
        /** @noinspection PhpUnhandledExceptionInspection */
        $min_height = $this->make('min-height', [ 'height' => $height ]);
        return $min_height;
    }

    /**
     * Defines the minimum width of the resulting image.
     *
     * Warning:
     *
     * When both width and min-width are set, the final image will be cropped according to width, so use this
     * combination with care.
     *
     * @param int $width
     * @return MinWidth
     * @throws InvalidOptionException
     */
    public function minWidth(int $width): MinWidth
    {
        /** @var MinWidth $min_width */
        /** @noinspection PhpUnhandledExceptionInspection */
        $min_width = $this->make('min-width', [ 'width' => $width ]);
        return $min_width;
    }

    /**
     * Defines padding size using CSS-style syntax. All arguments are optional but at least one dimension must be set.
     * Padded space is filled according to the background option.
     *
     * - top - top padding (and for all other sides if they haven't been explicitly set)
     * - right - right padding (and left if it hasn't been explicitly set)
     * - bottom - bottom padding
     * - left - left padding
     *
     * @param int|null $top
     * @param int|null $right
     * @param int|null $bottom
     * @param int|null $left
     * @return Padding
     * @throws InvalidOptionException
     */
    public function padding(?int $top = null, ?int $right = null, ?int $bottom = null, ?int $left = null): Padding
    {
        /** @var Padding $padding */
        /** @noinspection PhpUnhandledExceptionInspection */
        $padding = $this->make('padding', [ 'top' => $top, 'right' => $right, 'bottom' => $bottom, 'left' => $left ]);
        return $padding;
    }

    /**
     * Defines a list of presets to be used by imgproxy. Feel free to use as many presets in a single URL as you need.
     *
     * Default: empty
     *
     * @param string ...$presets
     * @return Preset
     * @throws InvalidOptionException
     */
    public function preset(string ...$presets): Preset
    {
        /** @var Preset $preset */
        /** @noinspection PhpUnhandledExceptionInspection */
        $preset = $this->make('padding', [ 'presets' => $presets ]);
        return $preset;
    }

    /**
     * Redefines quality of the resulting image, as a percentage. When set to 0, quality is assumed based on
     * IMGPROXY_QUALITY and format_quality.
     *
     * Default: 0
     *
     * @param int $quality
     * @return Quality
     * @throws InvalidOptionException
     */
    public function quality(int $quality): Quality
    {
        /** @var Quality $q */
        /** @noinspection PhpUnhandledExceptionInspection */
        $q = $this->make('quality', [ 'quality' => $quality ]);
        return $q;
    }

    /**
     * When set to 1, t or true, imgproxy will respond with a raw unprocessed, and unchecked source image. There are
     * some differences between raw and skip_processing options:
     *
     * - While the skip_processing option has some conditions to skip the processing, the raw option allows to skip
     * processing no matter what
     * - With the raw option set, imgproxy doesn't check the source image's type, resolution, and file size. Basically,
     * the raw option allows streaming of any file type
     * - With the raw option set, imgproxy won't download the whole image to the memory. Instead, it will stream the
     * source image directly to the response lowering memory usage
     * - The requests with the raw option set are not limited by the IMGPROXY_WORKERS config
     *
     * Default: false
     *
     * @param bool $raw
     * @return Raw
     * @throws InvalidOptionException
     */
    public function raw(bool $raw = true): Raw
    {
        /** @var Raw $raw */
        /** @noinspection PhpUnhandledExceptionInspection */
        $raw = $this->make('raw', [ 'raw' => $raw ]);
        return $raw;
    }

    /**
     * This is a meta-option that defines the resizing type, width, height, enlarge, and extend. All arguments are
     * optional and can be omitted to use their default values.
     *
     * @param string $type
     * @param int|null $width
     * @param int|null $height
     * @param int|null $enlarge
     * @param int|null $extend
     * @return Resize
     * @throws InvalidOptionException
     */
    public function resize(string $type, ?int $width=null, ?int $height=null, ?int $enlarge = null, ?int $extend = null): Resize
    {
        /** @var Resize $resize */
        /** @noinspection PhpUnhandledExceptionInspection */
        $resize = $this->make('resize', [
            'type' => $type,
            'width' => $width,
            'height' => $height,
            'enlarge' => $enlarge,
            'extend' => $extend
        ]);
        return $resize;
    }

    /**
     * Defines how imgproxy will resize the source image. Supported resizing types are:
     *
     * - fit: resizes the image while keeping aspect ratio to fit a given size.
     * - fill: resizes the image while keeping aspect ratio to fill a given size and crops projecting parts.
     * - fill-down: the same as fill, but if the resized image is smaller than the requested size, imgproxy will crop the result to keep the requested aspect ratio.
     * - force: resizes the image without keeping the aspect ratio.
     * - auto: if both source and resulting dimensions have the same orientation (portrait or landscape), imgproxy will use fill. Otherwise, it will use fit.
     *
     * @param string $type
     * @return ResizingType
     * @throws InvalidOptionException
     */
    public function resizingType(string $type): ResizingType
    {
        /** @var ResizingType $resizing_type */
        /** @noinspection PhpUnhandledExceptionInspection */
        $resizing_type = $this->make('resizing-type', [ 'type' => $type ]);
        return $resizing_type;
    }

    /**
     * When set to true, imgproxy will return attachment in the Content-Disposition header, and the browser will open
     * a 'Save as' dialog. This is normally controlled by the IMGPROXY_RETURN_ATTACHMENT configuration but this
     * processing option allows the configuration to be set for each request.
     * @param bool $value
     * @return ReturnAttachment
     * @throws InvalidOptionException
     */
    public function returnAttachment(bool $value = true): ReturnAttachment
    {
        /** @var ReturnAttachment $return_attachment */
        /** @noinspection PhpUnhandledExceptionInspection */
        $return_attachment = $this->make('return-attachment', [ 'value' => $value ]);
        return $return_attachment;
    }

    /**
     * Rotates the image on the specified angle. The orientation from the image metadata is applied before the rotation
     * unless autorotation is disabled.
     *
     * Info:
     * Only 0, 90, 180, 270, etc., degree angles are supported.
     *
     * @param int $angle
     * @return Rotate
     * @throws InvalidOptionException
     */
    public function rotate(int $angle): Rotate
    {
        /** @var Rotate $rotate */
        /** @noinspection PhpUnhandledExceptionInspection */
        $rotate = $this->make('rotate', [ 'angle' => $angle ]);
        return $rotate;
    }

    /**
     * When set, imgproxy will apply the sharpen filter to the resulting image. The value of sigma defines the size of
     * the mask imgproxy will use.
     *
     * As an approximate guideline, use 0.5 sigma for 4 pixels/mm (display resolution), 1.0 for 12 pixels/mm and 1.5
     * for 16 pixels/mm (300 dpi == 12 pixels/mm).
     *
     * Default: disabled
     *
     * @param float $sigma
     * @return Sharpen
     * @throws InvalidOptionException
     */
    public function sharpen(float $sigma): Sharpen
    {
        /** @var Sharpen $sharpen */
        /** @noinspection PhpUnhandledExceptionInspection */
        $sharpen = $this->make('sharpen', [ 'sigma' => $sigma ]);
        return $sharpen;
    }

    /**
     * This is a meta-option that defines the width, height, enlarge, and extend. All arguments are optional and can be
     * omitted to use their default values.
     * @param int|null $width
     * @param int|null $height
     * @param bool|null $enlarge
     * @param bool|null $extend
     * @return Size
     * @throws InvalidOptionException
     */
    public function size(?int $width = null, ?int $height = null, ?bool $enlarge = null, ?bool $extend = null): Size
    {
        /** @var Size $size */
        /** @noinspection PhpUnhandledExceptionInspection */
        $size = $this->make('size', [
            'width' => $width,
            'height' => $height,
            'enlarge' => $enlarge,
            'extend' => $extend
        ]);
        return $size;
    }

    /**
     * When set, imgproxy will skip the processing of the listed formats. Also available as the
     * IMGPROXY_SKIP_PROCESSING_FORMATS configuration.
     * @param string ...$extensions
     * @return SkipProcessing
     * @throws InvalidOptionException
     */
    public function skipProcessing(string ...$extensions): SkipProcessing
    {
        /** @var SkipProcessing $skip_processing */
        /** @noinspection PhpUnhandledExceptionInspection */
        $skip_processing = $this->make('skip-processing', [ 'extensions' => $extensions ]);
        return $skip_processing;
    }

    /**
     * When set to true, imgproxy will transform the embedded color profile (ICC) to sRGB and remove it from
     * the image. Otherwise, imgproxy will try to keep it as is. This is normally controlled by the
     * IMGPROXY_STRIP_COLOR_PROFILE configuration but this processing option allows the configuration to be set for
     * each request.
     * @param bool $strip
     * @return StripColorProfile
     * @throws InvalidOptionException
     */
    public function stripColorProfile(bool $strip = true): StripColorProfile
    {
        /** @var StripColorProfile $strip_color_profile */
        /** @noinspection PhpUnhandledExceptionInspection */
        $strip_color_profile = $this->make('strip-color-profile', [ 'strip' => $strip ]);
        return $strip_color_profile;
    }

    /**
     * When set to true, imgproxy will strip the metadata (EXIF, IPTC, etc.) on JPEG and WebP output images. This is
     * normally controlled by the IMGPROXY_STRIP_METADATA configuration but this processing option allows the
     * configuration to be set for each request.
     * @param bool $value
     * @return StripMetadata
     * @throws InvalidOptionException
     */
    public function stripMetadata(bool $value = true): StripMetadata
    {
        /** @var StripMetadata $strip_metadata */
        /** @noinspection PhpUnhandledExceptionInspection */
        $strip_metadata = $this->make('strip-metadata', [ 'value' => $value ]);
        return $strip_metadata;
    }

    /**
     * Removes surrounding background.
     *
     * - threshold - color similarity tolerance.
     * - color - (optional) a hex-coded value of the color that needs to be cut off.
     * - equal_hor - (optional) set to 1, t or true, imgproxy will cut only equal parts from left and right sides. That means that if 10px of background can be cut off from the left and 5px from the right, then 5px will be cut off from both sides. For example, this can be useful if objects on your images are centered but have non-symmetrical shadow.
     * - equal_ver - (optional) acts like equal_hor but for top/bottom sides.
     *
     * Warning:
     *
     * Trimming requires an image to be fully loaded into memory. This disables scale-on-load and significantly
     * increases memory usage and processing time. Use it carefully with large images.
     *
     * Info:
     *
     * If you know background color of your images then setting it explicitly via color will also save some resources
     * because imgproxy won't need to automatically detect it.
     *
     * Use a color value of FF00FF for trimming transparent backgrounds as imgproxy uses magenta as a transparency key.
     *
     * @param string $threshold
     * @param string|null $color
     * @return Trim
     * @throws InvalidOptionException
     */
    public function trim(string $threshold, ?string $color = null): Trim
    {
        /** @var Trim $trim */
        /** @noinspection PhpUnhandledExceptionInspection */
        $trim = $this->make('trim', [ 'threshold' => $threshold, 'color' => $color ]);
        return $trim;
    }

    /**
     * Places a watermark on the processed image.
     *
     * - url: When set, imgproxy will use the image from the specified URL as a watermark. url is the URL-safe
     *   Base64-encoded URL of the custom watermark.
     * - opacity: watermark opacity modifier. Final opacity is calculated like base_opacity * opacity.
     * - position: (optional) specifies the position of the watermark. Available values:
     *   - ce: (default) center
     *   - no: north (top edge)
     *   - so: south (bottom edge)
     *   - ea: east (right edge)
     *   - we: west (left edge)
     *   - noea: north-east (top-right corner)
     *   - nowe: north-west (top-left corner)
     *   - soea: south-east (bottom-right corner)
     *   - sowe: south-west (bottom-left corner)
     *   - re: repeat and tile the watermark to fill the entire image
     *   - ch: pro same as re but watermarks are placed in a chessboard order
     * - x_offset, y_offset - (optional) specify watermark offset by X and Y axes:
     *   - When x_offset or y_offset is greater than or equal to 1 or less than or equal to -1, imgproxy treats it as
     *     an absolute value.
     *   - When x_offset or y_offset is less than 1 and greater than -1, imgproxy treats it as a relative value.
     *   - When using re or ch position, these values define the spacing between the tiles.
     * - scale: (optional) a floating-point number that defines the watermark size relative to the resultant image
     *   size. When set to 0 or when omitted, the watermark size won't be changed.
     *
     * Default: disabled
     *
     * @param int $opacity
     * @param string|null $position
     * @param int|null $x
     * @param int|null $y
     * @param float|null $scale
     * @return Watermark
     * @throws InvalidOptionException
     */
    public function watermark(int $opacity, ?string $position = null, ?int $x = null, ?int $y = null, ?float $scale = null): Watermark
    {
        /** @var Watermark $watermark */
        /** @noinspection PhpUnhandledExceptionInspection */
        $watermark = $this->make('watermark', [
            'opacity' => $opacity,
            'position' => $position,
            'x' => $x,
            'y' => $y,
            'scale' => $scale
        ]);
        return $watermark;
    }

    /**
     * Defines the width of the resulting image. When set to 0, imgproxy will calculate width using the defined height
     * and source aspect ratio. When set to 0 and resizing type is force, imgproxy will keep the original width.
     *
     * Default: 0
     *
     * @param int $width
     * @return Width
     * @throws InvalidOptionException
     */
    public function width(int $width): Width
    {
        /** @var Width $w */
        /** @noinspection PhpUnhandledExceptionInspection */
        $w = $this->make('width', [ 'value' => $width ]);
        return $w;
    }

    /**
     * When set, imgproxy will multiply the image dimensions according to these factors. The values must be greater than 0.
     *
     * Can be combined with width and height options. In this case, imgproxy calculates scale factors for the provided size
     * and then multiplies it with the provided zoom factors.
     *
     * Info:
     *
     * Unlike the dpr option, the zoom option doesn't affect gravities offsets, watermark offsets, and paddings.
     *
     * @param float $x
     * @param float|null $y
     * @return Zoom
     * @throws InvalidOptionException
     */
    public function zoom(float $x, ?float $y = null): Zoom
    {
        /** @var Zoom $zoom */
        /** @noinspection PhpUnhandledExceptionInspection */
        $zoom = $this->make('zoom', [ 'x' => $x, 'y' => $y ]);
        return $zoom;
    }
}