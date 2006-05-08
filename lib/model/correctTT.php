<?php

function correctTTForXmlText($text) {
	return str_replace('&quot;', '"', str_replace('&#39;', '\'', $text));
}
?>
