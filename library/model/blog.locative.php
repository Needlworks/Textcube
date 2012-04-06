<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getLocatives($blogid) {
	return getEntries($blogid, 'id, userid, title, slogan, location', 'length(location) > 1 AND category > -1', 'location');
}

function suggestLocatives($blogid, $filter) {
	global $database;
	$locatives = array();
	$result = POD::queryAll('SELECT DISTINCT location, COUNT(*) cnt FROM '.$database['prefix'].'Entries WHERE blogid = '.$blogid.' AND location LIKE "'.POD::escapeString($filter).'%" GROUP BY location ORDER BY cnt DESC LIMIT 10');
	if ($result) {
		foreach ($result as $locative) {
			$locatives[] = $locative[0];
		}
	}
	return $locatives;
}
?>
