<?php
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
fireEvent($suri['directive'] . '/' . $suri['value']);
if (!headers_sent())
	respondNotFoundPage();
?>
