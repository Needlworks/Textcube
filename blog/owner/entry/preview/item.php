<?php
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
$entries = array();
if (!$entry = getEntry($owner, $suri['id'], true))
	$entry = getEntry($owner, $suri['id'], false);
if ($entry && ($entry['category'] >= 0)) {
	if (isset($entry['appointed']))
		$entry['published'] = $entry['appointed'];
	$entry['categoryLabel'] = getCategoryLabelById($owner, $entry['id']);
	$entries[0] = $entry;
}
unset($entry);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
