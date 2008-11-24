<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'updateCycle' => array('int', 0),
		'feedLife' => array('int', 0),
		'loadImage' => array(array('1', '2')),
		'allowScript' => array(array('1', '2')),
		'newWindow' => array(array('1', '2')) 
	)
);

require ROOT . '/library/dispatcher.php';
requireStrictRoute();
respond::ResultPage(setReaderSetting($blogid, $_POST));
?>
