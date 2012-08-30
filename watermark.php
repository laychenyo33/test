<?php
$src = $_GET['src'];

header('Content-type: image/jpeg');

if (eregi("150x150", $src)) {
	$watermark = imagecreatefrompng('empty.png');
} else {
	$watermark = imagecreatefrompng('watermark.png');
}
$watermark_width = imagesx($watermark);
$watermark_height = imagesy($watermark);
$image = imagecreatetruecolor($watermark_width, $watermark_height);
if(eregi('.gif',$src)) {
$image = imagecreatefromgif($src);
}
elseif(eregi('.jpeg',$src)||eregi('.jpg',$src)) {
$image = imagecreatefromjpeg($src);
}
elseif(eregi('.png',$src)) {
$image = imagecreatefrompng($src);
}
else {
exit("Your image is not a gif, jpeg or png image. Sorry.");
}
$size = getimagesize($src);
$dest_x = $size[0] - $watermark_width - 0;
$dest_y = $size[1] - $watermark_height - 0;
imagecolortransparent($watermark,imagecolorat($watermark,0,0));
imagecopyresampled($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $watermark_width, $watermark_height);

imagejpeg($image, "", 95);
imagedestroy($image);
imagedestroy($watermark);
?>