<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlogOwner.php';
if (!empty($_GET['name']) && setPrimaryDomain($blogid, $_GET['name']))
	respond::ResultPage(0);
respond::ResultPage( - 1);
?>
