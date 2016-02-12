<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'name' => array('string')
	)
);

require ROOT . '/library/preprocessor.php';
importlib("model.blog.attachment");
$file = array_pop($_FILES);
$attachment = getAttachmentByLabel($blogid, $suri['id'], $_GET['name']);
$result = escapeJSInCData(getPrettyAttachmentLabel($attachment)) . '!^|' . escapeJSInCData(getAttachmentValue($attachment));
echo 'result=' . base64_encode(trim($result));
?>
