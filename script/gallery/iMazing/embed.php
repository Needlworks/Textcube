<?php
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
$root = $_GET['r'];
$images = explode('*!',$_GET['i']);
$imageStr = '';
foreach($images as $value) {
	$imageStr .= $value.'*!';
}

?>
<html>
	<head>
		<script type="text/javascript" src="<?php echo  $root.'/script/common.js'?>"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"  /><style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	width:100%;
	height:100%;
}
-->
</style></head>
	<body>
		<script type="text/javascript">
			iMazing<?php echo  $_GET["d"]?>='';
			iMazing<?php echo  $_GET["d"]?>+='<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="100%" height="100%">';
			iMazing<?php echo  $_GET["d"]?>+='<param name="movie" value="<?php echo  $root?>/script/gallery/iMazing/main.swf" />';
			iMazing<?php echo  $_GET["d"]?>+='<param name="FlashVars" value="image=<?php echo  $imageStr?>&amp;frame=<?php echo  $_GET["f"]?>&amp;transition=<?php echo  $_GET["t"]?>&amp;navigation=<?php echo  $_GET["n"]?>&amp;slideshowInterval=<?php echo  $_GET["si"]?>&amp;page=<?php echo  $_GET["p"]?>&amp;align=<?php echo  $_GET["a"]?>&amp;skinPath=<?php echo  $root?>/script/gallery/iMazing/&amp;" />';
			iMazing<?php echo  $_GET["d"]?>+='<param name="allowScriptAccess" value="sameDomain"  />';
			iMazing<?php echo  $_GET["d"]?>+='<param name="menu" value="false"  />';
			iMazing<?php echo  $_GET["d"]?>+='<param name="quality" value="high"  />';
			iMazing<?php echo  $_GET["d"]?>+='<param name="bgcolor" value="#FFFFFF" />';
			iMazing<?php echo  $_GET["d"]?>+='<!--[if !IE]> <-->';
			iMazing<?php echo  $_GET["d"]?>+='<object type="application/x-shockwave-flash" data="<?php echo  $root?>/script/gallery/iMazing/main.swf" width="100%" height="100%">';
			iMazing<?php echo  $_GET["d"]?>+='<param name="FlashVars" value="image=<?php echo  $imageStr?>&amp;frame=<?php echo  $_GET["f"]?>&amp;transition=<?php echo  $_GET["t"]?>&amp;navigation=<?php echo  $_GET["n"]?>&amp;slideshowInterval=<?php echo  $_GET["si"]?>&amp;page=<?php echo  $_GET["p"]?>&amp;align=<?php echo  $_GET["a"]?>&amp;skinPath=<?php echo  $root?>/script/gallery/iMazing/&amp;" />';
			iMazing<?php echo  $_GET["d"]?>+='<param name="allowScriptAccess" value="sameDomain"  />';
			iMazing<?php echo  $_GET["d"]?>+='<param name="menu" value="false"  />';
			iMazing<?php echo  $_GET["d"]?>+='<param name="quality" value="high"  />';
			iMazing<?php echo  $_GET["d"]?>+='<param name="bgcolor" value="#FFFFFF" />';
			iMazing<?php echo  $_GET["d"]?>+='</object>';
			iMazing<?php echo  $_GET["d"]?>+='<!--> <![endif]-->';
			iMazing<?php echo  $_GET["d"]?>+='</object>';
			writeCode(iMazing<?php echo  $_GET["d"]?>);
		</script>
	</body>
</html>