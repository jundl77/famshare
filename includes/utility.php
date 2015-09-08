<?php

function make_thumb($src, $dest, $fileExt, $desired_width)
{
    $exif = exif_read_data($src, 'IFD0');

    // Read the source image
    if ($fileExt == 'gif') {
        $source_image = imagecreatefromgif($src);
    } elseif ($fileExt == 'jpg' || $fileExt == 'jpeg') {
        $source_image = imagecreatefromjpeg($src);
    } elseif ($fileExt == 'png') {
        $source_image = imagecreatefrompng($src);
    } elseif ($fileExt == 'wbmp') {
        $source_image = imagecreatefromwbmp($src);
    } else {
        // Return if not an image
        return;
    }

    // Fix Orientation of original image - this has to be done because of stupid apple products who can't save their
    // orientation like everybody else, ugh!
    switch ($exif['Orientation']) {
        case 3:
            $source_image = imagerotate($source_image, 180, 0);
            break;
        case 6:
            $source_image = imagerotate($source_image, -90, 0);
            break;
        case 8:
            $source_image = imagerotate($source_image, 90, 0);
            break;
    }

    // Save turned source image again (overwrites false orientation)
    if ($fileExt == 'gif') {
        imagegif($source_image, $src);
    } elseif ($fileExt == 'jpg' || $fileExt == 'jpeg') {
        imagejpeg($source_image, $src);
    } elseif ($fileExt == 'png') {
        imagepng($source_image, $src);
    } elseif ($fileExt == 'bmp') {
        imagewbmp($source_image, $src);
    }

    $width = imagesx($source_image);
    $height = imagesy($source_image);

    // Find the "desired height" of this thumbnail, relative to the desired width
    $desired_height = floor($height * ($desired_width / $width));

    // Create a new, "virtual" image
    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

    // Copy source image at a resized size
    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

    // Create the physical thumbnail image to its destination
    if ($fileExt == 'gif') {
        imagegif($virtual_image, $dest);
    } elseif ($fileExt == 'jpg' || $fileExt == 'jpeg') {
        imagejpeg($virtual_image, $dest);
    } elseif ($fileExt == 'png') {
        imagepng($virtual_image, $dest);
    } elseif ($fileExt == 'bmp') {
        imagewbmp($virtual_image, $dest);
    }
}