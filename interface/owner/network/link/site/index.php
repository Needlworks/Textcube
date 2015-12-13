<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'rss' => array('url')
	)
);
require ROOT . '/library/preprocessor.php';
importlib('model.blog.link');

if (!empty($_GET['rss'])) {
	list($st, $header, $body, $lmdate, $rval) = @xml_parser($_GET['rss'], '');
	$result = array();
	if ($rval) {
		list($title, $link) = str_dbi_check(@get_siteinfo($rval));
		if (Utils_Unicode::validate($title, true))
			$result['name'] = correctTTForXmlText(Utils_Unicode::correct(htmlspecialchars(trim($title))));
		else
			$result['name'] = correctTTForXmlText(Utils_Unicode::bring(htmlspecialchars(trim($title))));
		if (Utils_Unicode::validate($link, true))
			$result['url'] = correctTTForXmlText(Utils_Unicode::correct(htmlspecialchars(trim($link))));
		else
			$result['url'] = correctTTForXmlText(Utils_Unicode::bring(htmlspecialchars(trim($link))));
		Respond::PrintResult($result);
	} else {
		$result['url'] = $_GET['rss'];
		$result['name'] = '';
		Respond::PrintResult($result);
	}
	exit;
} else {
	Respond::ResultPage(-1);
}
?>
