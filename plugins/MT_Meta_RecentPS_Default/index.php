<?php
/*
		Cover page plugin for Recent Entries
		====================================
		Created by : J.Parker

		Last modified at : 2015.07.01 (for 2.0)
*/
function MT_Cover_getRecentEntries($parameters){
	global $skin;

	$context = Model_Context::getInstance();
	$data = $context->getProperty('plugin.config');

	importlib("model.blog.entry");
	importlib("model.blog.tag");
	$data['coverMode']	= !isset($data['coverMode']) ? 1 : $data['coverMode'];
	if(Utils_Misc::isMetaBlog() != true) $data['coverMode'] = 1;
	$data['screenshot']	= !isset($data['screenshot']) ? 1 : $data['screenshot'];
	$data['screenshotSize']	= !isset($data['screenshotSize']) ? 90 : $data['screenshotSize'];
	$data['paging'] = !isset($data['paging'])?'2':$data['paging'];
	$data['contentLength']	= !isset($data['contentLength']) ? 250 : $data['contentLength'];

	if (isset($parameters['preview'])) {
		// preview mode
		$retval = '표지에 최신 글 목록을 추가합니다.';
		return htmlspecialchars($retval);
	}
	$entryLength = isset($parameters['entryLength'])?$parameters['entryLength']:10;

	if (!is_dir(__TEXTCUBE_CACHE_DIR__."/thumbnail")) {
		@mkdir(__TEXTCUBE_CACHE_DIR__."/thumbnail");
		@chmod(__TEXTCUBE_CACHE_DIR__."/thumbnail", 0777);
	}
	if (!is_dir(__TEXTCUBE_CACHE_DIR__."/thumbnail/" . $context->getProperty('blog.id'))) {
		@mkdir(__TEXTCUBE_CACHE_DIR__."/thumbnail/" . $context->getProperty('blog.id'));
		@chmod(__TEXTCUBE_CACHE_DIR__."/thumbnail/" . $context->getProperty('blog.id'), 0777);
	}
	if (!is_dir(__TEXTCUBE_CACHE_DIR__."/thumbnail/" . $context->getProperty('blog.id') . "/coverPostThumbnail/")) {
		@mkdir(__TEXTCUBE_CACHE_DIR__."/thumbnail/" . $context->getProperty('blog.id') . "/coverPostThumbnail/");
		@chmod(__TEXTCUBE_CACHE_DIR__."/thumbnail/" . $context->getProperty('blog.id') . "/coverPostThumbnail/", 0777);
	}

	$page = ($data['paging'] == '1' && !empty($_GET['page'])) ? intval($_GET['page']) : 1;

	$cache = new PageCache;
	$cache->name = 'MT_Cover_RecentPS';
	if($cache->load()) { //If successful loads
		$cache->contents = unserialize($cache->contents);
		// If coverpage is single mode OR coverpage is coverblog and cache is not expired, return cache contents.
		if(($data['coverMode']==1 || $data['coverMode']==2) && array_key_exists($page, $cache->contents) && (Timestamp::getUNIXtime() - $cache->dbContents < 300)) {
			return $cache->contents[$page];
		}
	}

	$pool = DBModel::getInstance();
	$pool->reset("BlogSettings");
	$pool->setQualifier("name","eq",'visibility',true);
	$pool->setQualifier("value","<",2);
	$privateBlogId = $pool->getCell("blogid");
	
	$pool->reset("Entries");
	$pool->join("Categories","left",array(array("e.blogid","eq","c.blogid"),array("e.category","eq","c.id")));
	$pool->setQualifier("e.draft","eq",0);
	$pool->setQualifier("e.category","beq",0);
	if($privateBlogId) {
		$pool->setQualifier("e.blogid","hasnoneof",$privateBlogId);
	}
	
	if((Utils_Misc::isMetaBlog() == true) && doesHaveOwnership() && $context->getProperty('service.type','single') != 'single') {
		$pool->setQualifier("e.visibility",">",1);
		$pool->setQualifierSet(array("c.visibility",">",1),"OR",array("e.category","eq",0));	
	} else if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",1);
		$pool->setQualifierSet(array("c.visibility",">",1),"OR",array("e.category","eq",0));	
	}
	if ($data['coverMode'] != 2) {
		$pool->setQualifier("e.blogid","eq",$context->getProperty("blog.id"));
	}
	list($entries,$paging) = Paging::fetch($pool, $page, $entryLength);

	$html = '';
	foreach ((array)$entries as $entry){
		$tagLabelView = "";
		$blogid = ($data['coverMode']==2) ? $entry['blogid'] : $context->getProperty('blog.id');
		$entryTags = getTags($blogid, $entry['id']);
		$defaultURL = getDefaultURL($blogid);
		if (sizeof($entryTags) > 0) {
			$tags = array();
			foreach ($entryTags as $entryTag) {
				$tags[$entryTag['name']] = "<a href=\"{$defaultURL}/tag/" . (Setting::getBlogSettingGlobal('useSloganOnTag',true) ? URL::encode($entryTag['name'],$service['useEncodedURL']) : $entryTag['id']) . '">' . htmlspecialchars($entryTag['name']) . '</a>';
			}
			$tagLabelView = "<div class=\"post_tags\"><span>TAG : </span>".implode(",\r\n", array_values($tags))."</div>";
		}

		if (empty($entry['category'])) {
			$entry['label'] = _text('분류없음');
			$entry['link'] = "{$defaultURL}/category";
		} else {
			$entry['link'] = "{$defaultURL}/category/" . (Setting::getBlogSettingGlobal('useSloganOnCategory',true) ? URL::encode($entry['label'],$service['useEncodedURL']) : $entry['category']);
		}
		$permalink = "{$defaultURL}/" . (Setting::getBlogSettingGlobal('useSloganOnPost',true) ? "entry/" . URL::encode($entry['slogan'],$context->getProperty('service.useEncodedURL',false)) : $entry['id']);

		$html .= '<div class="coverpost">'.CRLF;
		if($imageName = MT_Cover_getAttachmentExtract($entry['content'])){
			if(($tempImageSrc = MT_Cover_getImageResizer($blogid, $imageName, $data['screenshotSize'])) && ($data['screenshot'] == 1)){
				$html .= '<div class="img_preview"><a href="'.$permalink.'"><img src="'.$tempImageSrc.'" alt="" /></a></div>'.CRLF;
			}
		}
		$html .= '	<div class="content_box">';
		$html .= '		<h2><a href="'.$permalink.'">'.htmlspecialchars($entry['title']).'</a></h2>'.CRLF;
		$html .= '		<div class="post_info">'.CRLF;
		$html .= '			<span class="category"><a href="'.htmlspecialchars($entry['link']).'">'.htmlspecialchars($entry['label']).'</a></span>'.CRLF;
		$html .= '			<span class="date">'.Timestamp::format5($entry['published']).'</span>'.CRLF;
		$html .= '			<span class="author"><span class="preposition">by </span>'.User::getName($entry['userid']).'</span>'.CRLF;
		$html .= '		</div>'.CRLF;
		$html .= '		<div class="post_content">'.htmlspecialchars(Utils_Unicode::lessenAsEm(removeAllTags(stripHTML($entry['content'])), $data['contentLength'])).'</div>'.CRLF;
		$html .=		$tagLabelView;
		$html .= '		<div class="clear"></div>'.CRLF;
		$html .= '	</div>';
		$html .= '</div>'.CRLF;
	}

	if ($data['paging'] == '1') {

		$paging['page'] = $page;
		$paging['total'] = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries e WHERE $multiple e.draft = 0 $visibility AND e.category >= 0");

		$html .= Paging::getPagingView($paging, $skin->paging, $skin->pagingItem).CRLF;

		$html .= '<script type="text/javascript">'.CRLF;
		$html .= '//<![CDATA['.CRLF;
		if ($paging['page'] > 1) {
			$html .= 'var prevURL = "'.$paging['url'].'?page='.($paging['page'] - 1).'"'.CRLF;
		}
		if ($paging['page'] < $paging['total']) {
			$html .= 'var nextURL = "'.$paging['url'].'?page='.($paging['page'] + 1).'"'.CRLF;
		}
		$html .= '//]]>'.CRLF;
		$html .= '</script>';
	}

	$target = $html;
	$cache->contents[$page] = $target;
	$cache->contents = serialize($cache->contents);
	$cache->dbContents = Timestamp::getUNIXtime();
	$cache->update();
	unset($cache);

	return $target;
}

function MT_Cover_getRecentEntries_purgeCache($target, $mother) {
	$cache = new PageCache;
	$cache->name = 'MT_Cover_RecentPS';
	$cache->purge();
	return $target;
}

function MT_Cover_getImageResizer($blogid, $filename, $cropSize){
	$context = Model_Context::getInstance();
	$tempFile = null;
	$thumbFilename = $filename;
	$imageURL = $context->getProperty('uri.service')."/attach/{$blogid}/{$filename}";
	if (extension_loaded('gd')) {
		if (stristr($filename, 'http://') ) {
			$thumbFilename = MT_Cover_getRemoteImageFilename($filename);
		}

		$thumbnailSrc = __TEXTCUBE_CACHE_DIR__."/thumbnail/{$blogid}/coverPostThumbnail/th_{$thumbFilename}";
		if (!file_exists($thumbnailSrc)) {
			$imageURL = MT_Cover_getCropProcess($blogid, $filename, $cropSize);
		} else {
			$imageURL = $context->getProperty('uri.service')."/thumbnail/{$blogid}/coverPostThumbnail/th_{$thumbFilename}";
			$imageInfo = getimagesize($thumbnailSrc);
			if ($imageInfo[0] != $cropSize) {
				$imageURL = MT_Cover_getCropProcess($blogid, $filename, $cropSize);
			}
		}
	} else {
		if(stristr($filename, 'http://') ){
			$imageURL = $filename;
		}
	}
	return $imageURL;
}

function MT_Cover_getCropProcess($blogid, $filename, $cropSize) {
	$context = Model_Context::getInstance();
	$tempFile = null;
	$imageURL = null;
	if(stristr($filename, 'http://') ){
		list($originSrc, $filename, $tempFile) = MT_Cover_getCreateRemoteImage($blogid, $filename);
	} else {
		$originSrc = __TEXTCUBE_ATTACH_DIR__."/{$blogid}/{$filename}";
	}

	$thumbnailSrc = __TEXTCUBE_CACHE_DIR__."/thumbnail/{$blogid}/coverPostThumbnail/th_{$filename}";
	if (file_exists($originSrc)) {
		$imageInfo = getimagesize($originSrc);

		$objThumbnail = new Utils_Image();
		if ($imageInfo[0] > $imageInfo[1])
			list($tempWidth, $tempHeight) = $objThumbnail->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], NULL, $cropSize);
		else
			list($tempWidth, $tempHeight) = $objThumbnail->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], $cropSize, null);

		$objThumbnail->imageFile = $originSrc;
		if ($objThumbnail->resample($tempWidth, $tempHeight) && $objThumbnail->cropRectBySize($cropSize, $cropSize)) {
			$imageURL = $context->getProperty('uri.service')."/thumbnail/{$blogid}/coverPostThumbnail/th_{$filename}";
			$objThumbnail->saveAsFile($thumbnailSrc);
		}

		unset($objThumbnail);
		if($tempFile) unlink($originSrc);
	} else {
		$imageURL = null;
	}

	return $imageURL;
}

function MT_Cover_getCreateRemoteImage($blogid, $filename) {
	$context = Model_Context::getInstance();
	$fileObject = false;
	$tmpDirectory = __TEXTCUBE_CACHE_DIR__."/thumbnail/{$blogid}/coverPostThumbnail/";
	$tempFilename = tempnam($tmpDirectory, "remote_");
	$fileObject = @fopen($tempFilename, "w");

	if ($fileObject) {
		$originSrc = $tempFilename;
		$remoteImage = MT_Cover_getHTTPRemoteImage($filename);
		$filename = MT_Cover_getRemoteImageFilename($filename);
		fwrite($fileObject, $remoteImage);
		fclose($fileObject);
		return array($originSrc, $filename, true);
	} else {
		return array(null, null, null);
	}
}

function MT_Cover_getHTTPRemoteImage($remoteImage) {
	$context = Model_Context::getInstance();

    $response = '';
	$remoteStuff = parse_url($remoteImage);
	$port = isset($remoteStuff['port']) ? $remoteStuff['port'] : 80;

	$socket = @fsockopen($remoteStuff['host'], $port);
    fputs($socket, "GET " . $remoteStuff['path'] . " HTTP/1.1\r\n");
    fputs($socket, "Host: " . $remoteStuff['host'] . "\r\n");
    fputs($socket, "User-Agent: Mozilla/4.0 (compatible; Textcube)\r\n");
    fputs($socket, "Accept-Encoding: identity\r\n");
    fputs($socket, "Connection: close\r\n");
    fputs($socket, "\r\n");

	while ($buffer = fread($socket, 1024)) {
		$response .= $buffer;
	}

	preg_match('/Content-Length: ([0-9]+)/', $response, $matches);
	return substr($response, - $matches[1]);
}

function MT_Cover_getRemoteImageFilename($filename) {
	$filename = md5($filename) . "." . Utils_Misc::getFileExtension($filename);
	return $filename;
}

function MT_Cover_getAttachmentExtract($content){
	$result = null;
	if(preg_match_all('/\[##_(1R|1L|1C|2C|3C|iMazing|Gallery)\|[^|]*\.(gif|jpg|jpeg|png|bmp|GIF|JPG|JPEG|PNG|BMP)\|.*_##\]/si', $content, $matches)) {
		$split = explode("|", $matches[0][0]);
		$result = $split[1];
	}else if(preg_match_all('/<img[^>]+?src=("|\')?([^\'">]*?)("|\')/si', $content, $matches)) {
		if( stristr($matches[2][0], 'http://') ){
			$result = $matches[2][0];
		}
	}
	return $result;
}

function MT_Cover_getRecentEntryStyle($target){
	$context = Model_Context::getInstance();
	$data = $context->getProperty('plugin.config');
	$data['cssSelect']	= !isset($data['cssSelect'])?1:$data['cssSelect'];
	if($data['cssSelect'] == 1){
		$target .= '<link rel="stylesheet" media="screen" type="text/css" href="' .$context->getProperty('plugin.uri') . '/style.css" />' . CRLF;
	}
	return $target;
}

function MT_Cover_getRecentEntries_DataSet($DATA){
	$cfg = Setting::fetchConfigVal($DATA);

	MT_Cover_getRecentEntries_purgeCache(null, null);
	return true;
}

function MT_Cover_getRecentEntries_ConfigOut_ko($plugin) {
	$manifest = NULL;

	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="MT_Cover_getRecentEntries_DataSet" >'.CRLF;
	$manifest .= '	<window width="500" height="345" />'.CRLF;
	$manifest .= '	<fieldset legend="표지 출력 설정">'.CRLF;
	$manifest .= '		<field title="출력 형태 :" name="coverMode" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[단일 사용자&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">다중 사용자</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="페이징 적용 :" name="paging" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1"><![CDATA[적용&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2" checked="checked">미적용</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="스크린 샷 :" name="screenshot" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[적용&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">미적용</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="스크린 샷 크기 :" name="screenshotSize" type="text" size="5" value="90" />'.CRLF;
	$manifest .= '		<field title="CSS 적용 :" name="cssSelect" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[적용&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">미적용</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="본문 길이 :" name="contentLength" type="text" size="5" value="250" />'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>'.CRLF;

	return $manifest;
}

function MT_Cover_getRecentEntries_ConfigOut_en($plugin) {

	$manifest = NULL;

	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="MT_Cover_getRecentEntries_DataSet" >'.CRLF;
	$manifest .= '	<window width="500" height="345" />'.CRLF;
	$manifest .= '	<fieldset legend="Cover list setup">'.CRLF;
	$manifest .= '		<field title="List mode :" name="coverMode" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[Single user&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">Multi user</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="Apply Pagination :" name="paging" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1"><![CDATA[Apply&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2" checked="checked">Not apply</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="Screenshot :" name="screenshot" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[Apply&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">Not apply</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="Screenshot size:" name="screenshotSize" type="text" size="5" value="90" />'.CRLF;
	$manifest .= '		<field title="Apply CSS :" name="cssSelect" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[Apply&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">Not apply</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="Content length :" name="contentLength" type="text" size="5" value="250" />'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>'.CRLF;

	return $manifest;
}
?>
