<?php

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
        OptionBuilder::setDefaults([]);
        $this->builder = new OptionBuilder();
    }

    public function testDefaults()
    {
        OptionBuilder::setDefaults([
            'auto-rotate' => [
                'rotate' => true
            ],
            'background' => [
                'color' => '000000'
            ],
        ]);

        $this->builder = new OptionBuilder(); // do this again to get the default options populated by the constructor

        Verify::Array($this->builder->getOptions())
            ->count(2)
            ->containsOnlyInstancesOf(AbstractOption::class);
    }

    public function testOptionsAreValid()
    {
        foreach (array_keys(OptionBuilder::OPTION_MAP) as $option) {
            $this->assertTrue($this->builder->isValid($option));
        }

        $this->assertFalse($this->builder->isValid('invalid'));
    }

    public function testMakeOption()
    {
        $this->assertInstanceOf(Blur::class, $this->builder->make('blur', [ 'sigma' => 0.3 ]));

        $this->assertThrows(InvalidArgumentException::class, function () {
            $this->builder->make('blur', [ 'sigma' => -0.5 ]);
        });
    }

    // -------------------------------------------------------------------------------------------------------------
    // Option methods...

    // tests
    public function testAutoRotate()
    {
        $auto_rotate_true = $this->builder->autoRotate(true);
        $auto_rotate_false = $this->builder->autoRotate(false);

        verify($auto_rotate_true)->instanceOf(AutoRotate::class);
        verify($auto_rotate_false)->instanceOf(AutoRotate::class);

        Verify::Array($auto_rotate_true->data())->count(1)->containsEquals(1);
        Verify::Array($auto_rotate_false->data())->count(1)->containsEquals(0);
    }

    public function testBackground()
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

    public function testBlur()
    {
        $this->assertInstanceOf(Blur::class, $this->builder->blur(0.3));

        $this->assertThrows(InvalidArgumentException::class, function () {
            $this->builder->blur(-0.5);
        });
    }

    public function testCacheBuster()
    {
        $result = $this->builder->cacheBuster('some-value');
        $this->assertInstanceOf(CacheBuster::class, $result);
    }

    public function testCrop()
    {
        $result = $this->builder->crop(300, 200, '1:2');
        $this->assertInstanceOf(Crop::class, $result);
    }

    public function testDpr()
    {
        $result = $this->builder->dpr(2);
        $this->assertInstanceOf(Dpr::class, $result);
    }
}
