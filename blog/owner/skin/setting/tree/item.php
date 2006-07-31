<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$categories = getCategories($owner);
printRespond(array('code' => urlencode(getCategoriesViewInSkinSetting(getEntriesTotalCount($owner), getCategories($owner), $suri['id']))));
?>
