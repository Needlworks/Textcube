<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
if (!empty($_GET['rss'])) {
	list($st, $header, $body, $lmdate, $rval) = @xml_parser($_GET['rss'], '');
	if ($rval) {
		list($title, $link) = str_dbi_check(@get_siteinfo($rval));
		if (isUTF8($title))
			$name = correctTTForXmlText(trim($title));
		else
			$name = correctTTForXmlText(iconvWrapper($service['encoding'], 'UTF-8', trim($title)));
		if (isUTF8($link))
			$url = correctTTForXmlText(trim($link));
		else
			$url = correctTTForXmlText(iconvWrapper($service['encoding'], 'UTF-8', trim($link)));
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?><response><name>$name</name><url>$url</url></response>");
	} else {
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?><response><name></name><url></url></response>");
	}
}
?>