<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (!empty($_GET['domain']) && setSecondaryDomain($owner, $_GET['domain'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>