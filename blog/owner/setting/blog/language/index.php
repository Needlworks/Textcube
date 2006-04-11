<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (!empty($_GET['language']) && setBlogLanguage($owner, $_GET['language'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>