<?
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'url' => array('url', 'default'=> null)
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(!empty($_GET['url']) && sendTrackback($owner, $suri['id'], $_GET['url']));
?>