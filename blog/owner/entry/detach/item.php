<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (!empty($_GET['name']) && deleteAttachment($owner, $suri['id'], $_GET['name']))
	respondResultPage(0);
else
	respondResultPage(-1);
?>
