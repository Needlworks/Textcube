<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setEditor($owner, $_GET['editorMode'], $_GET['strictXHTML'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>