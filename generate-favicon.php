<?php
/**
 * TaskFlow v1.1 - Favicon Generator
 * Copyright (c) 2026 Florian Hesse
 * Fischer Str. 11, 16515 Oranienburg
 * https://comnic-it.de
 * Alle Rechte vorbehalten.
 */
$src = imagecreatefrompng(__DIR__ . '/logo.png');
$w = imagesx($src);
$h = imagesy($src);

// Crop top portion (icon without text) and make it square
$cropH = (int)($h * 0.62);
$cropW = $cropH;
$x = (int)(($w - $cropW) / 2);
$y = 0;

// Create 64x64 favicon
$ico = imagecreatetruecolor(64, 64);
imagealphablending($ico, false);
imagesavealpha($ico, true);
$transparent = imagecolorallocatealpha($ico, 0, 0, 0, 127);
imagefill($ico, 0, 0, $transparent);
imagealphablending($ico, true);
imagecopyresampled($ico, $src, 0, 0, $x, $y, 64, 64, $cropW, $cropH);
imagepng($ico, __DIR__ . '/favicon.png');

// Also create 32x32 version
$ico32 = imagecreatetruecolor(32, 32);
imagealphablending($ico32, false);
imagesavealpha($ico32, true);
imagefill($ico32, 0, 0, $transparent);
imagealphablending($ico32, true);
imagecopyresampled($ico32, $src, 0, 0, $x, $y, 32, 32, $cropW, $cropH);
imagepng($ico32, __DIR__ . '/favicon-32.png');

imagedestroy($src);
imagedestroy($ico);
imagedestroy($ico32);

echo "Favicons created!";
