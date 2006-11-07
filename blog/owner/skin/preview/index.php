<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
list($entries, $paging) = getEntriesWithPaging($owner, $suri['page'], $blog['entriesOnPage']);
if(!Validator::filename($_GET['skin']) && $_GET['skin'] != "customize/$owner")
	respondNotFoundPage();
$skinSetting['skin'] = $_GET['skin'];
$skin = new Skin($skinSetting['skin'], true);

require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';

$pageTitle = _t('스킨 미리보기');

require ROOT . '/lib/piece/blog/end.php';
?>
