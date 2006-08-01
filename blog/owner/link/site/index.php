<?
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'rss' => array('url')
	)
);
require ROOT . '/lib/includeForOwner.php';
list($error, $feed) = getRemoteFeed(@$_GET['rss']);
if($error == 0)
	printRespond(array('error' => $error, 'name' => $feed['title'], 'url' => $feed['blogURL']));
else
	printRespond(array('error' => $error));
?>