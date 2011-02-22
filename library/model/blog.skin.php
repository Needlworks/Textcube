<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function setTreeSetting($blogid, $setting) {
	requireLibrary('blog.skin');
	
	if(empty($setting['showValueOnTree'])) $setting['showValueOnTree'] = 0;
	else $setting['showValueOnTree'] = 1;
	foreach ($setting as $key => $value) {
		Setting::setSkinSetting($key, $value, $blogid); 
	}
	CacheControl::flushSkin();
	Setting::getSkinSettings($blogid, true); // refresh skin cache
	return true;
}

function reloadSkin($blogid)
{
	$context = Model_Context::getInstance();
	$skinName = $context->getProperty('skin.skin');
	if (file_exists(ROOT . "/skin/$skinName/index.xml")) {
		$xml = file_get_contents(ROOT . "/skin/$skinName/index.xml");
		$xmls = new XMLStruct();
		if (!$xmls->open($xml, $context->getProperty('service.encoding')))
			return;
		$value = $xmls->getValue('/skin/default/commentMessage/none'); 
		if (is_null($value)) 
			Setting::setBlogSetting('noneCommentMessage', NULL, true);
		else
			Setting::setBlogSetting('noneCommentMessage', $value, true);

		$value = $xmls->getValue('/skin/default/commentMessage/single'); 
		if (is_null($value))
			Setting::setBlogSetting('singleCommentMessage', NULL, true);
		else
			Setting::setBlogSetting('singleCommentMessage', $value, true);

		$value = $xmls->getValue('/skin/default/trackbackMessage/none'); 
		if (is_null($value))
			Setting::setBlogSetting('noneTrackbackMessage', NULL, true);
		else
			Setting::setBlogSetting('noneTrackbackMessage', $value, true);

		$value = $xmls->getValue('/skin/default/trackbackMessage/single'); 
		if (is_null($value))
			Setting::setBlogSetting('singleTrackbackMessage', NULL, true);
		else
			Setting::setBlogSetting('singleTrackbackMessage', $value, true);
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
		
	if (file_exists(ROOT . "/skin/blog/$skinName/index.xml")) {
		$xml = file_get_contents(ROOT . "/skin/blog/$skinName/index.xml");
		$xmls = new XMLStruct();
		if (!$xmls->open($xml, $service['encoding']))
			return _t('실패했습니다.');
		$assignments = array('skin'=>$skinName);
		$value = $xmls->getValue('/skin/default/recentEntries');
		if (!empty($value) || is_numeric($value))
			$assignments['entriesOnRecent'] = $value;
		
		$value = $xmls->getValue('/skin/default/recentComments');
		if (!empty($value) || is_numeric($value))
			$assignments['commentsOnRecent'] = $value;
		
		$value = $xmls->getValue('/skin/default/itemsOnGuestbook');
		if (!empty($value) || is_numeric($value))
			$assignments['commentsOnGuestbook'] = $value;
		
		$value = $xmls->getValue('/skin/default/tagsInCloud');
		if (!empty($value) || is_numeric($value))
			$assignments['tagsOnTagbox'] = $value;
		
		$value = $xmls->getValue('/skin/default/sortInCloud');
		if (!empty($value) || is_numeric($value))
			$assignments['tagboxAlign'] = $value;
		
		$value = $xmls->getValue('/skin/default/recentTrackbacks');
		if (!empty($value) || is_numeric($value))
			$assignments['trackbacksOnRecent'] = $value;
		
		$value = $xmls->getValue('/skin/default/expandComment');
		if (isset($value))
			$assignments['expandComment'] = ($value ? '1' : '0');
		
		$value = $xmls->getValue('/skin/default/expandTrackback');
		if (isset($value))
			$assignments['expandTrackback'] = ($value ? '1' : '0');
		
		$value = $xmls->getValue('/skin/default/lengthOfRecentNotice');
		if (!empty($value) || is_numeric($value))
			$assignments['recentNoticeLength'] = $value;
		
		$value = $xmls->getValue('/skin/default/lengthOfRecentEntry');
		if (!empty($value) || is_numeric($value))
			$assignments['recentEntryLength'] = $value;
		
		$value = $xmls->getValue('/skin/default/lengthOfRecentComment');
		if (!empty($value) || is_numeric($value))
			$assignments['recentCommentLength'] = $value;

		$value = $xmls->getValue('/skin/default/lengthOfRecentTrackback');
		if (!empty($value) || is_numeric($value))
			$assignments['recentTrackbackLength'] = $value;

		$value = $xmls->getValue('/skin/default/lengthOfLink');
		if (!empty($value) || is_numeric($value))
			$assignments['linkLength'] = $value;

		$value = $xmls->getValue('/skin/default/contentWidth');
		if (!empty($value) || is_numeric($value))
			$assignments['contentWidth'] = $value;

		$value = $xmls->getValue('/skin/default/showListOnCategory');
		if (isset($value))
			$assignments['showListOnCategory'] = $value;

		$value = $xmls->getValue('/skin/default/showListOnArchive');
		if (isset($value))
			$assignments['showListOnArchive'] = $value;

		$value = $xmls->getValue('/skin/default/showListOnTag');
		if (isset($value))
			$assignments['showListOnTag'] = $value;

		$value = $xmls->getValue('/skin/default/showListOnSearch');
		if (isset($value))
			$assignments['showListOnSearch'] = $value;

		$value = $xmls->getValue('/skin/default/showListOnAuthor');
		if (isset($value))
			$assignments['showListOnAuthor'] = $value;

		$value = $xmls->getValue('/skin/default/tree/color');
		if (isset($value))
			$assignments['colorOnTree'] = $value;

		$value = $xmls->getValue('/skin/default/tree/bgColor');
		if (isset($value))
			$assignments['bgcolorOnTree'] = $value;

		$value = $xmls->getValue('/skin/default/tree/activeColor');
		if (isset($value))
			$assignments['activecolorOnTree'] = $value;

		$value = $xmls->getValue('/skin/default/tree/activeBgColor');
		if (isset($value))
			$assignments['activebgcolorOnTree'] = $value;

		$value = $xmls->getValue('/skin/default/tree/labelLength');
		if (!empty($value) || is_numeric($value))
			$assignments['labelLengthOnTree'] = $value;

		$value = $xmls->getValue('/skin/default/tree/showValue');
		if (isset($value))
			$assignments['showValueOnTree'] = ($value ? '1' : '0');

		foreach($assignments as $name => $value) {
			Setting::setSkinSetting($name, $value, $blogid);
		}
		
		// none/single/multiple
		$value = $xmls->getValue('/skin/default/commentMessage/none'); 
		if (is_null($value)) 
			Setting::setBlogSetting('noneCommentMessage', NULL, true);
		else
			Setting::setBlogSetting('noneCommentMessage', $value, true);
		$value = $xmls->getValue('/skin/default/commentMessage/single'); 
		if (is_null($value))
			Setting::setBlogSetting('singleCommentMessage', NULL, true);
		else
			Setting::setBlogSetting('singleCommentMessage', $value, true);
		$value = $xmls->getValue('/skin/default/trackbackMessage/none'); 
		if (is_null($value))
			Setting::setBlogSetting('noneTrackbackMessage', NULL, true);
		else
			Setting::setBlogSetting('noneTrackbackMessage', $value, true);
		$value = $xmls->getValue('/skin/default/trackbackMessage/single'); 
		if (is_null($value))
			Setting::setBlogSetting('singleTrackbackMessage', NULL, true);
		else
			Setting::setBlogSetting('singleTrackbackMessage', $value, true);
	} else {
		Setting::setBlogSetting('noneCommentMessage', NULL, true);
		Setting::setBlogSetting('singleCommentMessage', NULL, true);
		Setting::setBlogSetting('noneTrackbackMessage', NULL, true);
		Setting::setBlogSetting('singleTrackbackMessage', NULL, true);
		Setting::setSkinSetting('skin',$skinName, $blogid);
	}
	
	Setting::removeBlogSetting("sidebarOrder",true);
	CacheControl::flushAll();
	CacheControl::flushSkin();
	Path::removeFiles(ROOT . "/skin/blog/customize/".getBlogId()."/");
	Setting::getSkinSettings($blogid, true); // refresh skin cache
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
	Setting::setSkinSetting('skin',$skinSetting['skin'],$blogid);
	if (!Setting::setSkinSetting('skin',$skinSetting['skin'],$blogid))
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
		CacheControl::flushSkin();
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
	Setting::setBlogSetting('useAjaxComment',$setting['useAjaxComment'],true);
	Setting::setBlogSetting('useFOAF',(($setting['useFOAF'] == 1) ? 1: 0),true);
	Setting::setBlogSetting('entriesOnPage',$setting['entriesOnPage'],true);
	Setting::setBlogSetting('entriesOnList',$setting['entriesOnList'],true);
	CacheControl::flushCategory();
	CacheControl::flushTag();
	CacheControl::flushSkin();
	Setting::getSkinSettings($blogid, true); // refresh skin cache
	return true;
}
?>
