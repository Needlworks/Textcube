<?
define('ROOT', '../..');
require ROOT . '/lib/include.php';
fireEvent($suri['directive'] . '/' . $suri['value']);
if (!headers_sent())
	respondNotFoundPage();
?>