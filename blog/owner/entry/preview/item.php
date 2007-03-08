<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
$entries = array();
if (!$entry = getEntry($owner, $suri['id'], true))
	$entry = getEntry($owner, $suri['id'], false);
if ($entry && ($entry['category'] >= 0)) {
	if (isset($entry['appointed']))
		$entry['published'] = $entry['appointed'];
	$entry['categoryLabel'] = getCategoryLabelById($owner, $entry['category']);
	$entries[0] = $entry;
}
unset($entry);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';

$pageTitle = _t('미리보기') . ' - ' . $pageTitle;
require ROOT . '/lib/piece/blog/end.php';
?>
