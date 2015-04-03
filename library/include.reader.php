<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

if ($context->getProperty('service.reader') === false) {
    exit;
}

define('__TEXTCUBE_ADMINPANEL__', true);

$context->setProperty('import.component', array());
$context->setProperty('import.basics', array( // Basics
    'function/string',
    'function/time',
    'function/javascript',
    'function/html',
    'function/xml',
    'function/misc',
    'function/mail'));
$context->setProperty('import.library', array( // Library
    'auth'));
$context->setProperty('import.model', array( // Model
    'blog.service',
    'blog.blogSetting',
//	'blog.user',
    'blog.fx',
    'common.setting',
    'common.plugin',
    'reader.common'));
$context->setProperty('import.view', array( // View
    'html',
    'ownerView',
    'paging',
    'view'));
?>
