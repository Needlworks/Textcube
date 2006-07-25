<?php

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
	$text = preg_replace('/&nbsp;?/', ' ', $text);
	$text = trim(preg_replace('/\s+/', ' ', $text));
	if(!empty($text)) {
		$text = preg_replace('/&apos;?/', '\'', $text);
		$text = html_entity_decode($text, ENT_QUOTES);
	}
	return $text;
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

function addLinkSense($text, $attributes = '') {
	$text = eregi_replace("http://", "temporaryHttp://", $text);
	
	while (eregi("temporaryHttp://", $text)) {
		eregi("([href=\"'\t\n\r]*)temporaryHttp://([^ \t\r\n\"']+)", $text, $temp);
		if (eregi("href=", $temp[0])) {
			$text = str_replace($temp[0], str_replace("temporaryHttp://", "http://", $temp[0]), $text);
		} else {
			if (eregi("\?", $temp[0])) {
				$text = str_replace($temp[0], "$temp[1]<a href=\"http://$temp[2]\"$attributes rel=\"external nofollow\">$temp[2]</a>", $text);
			} else {
				eregi("([^a-z0-9]*)$", $temp[2], $temp2);
				if (!empty($temp2[1])) {
					$temp[2] = eregi_replace("([^a-z0-9]*)$", "", $temp[2]);
				}
				$text = str_replace($temp[0], "$temp[1]<a href=\"http://$temp[2]\"$attributes rel=\"external nofollow\">$temp[2]</a>$temp2[1]", $text);
			}
		}
	}
	return $text;
}

function addProtocolSense($url, $protocol = 'http://') {
	return ereg('^[[:alnum:]]+:', $url) ? $url : $protocol . $url;
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
	return strip_tags(ereg_replace('\[##.+##\]', '', $str));
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
		return "<span title=\"$str\">{$return_str}...</span>";
	else
		return $return_str . "..";
}

function link_cut($url, $checkURL = 35)
{
	$leftURL	= $checkURL - 14;
	$rightURL	= -8;
	$link = ($link == '' || $link == $url) ? ((strlen($url) > $checkURL) ? substr($url, 0 , $leftURL).' &hellip; '.substr($url, $rightURL) : $url) : stripslashes($link);
	return $link;
}
?>
