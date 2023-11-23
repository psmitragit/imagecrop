<?php
include('./constants.php');
//image crop and resize====================================================>
function image_resize($data, $height, $width)
{
	if ($data['name'] != "") {
		$uploadedFile = $data['tmp_name'];
		$sourceProperties = getimagesize($uploadedFile);
		$bigFileName = "big-" . time();
		$tmpFileName = "tmp-" . time();
		$thumbFileName = "resized-" . time();
		$dirPath = 'images/';
		$ext = pathinfo($data['name'], PATHINFO_EXTENSION);
		$imageType = $sourceProperties[2];
		$originalWidth = $sourceProperties[0];
		$originalHeight = $sourceProperties[1];
		$maxWidth = $width;
		$maxHeight = $height;
		if ($originalWidth < $maxWidth) {
			$maxWidth = $originalWidth;
		}
		if ($originalHeight < $maxHeight) {
			$maxHeight = $originalHeight;
		}
		if ($maxWidth == 0) {
			$maxWidth = round(($originalWidth / $originalHeight) * $maxHeight, 0);
		}
		if ($maxHeight == 0) {
			$maxHeight = round(($originalHeight / $originalWidth) * $maxWidth, 0);
		}
		switch ($imageType) {
			case IMAGETYPE_PNG:
				$resizedimg = resize($maxWidth, $maxHeight, $uploadedFile);
				imagepng($resizedimg, $dirPath . $tmpFileName . "." . $ext);
				$resizedimg = $dirPath . $tmpFileName . "." . $ext;
				$resizedimgname = $tmpFileName . "." . $ext;
				break;
			case IMAGETYPE_JPEG:
				$resizedimg = resize($maxWidth, $maxHeight, $uploadedFile);
				imagejpeg($resizedimg, $dirPath . $tmpFileName . "." . $ext, 99);
				$resizedimg = $dirPath . $tmpFileName . "." . $ext;
				$resizedimgname = $tmpFileName . "." . $ext;
				break;
			case IMAGETYPE_GIF:
				$resizedimg = resize($maxWidth, $maxHeight, $uploadedFile);
				imagegif($resizedimg, $dirPath . $tmpFileName . "." . $ext);
				$resizedimg = $dirPath . $tmpFileName . "." . $ext;
				$resizedimgname = $tmpFileName . "." . $ext;
				break;
			case IMAGETYPE_WEBP:
				$resizedimg = resize($maxWidth, $maxHeight, $uploadedFile);
				imagewebp($resizedimg, $dirPath . $tmpFileName . "." . $ext);
				$resizedimg = $dirPath . $tmpFileName . "." . $ext;
				$resizedimgname = $tmpFileName . "." . $ext;
				break;
			default:
				// echo "Invalid Image type.<br><a href='index.php'>Back</a>";
				return false;
				exit;
				break;
		}
		// move_uploaded_file($uploadedFile, $dirPath . $bigFileName . "." . $ext);
		$resizedimgsize = getimagesize($resizedimg);
		if ($width <= $resizedimgsize[0]) {
			$selectorwidth = $width;
		} else {
			$selectorwidth = $resizedimgsize[0];
		}
		if ($height <= $resizedimgsize[1]) {
			$selectorheight = $height;
		} else {
			$selectorheight = $resizedimgsize[1];
		}
		$return = [
			'status' => MANUAL_CROP_ENABLED ? 1 : 2,
			'image_name' => $resizedimgname,
			'maxWidth' => $maxWidth,
			'maxHeight' => $maxHeight,
			'resizedimg' => $resizedimg,
			'resize_height' => $resizedimgsize[1],
			'resize_width' => $resizedimgsize[0],
			'selector_width' => $selectorwidth,
			'selector_height' => $selectorheight,
			'dir_path' => $dirPath,
			'thumbFileName' => $thumbFileName,
			'ext' => $ext
		];
		return $return;
	} else {
		return false;
	}
}
function resize($maxWidth, $maxHeight, $originalFile)
{
	$info = getimagesize($originalFile);
	$mime = $info['mime'];
	switch ($mime) {
		case 'image/jpeg':
			$image_create_func = 'imagecreatefromjpeg';
			$image_save_func = 'imagejpeg';
			$new_image_ext = 'jpg';
			break;
		case 'image/png':
			$image_create_func = 'imagecreatefrompng';
			$image_save_func = 'imagepng';
			$new_image_ext = 'png';
			break;
		case 'image/gif':
			$image_create_func = 'imagecreatefromgif';
			$image_save_func = 'imagegif';
			$new_image_ext = 'gif';
			break;
		case 'image/webp':
			$image_create_func = 'imagecreatefromwebp';
			$image_save_func = 'imagewebp';
			$new_image_ext = 'webp';
			break;
		default:
			throw new Exception('Unknown image type.');
	}
	$img = $image_create_func($originalFile);
	list($width, $height) = getimagesize($originalFile);
	if ($width > $height) { // Landscape image - Calculate new width
		$newWidth = ($width / $height) * $maxHeight;
		$newHeight = $maxHeight;
		if ($newWidth < $maxWidth) {
			$newWidth = $maxWidth;
			$newHeight = ($height / $width) * $maxWidth;
		}
	} else { // Protrait or Square - Calculate new height
		$newWidth = $maxWidth;
		$newHeight = ($height / $width) * $maxWidth;
		if ($newHeight < $maxHeight) {
			$newWidth = ($width / $height) * $maxHeight;
			$newHeight = $maxHeight;
		}
	}
	$newWidth = round($newWidth, 0);
	$newHeight = round($newHeight, 0);
	$tmp = imagecreatetruecolor($newWidth, $newHeight);
	imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
	return $tmp;
}
function final_crop($data)
{
	if (isset($data['crop_image'])) {
		$y1 = $data['top'];
		$x1 = $data['left'];
		$w = $data['right'] == 0 ? null : $data['right'];
		$h = $data['bottom'] == 0 ? null : $data['bottom'];
		$image = $data['image'];
		$maxWidth = round($data['maxWidth'], 0);
		$maxHeight = round($data['maxHeight'], 0);
		$dirPath = $data['dirPath'];
		$thumbFileName = $data['thumbFileName'];
		$ext = $data['ext'];

		$sourceProperties = getimagesize($image);

		// If top, left, right, and bottom values are not set or are 0, auto crop from the middle
		if ($w === null && $h === null) {
			// Calculate center cropping

			$centerX = $sourceProperties[0] / 2;
			$centerY = $sourceProperties[1] / 2;
			$cropWidth = $maxWidth;
			$cropHeight = $maxHeight;

			$x1 = max(0, $centerX - ($cropWidth / 2));
			$y1 = max(0, $centerY - ($cropHeight / 2));
			$w = min($sourceProperties[0], $cropWidth);
			$h = min($sourceProperties[1], $cropHeight);
		}

		$resizedWidth = $sourceProperties[0];
		$resizedHeight = $sourceProperties[1];
		$imageType = $sourceProperties[2];
		switch ($imageType) {
			case IMAGETYPE_PNG:
				$imageSrc = imagecreatefrompng($image);
				$newImageLayer = imagecreatetruecolor($maxWidth, $maxHeight);
				$tmp = imageCropResize($imageSrc, $resizedWidth, $resizedHeight, $w, $h, $x1, $y1);
				$new_name = $thumbFileName . "_" . $maxWidth . "X" . $maxHeight . "." . $ext;
				unlink($image);
				imagepng($tmp, $dirPath . $new_name);
				break;
			case IMAGETYPE_JPEG:
				$imageSrc = imagecreatefromjpeg($image);
				$newImageLayer = imagecreatetruecolor($maxWidth, $maxHeight);
				$tmp = imageCropResize($imageSrc, $resizedWidth, $resizedHeight, $w, $h, $x1, $y1);
				$new_name = $thumbFileName . "_" . $maxWidth . "X" . $maxHeight . "." . $ext;
				unlink($image);
				imagejpeg($tmp, $dirPath . $new_name);
				break;
			case IMAGETYPE_GIF:
				$imageSrc = imagecreatefromgif($image);
				$newImageLayer = imagecreatetruecolor($maxWidth, $maxHeight);
				$tmp = imageCropResize($imageSrc, $resizedWidth, $resizedHeight, $w, $h, $x1, $y1);
				$new_name = $thumbFileName . "_" . $maxWidth . "X" . $maxHeight . "." . $ext;
				unlink($image);
				imagegif($tmp, $dirPath . $new_name);
				break;
			case IMAGETYPE_WEBP:
				$imageSrc = imagecreatefromwebp($image);
				$newImageLayer = imagecreatetruecolor($maxWidth, $maxHeight);
				$tmp = imageCropResize($imageSrc, $resizedWidth, $resizedHeight, $w, $h, $x1, $y1);
				$new_name = $thumbFileName . "_" . $maxWidth . "X" . $maxHeight . "." . $ext;
				unlink($image);
				imagewebp($tmp, $dirPath . $new_name);
				break;
			default:
				// echo "Invalid Image type.<br><a href='index.php'>Back</a>";
				return false;
				exit;
				break;
		}
		return $new_name;
	}
}
function imageCropResize($imageSrc, $imageWidth, $imageHeight, $maxWidth, $maxHeight, $x, $y)
{
	$newImageLayer = imagecreatetruecolor($maxWidth, $maxHeight);
	imagecopy($newImageLayer, $imageSrc, 0, 0, $x, $y, $imageWidth, $imageHeight);
	return $newImageLayer;
}
//image crop and resize====================================================>
