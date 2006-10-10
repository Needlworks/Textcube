<?php

function SB_Replacer($parameters)
{
	if (isset($parameters['preview'])) {
		// preview mode
		$retval = '[## tags ##]';
		if (isset($parameters['text'])) $retval = $parameters['text'];
		return htmlspecialchars($retval);
	}
	if (!isset($parameters['text'])) return '';
	$text = $parameters['text'];
	
	handleTags($text);
	return $text;
}

?>