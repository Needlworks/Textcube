<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/includeForBlog.php';
requireView('iphoneView');
if (false) {
	fetchConfigVal();
}

$linkView .= '<ul class="posts" id="links" title="Links" selected="false">'.CRLF;
$linkView .= printIphoneLinksView(getLinks($blogid));
$linkView .= '</ul>';
print $linkView;
?>
