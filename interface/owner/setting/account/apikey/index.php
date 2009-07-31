<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'APIKey' => array('string', 'default'=>'')
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
$result = false;
$result = changeAPIKey(getUserId(), $_POST['APIKey']);
if($result) Respond::ResultPage(0);
else Respond::ResultPage(-1);
?>
