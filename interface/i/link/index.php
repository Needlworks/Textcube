<?php
/// Copyright (c) 2004-2014, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
printMobileHTMLHeader();
printMobileHTMLMenu();

$linkView .= '<ul data-role="listview" class="posts" id="links" title="'._text('링크').'" selected="false">'.CRLF;
$linkView .= printMobileLinksView(getLinks($blogid));
$linkView .= '</ul>';
print $linkView;
printMobileHTMLFooter();
?>
