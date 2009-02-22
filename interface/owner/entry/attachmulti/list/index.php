<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'GET' => array(
		'name' => array('string')
	)
);

require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.attachment");
$file = array_pop($_FILES);
$attachment = getAttachmentByLabel($blogid, $suri['id'], $_GET['name']);
$result = escapeJSInCData(getPrettyAttachmentLabel($attachment)) . '!^|' . escapeJSInCData(getAttachmentValue($attachment));
echo 'result=' . base64_encode(trim($result));
?>
