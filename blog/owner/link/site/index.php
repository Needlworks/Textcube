<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
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
			$name = correctTTForXmlText(UTF8::correct(htmlspecialchars(trim($title))));
		else
			$name = correctTTForXmlText(UTF8::bring(htmlspecialchars(trim($title))));
		if (UTF8::validate($link, true))
			$url = correctTTForXmlText(UTF8::correct(htmlspecialchars(trim($link))));
		else
			$url = correctTTForXmlText(UTF8::bring(htmlspecialchars(trim($link))));
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?><response><name>$name</name><url>$url</url></response>");
	} else {
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?><response><name></name><url></url></response>");
	}
}
?>