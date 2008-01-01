<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
// Basics
require ROOT .'/lib/config.php';
require ROOT .'/lib/function/string.php';
require ROOT .'/lib/function/time.php';
require ROOT .'/lib/function/javascript.php';
require ROOT .'/lib/function/html.php';
require ROOT .'/lib/function/xml.php';
require ROOT .'/lib/function/misc.php';
require ROOT .'/lib/function/image.php';
require ROOT .'/lib/function/mail.php';
require ROOT .'/lib/functions.php';
// Library
require ROOT .'/lib/database.php';
require ROOT .'/lib/locale.php';
require ROOT .'/lib/auth.php';
// Model
require ROOT .'/lib/model/blog.service.php';
require ROOT .'/lib/model/blog.blogSetting.php';
require ROOT .'/lib/model/blog.user.php';
require ROOT .'/lib/model/common.paging.php';
require ROOT .'/lib/model/common.setting.php';
require ROOT .'/lib/model/common.plugin.php';
require ROOT .'/lib/model/reader.common.php';
// View
require ROOT .'/lib/view/html.php';
require ROOT .'/lib/view/pages.php';
require ROOT .'/lib/view/ownerView.php';
require ROOT .'/lib/view/paging.php';
require ROOT .'/lib/view/view.php';

// Initializing environment.
require ROOT .'/lib/initialize.php';
require ROOT .'/lib/plugins.php';

header('Content-Type: text/html; charset=utf-8');
// Check access control list
requireOwnership();
require ROOT .'/lib/pageACL.php';
?>
