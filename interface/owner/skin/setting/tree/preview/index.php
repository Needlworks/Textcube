<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'name' => array('string', 'mandatory' => false),
		'url' => array('string', 'mandatory' => false),
		'itemColor' => array('string', 'mandatory' => false),
		'itemBgColor' => array('string', 'mandatory' => false),
		'activeItemColor' => array('string', 'mandatory' => false),
		'activeItemBgColor' => array('string', 'mandatory' => false),
		'labelLength' => array('string', 'mandatory' => false),
		'showValue' => array('string', 'mandatory' => false)
	)
); 

require ROOT . '/library/preprocessor.php';
requireModel('blog.entry');
$selected = 0;

if (isset($_GET['name']))
	$skinSetting['tree'] = $_GET['name'];

$skinSetting['url'] = $service['path'] . "/skin/tree/{$skinSetting['tree']}";
$skinSetting['itemColor'] = isset($_GET['itemColor']) ? $_GET['itemColor'] : $skinSetting['colorontree'];
$skinSetting['itemBgColor'] = isset($_GET['itemBgColor']) ? $_GET['itemBgColor'] : $skinSetting['bgcolorontree'];
$skinSetting['activeItemColor'] = isset($_GET['activeItemColor']) ? $_GET['activeItemColor'] : $skinSetting['activecolorontree'];
$skinSetting['activeItemBgColor'] = isset($_GET['activeItemBgColor']) ? $_GET['activeItemBgColor'] : $skinSetting['activebgcolorontree'];
$skinSetting['labelLength'] = isset($_GET['labelLength']) ? $_GET['labelLength'] : $skinSetting['labellengthontree'];
$skinSetting['showValue'] = isset($_GET['showValue']) ? $_GET['showValue'] : $skinSetting['showvalueontree'];


if (isset($_GET['activeItemBgColor']))
	$skinSetting['activebgcolorontree'] = $_GET['activeItemBgColor'];
if (isset($_GET['labelLength']))
	$skinSetting['labellengthontree'] = $_GET['labelLength'];
if (isset($_GET['showValue']))
	$skinSetting['showvalueontree'] = $_GET['showValue'];
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title>Tree Structure Preview</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/skin.css" />
	<!--[if lte IE 6]><link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/skin.ie.css" /><![endif]-->
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $service['path'];?>";
			var blogURL = "<?php echo $blogURL;?>";
			var adminSkin = "<?php echo $adminSkinSetting['skin'];?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/jquery/jquery-<?php echo JQUERY_VERSION;?>.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/EAF4.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/common2.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/owner.js"></script>
	<style type="text/css">
		/*<![CDATA[*/
			body
			{
				background-color                    : #FFFFFF;
			}
		/*]]>*/
	</style>
</head>
<body id="tree-iframe">
<?php echo getCategoriesViewInSkinSetting(getEntriesTotalCount(getBlogId()), getCategories(getBlogId()), $selected);?>
</body>
</html>
