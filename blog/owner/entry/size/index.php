<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
printRespond(array ('error' => $result < 3 ? 0 : 1, 'result' => getAttachmentSizeLabel($_REQUEST['owner'],$_REQUEST['parent'])));
?>