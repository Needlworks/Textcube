<?php

function encodeMail($str) {
	return '=?utf-8?b?' . base64_encode($str) . '?=';
}
?>
