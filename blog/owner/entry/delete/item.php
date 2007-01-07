<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

$isAjaxRequest = checkAjaxRequest();

if ($isAjaxRequest) {
	if (deleteEntry($owner, $suri['id']) === true)
		respondResultPage(0);
	else
		respondResultPage(-1);
} else {
	deleteEntry($owner, $suri['id']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
}
?>
