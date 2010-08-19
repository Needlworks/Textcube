<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function stripHTML($text, $allowTags = array()) {
	$text = preg_replace('/<(script|style)[^>]*>.*?<\/\1>/si', '', $text);
	if(count($allowTags) == 0)
		$text = preg_replace('/<[\w\/!]+[^>]*>/', '', $text);
	else {
		preg_match_all('/<\/?([\w!]+)[^>]*?>/s', $text, $matches);
		for($i=0; $i<count($matches[0]); $i++) {
			if (!in_array(strtolower($matches[1][$i]), $allowTags))
				$text = str_replace($matches[0][$i], '', $text);
		}
	}
	$text = preg_replace('/&nbsp;?|\xc2\xa0\x20/', ' ', $text);
	$text = trim(preg_replace('/\s+/', ' ', $text));
	if(!empty($text))
		$text = str_replace(array('&#39;', '&apos;', '&quot;'), array('\'', '\'', '"'), $text);
	return $text;
}

function str_innerHTML($str) {
	$pattern = array( '/\r\n|\r|\n/' , '@</@' , '@"@');
	$replace = array( ''             , '<\/'  , '\"' );
	return preg_replace($pattern, $replace, $str);
}

function nl2brWithHTML($str) {
	$str = str_replace('[CODE]', '[CODE][HTML]', $str);
	$str = str_replace('[/CODE]', '[/HTML][/CODE]', $str);
	$inHTML = false;
	$out = '';
	while (true) {
		if ($inHTML) {
			$end = strpos($str, '[/HTML]');
			if ($end === false)
				break;
			else {
				$out .= substr($str, 0, $end);
				$str = substr($str, $end + 7);
				$inHTML = false;
			}
		} else {
			$offset = strpos($str, '[HTML]');
			if ($offset === false) {
				$out .= nl2br($str);
				break;
			} else {
				$out .= nl2br(substr($str, 0, $offset));
				$str = substr($str, $offset + 6);
				$inHTML = true;
			}
		}
	}
	return $out;
}

function parseURL($path) {
	// Reserved.
	return $path;
}

function addLinkSense($text, $attributes = '') {
	return preg_replace('@(\^|\s|"|\')(http://[^\s"\']+)@i','$1<a href="$2"' . $attributes . ' rel="external nofollow">$2</a>',$text);
}

function addProtocolSense($url, $protocol = 'http://') {
	return preg_match('/^[a-zA-Z0-9]+:/', $url) ? $url : $protocol . $url;
}

function decorateSrcInObject($html)
{
	$count = preg_match_all('@src="(.+)"@iU', $html, $matches, PREG_PATTERN_ORDER);
	while ($count > 0) {
		$orig = $matches[0][$count - 1];
		$filename = $matches[1][$count - 1];
		if (strncasecmp($filename, 'http://' , 7) != 0) {
			$html = str_replace($orig, substr($orig,0,4) . '"http://' . $_SERVER['HTTP_HOST'] . $filename . '"', $html);
		}
		$count--;
	}
	return $html;
}

function avoidFlashBorder($html, $tag='object') {
	$pos1 = $pos2 = 0;
	
	$str = strtolower($html);
	
	$result = '';
	while(($pos1 = strpos($str, "<$tag", $pos2)) !== false) {
		$result .= substr($html, $pos2, $pos1 - $pos2);
		$pos2 = $pos1;
		while(true) {
			if(($pos2 = strpos($str, "</$tag>", $pos2)) === false) {
				return $result . '<script type="text/javascript">//<![CDATA[' . CRLF
				    .'writeCode2("' . str_replace(array('"', "\r", "\n"), array('\"', '', "\\\r\n"), decorateSrcInObject(substr($html, $pos1))) . '")'.CRLF
				    .'//]]></script>';
			}
			$pos2 += strlen($tag) + 3;
			$chunk = substr($str, $pos1, $pos2 - $pos1);
			if(substr_count($chunk, "<$tag") == substr_count($chunk, "</$tag>"))
				break;
		}
		$result .= '<script type="text/javascript">//<![CDATA['. CRLF
		    .'writeCode2("' . str_replace(array('"', "\r", "\n"), array('\"', '', "\\\r\n"), decorateSrcInObject(substr($html, $pos1, $pos2 - $pos1))) . '")'.CRLF
		    .'//]]></script>';
	}
	return $result . substr($html, $pos2);
}

function str_tag_on($str) {
	$str = str_replace("&amp;", "&", $str);
	$str = str_replace("&lt;", "<", $str);
	return str_replace("&gt;", ">", $str);
}

function str_tag_off($str) {
	$str = str_replace("<", "&lt;", $str);
	return str_replace(">", "&gt;", $str);
}

function str_tag_truncate($str) {
	return strip_tags(preg_replace('/\[##(.+?)##\]/', '', $str));
}

function str_cut($str, $maxlen, $type) {
	$str = str_trans_rev($str);
	$len = strlen($str);
	if ($len <= $maxlen)
		return str_tag_off(str_trans($str));
	$return_str = "";
	for ($i = 0; $i < ($maxlen - 1); $i++) {
		if (ord(substr($str, $i, 1)) < 128) {
			$return_str .= substr($str, $i, 1);
		} else {
			$return_str .= substr($str, $i, 2);
			$i++;
		}
	}
	$str = str_tag_off(str_trans($str));
	$return_str = str_tag_off(str_trans($return_str));
	if ($type)
		return "<span title=\"$str\">{$return_str}..</span>";
	else
		return $return_str . "..";
}

function link_cut($url, $checkURL = 35)
{
	$leftURL	= $checkURL - 14;
	$rightURL	= -8;
	$link = (strlen($url) > $checkURL) ? substr($url, 0 , $leftURL).' &hellip; '.substr($url, $rightURL) : $url;
	return $link;
}
?>
