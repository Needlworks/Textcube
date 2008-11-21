<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
if (!empty($_GET['domain']) && setSecondaryDomain($blogid, $_GET['domain'])) {
	Respond::ResultPage(0);
}
Respond::ResultPage( - 1);
?>
