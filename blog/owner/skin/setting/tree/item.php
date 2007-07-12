<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
$categories = getCategories($blogid);
printRespond(array('code' => urlencode(getCategoriesViewInSkinSetting(getEntriesTotalCount($blogid), getCategories($blogid), $suri['id']))));
?>
