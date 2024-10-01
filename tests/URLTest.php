<?php declare(strict_types=1);

namespace Tests\Unit;

use Alanrogers\ImgproxyPhpClient\ImageClient;
use Alanrogers\ImgproxyPhpClient\ImageClientFactory;
use Codeception\AssertThrows;
use Codeception\Test\Unit;

class URLTest extends Unit
{
    use AssertThrows;

    private const string KEY = '37cb34fc5e380f06ba3f39a93552ff64a9aca339b079b4803b86812ef297c6b3';
    private const string SALT = 'b4da265e11b529e275944e7e38dbee428efe9afe782d30024ce42dd60553d78b';

    private ImageClient $client;

    protected function _before(): void
    {

    }

    public function testFactory(): void
    {
        $this->client = ImageClientFactory::getInstance(
            self::KEY,
            self::SALT
        );

        $this->assertInstanceOf(ImageClient::class, $this->client);

    }

    public function testURL(): void
    {
        $this->client = new ImageClient(
            self::KEY,
            self::SALT,
            true
        );

        $url = $this->client->imageURL(
            'https://static.alanrogers.com/assets/images/icons/apple-touch-icon.png',
            [],
            'png',
            false,
            false
        );
        codecept_debug($url);

    }
}