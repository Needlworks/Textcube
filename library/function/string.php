<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function str_trans($str) {
	return str_replace("'", "&#39;", str_replace("\"", "&quot;", $str));
}
function str_trans_rev($str) {
	return str_replace("&#39;", "'", str_replace("&quot;", "\"", $str));
}
function trim_url($str) {
	return ltrim(rtrim($str, '/'), '/');
}
?>
