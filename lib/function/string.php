<?

function utf8Lessen($str, $length, $tail = '..') {
	$l = strlen($str);
	for ($i = $n = 0; $i < $l; $i++, $n++) {
		if ($n >= $length)
			return substr($str, 0, $i) . $tail;
		$c = ord($str{$i});
		if (($c & 0xF0) == 0xF0)
			$i += 3;
		else if (($c & 0xE0) == 0xE0)
			$i += 2;
		else if (($c & 0xC0) == 0xC0)
			$i += 1;
	}
	return $str;
}

function isUTF8($str) {
	$length = strlen($str);
	for ($i = 0; $i < $length; $i++) {
		$high = ord($str{$i});
		if (($high == 0xC0) || ($high == 0xC1)) {
			return false;
		} else if ($high < 0x80) {
			continue;
		} else if ($high < 0xC0) {
			return false;
		} else if ($high < 0xE0) {
			if (++$i >= $length)
				return true;
			else if (($str{$i} & "\xC0") == "\x80")
				continue;
		} else if ($high < 0xF0) {
			if (++$i >= $length) {
				return true;
			} else if (($str{$i} & "\xC0") == "\x80") {
				if (++$i >= $length)
					return true;
				else if (($str{$i} & "\xC0") == "\x80")
					continue;
			}
		} else if ($high < 0xF5) {
			if (++$i >= $length) {
				return true;
			} else if (($str{$i} & "\xC0") == "\x80") {
				if (++$i >= $length) {
					return true;
				} else if (($str{$i} & "\xC0") == "\x80") {
					if (++$i >= $length)
						return true;
					else if (($str{$i} & "\xC0") == "\x80")
						continue;
				}
			}
		}
		return false;
	}
	return true;
}

function iconvWrapper($from, $to, $str) {
	if (function_exists('iconv'))
		return @iconv($from, $to, $str);
	else if (function_exists('mb_convert_encoding'))
		return @mb_convert_encoding($str, $to, $from);
	else {
		include_once (ROOT . '/iconv.php');
		if (function_exists('iconv'))
			return @iconv($from, $to, $str);
		else
			return false;
	}
}

function str_trans($str) {
	return str_replace("'", "&#39;", str_replace("\"", "&quot;", $str));
}

function str_trans_rev($str) {
	return str_replace("&#39;", "'", str_replace("&quot;", "\"", $str));
}
?>