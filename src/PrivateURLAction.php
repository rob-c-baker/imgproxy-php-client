<?php declare(strict_types=1);

namespace Alanrogers\ImgproxyPhpClient;

use Craft;
use craft\elements\Asset;

use craft\errors\FsException;
use Imagick;
use ImagickDraw;
use ImagickDrawException;
use ImagickException;
use ImagickPixel;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\Response;

/**
 * An action to add to a Craft CMS instance to return the contents of an asset - usually one with a private URL.
 */
class PrivateURLAction extends Action
{
    private const int DEFAULT_ERROR_IMAGE_WIDTH = 300;
    private const int DEFAULT_ERROR_IMAGE_HEIGHT = 300;

    private const int ERROR_IMAGE_CACHE_TTL = 3600;
    private string $allowed_token;

    public function __construct($id, $controller, $config = [])
    {
        parent::__construct($id, $controller, $config);

        $this->allowed_token = getenv('IMGPROXY_REQUEST_TOKEN');
    }

    public function run(): Response
    {
        // Check the auth / secret token
        $auth_header = $this->controller->request->headers->get('Authorization');
        if (!$auth_header || !str_starts_with($auth_header, 'Bearer ')) {
            $this->controller->response->setStatusCode(403);
            $this->controller->response->content = 'Unauthorised';
            return $this->controller->response;
        }

        $token = substr($auth_header, 7);
        if ($token !== $this->allowed_token) {
            $this->controller->response->setStatusCode(403);
            $this->controller->response->content = 'Unauthorised';
            return $this->controller->response;
        }

        // Required params:
        $asset_id = $this->controller->request->getQueryParam('id');

        // @todo Note: use make sure to encrypt the source URL imgproxy uses: https://docs.imgproxy.net/usage/encrypting_source_url
        // @todo Note: use `IMGPROXY_CUSTOM_REQUEST_HEADERS` to set a `Authorization: Bearer %token%` header

        // Optional params:
        $width = $this->controller->request->getQueryParam('w');
        $height = $this->controller->request->getQueryParam('h');
        $width = $width ? (int) $width : null;
        $height = $height ? (int) $height : null;

        $this->controller->response->format = Response::FORMAT_RAW;

        /** @var Asset $asset */
        $asset = Asset::find()->id($asset_id)->one();
        if ($asset === null) {
            $this->controller->response->setStatusCode(404);
            $this->controller->response->headers->set('Content-Type', 'image/png');
            $this->controller->response->content = $this->getErrorImage('Image not found', $width, $height);
            return $this->controller->response;
        }

        // @todo check that nginx is not gzipping this response as that is an output buffer that will prevent it streaming properly

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->controller->response->headers->set('Content-Type', $asset->getMimeType());
        try {
            $this->controller->response->stream = $asset->getStream();
        } catch (FsException|InvalidConfigException $e) {
            Craft::error('Could not create asset stream: ' . $e->getMessage(), 'PrivateURLAction');
        }

        return $this->controller->response;
    }

    private function getErrorImage(string $message=null, int $width=null, int $height=null): ?string
    {
        $data = $this->getCachedImage($message, $width, $height);
        if ($data !== null) {
            return $data;
        }

        $image = new Imagick();

        try {
            $image->newImage(
                $width ?? self::DEFAULT_ERROR_IMAGE_WIDTH,
                $height ?? self::DEFAULT_ERROR_IMAGE_HEIGHT,
                new ImagickPixel('rgb(232 232 232)'),
                'png'
            );

            $image->setImageFormat('png');

            if ($message) {

                $ctx = new ImagickDraw();
                $ctx->setFillColor(new ImagickPixel('rgb(74 74 74)'));
                $ctx->setFontSize(20);
                $ctx->setGravity(Imagick::GRAVITY_CENTER);

                $image->annotateImage($ctx, 0, 0, 0, $message);
            }

            $data = $image->getImageBlob();

            $this->setCachedImage($data, $message, $width, $height);

        } catch (ImagickException $e) {
            Craft::error('Could not create error image: ' . $e->getMessage(), 'PrivateURLAction');
        } catch (ImagickDrawException $e) {
            Craft::error('Could not create error image text: ' . $e->getMessage(), 'PrivateURLAction');
        }

        return $data;
    }

    private function getCachedImage(string $message=null, int $width=null, int $height=null): ?string
    {
        $cache_key = md5(
            ($message ?? '') .
            ($width ?? self::DEFAULT_ERROR_IMAGE_WIDTH) .
            ($height ?? self::DEFAULT_ERROR_IMAGE_HEIGHT)
        );

        if (Craft::$app->getCache()->exists($cache_key)) {
            return Craft::$app->getCache()->get($cache_key);
        }

        return null;
    }

    private function setCachedImage(string $data, string $message=null, int $width=null, int $height=null): void
    {
        $cache_key = md5(
            ($message ?? '') .
            ($width ?? self::DEFAULT_ERROR_IMAGE_WIDTH) .
            ($height ?? self::DEFAULT_ERROR_IMAGE_HEIGHT)
        );

        Craft::$app->getCache()->set($cache_key, $data, self::ERROR_IMAGE_CACHE_TTL);
    }
}