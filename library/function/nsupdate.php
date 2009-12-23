<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function setDomainAddress($domain, $address, $server, $ttl = 86400) {
	$process = proc_open('/usr/bin/nsupdate', array(array('pipe', 'r'), array('pipe', 'w'), array('pipe', 'w')), $pipes);
	if (is_resource($process)) {
		fwrite($pipes[0], "server $server\n");
		fwrite($pipes[0], "update delete $domain.\n");
		if ($address !== false)
			fwrite($pipes[0], "update add $domain. $ttl IN A $address\n");
		fwrite($pipes[0], "\n");
		fclose($pipes[0]);
		$result = true;
		if (fgetc($pipes[1]) !== false)
			$result = false;
		fclose($pipes[1]);
		if (fgetc($pipes[2]) !== false)
			$result = false;
		fclose($pipes[2]);
		proc_close($process);
		return $result;
	}
	return false;
}
?>
