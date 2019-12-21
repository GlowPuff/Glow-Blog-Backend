<?php
function make_thumb($src, $dest, $desired_width)
{
	//Crop height to 16:9 ratio
    $max_height = $desired_width * .5625;

	$source_image = imagecreatefromjpeg($src);
    $width = imagesx($source_image);
    $height = imagesy($source_image);

    //Crop height to 16:9 ratio
    if ($height > $width) {
        $height = $width * .5625;
    }

	//Calculate height based on $desired_width
    $desired_height = min(floor($height * ($desired_width / $width)), $max_height);

	//Virtual image with new resized dimensions
    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

    //Resample original image into resized image
    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

    //Save the new thumbnail
    imagejpeg($virtual_image, $dest, 83);
}
