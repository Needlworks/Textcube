<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setEditor($owner, $_GET['editorMode'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>