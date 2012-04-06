<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireModel("blog.attachment");


requireStrictRoute();
if (!empty($_GET['name']) && deleteAttachment($blogid, $suri['id'], $_GET['name']))
	Respond::ResultPage(0);
else
	Respond::ResultPage(-1);
?>
