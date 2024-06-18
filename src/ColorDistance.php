<?php

declare(strict_types = 1);

namespace Biano\Coma;

use function abs;
use function atan2;
use function cos;
use function exp;
use function pow;
use function sin;
use function sqrt;

/**
 * References used:
 * @link https://en.wikipedia.org/wiki/Color_difference
 * @link https://en.wikipedia.org/wiki/Lab_color_space
 * @link https://en.wikipedia.org/wiki/SRGB_color_space
 * @link https://github.com/THEjoezack/ColorMine (.NET example code)
 * @link https://gist.github.com/mikelikespie/641528 (JS example code).
 */
class ColorDistance
{

    /**
     * DeltaE calculation using the CIE76 formula.
     * Delta = 2.3 corresponds to a just noticeable difference.
     *
     * 1. assume that your RGB values are in the sRGB colorspace
     * 2. convert sRGB colors to L*a*b*
     * 3. compute deltaE between your two L*a*b* values.
     */
    public function cie76(Lab|XYZ|sRGB $color1, Lab|XYZ|sRGB $color2): float
    {
        $f1 = $this->toLab($color1);
        $f2 = $this->toLab($color2);

        $deltaL = $f2->l - $f1->l;
        $deltaA = $f2->a - $f1->a;
        $deltaB = $f2->b - $f1->b;

        $deltaE = $deltaL * $deltaL + $deltaA * $deltaA + $deltaB * $deltaB;

        return $deltaE < 0 ? 0 : sqrt($deltaE);
    }

    /**
     * DeltaE calculation using the CIE94 formula.
     * Delta = 2.3 corresponds to a just noticeable difference.
     */
    public function cie94(Lab|XYZ|sRGB $color1, Lab|XYZ|sRGB $color2): float
    {
        $Kl = 1.0;
        $K1 = .045;
        $K2 = 0.015;

        $Kc = 1.0;
        $Kh = 1.0;

        $f1 = $this->toLab($color1);
        $f2 = $this->toLab($color2);

        $deltaL = $f2->l - $f1->l;
        $deltaA = $f2->a - $f1->a;
        $deltaB = $f2->b - $f1->b;

        $c1 = sqrt($f1->a * $f1->a + $f1->b * $f1->b);
        $c2 = sqrt($f2->a * $f2->a + $f2->b * $f2->b);
        $deltaC = $c2 - $c1;

        $deltaH = $deltaA * $deltaA + $deltaB * $deltaB - $deltaC * $deltaC;
        $deltaH = $deltaH < 0 ? 0 : sqrt($deltaH);

        $Sl = 1.0;
        $Sc = 1 + $K1 * $c1;
        $Sh = 1 + $K2 * $c1;

        $deltaLKlsl = $deltaL / ($Kl * $Sl);
        $deltaCkcsc = $deltaC / ($Kc * $Sc);
        $deltaHkhsh = $deltaH / ($Kh * $Sh);

        $deltaE = $deltaLKlsl * $deltaLKlsl + $deltaCkcsc * $deltaCkcsc + $deltaHkhsh * $deltaHkhsh;

        return $deltaE < 0 ? 0 : sqrt($deltaE);
    }

    /**
     * Color Distance CIEDE2000
     *
     * @link http://www.ece.rochester.edu/~gsharma/ciede2000/ciede2000noteCRNA.pdf
     */
    public function ciede2000(Lab|XYZ|sRGB $color1, Lab|XYZ|sRGB $color2): float
    {
        $f1 = $this->toLab($color1);
        $f2 = $this->toLab($color2);

        $l1 = $f1->l;
        $a1 = $f1->a;
        $b1 = $f1->b;
        $l2 = $f2->l;
        $a2 = $f2->a;
        $b2 = $f2->b;

        $c1 = sqrt(pow($a1, 2) + pow($b1, 2));
        $c2 = sqrt(pow($a2, 2) + pow($b2, 2));
        $cb = ($c1 + $c2) / 2;

        $g = .5 * (1 - sqrt(pow($cb, 7) / (pow($cb, 7) + pow(25, 7))));

        $a1p = (1 + $g) * $a1;
        $a2p = (1 + $g) * $a2;

        $c1p = sqrt(pow($a1p, 2) + pow($b1, 2));
        $c2p = sqrt(pow($a2p, 2) + pow($b2, 2));

        $h1p = $a1p === 0.0 && $b1 === 0.0 ? 0.0 : atan2($b1, $a1p);
        $h2p = $a2p === 0.0 && $b2 === 0.0 ? 0.0 : atan2($b2, $a2p);

        $lpDelta = $l2 - $l1;
        $cpDelta = $c2p - $c1p;

        if ($c1p * $c2p === 0.0) {
            $hpDelta = 0;
        } elseif (abs($h2p - $h1p) <= 180) {
            $hpDelta = $h2p - $h1p;
        } elseif ($h2p - $h1p > 180) {
            $hpDelta = $h2p - $h1p - 360;
        } else {
            $hpDelta = $h2p - $h1p + 360;
        }

        $hp1Delta = 2 * sqrt($c1p * $c2p) * sin($hpDelta / 2);

        $lbp = ($l1 + $l2) / 2;
        $cbp = ($c1p + $c2p) / 2;

        if ($c1p * $c2p === 0.0) {
            $hbp = $h1p + $h2p;
        } elseif (abs($h1p - $h2p) <= 180) {
            $hbp = ($h1p + $h2p) / 2;
        } elseif ($h1p + $h2p < 360) {
            $hbp = ($h1p + $h2p + 360) / 2;
        } else {
            $hbp = ($h1p + $h2p - 360) / 2;
        }

        $t = 1 - .17 * cos($hbp - 30) + .24 * cos(2 * $hbp) + .32 * cos(3 * $hbp + 6) - .2 * cos(4 * $hbp - 63);

        $sigmaDelta = 30 * exp(-pow(($hbp - 275) / 25, 2));

        $rc = 2 * sqrt(pow($cbp, 7) / (pow($cbp, 7) + pow(25, 7)));

        $sl = 1 + (.015 * pow($lbp - 50, 2) / sqrt(20 + pow($lbp - 50, 2)));
        $sc = 1 + .045 * $cbp;
        $sh = 1 + .015 * $cbp * $t;

        $rt = -sin(2 * $sigmaDelta) * $rc;

        return sqrt(
            pow($lpDelta / $sl, 2) +
            pow($cpDelta / $sc, 2) +
            pow($hp1Delta / $sh, 2) +
            $rt * $cpDelta / $sc * $hp1Delta / $sh,
        );
    }

    /**
     * Not very useful, but interesting to compare.
     */
    public function simpleRgbDistance(sRGB $color1, sRGB $color2): float
    {
        $deltaR = ($color2->r - $color1->r) / 255;
        $deltaG = ($color2->g - $color1->g) / 255;
        $deltaB = ($color2->b - $color1->b) / 255;
        $deltaE = $deltaR * $deltaR + $deltaG * $deltaG + $deltaB * $deltaB;

        return $deltaE < 0
            ? $deltaE
            : sqrt($deltaE) * 57.73502691896258;  //  / sqrt(3) * 100;
    }

    private function toLab(Lab|XYZ|sRGB $color): Lab
    {
        if ($color instanceof Lab) {
            return $color;
        }

        return $color->toLab();
    }

}
