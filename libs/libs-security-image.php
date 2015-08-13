<?
#################################
#
# Author: Joel Finkel
# Email: finkel@sd-il.com
#
# Credits:
#	Concept by vImage by Rafael Machado Dohms (dooms@terra.com.br)
#
#	Coding example from HumanCheck 2.1 by Yuriy Horobey (yuriy@horobey.com)
#
#	The function, simpleRandString, is by demogracia@metropoliglobal.com and posted
#	to www.php.net
#
#################################

class securityImage {

var $inputParam = "style='color:blue;' class='search_product'";			// Public; $x->inputParam = "style='color:blue'"
var $name 	= "security";		// Public; $x->name = "mySecurityInputField"

var $codeLength = 4;			// Private; use setCodeLength()
var $fontSize	= 5;			// Private; use setFontSize()
var $fontColor  = "333333";		// Private; use setFontColor()

var $securityCode = "";			// Private

function securityImage() {

	session_start();

	/*
	 * Save this so it is available in the next instantiation; required for isValid().
	*/
	if (isset($_SESSION['securityCode'])) {
		$this->userSecurityCode = $_SESSION['securityCode'];
	} else {
		$this->userSecurityCode = "";
	}

	/*
	 * Save the items required by the instance created by securityImageImage.php
	*/
	if (isset($_SESSION['codeLength'])) {
		$this->codeLength = $_SESSION['codeLength'];
	}

	if (isset($_SESSION['fontSize'])) {
		$this->fontSize = $_SESSION['fontSize'];
	}

	if (isset($_SESSION['fontColor'])) {
		$this->fontColor = $_SESSION['fontColor'];
	}

	$this->imageFile = "../images/security/bgs/ws-security-image".rand(1,10).".jpg";
}

function simpleRandString($length=10, $list="1234567890") {
	/*
	 * Generates a random string with the specified length
	 * Chars are chosen from the provided [optional] list
	*/
	mt_srand((double)microtime()*1000000);

	$newstring = "";

	if ($length > 0) {
		while (strlen($newstring) < $length) {
			$newstring .= $list[mt_rand(0, strlen($list)-1)];
		}
	}
	return $newstring;
}

/*
 * Not to be called directly.  Called by securityImageImage.php.
*/
function showImage() {
	header("Content-type: image/jpeg");
	$this->generateImage();
	imagejpeg($this->img);
	imageDestroy($this->img);
}

/*
 * Private
*/
function generateImage() {

	$this->securityCode = $this->simpleRandString($this->codeLength);

	$_SESSION['securityCode'] = $this->securityCode;

	$img_path = dirname(__FILE__)."/$this->imageFile";

//	$this->img = ImageCreateFromJpeg($img_path);
//
//	$img_size = getimagesize($img_path);
	$img_size = array(200,35);
	$this->img = imagecreatetruecolor($img_size[0],$img_size[1]);

	$white = imagecolorallocate($this->img,255,255,255);
	$color = imagecolorallocate($this->img,
			hexdec(substr($this->fontColor, 1, 2)),
			hexdec(substr($this->fontColor, 3, 2)),
			hexdec(substr($this->fontColor, 5, 2))
			);
        imagefill($this->img, 0, 0, $white);
	$fw = imagefontwidth($this->fontSize);
	$fh = imagefontheight($this->fontSize);

	// create a new string with a blank space between each letter so it looks better
	$newstr = "";
	for ($i = 0; $i < strlen($this->securityCode); $i++) {
		$newstr .= $this->securityCode[$i] ." ";
	}

	// remove the trailing blank
	$newstr = trim($newstr);

	// center the string
	$x = ($img_size[0] - strlen($newstr) * $fw ) / 2;
    // Create some colors
    $white = imagecolorallocate($this->img, 255, 255, 255);
    $grey = imagecolorallocate($this->img, 128, 128, 128);
    $black = imagecolorallocate($this->img, 0, 0, 0);
	$rand_color = imagecolorallocate($this->img, rand(0,255), rand(0,255), rand(0,255));
	$graphic_color1 = imagecolorallocate($this->img, rand(0,255), rand(0,255), rand(0,255));
	$graphic_color2 = imagecolorallocate($this->img, rand(0,255), rand(0,255), rand(0,255));
    //imagefilledrectangle($this->img, 0, 0, 399, 29, $white);

    // The text to draw
    $text = 'Testing...';
    // Replace path by your own font path
    //$font = 'images/magik.TTF';
    //$font="images/BastardusSans.ttf";
//    $font="images/security/fonts/font".rand(1,23).".ttf";
    $font="images/security/fonts/font8.ttf";

    // Add some shadow to the text
    $r = 0;
    $baseY = 35;
    $targetY = $baseY+$r*1.3;
    $fontSize=35;
    for($s=0;$s<strlen($newstr);$s++){
        imagettftext($this->img, $fontSize, 0, 16+(25*$s), $targetY, $grey, $font, $newstr[$s]);
    }

    // Add the text
//    imagettftext($this->img, 16, 0, 10, 20, $black, $font, $newstr);
    /*
	// output each character at a random height and standard horizontal spacing
	for ($i = 0; $i < strlen($newstr); $i++) {
		$hz = mt_rand( 10, $img_size[1] - $fh - 5);
		imagechar( $this->img, $this->fontSize, $x + ($fw*$i), $hz, $newstr[$i], $color);
	}
	*/
	
	
   //繪入文字雜訊-線條
    for ($i = 0; $i < 5; $i++) {    //5條
        imageline(
            $this->img, 
            0, 
            rand() % $img_size[1], 
            $img_size[0], 
            rand() % $img_size[1], 
            $graphic_color1
        );
    }
    //繪入文字雜訊-點
    for ($i = 0; $i < 50; $i++) {   //50點
        imagesetpixel(
            $this->img, 
            rand() % $img_size[0], 
            rand() % $img_size[1], 
            $graphic_color2
        );
    }
	
}

/*
 * PUBLIC FUNCTIONS
*/
function showFormInput() {
	return "<input $this->inputParam type='text' name='$this->name' maxlengh='$this->codeLength' size='4' />";
}

function showFormImage() {
	global $cms_cfg;
	$id=md5(time());
	return "<img id=\"security_image".$id."\" src=\"".$cms_cfg['base_root']."security_image.php?c=".$id."\" width='".$this->imageWidth."' height='".$this->imageHight."' style='margin-left:5px;'> <img id=\"security_image".$id."\" src=\"".$cms_cfg['default_theme']."security/reload.png\" onclick=\"document.getElementById('security_image".$id."').src='".$cms_cfg['base_root']."security_image.php';\" style=\"cursor:pointer;\" />";
}

function isValid() {
	return !empty($_POST["$this->name"]) && ($_POST["$this->name"] == $this->userSecurityCode);
}

function setCodeLength($p) {

	$this->codeLength = $p;
	$_SESSION['codeLength'] = $this->codeLength;
}

function setFontSize($p) {

	$this->fontSize = $p;
	$_SESSION['fontSize'] = $this->fontSize;
}

function setFontColor($p) {

	$this->fontColor = $p;
	$_SESSION['fontColor'] = $this->fontColor;
}

function setImageFile($p) {

	$this->imageFile = $p;
	$_SESSION['imageFile'] = $this->imageFile;
}

function setImageSize($w, $h) {
    $this->imageWidth = $w;
    $this->imageHight = $h;
    $_SESSION['imageWidth'] = $this->imageWidth;
    $_SESSION['imageHight'] = $this->imageHight;
}

}
?>
