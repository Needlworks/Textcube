<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (useBlogSlogan($owner, $suri['id']))
	respondResultPage(0);
respondResultPage( - 1);
?>