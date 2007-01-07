<?php
/// Copyright (c) 2004-2007, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getLocatives($owner) {
	return getEntries($owner, 'id, title, slogan, location', 'length(location) > 1', 'location');
}
?>
