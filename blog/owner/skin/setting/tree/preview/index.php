<?
/// Copyright (C) 2004-2006, Tatter & Company. All rights reserved.

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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
<script type="text/javascript">
var servicePath = "<?=$service['path']?>"; var blogURL = "<?=$blogURL?>";
</script>
<script type="text/javascript" src="<?=$service['path']?>/script/common.js" ></script>
<script type="text/javascript" src="<?=$service['path']?>/script/owner.js" ></script>
<style type="text/css">
<!--
body {
	background-color: #FFFFFF;
}
-->
</style><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<script type="text/javascript">
</script>
<body>
	<?=getCategoriesViewInSkinSetting(getEntriesTotalCount($owner), getCategories($owner), $selected)?>
</body>
</html>
