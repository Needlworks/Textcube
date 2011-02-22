<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/*
	id : d
	frame : f
	transition : t
	navigation : n 
	slideshowInterval : si
	page : p
	align : a
	image : i (*!)
*/

$images = explode('*!',$_GET['i']);
$imageStr = '';
define('ROOT', '../../..');
require ROOT . '/library/preprocessor.php';
foreach($images as $value) {
	$imageStr .= $value.'*!';
}
?>
<html>
	<head>
		<script type="text/javascript" src="<?php echo $service['path'].'/resources/script/flash.js';?>"></script>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			/*<![CDATA[*/
				body
				{
					margin-left             : 0;
					margin-top              : 0;
					margin-right            : 0;
					margin-bottom           : 0;
					width                   : 100%;
					height                  : 100%;
				}
			/*]]>*/
		</style>
	</head>
	<body>
		<script type="text/javascript">
		//<![CDATA[
			AC_FL_RunContent( 
			   "classid","clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", 
			   "codebase","http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0", 
			   "width" , "100%",
			   "height" , "100%",
			   "src" , "<?php echo $service['path'];?>/resources/script/gallery/iMazing/main",
			   "FlashVars", "image=<?php echo $imageStr;?>&frame=<?php echo $_GET["f"];?>&transition=<?php echo $_GET["t"];?>&navigation=<?php echo $_GET["n"];?>&slideshowInterval=<?php echo $_GET["si"];?>&page=<?php echo $_GET["p"];?>&align=<?php echo $_GET["a"];?>&skinPath=<?php echo $service['path'];?>/resources/script/gallery/iMazing/&",
			   "allowscriptAccess", "sameDomain", 
			   "menu", "false");
		//]]>
		</script>
	</body>
</html>
