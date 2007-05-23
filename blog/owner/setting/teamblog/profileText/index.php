<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'teamblogUserProfile' => array('string','default'=>null)
	)
);
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();

function changeProfile($owner,$profile){
	global $database, $_SESSION;

  $admin = $_SESSION['admin'];
  
  $profile = str_replace("\r","", $profile);
  $profile = str_replace("\n","<br>", $profile);
  
	$sql="UPDATE `{$database['prefix']}Teamblog` SET `profile`='$profile'  WHERE `userid` = '$admin' and `teams`='$owner'";
	return DBQuery::execute($sql);
}

if (changeProfile($owner, $_POST['teamblogUserProfile'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>