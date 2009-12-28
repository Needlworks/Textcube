<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
final class HTTPRequest {
	var $method = 'GET', $url, $async = false, $contentType = 'application/x-www-form-urlencoded', $content = '', $eTag, $lastModified, $referer, $timeout = 10, $responseText, $pathToSave;
	
	function __construct() {
		switch (func_num_args()) {
			case 0:
				break;
			case 1:
				$this->url = func_get_arg(0);
				break;
			default:
			case 2:
				$this->method = func_get_arg(0);
				$this->url = func_get_arg(1);
				break;
		}
	}
	function send($content = null) {
		if (empty($this->url))
			return false;
		if (!is_null($content))
			$this->content = $content;
		$request = @parse_url($this->url);
		for ($trial = 0; $trial < 5; $trial++) {
			unset($this->_response);
			if (empty($request['scheme']) || ($request['scheme'] != 'http') || empty($request['host']))
				return false;
			if (empty($request['port']))
				$request['port'] = 80;
			if (empty($request['path']))
				$request['path'] = '/';
			if (!$socket = @fsockopen($request['host'], $request['port'], $errno, $errstr, $this->timeout))
				return false;
				
			$path = empty($request['query']) ? $request['path'] : $request['path'] . '?' . $request['query'];
			fwrite($socket, $this->method . ' ' . $path . " HTTP/1.1\r\n");
			fwrite($socket, 'Host: ' . $request['host'] . "\r\n");
			fwrite($socket, "User-Agent: Mozilla/4.0 (compatible; Textcube ".TEXTCUBE_VERSION.")\r\n");
			fwrite($socket, "Accept-Encoding: identity\r\n");
			if (!is_null($this->referer))
				fwrite($socket, "Referer: {$this->referer}\r\n");
			if (!is_null($this->eTag))
				fwrite($socket, "If-None-Match: {$this->eTag}\r\n");
			if (!is_null($this->lastModified)) {
				if (is_numeric($this->lastModified))
					$this->lastModified = Timestamp::getRFC1123GMT($this->lastModified);
				fwrite($socket, "If-Modified-Since: {$this->lastModified}\r\n");
			}
			if (!empty($this->content)) {
				fwrite($socket, "Content-Type: {$this->contentType}\r\n");
				fwrite($socket, "Content-Length: " . strlen($this->content) . "\r\n");
			}
			fwrite($socket, "Connection: close\r\n");
			fwrite($socket, "\r\n");
			if ($this->content !== false)
				fwrite($socket, $this->content);
			if($this->async)
				return true;
			
			for (; $trial < 5; $trial++) {
				if (!$line = fgets($socket)) {
					fclose($socket);
					return false;
				}
				if (!preg_match('@^HTTP/([0-9\.]+)[ \t]+([0-9]+)[ \t]+@', $line, $match)) {
					fclose($socket);
					return false;
				}
				$this->_response['version'] = $match[1];
				$this->_response['status'] = $match[2];
				while ($line = fgets($socket)) {
					$line = rtrim($line);
					if (empty($line))
						break;
					$header = explode(': ', $line, 2);
					if (count($header) != 2)
						continue;
					$header[0] = strtolower($header[0]);
					switch ($header[0]) {
						case 'connection':
						case 'content-length':
						case 'content-type':
						case 'date':
						case 'etag':
						case 'last-modified':
						case 'location':
						case 'transfer-encoding':
						case 'X-Pingback':
						case 'X-CommunicationFeed':
							$this->_response[$header[0]] = trim($header[1]);
							break;
					}
				}
				if ($this->_response['status'] != 100)
					break;
				unset($this->_response);
			}
			if (in_array($this->_response['status'], array(300, 301, 302, 307))) {
				fclose($socket);
				if (empty($this->_response['location']))
					return false;
				$this->url = $this->_response['location'];
				$request['path'] = '/';
				foreach (parse_url($this->_response['location']) as $key => $value)
					$request[$key] = $value;
				continue;
			}
			break;
		}
		
		switch ($this->_response['status']) {
			case 200: // OK
				break;
			case 300: // Multiple Choices
			case 301: // Moved Permanently
			case 302: // Found
			default:
				fclose($socket);
				return false;
			case 304: // Not Modified
				if (is_null($this->eTag) && is_null($this->lastModified)) {
					fclose($socket);
					return false;
				}
				break;
		}

		if ($this->pathToSave) {
			if (!$fp = fopen($this->pathToSave, 'wb+')) {
				fclose($socket);
				return false;
			}
			if ($this->getResponseHeader('Transfer-Encoding') == 'chunked') {
				while ($line = fgets($socket)) {
					$chunkSize = hexdec(trim($line));
					if ($chunkSize == 0)
						break;					
					$readBuffer = '';
					while(strlen($readBuffer) < $chunkSize + 2)
						$readBuffer .= fread($socket, $chunkSize + 2 - strlen($readBuffer));
					fwrite($fp, substr($readBuffer, 0, strlen($readBuffer) - 2));
				}
			} else if (!empty($this->_response['content-length'])) {
				while (ftell($fp) < $this->_response['content-length'])
					fwrite($fp, fread($socket, 10240));
			} else if (!empty($this->_response['content-type'])) {
				while (!feof($socket))
					fwrite($fp, fread($socket, 10240));
			}
			fclose($fp);
		} else {
			$this->responseText = '';
			if ($this->getResponseHeader('Transfer-Encoding') == 'chunked') {
				while ($line = fgets($socket)) {
					$chunkSize = hexdec(trim($line));
					if ($chunkSize == 0)
						break;					
					$readBuffer = '';
					while(strlen($readBuffer) < $chunkSize + 2)
						$readBuffer .= fread($socket, $chunkSize + 2 - strlen($readBuffer));
					$this->responseText .= substr($readBuffer, 0, strlen($readBuffer) - 2);
				}
			} else if (!empty($this->_response['content-length'])) {
				while (strlen($this->responseText) < $this->_response['content-length'])
					$this->responseText .= fread($socket, $this->_response['content-length'] - strlen($this->responseText));
			} else if (!empty($this->_response['content-type'])) {
				while (!feof($socket))
					$this->responseText .= fread($socket, 10240);
			}
		}

		fclose($socket);
		return true;
	}
	
	function getResponseHeader($name) {
		$name = strtolower($name);
		return (isset($this->_response[$name]) ? $this->_response[$name] : null);
	}
}
?>
