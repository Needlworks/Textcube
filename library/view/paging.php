<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// NOTE : This file and function will be deprecated. Use Paging object instead.
function getPagingView(& $paging, & $template, & $itemTemplate, $useSkinCache = false) {
    return Paging::getPagingView($paging, $template, $itemTemplate, $useSkinCache);
}

?>
