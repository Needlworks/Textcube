<?php
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (isset($_GET['timezone'])) {
	requireComponent('Tattertools.Data.BlogSetting');
	if (BlogSetting::setTimezone($_GET['timezone']))
		respondResultPage(0);
}
respondResultPage( - 1);
?>