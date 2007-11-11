<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
requireModel("blog.link");
requireStrictRoute();
$respond = array();
list($result,$visible) = toggleVisibility($blogid, $suri['id']);
printRespond( array( 'error' => $result ? 0:1, 'visible' => $visible ), false );
?>
