<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function correctTTForXmlText($text) {
	return str_replace('&quot;', '"', str_replace('&#39;', '\'', $text));
}
?>
