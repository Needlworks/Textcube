<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('__TEXTCUBE_CUSTOM_HEADER__',true);
require ROOT . '/library/preprocessor.php';
fireEvent($suri['directive'] . '/' . $suri['value']);
if (!headers_sent())
	Respond::NotFoundPage();
?>
