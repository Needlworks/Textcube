<?
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'rss' => array('url')
	)
);
require ROOT . '/lib/includeForOwner.php';
if (!empty($_GET['rss'])) {
	list($st, $header, $body, $lmdate, $rval) = @xml_parser($_GET['rss'], '');
	if ($rval) {
		list($title, $link) = str_dbi_check(@get_siteinfo($rval));
		if (UTF8::validate($title, true))
			$name = correctTTForXmlText(UTF8::correct(trim($title)));
		else
			$name = correctTTForXmlText(UTF8::bring(trim($title)));
		if (UTF8::validate($link, true))
			$url = correctTTForXmlText(UTF8::correct(trim($link)));
		else
			$url = correctTTForXmlText(UTF8::bring(trim($link)));
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?><response><name>$name</name><url>$url</url></response>");
	} else {
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?><response><name></name><url></url></response>");
	}
}
?>