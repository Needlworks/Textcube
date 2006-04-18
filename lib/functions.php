<?

function requestHttp($method, $url, $content = false, $contentType = 'application/x-www-form-urlencoded') {
	$info = parse_url($url);
	$socket = fsockopen($info['host'], empty($info['port']) ? 80 : $info['port'], $errno, $errstr, 10);
	if ($socket === false)
		return false;
	$path = empty($info['query']) ? $info['path'] : "{$info['path']}?{$info['query']}";
	fputs($socket, "$method $path HTTP/1.1\r\n");
	fputs($socket, "Host: {$info['host']}\r\n");
	fputs($socket, 'User-Agent: Mozilla/4.0 (compatible; ' . TATTERTOOLS_NAME . ")\r\n");
	if ($content !== false) {
		fputs($socket, "Content-Type: $contentType\r\n");
		fputs($socket, "Content-Length: " . strlen($content) . "\r\n");
	}
	fputs($socket, "Connection: close\r\n");
	fputs($socket, "\r\n");
	if ($content !== false)
		fputs($socket, $content);
	$response = '';
	while (!feof($socket))
		$response .= fgets($socket, 10240);
	fclose($socket);
	return explode("\r\n\r\n", $response, 2);
}

function checkResponseXML($responseText) {
	global $service;

	$xmls = new XMLStruct();
	if(!$xmls->open($responseText, $service['encoding']))
		return false;
	if(($error = $xmls->getValue('/response/error')) !== null)
		return intval($error);
	else
		return false;
}

function str_innerHTML($str) {
	return str_replace('"', '\"', preg_replace('/\r\n|\r|\n/', '', $str));
}

function update($sql) {
	mysql_query($sql);
	$result = mysql_affected_rows();
	if ($result > 0) {
		return $result;
	} else if ($result == 0) {
		return $result;
	} else {
		$error[] = mysql_error();
		return $result;
	}
}

function select($sql) {
	$list = array();
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		$list[] = $row;
	}
	if (count($list) == 1) {
		if (array_key_exists("count(*)", $list[0])) {
			return $list[0]["count(*)"];
		}
	}
	return $list;
}

function size($sql) {
	$list = array();
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result)) {
		$list[] = $row;
	}
	return $list[0][0];
}

function copyRecusive($source, $target, $chkPrint = false) {
	if (Path::getBaseName($source) == "." || Path::getBaseName($source) == "..") {
		return;
	}
	if (!is_dir($source)) {
		copy($source, $target);
		return;
	}
	if (!file_exists($target) || !is_dir($target)) {
		mkdir($target);
		@chmod($target, 0777);
	}
	$d = dir($source);
	while ($entry = $d->read()) {
		copyRecusive("$source/$entry", "$target/$entry", $chkPrint);
	}
	$d->close();
}

function deltree($dir) {
	$d = dir($dir);
	while ($f = $d->read()) {
		if ($f != "." && $f != "..") {
			if (is_dir($dir . $f)) {
				deltree($dir . $f . "/");
				rmdir($dir . $f);
			}
			if (is_file($dir . $f))
				unlink($dir . $f);
		}
	}
	$d->close();
}
?>