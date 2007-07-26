<?php
function MT_getRecentEntries($parameters){
	global $database,$blogid,$blogURL,$blog,$defaultURL;
	requireComponent('Textcube.Core');
	requireComponent('Needlworks.Cache.PageCache');
	requireModel("blog.entry");
	requireModel("blog.tag");
	if (isset($parameters['preview'])) {
		// preview mode
		$retval = '메타페이지에 최신 글 목록을 보여줍니다.';
		return htmlspecialchars($retval);
	}
	$entryLength = isset($parameters['entryLength'])?$parameters['entryLength']:10;

	if (!is_dir(ROOT."/attach/{$blogid}/metaPostThumbnail/")) {
		@mkdir(ROOT."/attach/{$blogid}/metaPostThumbnail/");
		@chmod(ROOT."/attach/{$blogid}/metaPostThumbnail/", 0777);
	}

	$cache = new PageCache;
	$cache->name = 'MT_RecentPS';
	if($cache->load()) {
		return $cache->contents;
	} else {
		$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0 AND (c.visibility > 1 OR e.category = 0)';
		$entries = DBQuery::queryAll("SELECT e.id, e.userid, e.title, e.content, e.slogan, e.category, e.published, c.label 
			FROM {$database['prefix']}Entries e
			LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
			WHERE e.blogid = $blogid AND e.draft = 0 $visibility AND e.category >= 0 
			ORDER BY published DESC LIMIT $entryLength");	
		
		$html = '';
		foreach ($entries as $entry){
			$tagLabelView = "";
			$entryTags = getTags($entry['id']);
			if (sizeof($entryTags) > 0) {
				$tags = array();
				foreach ($entryTags as $entryTag) {
					$tags[$entryTag['name']] = "<a href=\"$defaultURL/tag/" . encodeURL($entryTag['name']) . '"' . ((count($entries) == 1 && getBlogSetting('useRelTag', true)) ? ' rel="tag"' : '') . '>' . htmlspecialchars($entryTag['name']) . '</a>';
				}
				$tagLabelView = "<div class=\"post_tags\"><span>TAG : </span>".implode(",\r\n", array_values($tags))."</div>";
			}
			$categoryName = htmlspecialchars(empty($entry['category']) ? _text('분류없음') : $entry['label']);
			$categoryLink = empty($entry['category']) ? "$blogURL/category/" : "$blogURL/category/".encodeURL($categoryName);
			$permalink = "$blogURL/" . ($blog['useSlogan'] ? "entry/" . encodeURL($entry['slogan']) : $entry['id']);
	
			$html .= '<div class="metapost">'.CRLF;
			if($imageName = MT_getAttachmentExtract($entry['content'])){
				if($tempImageSrc = MT_getImageResizer($imageName)){
					$html .= '<div class="img_preview" style="background:url('.$tempImageSrc.') top center no-repeat #ffffff;\"><img src="'.$blogURL.'/image/spacer.gif" onclick="window.location.href=\''.$permalink.'\'; return false;" /></div>'.CRLF;
				}
			}
			$html .= '	<h2><a href="'.$permalink.'">'.$entry['title'].'</a></h2>'.CRLF;
			$html .= '	<div class="post_info">'.CRLF;
			$html .= '		<span class="category"><a href="'.$categoryLink.'">'.$categoryName.'</a></span>'.CRLF;
			$html .= '		<span class="date">'.Timestamp::format5($entry['published']).'</span>'.CRLF;
			$html .= '		<span class="author">by '.User::getName($entry['userid']).'</span>'.CRLF;
			$html .= '	</div>'.CRLF;
			$html .= '	<div class="post_content">'.htmlspecialchars(UTF8::lessenAsEm(removeAllTags(stripHTML($entry['content'])),250)).'</div>'.CRLF;
			$html .=	$tagLabelView;
			$html .= '	<div class="clear"></div>'.CRLF;
			$html .= '</div>'.CRLF;
		}
		$target = $html;
		$cache->contents = $target;
		$cache->update();
		unset($cache);
		return $target;
	}
}

function MT_getRecentEntries_purgeCache($mother, $target) {
	requireComponent('Needlworks.Cache.PageCache');

	$cache = new PageCache;
	$cache->name = 'MT_RecentPS';
	$cache->purge();
	return $target;
}

function MT_getImageResizer($filename){
	global $blogid, $blogURL;
	requireComponent('Textcube.Function.Image');

	$imagePath = ROOT . "/attach/{$blogid}/{$filename}";
	$savePath = ROOT . "/attach/{$blogid}/metaPostThumbnail/th_{$filename}";
	$srcPath = "{$blogURL}/attach/{$blogid}/metaPostThumbnail/th_{$filename}";

	if(file_exists($imagePath)){
		if(!file_exists($savePath)){
			$imageInfo = getimagesize($imagePath);
			$attachedImage = new Image();
			$tempSize = $attachedImage->calcOptimizedImageSize($imageInfo[0],$imageInfo[1],null,90);
			if($imageInfo[1] < 90){
				$tempSize[0] = round($tempSize[0] * (90 / $tempSize[1]));
				$tempSize[1] = 90;
			}
			$attachedImage->imageFile = $imagePath;
			if ($attachedImage->resample($tempSize[0], $tempSize[1])) {
				$attachedImage->createThumbnailIntoFile($savePath);
			}
			unset($attachedImage);
		}
		return $srcPath;
	}else{
		return '';
	}
}

function MT_getAttachmentExtract($content){
	$result = null;
	if($count = preg_match_all('/\[##_(1R|1L|1C|2C|3C|iMazing|Gallery)\|([^\'"]+\.(?:gif|jpg\jpeg|png|bmp|GIF|JPG|JPEG|PNG|BMP))\|.*_##\]/si', $content, $matches)) {
		$tempfile = explode("|",$matches[2][0]);
		$result = $tempfile[0];
	}else if(preg_match_all('/<img[^>]+?src=("|\')?(.*?)("|\')/si', $content, $matches)) {
		if( !eregi("http://", $matches[2][0]) ){
			$result = basename($matches[2][0]);
		}
	}
	return $result;
}

function MT_getRecentEntryStyle($target){
	global $pluginURL;
	ob_start();
?>
<style type="text/css">
	.metapage .metapost {clear:both; border-bottom:1px solid #ddd; margin:10px 0;}
	.metapage .metapost h2{ font-size: 120%; padding-right:5px; } 
	.metapage .metapost h2 a{letter-spacing:-1px;line-height:125%;}
	.metapage .metapost .img_preview{ float:left; margin:0 7px 7px 0;width:80px; height:80px;border:1px solid #ccc;overflow:hidden;text-align:center;background-color:#fff;}
	.metapage .metapost .img_preview img { width:80px; height:80px; cursor:pointer;}
	.metapage .metapost .post_info {}
	.metapage .metapost .post_info .category a   { font:1em Dotum, Arial, sans-serif;  color:#888;  margin-right:6px;}
	.metapage .metapost .post_info .date         { font:0.9em Verdana, Helvetica, Arial, Gulim, sans-serif;  color:#888;}
	.metapage .metapost .post_content { margin:5px 0;line-height:125%;overflow:hidden;}
	.metapage .metapost .post_tags {padding:5px 5px 5px 40px;  background:url(<?php echo $pluginURL;?>/images/entryTag.gif) center left no-repeat; clear:both;}
	.metapage .metapost .post_tags span {display:none;}
	.metapage .clear {clear:both;}
</style>
<?php
	$target .= ob_get_contents();
	ob_end_clean();
	return $target;
}
?>
