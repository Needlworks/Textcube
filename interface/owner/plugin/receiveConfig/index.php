<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(	'POST' => array(	'Name' => array('string'),	
									'DATA' => array('string')
									)
		);
require ROOT . '/library/preprocessor.php';

$pluginName = $_POST['Name'];
$DATA = $_POST['DATA'];
$result = handleDataSet($pluginName, $DATA );
Respond::PrintResult($result);
?>
