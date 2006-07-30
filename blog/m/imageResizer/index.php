<?php
define('__TATTERTOOLS_MOBILE__', true);
define('ROOT', '../../..');
$IV = array(
	'GET' => array(
		'f' => array('filename')
	)
);
require ROOT . '/lib/include.php';
$imagePath = ROOT . "/attach/$owner/{$_GET['f']}";
if ($fp = @fopen($imagePath, 'r')) {
	$imageInfo = @getimagesize($imagePath);
	$canvasWidth = 240;
	$canvasHeight = round($imageInfo[1] * ($canvasWidth / $imageInfo[0]));
	if (function_exists('gd_info') && resampleImage(240, $canvasHeight, $imagePath, "reduce", "browser")) {
		// Ελ°ϊ.
	} else {
		while (!feof($fp)) {
			echo fread($fp, 8192);
			flush();
		}
	}
	fclose($fp);
} else
	respondNotFoundPage();
?>