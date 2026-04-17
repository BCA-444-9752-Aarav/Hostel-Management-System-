<?php
// Generate a simple QR code placeholder
header('Content-Type: image/png');
$width = 200;
$height = 200;
$image = imagecreate($width, $height);

// Colors
$black = imagecolorallocate($image, 0, 0, 0);
$white = imagecolorallocate($image, 255, 255, 255);

// Fill background
imagefill($image, 0, 0, $white);

// Create a simple QR-like pattern
for ($x = 0; $x < $width; $x += 10) {
    for ($y = 0; $y < $height; $y += 10) {
        if (rand(0, 1)) {
            imagefilledrectangle($image, $x, $y, $x + 8, $y + 8, $black);
        }
    }
}

// Add center square
imagefilledrectangle($image, 80, 80, 120, 120, $white);
imagefilledrectangle($image, 85, 85, 115, 115, $black);
imagefilledrectangle($image, 90, 90, 110, 110, $white);
imagefilledrectangle($image, 95, 95, 105, 105, $black);

imagepng($image);
imagedestroy($image);
?>
