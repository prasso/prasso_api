@php
    $hex = $site->main_color ?? null;

    $hex = is_string($hex) && strlen($hex) > 0 ? $hex : null;

    $hexToRgb = function (?string $color) {
        if (!$color) return null;
        $color = ltrim($color, '#');
        if (strlen($color) === 3) {
            $color = str_repeat($color[0], 2) . str_repeat($color[1], 2) . str_repeat($color[2], 2);
        }
        if (strlen($color) !== 6) return null;
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        return [$r, $g, $b];
    };

    $rgbToTuple = function (?array $rgb) {
        if (!$rgb) return null;
        return $rgb[0] . ' ' . $rgb[1] . ' ' . $rgb[2];
    };

    $relativeLuminance = function (array $rgb) {
        [$r,$g,$b] = $rgb;
        $toLinear = function ($c) {
            $c = $c / 255;
            return $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
        };
        $R = $toLinear($r);
        $G = $toLinear($g);
        $B = $toLinear($b);
        return 0.2126*$R + 0.7152*$G + 0.0722*$B;
    };

    $pickForeground = function (array $bgRgb) use ($relativeLuminance) {
        // Choose white for dark backgrounds, black for light backgrounds
        $lum = $relativeLuminance($bgRgb);
        return $lum < 0.6 ? [255,255,255] : [17,24,39]; // white vs gray-900
    };

    // Derive shades using Site::adjustBrightness if available
    $c500 = $hex;
    $c600 = $hex ? $site->adjustBrightness($hex, -15) : null;
    $c700 = $hex ? $site->adjustBrightness($hex, -30) : null;
    $c800 = $hex ? $site->adjustBrightness($hex, -45) : null;

    $rgb500 = $hexToRgb($c500);
    $rgb600 = $hexToRgb($c600);
    $rgb700 = $hexToRgb($c700);
    $rgb800 = $hexToRgb($c800);

    $fgRgb = $rgb500 ? $pickForeground($rgb500) : null;
@endphp

@if ($rgb500 && $rgb600 && $rgb700 && $rgb800)
<style>
:root{
    --primary-500: {{ $rgbToTuple($rgb500) }};
    --primary-600: {{ $rgbToTuple($rgb600) }};
    --primary-700: {{ $rgbToTuple($rgb700) }};
    --primary-800: {{ $rgbToTuple($rgb800) }};
    @if ($fgRgb)
    --primary-foreground: {{ $rgbToTuple($fgRgb) }};
    @endif
}
</style>
@endif
