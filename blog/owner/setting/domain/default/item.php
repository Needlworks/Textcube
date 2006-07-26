<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setDefaultDomain($owner, $suri['id'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>