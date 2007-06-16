<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require 'config.php';
include_once ROOT . '/config.php';
require 'function/string.php';
require 'function/time.php';
require 'function/javascript.php';
require 'function/html.php';
require 'function/xml.php';
require 'function/mysql.php';
require 'function/misc.php';
require 'function/image.php';
require 'function/mail.php';
require 'functions.php';
require 'database.php';
require 'model/blog.service.php';
require 'model/blog.blogSetting.php';
require 'model/blog.category.php';
require 'model/common.paging.php';
require 'model/blog.teamblog.php';
require 'model/common.setting.php';
require 'model/blog.skin.php';
require 'model/common.plugin.php';
require 'suri.php';
require 'session.php';
require 'auth.php';
require 'model/blog.user.php';
require 'locale.php';
require 'plugins.php';
require 'view/html.php';
require 'view/pages.php';
require 'view/ownerView.php';
require 'view/paging.php';
require 'view/view.php';
require 'pageACL.php';
header('Content-Type: text/html; charset=utf-8');
requireOwnership();
?>
