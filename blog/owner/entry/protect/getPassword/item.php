<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
$password = DBQuery::queryCell("SELECT `password` FROM `{$database['prefix']}Entries` WHERE `owner` = $owner AND `id` = {$suri['id']} AND `draft` = 0");
if (is_null($password)) $password = '';
printRespond(array('error' => 0, 'password' => $password));
?>