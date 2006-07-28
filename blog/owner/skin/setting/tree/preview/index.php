<?
define('ROOT', '../../../../../..');
$IV = array(
	'POST' => array(
		'itemColor' => array('string', 'mandatory' => false),
		'itemBgColor' => array('string', 'mandatory' => false),
		'activeItemColor' => array('string', 'mandatory' => false),
		'activeItemBgColor' => array('string', 'mandatory' => false),
		'labelLength' => array('string', 'mandatory' => false),
		'showValue' => array('string', 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
$categories = getCategories($owner);
$selected = 0;
$treeSkin = getCategoriesSkin();
if (empty($_GET['url'])) {
	$categoriesSkin = $treeSkin;
} else {
	$categoriesSkin = array('name' => $treeSkin['name'], 'url' => $_GET['url'], 'labelLength' => $_GET['labelLength'], 'showValue' => $_GET['showValue'], 'itemColor' => $_GET['itemColor'], 'itemBgColor' => $_GET['itemBgColor'], 'activeItemColor' => $_GET['activeItemColor'], 'itemBgColor' => $_GET['itemBgColor'], 'activeItemBgColor' => $_GET['activeItemBgColor']);
}
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
	<?=getCategoriesViewInSkinSetting($categories, $selected, $categoriesSkin)?>
</body>
</html>
