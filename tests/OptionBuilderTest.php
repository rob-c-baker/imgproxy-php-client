<?php declare(strict_types=1);

namespace Tests\Unit;

use Alanrogers\ImgproxyPhpClient\OptionBuilder;
use Codeception\AssertThrows;
use Codeception\Test\Unit;
use Codeception\Verify\Verify;
use InvalidArgumentException;
use Onliner\ImgProxy\Options\AbstractOption;
use Onliner\ImgProxy\Options\AutoRotate;
use Onliner\ImgProxy\Options\Background;
use Onliner\ImgProxy\Options\Blur;
use Onliner\ImgProxy\Options\CacheBuster;
use Onliner\ImgProxy\Options\Crop;
use Onliner\ImgProxy\Options\Dpr;

class OptionBuilderTest extends Unit
{
    use AssertThrows;

    private OptionBuilder $builder;

    protected function _before(): void
    {
        $this->builder = new OptionBuilder();
    }

    public function testDefaults(): void
    {
        $this->builder->setDefaults([
            'auto-rotate' => [
                'rotate' => true
            ],
            'background' => [
                'color' => '000000'
            ],
        ]);

        Verify::Array($this->builder->getOptions())
            ->count(2)
            ->containsOnlyInstancesOf(AbstractOption::class);
    }

    public function testOptionsAreValid(): void
    {
        foreach (array_keys(OptionBuilder::OPTION_MAP) as $option) {
            $this->assertTrue($this->builder->isValid($option));
        }

        $this->assertFalse($this->builder->isValid('invalid'));
    }

    public function testMakeOption(): void
    {
        $this->assertInstanceOf(Blur::class, $this->builder->make('blur', [ 'sigma' => 0.3 ]));

        $this->assertThrows(InvalidArgumentException::class, function () {
            $this->builder->make('blur', [ 'sigma' => -0.5 ]);
        });
    }

    // -------------------------------------------------------------------------------------------------------------
    // Option methods...

    // tests
    public function testAutoRotate(): void
    {
        $auto_rotate_true = $this->builder->autoRotate(true);
        $auto_rotate_false = $this->builder->autoRotate(false);

        verify($auto_rotate_true)->instanceOf(AutoRotate::class);
        verify($auto_rotate_false)->instanceOf(AutoRotate::class);

        Verify::Array($auto_rotate_true->data())->count(1)->containsEquals(1);
        Verify::Array($auto_rotate_false->data())->count(1)->containsEquals(0);
    }

    public function testBackground(): void
    {
        $this->assertThrows(InvalidArgumentException::class, function () {
            $this->builder->background('red');
        });

        $bg = $this->builder->background('ffffff');
        verify($bg)->instanceOf(Background::class);
        Verify::Array($bg->data())->count(1)->containsEquals('ffffff');

        $bg = $this->builder->background('100:200:255');
        Verify::Array($bg->data())->count(1)->containsEquals('100:200:255');
    }

    public function testBlur(): void
    {
        $this->assertInstanceOf(Blur::class, $this->builder->blur(0.3));

        $this->assertThrows(InvalidArgumentException::class, function () {
            $this->builder->blur(-0.5);
        });
    }

    public function testCacheBuster(): void
    {
        $result = $this->builder->cacheBuster('some-value');
        $this->assertInstanceOf(CacheBuster::class, $result);
    }

    public function testCrop(): void
    {
        $result = $this->builder->crop(300, 200, 'ce:0:0'); // @todo test more gravities
        $this->assertInstanceOf(Crop::class, $result);
    }

    public function testDpr(): void
    {
        $result = $this->builder->dpr(2);
        $this->assertInstanceOf(Dpr::class, $result);
    }
}
