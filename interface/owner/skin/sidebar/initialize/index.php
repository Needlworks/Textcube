<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlogOwner.php';
requireStrictRoute();
requireLibrary('blog.skin');

if (!array_key_exists('viewMode', $_REQUEST)) $_REQUEST['viewMode'] = '';
else $_REQUEST['viewMode'] = '?' . $_REQUEST['viewMode'];

setting::removeBlogSettingGlobal('sidebarOrder');
Skin::purgeCache();
header('Location: '. $blogURL . '/owner/skin/sidebar' . $_REQUEST['viewMode']);
?>
