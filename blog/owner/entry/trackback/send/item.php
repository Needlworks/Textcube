<?
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'url' => array('url')
	)
);
require ROOT . '/lib/includeForOwner.php';
respondResultPage(!empty($_GET['url']) && sendTrackback($owner, $suri['id'], $_GET['url']));
?>