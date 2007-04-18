<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
if(array_key_exists('useBlogAPI',  $_REQUEST)) {
	if (($_REQUEST['useBlogAPI'] == "yes") || ($_REQUEST['useBlogAPI'] == "1") || ($_REQUEST['useBlogAPI'] == "true"))
		$useBlogAPI = '1';
	else 
		$useBlogAPI = '0';
} else $useBlogAPI = '0';

if (!array_key_exists('defaultEditor',$_REQUEST) || !array_key_exists('defaultFormatter',$_REQUEST))
	respondResultPage( -1);

if (setUserSetting("defaultEditor", $_REQUEST['defaultEditor']) && setUserSetting("defaultFormatter", $_REQUEST['defaultFormatter']) && setUserSetting("useBlogAPI", $useBlogAPI)) {
	respondResultPage(0);
}
respondResultPage( -1);
?>
