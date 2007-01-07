<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'updateCycle' => array('int', 0),
		'feedLife' => array('int', 0),
		'loadImage' => array(array('1', '2')),
		'allowScript' => array(array('1', '2')),
		'newWindow' => array(array('1', '2')) 
	)
);

require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
respondResultPage(setReaderSetting($owner, $_POST));
?>
