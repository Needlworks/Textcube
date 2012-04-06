<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$trace = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

function trace($message) {
	global $trace;
	if (strlen($message) == 0)
		return;
	@socket_sendto($trace, $message, strlen($message), 0, 'localhost', 7535);
}

function traceln($message = '') {
	trace($message);
	trace("\n");
}
traceln();
traceln("## New request from {$_SERVER['REMOTE_ADDR']} #################################");
?>
