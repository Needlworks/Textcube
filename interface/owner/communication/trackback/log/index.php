<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireModel("blog.response.remote");

$result = getTrackbackLog($blogid, $suri['id']);
if ($result !== false) {
	$result = str_replace(' ', '&nbsp;', $result);
	Respond::PrintResult(array('error' => 0, 'result' => $result));
}
else
	Respond::PrintResult(array('error' => 1, 'msg' => POD::error()));
?> 
