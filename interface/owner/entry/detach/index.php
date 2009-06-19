<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireModel("blog.attachment");


requireStrictRoute();
if (!empty($_GET['name']) && deleteAttachment($blogid, $suri['id'], $_GET['name']))
	Utils_Respond::ResultPage(0);
else
	Utils_Respond::ResultPage(-1);
?>
