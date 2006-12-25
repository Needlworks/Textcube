<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
Timezone::set(isset($blog['timezone']) ? $blog['timezone'] : $service['timezone']);
DBQuery::query('SET time_zone = \'' . Timezone::getCanonical() . '\'');
Locale::setDirectory(ROOT . '/language');
Locale::set(isset($blog['language']) ? $blog['language'] : $service['language']);

if (!isset($blog['blogLanguage'])) {
	$blog['blogLanguage'] = $service['language'];
}

if (is_file($__locale['directory'] . '/' . $blog['blogLanguage'] . ".php")) {
	$__outText = getOutLanguage($__locale['directory'] . '/' . $blog['blogLanguage'] . ".php");
}

function getOutLanguage($languageFile) {
	include($languageFile);
	return $__text;
}

// 외부 스킨용 언어 변환 함수.
// _t()는 관리자 언어설정에 따르지만, _text()는 skin의 언어설정(메타정보)을 따른다. 1.1 버전의 추가사항임.
function _text($t) {
	global $__outText;
	
	if (isset($__outText) && isset($__outText[$t])) {
		return $__outText[$t];
	} else {
		return $t;
	}
}

function _textf($t) {
	$t = _text($t);
	if (func_num_args() <= 1)
		return $t;
	for ($i = 1; $i < func_num_args(); $i++) {
		$arg = func_get_arg($i);
		$t = str_replace('%' . $i, $arg, $t);
	}
	return $t;
}

?>
