<?php

declare(strict_types = 1);

namespace Biano\Coma;

use PHPUnit\Framework\TestCase;

class ColorDistanceTest extends TestCase
{

    private ColorDistance $colorDistance;

    /** @var list<\Biano\Coma\sRGB> */
    private array $colors;

    protected function setUp(): void
    {
        $this->colorDistance = new ColorDistance();
        $this->colors = [
            new sRGB(1, 5, 250),
            new sRGB(0, 0, 208),
            new sRGB(0, 0, 0),
            new sRGB(255, 255, 255),
        ];
    }

    public function testSimple(): void
    {
        $simple = $this->colorDistance->simpleRgbDistance($this->colors[0], $this->colors[1]);

        self::assertEqualsWithDelta(9.57912207, $simple, 0.0001);
    }

    public function testCie76(): void
    {
        $cie76 = $this->colorDistance->cie76($this->colors[0], $this->colors[1]);

        self::assertEqualsWithDelta(17.4763756, $cie76, 0.0001);
    }

    public function testCie94(): void
    {
        $cie94 = $this->colorDistance->cie94($this->colors[0], $this->colors[1]);

        self::assertEqualsWithDelta(6.840347, $cie94, 0.0001);
    }

    public function testCiede2000(): void
    {
        $ciede2000 = $this->colorDistance->ciede2000($this->colors[0], $this->colors[1]);

        self::assertEqualsWithDelta(5.486647, $ciede2000, 0.0001);
    }

    public function testMaxDistance(): void
    {
        $cie94 = $this->colorDistance->cie94($this->colors[2], $this->colors[3]);
        $cie76 = $this->colorDistance->cie76($this->colors[2], $this->colors[3]);
        $ciede2000 = $this->colorDistance->ciede2000($this->colors[2], $this->colors[3]);
        $simple = $this->colorDistance->simpleRgbDistance($this->colors[2], $this->colors[3]);

        self::assertEqualsWithDelta(100.0, $cie76, 0.0001);
        self::assertEqualsWithDelta(100.0, $cie94, 0.0001);
        self::assertEqualsWithDelta(100.0, $ciede2000, 0.0001);
        self::assertEqualsWithDelta(100.0, $simple, 0.0001);
    }

    public function testNoDistance(): void
    {
        $cie94 = $this->colorDistance->cie94($this->colors[2], $this->colors[2]);
        $cie76 = $this->colorDistance->cie76($this->colors[2], $this->colors[2]);
        $ciede2000 = $this->colorDistance->ciede2000($this->colors[2], $this->colors[2]);
        $simple = $this->colorDistance->simpleRgbDistance($this->colors[2], $this->colors[2]);

        self::assertEqualsWithDelta(0.0, $cie76, 0.0001);
        self::assertEqualsWithDelta(0.0, $cie94, 0.0001);
        self::assertEqualsWithDelta(0.0, $ciede2000, 0.0001);
        self::assertEqualsWithDelta(0.0, $simple, 0.0001);
    }

}
