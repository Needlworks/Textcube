<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
if(array_key_exists('useBlogAPI',  $_REQUEST)) {
	if (($_REQUEST['useBlogAPI'] == "yes") || ($_REQUEST['useBlogAPI'] == "1") || ($_REQUEST['useBlogAPI'] == "true"))
		$useBlogAPI = '1';
	else 
		$useBlogAPI = '0';
} else $useBlogAPI = '0';

if (!array_key_exists('defaultEditor',$_REQUEST) || !array_key_exists('defaultFormatter',$_REQUEST))
	Respond::ResultPage( -1);

if (Setting::setBlogSettingGlobal("defaultEditor", $_REQUEST['defaultEditor']) 
		&& Setting::setBlogSettingGlobal("defaultFormatter", $_REQUEST['defaultFormatter']) 
		&& Setting::setBlogSettingGlobal("useBlogAPI", $useBlogAPI) 
		&& Setting::setBlogSettingGlobal("blogApiPassword", $_REQUEST['blogApiPassword']) ) {
	Respond::ResultPage(0);
}
Respond::ResultPage( -1);
?>
