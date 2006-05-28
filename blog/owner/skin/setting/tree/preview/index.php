<?
define('ROOT', '../../../../../..');
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title>Tree Structure Preview</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?=$service['path'].$service['adminSkin']?>/skin.css" />
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?=$service['path']?>";
			var blogURL = "<?=$blogURL?>";
			var adminSkin = "<?=$service['adminSkin']?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?=$service['path']?>/script/common.js"></script>
	<script type="text/javascript" src="<?=$service['path']?>/script/owner.js"></script>
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
<?=getCategoriesViewInSkinSetting($categories, $selected, $categoriesSkin)?>
</body>
</html>
