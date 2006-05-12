<?
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
		<script type="text/javascript" src="<?=$root.'/script/common.js'?>"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style type="text/css">
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
			iMazing<?=$_GET["d"]?>='';
			iMazing<?=$_GET["d"]?>+='<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="100%" height="100%">';
			iMazing<?=$_GET["d"]?>+='<param name="movie" value="<?=$root?>/script/gallery/iMazing/main.swf"/>';
			iMazing<?=$_GET["d"]?>+='<param name="FlashVars" value="image=<?=$imageStr?>&amp;frame=<?=$_GET["f"]?>&amp;transition=<?=$_GET["t"]?>&amp;navigation=<?=$_GET["n"]?>&amp;slideshowInterval=<?=$_GET["si"]?>&amp;page=<?=$_GET["p"]?>&amp;align=<?=$_GET["a"]?>&amp;skinPath=<?=$root?>/script/gallery/iMazing/&amp;"/>';
			iMazing<?=$_GET["d"]?>+='<param name="allowScriptAccess" value="sameDomain" />';
			iMazing<?=$_GET["d"]?>+='<param name="menu" value="false" />';
			iMazing<?=$_GET["d"]?>+='<param name="quality" value="high" />';
			iMazing<?=$_GET["d"]?>+='<param name="bgcolor" value="#FFFFFF"/>';
			iMazing<?=$_GET["d"]?>+='<!--[if !IE]> <-->';
			iMazing<?=$_GET["d"]?>+='<object type="application/x-shockwave-flash" data="<?=$root?>/script/gallery/iMazing/main.swf" width="100%" height="100%">';
			iMazing<?=$_GET["d"]?>+='<param name="FlashVars" value="image=<?=$imageStr?>&amp;frame=<?=$_GET["f"]?>&amp;transition=<?=$_GET["t"]?>&amp;navigation=<?=$_GET["n"]?>&amp;slideshowInterval=<?=$_GET["si"]?>&amp;page=<?=$_GET["p"]?>&amp;align=<?=$_GET["a"]?>&amp;skinPath=<?=$root?>/script/gallery/iMazing/&amp;"/>';
			iMazing<?=$_GET["d"]?>+='<param name="allowScriptAccess" value="sameDomain" />';
			iMazing<?=$_GET["d"]?>+='<param name="menu" value="false" />';
			iMazing<?=$_GET["d"]?>+='<param name="quality" value="high" />';
			iMazing<?=$_GET["d"]?>+='<param name="bgcolor" value="#FFFFFF"/>';
			iMazing<?=$_GET["d"]?>+='</object>';
			iMazing<?=$_GET["d"]?>+='<!--> <![endif]-->';
			iMazing<?=$_GET["d"]?>+='</object>';
			writeCode(iMazing<?=$_GET["d"]?>);
		</script>
	</body>
</html>