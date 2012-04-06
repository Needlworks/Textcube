<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'names' => array('string', 'default' => null)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.attachment");


requireStrictRoute();
if (!empty($_POST['names']) && deleteAttachmentMulti($blogid, $suri['id'], $_POST['names']))
	Respond::ResultPage(0);
else
	Respond::ResultPage( - 1);
?>
