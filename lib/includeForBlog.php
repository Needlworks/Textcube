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
if (defined( 'TCDEBUG')) __tcSqlLogPoint('end of basic function loading');
// Library
require ROOT .'/lib/database.php';
require ROOT .'/lib/locale.php';
require ROOT .'/lib/auth.php';
require ROOT .'/lib/blog.skin.php';
if (defined( 'TCDEBUG')) __tcSqlLogPoint('end of blog.skin.php');
// Models
require ROOT .'/lib/model/blog.service.php';
require ROOT .'/lib/model/blog.archive.php';
require ROOT .'/lib/model/blog.attachment.php';
require ROOT .'/lib/model/blog.blogSetting.php';
require ROOT .'/lib/model/blog.category.php';
require ROOT .'/lib/model/blog.comment.php';
require ROOT .'/lib/model/blog.entry.php';
require ROOT .'/lib/model/blog.keyword.php';
require ROOT .'/lib/model/blog.notice.php';
require ROOT .'/lib/model/blog.link.php';
require ROOT .'/lib/model/blog.locative.php';
require ROOT .'/lib/model/blog.sidebar.php';
require ROOT .'/lib/model/blog.statistics.php';
require ROOT .'/lib/model/blog.trackback.php';
require ROOT .'/lib/model/blog.tag.php';
require ROOT .'/lib/model/blog.user.php';
require ROOT .'/lib/model/common.setting.php';
require ROOT .'/lib/model/common.paging.php';
require ROOT .'/lib/model/common.plugin.php';
require ROOT .'/lib/model/common.module.php';
if (defined( 'TCDEBUG')) __tcSqlLogPoint('End of model loading');
// Views
require ROOT .'/lib/view/html.php';
require ROOT .'/lib/view/paging.php';
require ROOT .'/lib/view/view.php';
if (defined( 'TCDEBUG')) __tcSqlLogPoint('End of view loading');
// Initializing environment.
require ROOT .'/lib/initialize.php';
if (defined( 'TCDEBUG')) __tcSqlLogPoint('End of initializing');
require ROOT .'/lib/plugins.php';
if (defined( 'TCDEBUG')) __tcSqlLogPoint('End of plugin parsing');

header('Content-Type: text/html; charset=utf-8');
?>
