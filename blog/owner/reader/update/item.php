<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlog.php';
requireStrictRoute();
if ($feed = DBQuery::queryRow("SELECT * FROM {$database['prefix']}Feeds WHERE id = {$suri['id']}"))
	respondResultPage(updateFeed($feed));
else
	respondResultPage(-1);
?>