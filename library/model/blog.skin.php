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
		colorontree				= '{$setting['colorontree']}',
		bgcolorontree 			= '{$setting['bgcolorontree']}',
		activecolorontree		= '{$setting['activecolorontree']}',
		activebgcolorontree 	= '{$setting['activebgcolorontree']}',
		labellengthontree 		= {$setting['labellengthontree']},
		showvalueontree 		= " . (empty($setting['showvalueontree']) ? 0 : 1) . "
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
			array_push($assignments, "entriesonrecent=$value");
		$value = $xmls->getValue('/skin/default/recentComments');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "commentsonrecent=$value");
		$value = $xmls->getValue('/skin/default/itemsOnGuestbook');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "commentsonguestbook=$value");
		$value = $xmls->getValue('/skin/default/tagsInCloud');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "tagsontagbox=$value");
		$value = $xmls->getValue('/skin/default/sortInCloud');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "tagboxalign=$value");
		$value = $xmls->getValue('/skin/default/recentTrackbacks');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "trackbacksonrecent=$value");
		$value = $xmls->getValue('/skin/default/expandcomment');
		if (isset($value))
			array_push($assignments, 'expandcomment=' . ($value ? '1' : '0'));
		$value = $xmls->getValue('/skin/default/expandtrackback');
		if (isset($value))
			array_push($assignments, 'expandtrackback=' . ($value ? '1' : '0'));
		$value = $xmls->getValue('/skin/default/lengthOfRecentNotice');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentnoticelength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfRecentEntry');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recententrylength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfRecentComment');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recentcommentlength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfRecentTrackback');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "recenttrackbacklength=$value");
		$value = $xmls->getValue('/skin/default/lengthOfLink');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "linklength=$value");
		$value = $xmls->getValue('/skin/default/showlistoncategory');
		if (isset($value))
			array_push($assignments, "showlistoncategory=$value");
		$value = $xmls->getValue('/skin/default/showlistonarchive');
		if (isset($value))
			array_push($assignments, "showlistonarchive=$value");
		$value = $xmls->getValue('/skin/default/showlistontag');
		if (isset($value))
			array_push($assignments, "showlistontag=$value");
		$value = $xmls->getValue('/skin/default/showlistonsearch');
		if (isset($value))
			array_push($assignments, "showlistonsearch=$value");
		$value = $xmls->getValue('/skin/default/tree/color');
		if (isset($value))
			array_push($assignments, "colorontree='$value'");
		$value = $xmls->getValue('/skin/default/tree/bgColor');
		if (isset($value))
			array_push($assignments, "bgcolorontree='$value'");
		$value = $xmls->getValue('/skin/default/tree/activeColor');
		if (isset($value))
			array_push($assignments, "activecolorontree='$value'");
		$value = $xmls->getValue('/skin/default/tree/activeBgColor');
		if (isset($value))
			array_push($assignments, "activebgcolorontree='$value'");
		$value = $xmls->getValue('/skin/default/tree/labelLength');
		if (!empty($value) || is_numeric($value))
			array_push($assignments, "labellengthontree=$value");
		$value = $xmls->getValue('/skin/default/tree/showValue');
		if (isset($value))
			array_push($assignments, 'showvalueontree=' . ($value ? '1' : '0'));
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
		$setting[$key] = POD::escapeString($value);
	}
	$sql = "
	UPDATE {$database['prefix']}SkinSettings 
	SET 
		skin 					= \"" . $skinSetting['skin'] . "\",
		entriesonrecent			= " . $setting['entriesonrecent'] . ',
		commentsonrecent			= ' . $setting['commentsonrecent'] . ',
		commentsonguestbook		= ' . $setting['commentsonguestbook'] . ',
		archivesonpage	 		= ' . $setting['archivesonpage'] . ',
		tagsontagbox			= ' . $setting['tagsontagbox'] . ',
		tagboxalign				= ' . $setting['tagboxalign'] . ',
		trackbacksonrecent		= ' . $setting['trackbacksonrecent'] . ',
		showlistoncategory		= ' . $setting['showlistoncategory'] . ',
		showlistonarchive		= ' . $setting['showlistonarchive'] . ',
		showlistontag			= ' . $setting['showlistontag'] . ',
		showlistonauthor			= ' . $setting['showlistonauthor'] . ',
		showlistonsearch			= ' . $setting['showlistonsearch'] . ',
		expandcomment				= ' . $setting['expandcomment'] . ',
		expandtrackback			= ' . $setting['expandtrackback'] . ',
		recentnoticelength 		= ' . $setting['recentnoticelength'] . ',
		recententrylength 		= ' . $setting['recententrylength'] . ',
		recentcommentlength 		= ' . $setting['recentcommentlength'] . ',
		recenttrackbacklength 	= ' . $setting['recenttrackbacklength'] . ',
		linklength 				= ' . $setting['linklength'] . '
	WHERE blogid =' . $blogid;
	if (!POD::execute($sql)) {
		return false;
	}
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
