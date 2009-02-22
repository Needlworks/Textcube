<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(	'POST' => array(	'Name' => array('string'),	
									'DATA' => array('string')
									)
		);
require ROOT . '/library/includeForBlogOwner.php';
if (false) {
    fetchConfigVal();
}
$pluginName = $_POST['Name'];
$DATA = $_POST['DATA'];
$result = handleDataSet($pluginName, $DATA );
respond::PrintResult($result);
?>
