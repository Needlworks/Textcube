<?
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'url' => array('url')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireComponent('Eolin.PHP.HTTPRequest');
if (preg_match('/\.jpe?g/i', $_GET['url']))
	header('Content-type: image/jpeg');
else if (preg_match('/\.gif/i', $_GET['url']))
	header('Content-type: image/gif');
else if (preg_match('/\.png/i', $_GET['url']))
	header('Content-type: image/png');
$request = new HTTPRequest($_GET['url']);
if ($request->send()) {
	echo $request->responseText;
} else
	respondNotFoundPage();
?>