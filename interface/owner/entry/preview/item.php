<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlog.php';
requireModel('blog.entry');
$entries = array();
if (!$entry = getEntry($blogid, $suri['id'], true))
	$entry = getEntry($blogid, $suri['id'], false);
if ($entry && ($entry['category'] >= 0)) {
	if (isset($entry['appointed']))
		$entry['published'] = $entry['appointed'];
	$entry['categoryLabel'] = getCategoryLabelById($blogid, $entry['category']);
	$entries[0] = $entry;
}
unset($entry);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';

$pageTitle = _t('미리보기') . ' - ' . $pageTitle;
require ROOT . '/lib/piece/blog/end.php';
?>
