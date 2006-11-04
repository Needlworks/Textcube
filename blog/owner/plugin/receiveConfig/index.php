<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../..');
$IV = array(	'POST' => array(	'Name' => array('string'),	
									'DATA' => array('string')
									)
		);
require ROOT . '/lib/includeForOwner.php';
if (false) {
    fetchConfigVal();
}
$pluginName = $_POST['Name'];
$DATA = $_POST['DATA'];
$result = handleDataSet($pluginName, $DATA );
printRespond($result);
?>
