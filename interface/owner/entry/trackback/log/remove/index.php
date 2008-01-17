<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.trackback");


requireStrictRoute();
if (deleteTrackbackLog($blogid, $suri['id']) !== false)
	respond::ResultPage(0);
else
	respond::ResultPage( - 1);
?> 
