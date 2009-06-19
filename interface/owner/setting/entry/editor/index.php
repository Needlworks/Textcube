<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
if(array_key_exists('useBlogAPI',  $_REQUEST)) {
	if (($_REQUEST['useBlogAPI'] == "yes") || ($_REQUEST['useBlogAPI'] == "1") || ($_REQUEST['useBlogAPI'] == "true"))
		$useBlogAPI = '1';
	else 
		$useBlogAPI = '0';
} else $useBlogAPI = '0';

if (!array_key_exists('defaultEditor',$_REQUEST) || !array_key_exists('defaultFormatter',$_REQUEST))
	Utils_Respond::ResultPage( -1);

if (setBlogSetting("defaultEditor", $_REQUEST['defaultEditor']) 
		&& setBlogSetting("defaultFormatter", $_REQUEST['defaultFormatter']) 
		&& setBlogSetting("useBlogAPI", $useBlogAPI) 
		&& setBlogSetting("blogApiPassword", $_REQUEST['blogApiPassword']) ) {
	Utils_Respond::ResultPage(0);
}
Utils_Respond::ResultPage( -1);
?>
