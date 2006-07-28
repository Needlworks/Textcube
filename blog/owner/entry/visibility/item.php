<?
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'visibility' => array('int', 0, 3, 'default' => 0)
	)
);
require ROOT . '/lib/includeForOwner.php';
respondResultPage(setEntryVisibility($suri['id'], isset($_GET['visibility']) ? $_GET['visibility'] : 0));
?>