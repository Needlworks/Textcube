<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

/***** Functions below this line is symbolic links for legacy support *****/
/* NOTE : DO NOT USE THESE FUNCTIONS TO IMPLEMENT MODELS / PLUGINS. 
   THESE FUNCTIONS WILL BE DEPRECATED SOON. */

function mysql_tt_escape_string($string, $link = null) {
	requireComponent('Eolin.PHP.Core');
	return DBQuery::escapeString($string, $link);
}

function mysql_tc_escape_string($string, $link = null) {
	requireComponent('Eolin.PHP.Core');
	return DBQuery::escapeString($string, $link);
}

?>
