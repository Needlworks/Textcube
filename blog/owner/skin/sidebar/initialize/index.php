<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();

if (!array_key_exists('viewMode', $_REQUEST)) $_REQUEST['viewMode'] = '';
else $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

DBQuery::execute("DELETE FROM `{$database['prefix']}UserSettings` WHERE `user` = {$owner} AND `name` = 'sidebarOrder'");
header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
?>
