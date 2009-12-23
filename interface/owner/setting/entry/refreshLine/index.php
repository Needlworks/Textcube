<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
if(Setting::removeBlogSetting('LineSetting',true)) Respond::ResultPage(0);
else Respond::ResultPage(-1);
?>