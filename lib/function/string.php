<?php

function str_trans($str) {
	return str_replace("'", "&#39;", str_replace("\"", "&quot;", $str));
}

function str_trans_rev($str) {
	return str_replace("&#39;", "'", str_replace("&quot;", "\"", $str));
}

// 외부 스킨용 언어 변환 함수.
// _t()는 관리자 언어설정에 따르지만, _text()는 skin의 언어설정(메타정보)을 따른다. 1.1 버전의 추가사항임.
function _text($t) {
	global $skinLanguage;
	
	return $t;
}
?>
