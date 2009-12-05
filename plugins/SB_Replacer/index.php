<?php
/* Sidebar replacer patch example plugin for Textcube 1.1
   ----------------------------------
   Version 1.1
   Tatter and Friends development team.

   Creator          : gendoh
   Maintainer       : gendoh

   Created at       : 2006.9.3
   Last modified at : 2009.10.28

 This plugin is an example of replacer patch for sidebar.
 For the detail, visit http://forum.tattersite.com/ko

 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
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
