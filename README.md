# Image Proxy PHP Client

A PHP client to generate URLs for images on an `imgproxy` instance.

Also includes Twig Extension for use in Twig projects (like Craft CMS).

## Usage

Get the Image client index to inject into a service provider, or to use from a plain PHP context:

```php
use ImgproxyPhpClient\ImageClientFactory;

$key = getenv('IMGPROXY_KEY');
$salt = getenv('IMGPROXY_SALT');

$client = ImageClientFactory::getInstance($key, $salt);
```

Or if if you don't need an instance in a service provider but still want to use the Twig extension:

```php
use ImgproxyPhpClient\ImageClientFactory;

$key = getenv('IMGPROXY_KEY');
$salt = getenv('IMGPROXY_SALT');

ImageClientFactory::setInstance($key, $salt, true);
```

To use the Twig extension:

```php
use ImgproxyPhpClient\TwigExtension;
$extension = new TwigExtension();
// Then inject into Twig
```
