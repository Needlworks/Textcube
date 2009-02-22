<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/includeForBlogOwner.php';
requireModel("blog.trackback");
requireComponent('Textcube.Function.Respond');

$result = getTrackbackLog($blogid, $suri['id']);
if ($result !== false) {
	$result = str_replace(' ', '&nbsp;', $result);
	respond::PrintResult(array('error' => 0, 'result' => $result));
}
else
	respond::PrintResult(array('error' => 1, 'msg' => POD::error()));
?> 
