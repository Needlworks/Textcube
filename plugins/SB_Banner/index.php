<?php
/* Sidebar Banner example plugin for Textcube 1.1
   ----------------------------------
   Version 1.0
   Tatter and Friends development team.

   Creator          : gendoh
   Maintainer       : gendoh

   Created at       : 2006.9.3
   Last modified at : 2006.11.11

 This plugin is an example for adding banners into sidebar.
 For the detail, visit http://forum.tattersite.com/ko

 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function SB_Banner_withImage($parameters)
{
	if (isset($parameters['preview'])) {
		// preview mode
		$retval = '<a href="refsrc"><img src="imgsrc"/></a>';
		return htmlspecialchars($retval);
	}
	if (!isset($parameters['imgsrc']) || !isset($parameters['href'])) return '';
	$imgsrc = $parameters['imgsrc'];
	$refsrc = $parameters['href'];
	
	$retVal = '<a href="' . $refsrc . '" ><img src="' . $imgsrc . '" /></a>';
	
	return $retVal;
}

function SB_Banner_withCode($parameters)
{
	if (isset($parameters['preview'])) {
		// preview mode
		$retval = '<html codes~>';
		return htmlspecialchars($retval);
	}
	if (!isset($parameters['text'])) return '';
	$text = $parameters['text'];
	return $text;
}

?>
