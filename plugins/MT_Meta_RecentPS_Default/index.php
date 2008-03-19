<?php
function MT_Cover_getRecentEntries($parameters){
	global $database,$blog,$serviceURL,$configVal, $defaultURL;
	requireComponent('Textcube.Core');
	requireComponent('Needlworks.Cache.PageCache');
	requireComponent('Textcube.Function.Setting');
	requireComponent('Textcube.Function.misc');
	requireModel("blog.entry");
	requireModel("blog.tag");
	$data = setting::fetchConfigVal($configVal);
	$data['coverMode']	= !isset($data['coverMode'])?1:$data['coverMode'];
	if(setting::isMetaBlog() != true) $data['coverMode'] = 1;
	$data['screenshot']	= !isset($data['screenshot'])?1:$data['screenshot'];

	if (isset($parameters['preview'])) {
		// preview mode
		$retval = '표지에 최신 글 목록을 추가합니다.';
		return htmlspecialchars($retval);
	}
	$entryLength = isset($parameters['entryLength'])?$parameters['entryLength']:10;

	if (!is_dir(ROOT."/cache/thumbnail")) {
		@mkdir(ROOT."/cache/thumbnail");
		@chmod(ROOT."/cache/thumbnail", 0777);
	}
	if (!is_dir(ROOT."/cache/thumbnail/" . getBlogId())) {
		@mkdir(ROOT."/cache/thumbnail/" . getBlogId());
		@chmod(ROOT."/cache/thumbnail/" . getBlogId(), 0777);
	}
	if (!is_dir(ROOT."/cache/thumbnail/" . getBlogId() . "/coverPostThumbnail/")) {
		@mkdir(ROOT."/cache/thumbnail/" . getBlogId() . "/coverPostThumbnail/");
		@chmod(ROOT."/cache/thumbnail/" . getBlogId() . "/coverPostThumbnail/", 0777);
	}

	$cache = new PageCache;
	$cache->name = 'MT_Cover_RecentPS';
	if($cache->load()) { //If successful loads
		// If coverpage is single mode OR coverpage is coverblog and cache is not expired, return cache contents.
		if(($data['coverMode']==1 || $data['coverMode']==2) && (Timestamp::getUNIXtime() - $cache->dbContents < 300)) {
			return $cache->contents;
		}	
	}
	
	if((misc::isMetaBlog() == true) && doesHaveOwnership()) {
		$visibility = 'AND e.visibility > 1 AND (c.visibility > 1 OR e.category = 0)';
	} else {
		$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 1 AND (c.visibility > 1 OR e.category = 0)';
	}
	$multiple = ($data['coverMode']==2) ? '' : 'e.blogid = ' . getBlogId() . ' AND';
	$entries = POD::queryAll("SELECT e.blogid, e.id, e.userid, e.title, e.content, e.slogan, e.category, e.published, e.contentFormatter, c.label 
		FROM {$database['prefix']}Entries e
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE $multiple e.draft = 0 $visibility AND e.category >= 0 
		ORDER BY published DESC LIMIT $entryLength");	
	
	$html = '';
	foreach ($entries as $entry){
		$tagLabelView = "";
		$blogid = ($data['coverMode']==2) ? $entry['blogid'] : getBlogId();
		$entryTags = getTags($blogid, $entry['id']);
		$defaultURL = getDefaultURL($blogid);
		if (sizeof($entryTags) > 0) {
			$tags = array();
			foreach ($entryTags as $entryTag) {
				$tags[$entryTag['name']] = "<a href=\"{$defaultURL}/tag/" . encodeURL($entryTag['name']) . '">' . htmlspecialchars($entryTag['name']) . '</a>';
			}
			$tagLabelView = "<div class=\"post_tags\"><span>TAG : </span>".implode(",\r\n", array_values($tags))."</div>";
		}
		$categoryName = htmlspecialchars(empty($entry['category']) ? _text('분류없음') : $entry['label']);
		$categoryLink = "{$defaultURL}/" . (empty($entry['category']) ? "category/" : "category/".encodeURL($categoryName));
		$permalink = "{$defaultURL}/" . ($blog['useSlogan'] ? "entry/" . encodeURL($entry['slogan']) : $entry['id']);

		$html .= '<div class="coverpost">'.CRLF;
		if($imageName = MT_Cover_getAttachmentExtract($entry['content'])){
			if(($tempImageSrc = MT_Cover_getImageResizer($blogid, $imageName)) && ($data['screenshot'] == 1)){
				$html .= '<div class="img_preview"><a href="'.$permalink.'"><img src="'.$tempImageSrc.'" alt="" /></a></div>'.CRLF;
			}
		}
		$html .= '	<div class="content_box">';
		$html .= '		<h2><a href="'.$permalink.'">'.htmlspecialchars($entry['title']).'</a></h2>'.CRLF;
		$html .= '		<div class="post_info">'.CRLF;
		$html .= '			<span class="category"><a href="'.$categoryLink.'">'.$categoryName.'</a></span>'.CRLF;
		$html .= '			<span class="date">'.Timestamp::format5($entry['published']).'</span>'.CRLF;
		$html .= '			<span class="author"><span class="preposition">by </span>'.User::getName($entry['userid']).'</span>'.CRLF;
		$html .= '		</div>'.CRLF;
		$html .= '		<div class="post_content">'.htmlspecialchars(UTF8::lessenAsEm(removeAllTags(stripHTML($entry['content'])),250)).'</div>'.CRLF;
		$html .=		$tagLabelView;
		$html .= '		<div class="clear"></div>'.CRLF;
		$html .= '	</div>';
		$html .= '</div>'.CRLF;
	}
	$target = $html;
	$cache->contents = $target;
	$cache->dbContents = Timestamp::getUNIXtime();
	$cache->update();
	unset($cache);
	return $target;
}

function MT_Cover_getRecentEntries_purgeCache($target, $mother) {
	requireComponent('Needlworks.Cache.PageCache');
	$cache = new PageCache;
	$cache->name = 'MT_Cover_RecentPS';
	$cache->purge();
	return $target;
}

function MT_Cover_getImageResizer($blogid, $filename){
	global $serviceURL;
	
	$originSrc = ROOT . "/attach/{$blogid}/{$filename}";
	$currentBlogId = getBlogId();
	$cropSize = 90;
	
	if (file_exists($originSrc)) {
		$imageInfo = getimagesize($originSrc);
		$newSrc = ROOT . "/cache/thumbnail/{$currentBlogId}/coverPostThumbnail/th_{$filename}";
		$imageURL = "{$serviceURL}/thumbnail/{$currentBlogId}/{$filename}";
		
		if (extension_loaded('gd')) {
			if (!file_exists($newSrc)) {
				requireComponent('Textcube.Function.Image');
				
				$objThumbnail = new Image();
				if ($imageInfo[0] > $imageInfo[1])
					list($tempWidth, $tempHeight) = $objThumbnail->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], NULL, 90);
				else
					list($tempWidth, $tempHeight) = $objThumbnail->calcOptimizedImageSize($imageInfo[0], $imageInfo[1], 90, null);
				
				$objThumbnail->imageFile = $originSrc;
				if ($objThumbnail->resample($tempWidth, $tempHeight) && $objThumbnail->cropRectBySize($cropSize, $cropSize)) {
					$imageURL = "{$serviceURL}/thumbnail/{$currentBlogId}/coverPostThumbnail/th_{$filename}";
					$objThumbnail->saveAsFile($newSrc);
				}
				unset($objThumbnail);
			} else {
				$imageURL = "{$serviceURL}/thumbnail/{$currentBlogId}/coverPostThumbnail/th_{$filename}";
			}
		}
		
		return $imageURL;
	} else {
		return NULL;
	}
}

function MT_Cover_getAttachmentExtract($content){
	$result = null;
	if(preg_match_all('/\[##_(1R|1L|1C|2C|3C|iMazing|Gallery)\|[^|]*\.(gif|jpg|jpeg|png|bmp|GIF|JPG|JPEG|PNG|BMP)\|.*_##\]/si', $content, $matches)) {
		$split = explode("|", $matches[0][0]);
		$result = $split[1];
	}else if(preg_match_all('/<img[^>]+?src=("|\')?([^\'">]*?)("|\')/si', $content, $matches)) {
		if( !stristr('http://', $matches[2][0]) ){
			$result = basename($matches[2][0]);
		}
	}
	return $result;
}

function MT_Cover_getRecentEntryStyle($target){
	global $pluginURL, $configVal;
	requireComponent('Textcube.Function.Setting');
	$data = setting::fetchConfigVal($configVal);
	$data['cssSelect']	= !isset($data['cssSelect'])?1:$data['cssSelect'];
	if($data['cssSelect'] == 1){
		$target .= '<link rel="stylesheet" media="screen" type="text/css" href="' . $pluginURL . '/style.css" />' . CRLF;
	}
	return $target;
}

function MT_Cover_getRecentEntries_DataSet($DATA){
	requireComponent('Textcube.Function.Setting');
	requireComponent('Needlworks.Cache.PageCache');
	$cfg = setting::fetchConfigVal($DATA);

	$cache = new PageCache;
	$cache->name = 'MT_Cover_RecentPS';
	$cache->purge();
	return true;
}

function MT_Cover_getRecentEntries_ConfigOut_ko($plugin) {
	global $service;
	
	$manifest = NULL;

	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="MT_Cover_getRecentEntries_DataSet" >'.CRLF;
	$manifest .= '	<window width="500" height="298" />'.CRLF;
	$manifest .= '	<fieldset legend="표지 출력 설정">'.CRLF;
	$manifest .= '		<field title="출력 형태 :" name="coverMode" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[단일 사용자&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">다중 사용자</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="스크린 샷 :" name="screenshot" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[적용&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">미적용</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="CSS 적용 :" name="cssSelect" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[적용&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">미적용</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>'.CRLF;
	
	return $manifest;
}

function MT_Cover_getRecentEntries_ConfigOut_en($plugin) {
	global $service;
	
	$manifest = NULL;

	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="MT_Cover_getRecentEntries_DataSet" >'.CRLF;
	$manifest .= '	<window width="500" height="298" />'.CRLF;
	$manifest .= '	<fieldset legend="Cover list setup">'.CRLF;
	$manifest .= '		<field title="List mode :" name="coverMode" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[Single user&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">Multi user</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="Screenshot:" name="screenshot" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[Apply&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">Not apply</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '		<field title="CSS Apply :" name="cssSelect" type="radio"  >'.CRLF;
	$manifest .= '			<op value="1" checked="checked"><![CDATA[Apply&nbsp;]]></op>'.CRLF;
	$manifest .= '			<op value="2">Not apply</op>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>'.CRLF;
	
	return $manifest;
}
?>
