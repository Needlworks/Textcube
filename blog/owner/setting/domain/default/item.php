<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
if (setDefaultDomain($owner, $suri['id'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>