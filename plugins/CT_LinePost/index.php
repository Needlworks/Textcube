<?php
/**
 * line Post for Textcube 2.0
 * ----------------------------------
 * Version 2.0
 * By Jeongkyu Shin
 *
 * Created at       : 2009.06.04
 * Last modified at : 2015.02.01
 *  
 * This plugin makes you to post line.
 * For the detail, visit http://forest.nubimaru.com
 * 
 * General Public License
 * http://www.gnu.org/licenses/gpl.html
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
*/

/// Posting widget for center.
function linePost_widget($target) {
	$context = Model_Context::getInstance();
	$blogURL = $context->getProperty('uri.blog');
    $pluginURL = $context->getProperty('plugin.uri');
	$public = _t('공개');
	$private = _t('비공개');
	$write = _t('쓰기');
	$view  = <<<EOS
	<script type="text/javascript" src="{$blogURL}/plugin/linePostWidget.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="{$pluginURL}/widget.css" />
	<div id="linePost_widget">
		<input type="radio" id="linePost_public" class="radio" name="category" value="2" checked="checked" />
		<label for="linePost_public">{$public}</label>
		<input type="radio" id="linePost_private" class="radio" name="category" value="1"  />
		<label for="linePost_private">{$private}</label>
		<textarea id="linePost_widget_textarea" maxlength="150" onkeypress="if (event.keyCode == 13) { return false; }"></textarea><br />	
		<input id="linePost_widget_button" type="button" class="input-button" value="{$write}" onclick="linePost_save();return false;"/>
	</div>
EOS;
	
	return $view;
}

/// Dynamic JavaScript for Center Widget.
function linePost_widget_Javascript($target) {
	$context = Model_Context::getInstance();
	$blogURL = $context->getProperty('uri.blog');
	$view = <<< EOS
/// Copyright (C) Jeongkyu Shin. / Needlworks
/// Line Post Widget for Textcube
//<![CDATA[
function linePost_getData() {
	var content = trim(document.getElementById("linePost_widget_textarea").value);
	if(content == "") return null;
	return ("content=" + content +
			"&mode=ajax");
}
function linePost_save() {
	/// Set default values.
	var data = linePost_getData();
	if(data == null) return false;
	var request = new HTTPRequest("POST", "{$blogURL}/line/");
	request.onSuccess = function () {
		PM.removeRequest(this);
		PM.showMessage(_t("저장되었습니다"), "center", "bottom");
		document.getElementById("linePost_widget_textarea").value = "";
	}
	request.onError = function() {
		PM.removeRequest(this);
		PM.showErrorMessage(_t("저장하지 못했습니다"), "center", "bottom");
	}
	PM.addRequest(request, _t("저장하고 있습니다"));
	request.send(data);
	return true;
}
//]]>	
EOS;
	header('Content-Type: text/javascript; charset=UTF-8');
	echo $view;
}
?>