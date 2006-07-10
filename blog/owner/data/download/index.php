<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
if (file_exists(ROOT . "/cache/backup/$owner.xml")) {
	header('Content-Disposition: attachment; filename="'.TATTERTOOLS_NAME.'-Backup-' . Timestamp::getDate(filemtime(ROOT . "/cache/backup/$owner.xml")) . '.xml"');
	header('Content-Description: '.TATTERTOOLS_NAME.' Backup Data');
	header('Content-Transfer-Encoding: binary');
	header('Content-Type: application/xml');
	readfile(ROOT . "/cache/backup/$owner.xml");
} else {
	respondNotFoundPage();
}
?>