<?php
function MT_getImageResizer($target){
	global $owner;
	requireComponent('Textcube.Function.Image');
	$_GET['f'] = isset($_GET['f'])?$_GET['f']:'empty.gif';
	$imagePath = ROOT . "/attach/{$owner}/{$_GET['f']}";
	if ($fp = @fopen($imagePath, 'r')) {
		$imageInfo = getimagesize($imagePath);
		$tempSize = Image::calcOptimizedImageSize($imageInfo[0],$imageInfo[1],null,90);
		if($imageInfo[1] < 90){
			$tempSize[0] = round($tempSize[0] * (90 / $tempSize[1]));
			$tempSize[1] = 90;
		}
		$attachedImage = new Image();
		$attachedImage->imageFile = $imagePath;
		if ($attachedImage->resample($tempSize[0], $tempSize[1])) {
			$attachedImage->createThumbnailIntoCache();	
		}
		unset($attachedImage);
		fclose($fp);
	}
}
function MT_getRecentEntries($parameters){
	global $database,$skinSetting,$owner,$blogURL,$blog,$defaultURL;
	requireComponent('Textcube.Core');
	requireModel("blog.entry");
	requireModel("blog.tag");
	if (isset($parameters['preview'])) {
		// preview mode
		$retval = '메타페이지에 최신 글 목록을 보여줍니다.';
		return htmlspecialchars($retval);
	}
	$entryLength = isset($parameters['entryLength'])?$parameters['entryLength']:10;
	
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0 AND (c.visibility > 1 OR e.category = 0)';
	$entries = DBQuery::queryAll("SELECT e.id, e.userid, e.title, e.content, e.slogan, e.category, e.published, c.label 
		FROM {$database['prefix']}Entries e
		LEFT JOIN {$database['prefix']}Categories c ON e.blogid = c.blogid AND e.category = c.id 
		WHERE e.blogid = $owner AND e.draft = 0 $visibility AND e.category >= 0 
		ORDER BY published DESC LIMIT $entryLength");	
	
	$html = '';
	foreach ($entries as $entry){
		$entryTags = getTags($entry['id']);
		if (sizeof($entryTags) > 0) {
			$tags = array();
			foreach ($entryTags as $entryTag) {
				$tags[$entryTag['name']] = "<a href=\"$defaultURL/tag/" . encodeURL($entryTag['name']) . '"' . ((count($entries) == 1 && getBlogSetting('useRelTag', true)) ? ' rel="tag"' : '') . '>' . htmlspecialchars($entryTag['name']) . '</a>';
			}
			$tagLabelView = "<div class=\"post_tags\"><span>TAG : </span>".implode(",\r\n", array_values($tags))."</div>";
		}else{
			$tagLabelView = "";
		}
		$categoryName = htmlspecialchars(empty($entry['category']) ? _text('분류없음') : $entry['label']);
		$categoryLink = empty($entry['category']) ? "$blogURL/category/" : "$blogURL/category/".encodeURL($categoryName);
		$permalink = "$blogURL/" . ($blog['useSlogan'] ? "entry/" . encodeURL($entry['slogan']) : $entry['id']);
		$imageName = DBQuery::queryCell("SELECT name FROM {$database['prefix']}Attachments WHERE blogid = {$owner} AND parent = {$entry['id']} AND width > 0 AND height > 0 ORDER BY attached ASC");
		$imagePreview = ($imageName)?"<div class=\"img_preview\" style=\"background:url({$blogURL}/plugin/mtimageresizer?f={$imageName}) top center no-repeat #ffffff;\" onclick=\"window.location.href='{$permalink};?>';\"></div>":"";
		$html .= '<div class="metapost">'.CRLF;
		$html .=	$imagePreview;
		$html .= '	<h2><a href="'.$permalink.'">'.$entry['title'].'</a></h2>'.CRLF;
		$html .= '	<div class="post_info">'.CRLF;
		$html .= '		<span class="category"><a href="'.$categoryLink.'">'.$categoryName.'</a></span>'.CRLF;
		$html .= '		<span class="date">'.Timestamp::format5($entry['published']).'</span>'.CRLF;
		$html .= '		<span class="author">by '.User::authorName($owner,$entry['id']).'</span>'.CRLF;
		$html .= '	</div>'.CRLF;
		$html .= '	<div class="post_content">'.htmlspecialchars(UTF8::lessenAsEm(removeAllTags(stripHTML($entry['content'])),250)).'</div>'.CRLF;
		$html .=	$tagLabelView;
		$html .= '</div>'.CRLF;
	}
	$target = $html;
	return $target;
}

function MT_getRecentEntryStyle($target){
	global $pluginURL;
	ob_start();
?>
<style type="text/css">
	.metapage .metapost {clear:both; border-bottom:1px solid #ddd; margin:10px 0;}
	.metapage .metapost h2{ font-size: 120%; padding-right:5px; } 
	.metapage .metapost h2 a{letter-spacing:-1px;line-height:125%;}
	.metapage .metapost .img_preview{ float:left; margin:0 7px 7px 0;width:80px; height:80px;border:1px solid #ccc;cursor:pointer}
	.metapage .metapost .post_info {}
	.metapage .metapost .post_info .category a   { font:1em Dotum, Arial, sans-serif;  color:#888;  margin-right:6px;}
	.metapage .metapost .post_info .date         { font:0.9em Verdana, Helvetica, Arial, Gulim, sans-serif;  color:#888;}
	.metapage .metapost .post_content { margin:5px 0;line-height:125%;overflow:hidden;}
	.metapage .metapost .post_tags {padding:5px 5px 5px 40px;  background:url(<?php echo $pluginURL;?>/images/entryTag.gif) center left no-repeat; clear:both;}
	.metapage .metapost .post_tags span {display:none;}
	.metapage .clear { clear:both;height:0px;}
</style>
<?php
	$target .= ob_get_contents();
	ob_end_clean();
	return $target;
}
?>
