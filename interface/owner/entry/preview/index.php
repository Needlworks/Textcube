<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireModel('blog.entry');
$entries = array();
if (is_null($entry = getEntry($blogid, $suri['id'], true)))
	$entry = getEntry($blogid, $suri['id'], false);
if (!is_null($entry) && ($entry['category'] >= 0)) {
	if (isset($entry['appointed']))
		$entry['published'] = $entry['appointed'];
	$entry['categoryLabel'] = getCategoryLabelById($blogid, $entry['category']);
	$entries[0] = $entry;
}
unset($entry);
require ROOT . '/interface/common/blog/begin.php';
require ROOT . '/interface/common/blog/entries.php';

$pageTitle = _t('미리보기') . ' - ' . $pageTitle;
require ROOT . '/interface/common/blog/end.php';
?>
