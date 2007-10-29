<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

// Initialize locale variable.
$__locale = array(
	'locale' => null,
	'directory' => './locale',
	'domain' => null,
	);

function _t_noop($t) {
	/* just for extracting by xgettext */
	return $t;
}

// Administration panel language resource.
// Text.
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

// Set timezone.
Timezone::set(isset($blog['timezone']) ? $blog['timezone'] : $service['timezone']);
DBQuery::query('SET time_zone = \'' . Timezone::getCanonical() . '\'');

// 관리자 화면 locale 불러오기.
// TODO : po지원하도록 변경해야 함.
Locale::setDirectory(ROOT . '/language');
Locale::set(isset($blog['language']) ? $blog['language'] : $service['language']);

// 스킨 화면 locale 불러오기.
if (!isset($blog['blogLanguage'])) {
	$blog['blogLanguage'] = $service['language'];
}
Locale::setSkinLocale(isset($blog['blogLanguage']) ? $blog['blogLanguage'] : $service['language']);

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
