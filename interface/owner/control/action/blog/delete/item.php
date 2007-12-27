<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';

requireStrictRoute();

$result = removeBlog($suri['id']);

if ($result===true) {
	printRespond(array('error' => 0 , 'result' =>$suri['id']));
}
else {
	printRespond(array('error' => -1 , 'result' =>$result));
}

?>
