<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
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
require 'model/service.php';
require 'model/archive.php';
require 'model/attachment.php';
require 'model/blogSetting.php';
require 'model/category.php';
require 'model/comment.php';
require 'model/entry.php';
require 'model/keyword.php';
require 'model/notice.php';
require 'model/link.php';
require 'model/locative.php';
require 'model/paging.php';
require 'model/rss.php';
require 'model/sidebar.php';
require 'model/statistics.php';
require 'model/trackback.php';
require 'model/trash.php';
require 'model/tag.php';
require 'model/setting.php';
require 'model/skin.php';
require 'model/reader.php';
require 'model/plugin.php';
require 'suri.php';
require 'session.php';
require 'auth.php';
require 'model/user.php';
require 'locale.php';
require 'plugins.php';
require 'view/html.php';
require 'view/pages.php';
require 'view/ownerView.php';
require 'view/paging.php';
require 'view/view.php';
require 'skin.php';
header('Content-Type: text/html; charset=utf-8');
requireOwnership();
?>
