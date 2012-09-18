<?php
require "class/wideimage/WideImage.php";
$src = $_GET['src'];



if (eregi("150x150", $src)) {
	$watermark = WideImage::load('empty.png');
} else {
	$watermark = WideImage::load('watermark.png');
}

$base = WideImage::load($src);
$water_height = round($base->getHeight()/4,0);
$watermark = $watermark->resize(null,$water_height);
$water_width = $watermark->getWidth();

$dest_x = round(($base->getWidth() - $water_width)/2,0);
$dest_y = round(($base->getHeight() - $water_height)/2,0) ;
$res = $base->merge($watermark, $dest_x, $dest_y);

if(eregi('.gif',$src)) {
    $res->output('gif');
}elseif(eregi('.jpeg',$src)||eregi('.jpg',$src)) {
    $res->output('jpg');
}elseif(eregi('.png',$src)) {
    $res->output('png');
}

$base->destroy();
$watermark->destroy();       
$res->destroy();
?>