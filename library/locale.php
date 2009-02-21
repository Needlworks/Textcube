<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function _t_noop($t) {
	/* just for extracting by xgettext */
	return $t;
}

// Administration panel language resource.
// Translate text only.
function _t($t) {
	global $__locale, $__text;
	if (isset($__locale['domain']) && isset($__text[$__locale['domain']][$t]))
		return $__text[$__locale['domain']][$t];
	else if (isset($__text[$t]))
		return $__text[$t];
	return $t;
}

// Text with parameters.
function _f($t) {
	$t = _t($t);
	if (func_num_args() <= 1)
		return $t;
	for ($i = 1; $i < func_num_args(); $i++) {
		$arg = func_get_arg($i);
		$t = str_replace('%' . $i, $arg, $t);
	}
	return $t;
}

// 외부 스킨용 언어 변환 함수.
// _t()는 관리자 언어설정에 따르지만, _text()는 skin의 언어설정(메타정보)을 따른다. 1.1 버전의 추가사항임.

function _text($t) {
	global $__skinText;
	if (isset($__skinText) && isset($__skinText[$t])) {
		return $__skinText[$t];
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