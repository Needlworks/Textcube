<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function xml_parser($url, $mdate) {
	if ($mdate == "0")
		$mdate = "";
	list($header, $body, $lmdate) = @get_remotefile($url, $mdate);
	$st = get_sock_status($header);
	if ($st == 200) {
		$vals = tt_xml_parser_into_struct($body);
	}
	return array($st, $header, $body, $lmdate, $vals);
}

function tt_xml_parser_into_struct($body) {
	$h_set = array();
	$r_set = array();
	while ($n1 = strpos($body, "<![CDATA[")) {
		$n2 = strpos($body, "]]>");
		if (!$n2 || $n1 > $n2)
			break;
		$stamp = get_timestamp2();
		$cval = substr($body, $n1 + 9, $n2 - $n1 - 9);
		$body = str_replace("<![CDATA[" . $cval . "]]>", $stamp, $body);
		$h_set[$stamp] = trim($cval);
	}
	$b_set = explode("<", $body);
	foreach ($b_set as $k => $row) {
		$inp = array();
		$att_set = array();
		$row = trim($row);
		if (!$row)
			continue;
		if (substr($row, 0, 1) == "/") {
			if (strpos($row, "item")) {
				$inp['tag'] = "ITEM";
				$inp['type'] = "close";
				array_push($r_set, $inp);
			}
			continue;
		}
		if( strpos( $row, ">" ) === false ) {
			continue;
		}
		list($tag_inf, $val) = explode(">", $row);
		if ($tag_nid = strpos($tag_inf, " ")) {
			$tag = substr($tag_inf, 0, $tag_nid);
			$tag_inf = substr($tag_inf, $tag_nid);
			while ($tag_n1 = strpos($tag_inf, "=")) {
				$tag_n2 = strpos($tag_inf, "\"", $tag_n1 + 2);
				$att_inf = substr($tag_inf, 1, $tag_n2);
				$tag_inf = substr($tag_inf, $tag_n2 + 1);
				$att_var = trim(strtoupper(substr($att_inf, 0, $tag_n1 - 1)));
				$att_val = trim(str_tag_on(str_replace("\"", "", substr($att_inf, $tag_n1 + 1))));
				$att_set[$att_var] = $att_val;
			}
			$inp['attributes'] = $att_set;
		} else {
			$tag = $tag_inf;
		}
		if (!$tag || $tag == "?XML")
			continue;
		$tag = strtoupper($tag);
		$inp['tag'] = $tag;
		$val = trim($val);
		if (isset($h_set[$val]))
			$val = $h_set[$val];
		else if ($val)
			$val = str_tag_on($val);
		$inp['value'] = str_replace("document.cookie", "document.&#99;ookie", $val);
		array_push($r_set, $inp);
	}
	return $r_set;
}

function get_remotefile($url, $mdate) {
	$url_stuff = parse_url($url);
	if (!$fp = @fsockopen($url_stuff['host'], (isset($url_stuff['port']) ? ($url_stuff['port']) : ("80")), $errno, $errstr, 2))
		return false;
	else {
		if (empty($url_stuff['path'])) {
			$url_stuff['path'] = "/";
		}
		if (!empty($url_stuff['query'])) {
			$url_stuff['path'] .= "?";
		} else {
			$url_stuff['query'] = "";
		}
		$header = "GET " . $url_stuff['path'] . $url_stuff['query'] . " HTTP/1.0";
		$header .= "\r\nHost: " . $url_stuff['host'];
		$header .= "\r\nIf-Modified-Since: $mdate";
		$header .= "\r\n\r\n";
		fputs($fp, $header);
		$header = '';
		$body = '';
		$lmdate = '';
		$act = false;
		$cnt = 0;
		socket_set_timeout($fp, 4);
		while ((!feof($fp))) {
			if ($cnt == 15)
				break;
			$line = fgets($fp, 1024);
			$ss = socket_get_status($fp);
			if ($ss['timed_out'])
				return false;
			if (!$act) {
				if (strpos($line, "\r\n", 0) == 0)
					$act = true;
				if (($n1 = strpos($line, "Last-Modified:")) !== false)
					$lmdate = trim(substr($line, $n1 + 14));
				if ( substr($line,0,9) == "Location:" ) {
					$loc = trim(substr($line, 9));
					break;
				}
				$header .= $line;
			} else {
				$body .= $line;
				if (strpos($line, "</item>") !== false)
					$cnt++;
			}
		}
		fclose($fp);
	}
	if (!empty($loc))
		list($header, $body, $lmdate) = get_remotefile($loc, $mdate);
	return array($header, $body, $lmdate);
}

function get_sock_status($str) {
	if (strpos($str, "200 OK"))
		return 200;
	if (strpos($str, "304 Not Modified"))
		return 304;
	else
		return 404;
}

function get_timestamp2() {
	list($usec) = explode(" ", microtime());
	return date("ymdHis", time()) . substr($usec, 2, 6);
}

function str_dbi_check($array) {
	if (count($array)) {
		while (list($key, $val) = each($array)) {
			$array[$key] = str_replace("'", "&#39;", $val);
		}
	}
	return $array;
}

function get_siteinfo($xmlinfo) {
	$st = $sl = $sd = '';
	foreach ($xmlinfo as $k => $row) {
		if (!$st && $row['tag'] == "TITLE")
			$st = $row['value'];
		else if (!$sl && $row['tag'] == "LINK")
			$sl = $row['value'];
		else if (!$sd && $row['tag'] == "DESCRIPTION")
			$sd = $row['value'];
		else if ($row['tag'] == "ITEM")
			break;
	}
	return array($st, $sl, $sd);
}

function correctTTForXmlText($text) {
	return str_replace('&quot;', '"', str_replace('&#39;', '\'', $text));
}

function checkResponseXML($responseText) {
	global $service;

	$xmls = new XMLStruct();
	if(!$xmls->open(trim($responseText), $service['encoding']))
		return false;
	if(($error = $xmls->getValue('/response/error')) !== null)
		return intval($error);
	else
		return false;
}
?>
