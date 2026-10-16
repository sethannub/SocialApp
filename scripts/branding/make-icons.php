<?php

/**
 * Builds every app icon in app/public from two 1024px masters (rendered by build-icons.ps1).
 * Usage (inside the shoutrrr:dev image, which has GD):
 *   php make-icons.php <dir with master-round.png + master-full.png> <public dir>
 */

[$script, $masters, $public] = $argv + [null, null, null];

if ($masters === null || $public === null) {
    fwrite(STDERR, "usage: php make-icons.php <masters dir> <public dir>\n");
    exit(1);
}

$round = imagecreatefrompng("{$masters}/master-round.png");
$full = imagecreatefrompng("{$masters}/master-full.png");

/** @var array<string, array{0: int, 1: GdImage}> $targets */
$targets = [
    'shoutrrr.png' => [512, $round],
    'favicon-16x16.png' => [16, $round],
    'favicon-32x32.png' => [32, $round],
    'favicon-48x48.png' => [48, $round],
    'android-chrome-192x192.png' => [192, $round],
    'android-chrome-512x512.png' => [512, $round],
    'icon-16.png' => [16, $round],
    'icon-32.png' => [32, $round],
    'icon-48.png' => [48, $round],
    'icon-64.png' => [64, $round],
    'icon-96.png' => [96, $round],
    'icon-128.png' => [128, $round],
    'apple-touch-icon.png' => [180, $full],
    'icon-180.png' => [180, $full],
    'mstile-150x150.png' => [150, $full],
    'icon-192.png' => [192, $full],
    'icon-512.png' => [512, $full],
];

function resized(GdImage $source, int $size): GdImage
{
    $out = imagecreatetruecolor($size, $size);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
    imagecopyresampled($out, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));

    return $out;
}

$icoEntries = [];

foreach ($targets as $file => [$size, $source]) {
    $image = resized($source, $size);
    imagepng($image, "{$public}/{$file}", 9);
    echo "{$file} {$size}x{$size}\n";

    if (in_array($file, ['favicon-16x16.png', 'favicon-32x32.png', 'favicon-48x48.png'], true)) {
        $icoEntries[$size] = (string) file_get_contents("{$public}/{$file}");
    }
}

// favicon.ico: ICO container holding the 16/32/48 PNGs (PNG-in-ICO, supported by all current browsers).
ksort($icoEntries);
$header = pack('vvv', 0, 1, count($icoEntries));
$directory = '';
$data = '';
$offset = 6 + 16 * count($icoEntries);

foreach ($icoEntries as $size => $png) {
    $directory .= pack('CCCCvvVV', $size, $size, 0, 0, 1, 32, strlen($png), $offset);
    $data .= $png;
    $offset += strlen($png);
}

file_put_contents("{$public}/favicon.ico", $header.$directory.$data);
echo "favicon.ico 16/32/48\n";

// Bounds of the non-transparent artwork in the mail logo, for AppIconAssetsTest.
$logo = imagecreatefrompng("{$public}/shoutrrr.png");
[$left, $top, $right, $bottom] = [imagesx($logo), imagesy($logo), 0, 0];

for ($y = 0; $y < imagesy($logo); $y++) {
    for ($x = 0; $x < imagesx($logo); $x++) {
        if (((imagecolorat($logo, $x, $y) & 0x7F000000) >> 24) < 127) {
            $left = min($left, $x);
            $top = min($top, $y);
            $right = max($right, $x);
            $bottom = max($bottom, $y);
        }
    }
}

echo "bounds left={$left} right={$right} top={$top} bottom={$bottom}\n";
