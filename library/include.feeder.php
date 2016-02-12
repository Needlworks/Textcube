<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

if ($context->getProperty('service.reader') === false) {
    exit;
}

define('NO_LOCALE', true);

$context->setProperty('import.library', array(
    'function.string',
    'function.time',
    'function.javascript',
    'function.html',
    'function.xml',
    'function.mail',
    'DEBUG : Basic functions loaded.',
    'auth',
    'DEBUG : Default library loaded.',
    'model.blog.service',
    'model.blog.blogSetting',
    'model.common.setting',
    'model.common.plugin',
    'model.common.reader',
    'DEBUG : Models loaded.'
));
?>
