<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$context->setProperty('import.component', array());
$context->setProperty('import.basics', array( // Basics
    'function/file',
    'function/string',
    'function/time',
    'function/javascript',
    'function/html',
    'function/xml',
    'function/misc',
    'function/mail'));
$context->setProperty('import.library', array( // Library
    'blog.skin',
    'auth'));
$context->setProperty('import.model', array( // Model
    'blog.service',
    'blog.blogSetting',
    'blog.category',
    'blog.skin',
    'blog.tag',
    'blog.keyword',
    'blog.archive',
    'blog.page',
    'blog.notice',
    'blog.link',
    'blog.fx',
    'common.plugin',
    'common.module',
    'common.setting'));
$context->setProperty('import.view', array( // View
    'html',
    'ownerView',
    'paging',
    'view'));
?>
