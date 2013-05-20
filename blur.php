<?php

/**
 * @param $image | image resource
 * @param float $blurPercentage 
 * @return image resource | a new image, does not edit the original image argument
 */
function blur($image, $blurPercentage){
	$width = imagesx($image);
	$height = imagesy($image);
	$blurFactor = round($width * ($blurPercentage/100))?:1; //minimum 1
	
	$newImage = imagecreatetruecolor($width, $height);
	$blankColour = array(
				'red' => 0,
				'green' => 0,
				'blue' => 0
			);//for convience. Don't want to create an array width*height times
			
	//bounds:
	$minX = $minY = 0;
	$maxX = $width - 1;
	$maxY = $height - 1;

	for($x = 0; $x < $width; $x++){//horizontal
		for($y = 0; $y < $height; $y++){//vertical
			
			$currentPixelColour = getColourFromPixel($image, $x, $y);
			$colourSums = $blankColour;//start with a blank RGB array
			
			//bounds; get surrounding pixels of the current one (distance determined by blurFactor)
			$firstNeighbourX = $x - $blurFactor;
			$firstNeighbourX = $firstNeighbourX >= $minX ? $firstNeighbourX : $minX; //bounds check
			
			$lastNeighbourX = $x + $blurFactor;		
			$lastNeighbourX = $lastNeighbourX <= $maxX ? $lastNeighbourX : $maxX;
			
			$firstNeighbourY = $y - $blurFactor;
			$firstNeighbourY = $firstNeighbourY >= $minY ? $firstNeighbourY : $minY;
			
			$lastNeighbourY = $y + $blurFactor;
			$lastNeighbourY = $lastNeighbourY <= $maxY ? $lastNeighbourY : $maxY;
			
			//go through each neighbour (actually includes current pixel too) and increment the sum of each channel (rgb)
			for($neighbourX = $firstNeighbourX; $neighbourX < $lastNeighbourX; $neighbourX++){
				for($neighbourY = $firstNeighbourY; $neighbourY < $lastNeighbourY; $neighbourY++){
									
					$currentNeighbourColour = getColourFromPixel($image, $neighbourX, $neighbourY));

					foreach($colourSums as $channel => &$sum){
						$sum += $currentNeighbourColour[$channel];
					}
				}
			}
			
			//compute the average of each channel
			$numberOfNeighbours = ($lastNeighbourX - $firstNeighbourX) * ($lastNeighbourY - $firstNeighbourY);			
			foreach($colourSums as $channel => &$sum){
				$currentPixelColour[$channel] = round($sum/$numberOfNeighbours);
			}		
			
			$newColour = imagecolorallocate(
					$newImage, 
					$currentPixelColour['red'], 
					$currentPixelColour['green'], 
					$currentPixelColour['blue']
				);		
			imagesetpixel($newImage, $x, $y, $newColour);//override current pixel in new image with average
		}
	}
	
	return $newImage;
}
	
	
/**
 * @param $image | image resource
 * @param int $x
 * @param int $y
 *
 * @return image resource
 */
function getColourFromPixel($image, $x, $y){
	return imagecolorsforindex($image, imagecolorat($image, $neighbourX, $neighbourY));
}


/************************************************************************************
 * Example usage:
 ************************************************************************************/

ini_set('max_execution_time', 500);
$blurPercentage = (float) ( isset($_GET['blur']) ?  $_GET['blur'] : 1 );

$image = imagecreatefromjpeg("example.jpg");
$newImage = blur($image, 5);

imagedestroy($image);//free up memory

//Ouput the blurred image:

header('Content-Type: image/jpeg');//MIME type
imagejpeg($newImage);//create and output the image

imagedestroy($newImage);