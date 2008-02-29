<?php
/* Spacer plugin for Textcube 1.1
   ----------------------------------
   Version 1.0
   Tatter and Friends development team.

   Creator          : inureyes
   Maintainer       : inureyes

   Created at       : 2006.11.1
   Last modified at : 2006.11.2
 
 This plugin adds space on the sidebar.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function SB_Spacer($target)
{
	global $configVal;
	requireComponent('Textcube.Function.misc');
	$data = setting::fetchConfigVal($configVal);
	if(!is_null($data) && array_key_exists('height', $data)){
		$height = $data['height'];
	} else {
		$height = '20';
	}
	$text = '<div class="SB_Spacer" style="height:'.$height.'px;"></div>';
	return $text;
}

function SB_Spacer_DataSet($data){
	requireComponent('Textcube.Function.misc');
	if(!is_integer(intval($data['height']))) return false;
	$cfg = setting::fetchConfigVal($data);
	return true;
}
?>
