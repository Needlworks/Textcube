<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = getAttachmentSizeLabel($_REQUEST['owner'],$_REQUEST['parent']);
printRespond(array ('error' => empty($result) ? 1 : 0, 'result' => $result));
?> 