<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
printRespond(array('code' => urlencode(getCategoriesViewInSkinSetting(getEntriesTotalCount($owner), getCategories($owner), $suri['id']))));
?>