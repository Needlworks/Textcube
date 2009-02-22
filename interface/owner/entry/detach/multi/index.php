<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'names' => array('string', 'default' => null)
	)
);
require ROOT . '/library/preprocessor.php';
requireModel("blog.attachment");


requireStrictRoute();
if (!empty($_POST['names']) && deleteAttachmentMulti($blogid, $suri['id'], $_POST['names']))
	respond::ResultPage(0);
else
	respond::ResultPage( - 1);
?>
