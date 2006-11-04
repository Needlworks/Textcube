<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$result = getTrackbackLog($owner, $suri['id']);
if ($result !== false) {
	$result = str_replace(' ', '&nbsp;', $result);
	printRespond(array('error' => 0, 'result' => $result));
}
else
	printRespond(array('error' => 1, 'msg' => mysql_error()));
?> 