<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
$locatives = getLocatives($blogid);
require ROOT . '/interface/common/blog/begin.php';
require ROOT . '/interface/common/blog/locatives.php';
require ROOT . '/interface/common/blog/end.php';
?>
