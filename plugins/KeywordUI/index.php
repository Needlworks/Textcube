<?php
/* KeywordUI for Textcube 1.5
   ----------------------------------
   Version 1.5
   Needlworks.

   Creator          : inureyes
   Maintainer       : inureyes

   Created at       : 2006.10.3
   Last modified at : 2007.5.25
 
 This plugin enables keyword / keylog feature in Textcube.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function KeywordUI_bindKeyword($target,$mother) {
	global $blogURL;
	$target = "<a href=\"#\" class=\"key1\" onclick=\"openKeyword('$blogURL/keylog/" . rawurlencode($target) . "')\">{$target}</a>";

	return $target;
}

function KeywordUI_setSkin($target,$mother) {
	global $pluginPath;
	return $pluginPath."/keylogSkin.html";
}
function KeywordUI_bindTag($target,$mother) {
	global $blogURL, $pluginURL, $configVal;
	requireModel('blog.keyword');
	$blogid = getBlogId();
	if(isset($mother) && isset($target)){
		$tagsWithKeywords = array();
		$keywordNames = getKeywordNames($blogid);
		foreach($target as $tag => $tagLink) {
			if(array_search($tag,$keywordNames) !== false)
				$tagsWithKeywords[$tag] = $tagLink."<a href=\"#\" class=\"key1\" onclick=\"openKeyword('$blogURL/keylog/".encodeURL($tag)."')\"><img src=\"".$pluginURL."/images/flag_green.gif\" alt=\"Keyword ".$tag."\"/></a>";
			else $tagsWithKeywords[$tag] = $tagLink;
		}
		$target = $tagsWithKeywords;
	}
	return $target;
}

function KeywordUI_handleConfig($data){
	requireComponent('Textcube.Function.misc');
	$config = misc::fetchConfigVal($data);
	if($config['useKeywordAsTag'] == true) misc::setBlogSettingGlobal('useKeywordAsTag',true);
	return true;
}

?>
