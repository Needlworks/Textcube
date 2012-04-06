<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
if (setDefaultDomain($blogid, $suri['id'])) {
	Respond::ResultPage(0);
}
Respond::ResultPage( - 1);
?>
