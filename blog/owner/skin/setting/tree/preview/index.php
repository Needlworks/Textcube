<?php

define('ROOT', '../../../../../..');
$IV = array(
	'GET' => array(
		'name' => array('string', 'mandatory' => false),
		'itemColor' => array('string', 'mandatory' => false),
		'itemBgColor' => array('string', 'mandatory' => false),
		'activeItemColor' => array('string', 'mandatory' => false),
		'activeItemBgColor' => array('string', 'mandatory' => false),
		'labelLength' => array('string', 'mandatory' => false),
		'showValue' => array('string', 'mandatory' => false)
	)
); 

require ROOT . '/lib/includeForOwner.php';
$selected = 0;
if (isset($_GET['name']))
	$skinSetting['tree'] = $_GET['name'];
if (isset($_GET['itemColor']))
	$skinSetting['colorOnTree'] = $_GET['itemColor'];
if (isset($_GET['itemBgColor']))
	$skinSetting['bgColorOnTree'] = $_GET['itemBgColor'];
if (isset($_GET['activeItemColor']))
	$skinSetting['activeColorOnTree'] = $_GET['activeItemColor'];
if (isset($_GET['activeItemBgColor']))
	$skinSetting['activeBgColorOnTree'] = $_GET['activeItemBgColor'];
if (isset($_GET['labelLength']))
	$skinSetting['labelLengthOnTree'] = $_GET['labelLength'];
if (isset($_GET['showValue']))
	$skinSetting['showValueOnTree'] = $_GET['showValue'];
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title>Tree Structure Preview</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin']?>/skin.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin']?>/skin.opera.css" />
	<!--[if lte IE 6]><link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin']?>/skin.ie.css" /><![endif]-->
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $service['path']?>";
			var blogURL = "<?php echo $blogURL?>";
			var adminSkin = "<?php echo $adminSkinSetting['skin']?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/common.js"></script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/owner.js"></script>
	<style type="text/css">
		<!--
			body
			{
				background-color                    : #FFFFFF;
			}
		-->
	</style>
</head>
<body id="tree-iframe">
<?php echo getCategoriesViewInSkinSetting(getEntriesTotalCount($owner), getCategories($owner), $selected)?>
</body>
</html>
