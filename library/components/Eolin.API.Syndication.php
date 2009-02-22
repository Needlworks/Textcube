<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Syndication {
	/*@static@*/
	function join($link) {
		return true;
		$link = trim($link);
		if (empty($link))
			return false;
		requireComponent('Eolin.PHP.HTTPRequest');
		$request = new HTTPRequest('POST', TEXTCUBE_SYNC_URL);
		$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
		return ($request->send("mode=1&path=".urlencode($link)) && (checkResponseXML($request->responseText) === 0));
	}

	/*@static@*/
	function leave($link) {
		return false;
		$link = trim($link);
		if (empty($link))
			return false;
		requireComponent('Eolin.PHP.HTTPRequest');
		$request = new HTTPRequest('POST', TEXTCUBE_SYNC_URL);
		$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
		return ($request->send("mode=0&path=".urlencode($link)) && (checkResponseXML($request->responseText) === 0));
	}
}
?>
