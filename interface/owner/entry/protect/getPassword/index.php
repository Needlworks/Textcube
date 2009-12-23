<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';


$password = POD::queryCell("SELECT password
		FROM {$database['prefix']}Entries
		WHERE blogid = ".getBlogId()." AND id = {$suri['id']} AND draft = 0");
if (is_null($password)) $password = '';
Respond::PrintResult(array('error' => 0, 'password' => $password));
?>
