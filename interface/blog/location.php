<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
if (false) {
	fetchConfigVal();
}
$locatives = getLocatives($blogid);
require ROOT . '/interface/common/blog/begin.php';
require ROOT . '/interface/common/blog/locatives.php';
require ROOT . '/interface/common/blog/end.php';
?>
