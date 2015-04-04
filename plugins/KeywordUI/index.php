<?php
/* KeywordUI for Textcube 1.10.5
   -----------------------------
   Version 1.10.5
   Needlworks.

   Creator          : inureyes
   Maintainer       : inureyes

   Created at       : 2006.10.3
   Last modified at : 2015.3.4
 
 This plugin enables keyword / keylog feature in Textcube.
 For the detail, visit http://forum.tattersite.com/ko


 General Public License
 http://www.gnu.org/licenses/gpl.html

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
function KeywordUI_bindKeyword($target, $mother) {
    $context = Model_Context::getInstance();
    $target = "<a href=\"#\" class=\"key1\" onclick=\"openKeyword('" . $context->getProperty("uri.blog") . "/keylog/" . rawurlencode($target) . "'); return false\">{$target}</a>";

    return $target;
}

function KeywordUI_setSkin($target, $mother) {
    global $pluginPath;
    return $pluginPath . "/keylogSkin.html";
}

function KeywordUI_bindTag($target, $mother) {
    $context = Model_Context::getInstance();
    importlib('model.blog.keyword');
    $blogid = getBlogId();
    $blogURL = $context->getProperty("uri.blog");
    $pluginURL = $context->getProperty("plugin.uri");
    if (isset($mother) && isset($target)) {
        $tagsWithKeywords = array();
        $keywordNames = getKeywordNames($blogid);
        foreach ($target as $tag => $tagLink) {
            if (in_array($tag, $keywordNames) == true) {
                $tagsWithKeywords[$tag] = $tagLink . "<a href=\"#\" class=\"key1\" onclick=\"openKeyword('$blogURL/keylog/" . URL::encode($tag) . "'); return false\"><img src=\"" . $pluginURL . "/images/flag_green.gif\" alt=\"Keyword " . $tag . "\"/></a>";
            } else {
                $tagsWithKeywords[$tag] = $tagLink;
            }
        }
        $target = $tagsWithKeywords;
    }
    return $target;
}

function KeywordUI_handleConfig($data) {
    $config = Setting::fetchConfigVal($data);
    if ($config['useKeywordAsTag'] == true) {
        Setting::setBlogSettingGlobal('useKeywordAsTag', true);
    }
    if ($config['useKeywordAsCategory'] == true) {
        Setting::setBlogSettingGlobal('useKeywordAsCategory', true);
    }
    return true;
}

?>
