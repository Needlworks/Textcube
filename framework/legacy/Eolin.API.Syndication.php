<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class Syndication {
	/*@static@*/
	function join($link) {
		return true;
		$link = trim($link);
		if (empty($link))
			return false;
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
		$request = new HTTPRequest('POST', TEXTCUBE_SYNC_URL);
		$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
		return ($request->send("mode=0&path=".urlencode($link)) && (checkResponseXML($request->responseText) === 0));
	}
}
?>
