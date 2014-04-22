<?php
require_once "class/barcode39.php";
$code = $_GET['barcode']?$_GET['barcode']:"chunhsin";
$bc = new Barcode39($code);
$bc->barcode_bar_thick = 6;
$bc->barcode_bar_thin = 3;
$bc->barcode_height = 120;
$bc->draw();
?>
