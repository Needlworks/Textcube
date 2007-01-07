<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(
	'GET' => array(
		'parent' => array('int')
	)
);
require ROOT . '/lib/includeForOwner.php';
$result = getAttachmentSizeLabel($owner, $_GET['parent']);
printRespond(array ('error' => empty($result) ? 1 : 0, 'result' => $result));
?> 