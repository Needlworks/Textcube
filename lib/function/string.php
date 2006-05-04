<?

function str_trans($str) {
	return str_replace("'", "&#39;", str_replace("\"", "&quot;", $str));
}

function str_trans_rev($str) {
	return str_replace("&#39;", "'", str_replace("&quot;", "\"", $str));
}
?>