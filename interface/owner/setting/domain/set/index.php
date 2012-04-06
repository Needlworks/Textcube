<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'defaultDomain' => array(array('0', '1')),
		'primaryDomain' => array('string'),
		'secondaryDomain' => array('domain', 'default' => '')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();

if(empty($_POST['secondaryDomain']) && $_POST['defaultDomain'] == 1)
	Respond::ResultPage(4);
else if( ($result = setPrimaryDomain($blogid, $_POST['primaryDomain'])) > 0 )
	Respond::PrintResult(array('error' => 2, 'msg' => $result));
else if( ($result = setSecondaryDomain($blogid, $_POST['secondaryDomain'])) > 0 )
	Respond::PrintResult(array('error' => 3, 'msg' => $result));
else if(!setDefaultDomain($blogid, $_POST['defaultDomain']))
	Respond::ResultPage(1);
else
	Respond::ResultPage(0);
?>
