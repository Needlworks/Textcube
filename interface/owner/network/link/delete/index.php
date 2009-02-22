<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.link");
requireStrictRoute();
respond::ResultPage(deleteLink($blogid, $suri['id']));
?>
