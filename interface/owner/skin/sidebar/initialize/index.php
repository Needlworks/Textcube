<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$ajaxcall = isset($_REQUEST['ajaxcall']) ? true : false;

require ROOT . '/library/preprocessor.php';
requireStrictRoute();


if (!array_key_exists('viewMode', $_REQUEST)) $_REQUEST['viewMode'] = '';
else $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

Model_Setting::removeBlogSettingGlobal('sidebarOrder');
Skin::purgeCache();
if($ajaxcall == false) header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
else respond::ResultPage(0);
?>
