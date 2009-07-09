<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function setTreeSetting($blogid, $setting) {
	global $database;
	requireLibrary('blog.skin');
	foreach ($setting as $key => $value)
		$setting[$key] = POD::escapeString($value);
	$sql = "
	UPDATE {$database['prefix']}SkinSettings
	SET 
		tree 					= '{$setting['tree']}',
		colorOnTree				= '{$setting['colorOnTree']}',
		bgcolorOnTree 			= '{$setting['bgcolorOnTree']}',
		activecolorOnTree		= '{$setting['activecolorOnTree']}',
		activebgcolorOnTree 	= '{$setting['activebgcolorOnTree']}',
		labelLengthOnTree 		= {$setting['labelLengthOnTree']},
		showValueOnTree 		= " . (empty($setting['showValueOnTree']) ? 0 : 1) . "
	WHERE blogid = $blogid";
	if (POD::execute($sql)) {
		Skin::purgeCache();
		getSkinSetting($blogid, true); // refresh skin cache
		return true;
	} else {
		Respond::ErrorPage(POD::error());
	}
}

function reloadSkin($blogid)
{
	global $database, $service;
	$skinSetting = getSkinSetting($blogid);
	$skinName = $skinSetting['skin'];
	if (file_exists(ROOT . "/skin/$skinName/index.xml")) {
		$xml = file_get_contents(ROOT . "/skin/$skinName/index.xml");
		$xmls = new XMLStruct();
		if (!$xmls->open($xml, $service['encoding']))
			return;
		$value = $xmls->getValue('/skin/default/commentMessage/none'); 
		if (is_null($value)) 
			setBlogSetting('noneCommentMessage', NULL);
		else
			setBlogSetting('noneCommentMessage', $value);
		$value = $xmls->getValue('/skin/default/commentMessage/single'); 
		if (is_null($value))
			setBlogSetting('singleCommentMessage', NULL);
		else
			setBlogSetting('singleCommentMessage', $value);
		$value = $xmls->getValue('/skin/default/trackbackMessage/none'); 
		if (is_null($value))
			setBlogSetting('noneTrackbackMessage', NULL);
		else
			setBlogSetting('noneTrackbackMessage', $value);
		$value = $xmls->getValue('/skin/default/trackbackMessage/single'); 
		if (is_null($value))
			setBlogSetting('singleTrackbackMessage', NULL);
		else
			setBlogSetting('singleTrackbackMessage', $value);
	}
}

function selectSkin($blogid, $skinName) {
	global $database, $service;
	requireComponent('Needlworks.Cache.PageCache');
	requireLibrary('blog.skin');
	$blogid = getBlogId();
	if (empty($skinName))
		return _t('실패했습니다.');
		
	if (strncmp($skinName, 'customize/', 10) == 0) {
		if (strcmp($skinName, "customize/$blogid") != 0)
			return _t('실패 했습니다');
	} else {
		$skinName = Path::getBaseName($skinName);
		if (($skinName === '.') || ($skinName ==='..'))
			return _t('실패 했습니다');
	}
		
	if (file_exists(ROOT . "/skin/$skinName/index.xml")) {
		$xml = file_get_contents(ROOT . "/skin/$skinName/index.xml");
		$xmls = new XMLStruct();
		if (!$xmls->open($xml, $service['encoding']))
			return _t('실패했습니다.');
		$assignments = array("skin='$skinName'");
		$value = $xmls->getValue('/skin/default/recentEntries');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "entriesOnRecent=$value");
		$value = $xmls->getValue('/skin/default/recentComments');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "commentsOnRecent=$value");
		$value = $xmls->getValue('/skin/default/itemsOnGuestbook');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "commentsOnGuestbook=$value");
		$value = $xmls->getValue('/skin/default/tagsInCloud');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "tagsOnTagbox=$value");
		$value = $xmls->getValue('/skin/default/sortInCloud');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "tagboxAlign=$value");
		$value = $xmls->getValue('/skin/default/recentTrackbacks');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "trackbacksOnRecent=$value");
		$value = $xmls->getValue('/skin/default/expandComment');
		if (isset($value))
			array_push($assignments, 'expandComment=' . ($value ? '1' : '0'));
		$value = $xmls->getValue('/skin/default/expandTrackback');
		if (isset($value))
			array_push($assignments, 'expandTrackback=' . ($value ? '1' : '0'));
		$value = $xmls->getValue('/skin/default/lengthOfRecentNotice');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentNoticeLength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfRecentEntry');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentEntryLength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfRecentComment');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentCommentLength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfRecentTrackback');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentTrackbackLength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfLink');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "linkLength=$value");
		$value = $xmls->getValue('/skin/default/showListOnCategory');
		if (isset($value))
			array_push($assignments, "showListOnCategory=$value");
		$value = $xmls->getValue('/skin/default/showListOnArchive');
		if (isset($value))
			array_push($assignments, "showListOnArchive=$value");
		$value = $xmls->getValue('/skin/default/showListOnTag');
		if (isset($value))
			array_push($assignments, "showListOnTag=$value");
		$value = $xmls->getValue('/skin/default/showListOnSearch');
		if (isset($value))
			array_push($assignments, "showListOnSearch=$value");
		$value = $xmls->getValue('/skin/default/tree/color');
		if (isset($value))
			array_push($assignments, "colorOnTree='$value'");
		$value = $xmls->getValue('/skin/default/tree/bgColor');
		if (isset($value))
			array_push($assignments, "bgcolorOnTree='$value'");
		$value = $xmls->getValue('/skin/default/tree/activeColor');
		if (isset($value))
			array_push($assignments, "activecolorOnTree='$value'");
		$value = $xmls->getValue('/skin/default/tree/activeBgColor');
		if (isset($value))
			array_push($assignments, "activebgcolorOnTree='$value'");
		$value = $xmls->getValue('/skin/default/tree/labelLength');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "labelLengthOnTree=$value");
		$value = $xmls->getValue('/skin/default/tree/showValue');
		if (isset($value))
			array_push($assignments, 'showValueOnTree=' . ($value ? '1' : '0'));
		$sql = "UPDATE {$database['prefix']}SkinSettings SET " . implode(',', $assignments) . " WHERE blogid = $blogid";
		
		// none/single/multiple
		$value = $xmls->getValue('/skin/default/commentMessage/none'); 
		if (is_null($value)) 
			setBlogSetting('noneCommentMessage', NULL);
		else
			setBlogSetting('noneCommentMessage', $value);
		$value = $xmls->getValue('/skin/default/commentMessage/single'); 
		if (is_null($value))
			setBlogSetting('singleCommentMessage', NULL);
		else
			setBlogSetting('singleCommentMessage', $value);
		$value = $xmls->getValue('/skin/default/trackbackMessage/none'); 
		if (is_null($value))
			setBlogSetting('noneTrackbackMessage', NULL);
		else
			setBlogSetting('noneTrackbackMessage', $value);
		$value = $xmls->getValue('/skin/default/trackbackMessage/single'); 
		if (is_null($value))
			setBlogSetting('singleTrackbackMessage', NULL);
		else
			setBlogSetting('singleTrackbackMessage', $value);
	} else {
		setBlogSetting('noneCommentMessage', NULL);
		setBlogSetting('singleCommentMessage', NULL);
		setBlogSetting('noneTrackbackMessage', NULL);
		setBlogSetting('singleTrackbackMessage', NULL);
		$sql = "UPDATE {$database['prefix']}SkinSettings SET skin='{$skinName}' WHERE blogid = $blogid";
	}
	$result = POD::query($sql);
	if (!$result) {
		return _t('실패했습니다.');
	}
	
	removeBlogSetting("sidebarOrder");
	CacheControl::flushAll();
	Skin::purgeCache();
	Path::removeFiles(ROOT . "/skin/customize/".getBlogId()."/");
	getSkinSetting($blogid, true); // refresh skin cache
	return true;
}

function writeSkinHtml($blogid, $contents, $mode, $file) {
	global $database;
	global $skinSetting;
	requireComponent('Needlworks.Cache.PageCache');
	requireLibrary('blog.skin');
	if ($mode != 'skin' && $mode != 'skin_keyword' && $mode != 'style')
		return _t('실패했습니다.');
	if ($skinSetting['skin'] != "customize/$blogid") {
		if (!@file_exists(ROOT . "/skin/blog/customize/$blogid")) {
			if (!@mkdir(ROOT . "/skin/blog/customize/$blogid"))
				return _t('권한이 없습니다.');
			@chmod(ROOT . "/skin/blog/customize/$blogid", 0777);
		}
		deltree(ROOT . "/skin/blog/customize/$blogid");
		copyRecusive(ROOT . "/skin/blog/{$skinSetting['skin']}", ROOT . "/skin/blog/customize/$blogid");
	}
	$skinSetting['skin'] = "customize/$blogid";
	$sql = "UPDATE {$database['prefix']}SkinSettings SET skin = '{$skinSetting['skin']}' WHERE blogid = $blogid";
	$result = POD::query($sql);
	if (!$result)
		return _t('실패했습니다.');
	//if ($mode == 'style')
	//	$file = $mode . '.css';
	//else
	//	$file = $mode . '.html';
	if (!is_writable(ROOT . "/skin/blog/customize/$blogid/$file"))
		return ROOT . _t('권한이 없습니다.') . " -> /skin/blog/customize/$blogid/$file";
	$handler = fopen(ROOT . "/skin/blog/customize/$blogid/$file", 'w');
	if (fwrite($handler, $contents) === false) {
		fclose($handler);
		return _t('실패했습니다.');
	} else {
		fclose($handler);
		@chmod(ROOT . "/skin/blog/customize/$blogid/$file", 0666);
		CacheControl::flushAll();
		Skin::purgeCache();
		return true;
	}
}

function getCSSContent($blogid, $file) {
	global $skinSetting;
	return @file_get_contents(ROOT . "/skin/blog/{$skinSetting['skin']}/$file");
}

function setSkinSetting($blogid, $setting) {
	global $database;
	global $skinSetting;
	
	requireLibrary('blog.skin');
	$blogid = getBlogId();
	if (strncmp($skinSetting['skin'], 'customize/', 10) == 0) {
		if (strcmp($skinSetting['skin'], "customize/$blogid") != 0)
			return false;
	} else {
		$skinSetting['skin'] = Path::getBaseName($skinSetting['skin']);
		if (($skinSetting['skin'] === '.') || ($skinSetting['skin'] ==='..'))
			return _t('실패 했습니다');
	}
	
	$skinpath = ROOT . '/skin/blog/' . $skinSetting['skin'];
	if (!is_dir($skinpath))
		return _t('실패 했습니다');

	foreach ($setting as $key => $value) {
		Setting::setSkinSetting($key, $value, $blogid);
	}
	Setting::setSkinSetting('skin', $skinSetting['skin'], $blogid);
	Setting::setBlogSetting('useMicroformat',$setting['useMicroformat'],true);
	Setting::setBlogSetting('useFOAF',(($setting['useFOAF'] == 1) ? 1: 0),true);
	Setting::setBlogSetting('entriesOnPage',$setting['entriesOnPage'],true);
	Setting::setBlogSetting('entriesOnList',$setting['entriesOnList'],true);
	CacheControl::flushCategory();
	CacheControl::flushTag();
	Skin::purgeCache();
	getSkinSetting($blogid, true); // refresh skin cache
	return true;
}
?>
