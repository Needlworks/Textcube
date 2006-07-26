<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$categories = getCategories($owner);
printRespond(array('code' => urlencode(getCategoriesViewInSkinSetting($categories, $suri['id'], getCategoriesSkin()))));
?>