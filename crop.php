<?php
/* turn off error reporting */
error_reporting(0);
ini_set('display_errors', 0);
//-------------------------------------------------------------------
$size = $_GET['size'];
$img = $_GET['img'];

if(empty($img) || empty($size)){
	exit;
}
//-------------------------------------------------------------------

$allowedExtension = array('jpg','jpeg','png','gif');

/* extract image name & extension */
$imgInfo = pathinfo($img);
$baseName = $imgInfo['basename'];
$fileName = $imgInfo['filename'];
$ext = $imgInfo['extension'];

$dim = explode("x", $size);
$height = (int)$dim[0];
$width = (int)$dim[1];

if(!in_array($ext,$allowedExtension)){
	exit;
}
//-------------------------------------------------------------------
$source_image = __DIR__."/0/{$baseName}";

if(!file_exists(__DIR__."/0/")){
	mkdir(__DIR__."/0/",0777,true);
}
//-------------------------------------------------------------------

$imgSize = file_put_contents($source_image, file_get_contents($img));
if($imgSize){
	$dest_image = __DIR__."/{$size}/{$fileName}[{$imgSize}].{$ext}";
	
	if(file_exists($dest_image)){
		output_img($dest_image,$ext);
		exit;
	}
}else{
	exit;
}

//-------------------------------------------------------------------
if(is_int($width) && is_int($height) && file_exists($source_image)){
	
	if(!file_exists(__DIR__."/$size/")){
		mkdir(__DIR__."/$size/",0777,true);
	}
	
	createthumb($source_image,$dest_image,$width,$height);
	
	if(file_exists($dest_image)){
		output_img($dest_image,$ext);
		exit;
	}
}


//-------------------------------------------------------------------
function output_img($img,$ext){
	$fp = fopen($img, 'rb');

	// send the right headers
	header("Content-Type: image/$ext");
	header("Content-Length: " . filesize($img));

	// dump the picture and stop the script
	fpassthru($fp);
}

function createthumb($source_image,$destination_image_url, $get_width, $get_height){
	
	ini_set('memory_limit','512M');
	set_time_limit(0);
 
	$image_array         = explode('/',$source_image);
	$image_name = $image_array[count($image_array)-1];
	$max_width     = $get_width;
	$max_height =$get_height;
	$quality = 100;
 
	//Set image ratio
	list($width, $height) = getimagesize($source_image);
	$ratio = ($width > $height) ? $max_width/$width : $max_height/$height;
	$ratiow = $width/$max_width ;
	$ratioh = $height/$max_height;
	$ratio = ($ratiow > $ratioh) ? $max_width/$width : $max_height/$height;
 
	if($width > $max_width || $height > $max_height) {
		$new_width = $width * $ratio;
		$new_height = $height * $ratio;
	} else {
		$new_width = $width;
		$new_height = $height;
	}
 
	if (preg_match("/.jpg/i","$source_image") or preg_match("/.jpeg/i","$source_image")) {
		//JPEG type thumbnail
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image = imagecreatefromjpeg($source_image);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		imagejpeg($image_p, $destination_image_url, $quality);
		imagedestroy($image_p);
 
	} elseif (preg_match("/.png/i", "$source_image")){
		//PNG type thumbnail
		$im = imagecreatefrompng($source_image);
		$image_p = imagecreatetruecolor ($new_width, $new_height);
		imagealphablending($image_p, false);
		imagecopyresampled($image_p, $im, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		imagesavealpha($image_p, true);
		imagepng($image_p, $destination_image_url);
 
	} elseif (preg_match("/.gif/i", "$source_image")){
		//GIF type thumbnail
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image = imagecreatefromgif($source_image);
		$bgc = imagecolorallocate ($image_p, 255, 255, 255);
		imagefilledrectangle ($image_p, 0, 0, $new_width, $new_height, $bgc);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		imagegif($image_p, $destination_image_url, $quality);
		imagedestroy($image_p);
 
	} else {
		//echo 'unable to load image source';
		//exit;
	}
}