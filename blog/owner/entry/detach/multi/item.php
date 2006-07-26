<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (!empty($_POST['names']) && deleteAttachmentMulti($owner, $suri['id'], $_POST['names']))
	respondResultPage(0);
else
	respondResultPage( - 1);
?>