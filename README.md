CoMa – PHP Color Math Library
===============

[![Latest Stable Version](https://img.shields.io/packagist/v/biano/coma.svg?style=flat-square&colorB=blue)](https://packagist.org/packages/biano/coma)
[![PHPStan](https://img.shields.io/badge/style-level%209-brightgreen.svg?style=flat-square&label=phpstan)](https://github.com/phpstan/phpstan)
[![Total Downloads](https://img.shields.io/packagist/dt/biano/coma.svg?style=flat-square&colorB=blue)](https://packagist.org/packages/biano/coma/stats)
[![Software License](https://img.shields.io/github/license/bianocz/coma-php.svg?style=flat-square&colorB=blue)](./LICENSE)

Php library to convert between [sRGB](//en.wikipedia.org/wiki/SRGB), [XYZ](//en.wikipedia.org/wiki/XYZ_color_space), and [Lab](//en.wikipedia.org/wiki/Lab_color_space) color spaces, and calculate various [color distance metrics](//en.wikipedia.org/wiki/Color_difference) (delta E). 

Currently CIE76, CIE94 and CIEDE2000 are implemented.

```php
use Biano\Coma\ColorDistance;
use Biano\Coma\sRGB;

$color1 = new sRGB(1, 5, 250);
$color2 = new sRGB(0, 0, 208);

$cd = new ColorDistance;
$cie94 = $cd->cie94($color1, $color2);

echo 'The CIE94 ∆E is ' . $cie94 . ' between ' . $color1->toHex() . ' and ' . $color2->toHex() . '.';
```
