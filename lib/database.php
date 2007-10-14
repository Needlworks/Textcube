<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

DBQuery::bind($database);

function tc_escape_string($string, $link = null) {
	return DBQuery::escapeString($string, $link);
}
?>
