<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'names' => array('string', 'default' => null)
	)
);
require ROOT . '/library/preprocessor.php';
importlib("model.blog.attachment");


requireStrictRoute();
if (!empty($_POST['names']) && deleteAttachmentMulti($blogid, $suri['id'], $_POST['names']))
	Respond::ResultPage(0);
else
	Respond::ResultPage( - 1);
?>
