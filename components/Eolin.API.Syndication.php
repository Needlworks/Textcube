<?php
class Syndication {
	/*@static@*/
	function join($link) {
		return true;
		$link = trim($link);
		if (empty($link))
			return false;
		requireComponent('Eolin.PHP.HTTPRequest');
		$request = new HTTPRequest('POST', TATTERTOOLS_SYNC_URL);
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
		$request = new HTTPRequest('POST', TATTERTOOLS_SYNC_URL);
		$request->contentType = 'application/x-www-form-urlencoded; charset=utf-8';
		return ($request->send("mode=0&path=".urlencode($link)) && (checkResponseXML($request->responseText) === 0));
	}
}
?>