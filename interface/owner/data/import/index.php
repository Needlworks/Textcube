<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
ini_set('display_errors', 'off');
$IV = array(
	'POST' => array(
		'importFrom' => array(array('server', 'uploaded', 'web')),
		'backupURL' => array('url', 'default' => null),
		'correctData' => array(array('on'), 'default' => null)
	),
	'FILES' => array(
		'backupPath' => array('file', 'default' => array() )
	)
);
require ROOT . '/library/preprocessor.php';
requireStrictRoute();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Textcube Data Importing</title>
	<script type="text/javascript">
		//<![CDATA[
			var pi = window.parent.document.getElementById("progressIndicator");
			var pt = window.parent.document.getElementById("progressText");
			var pts = window.parent.document.getElementById("progressTextSub");
		//]]>
	</script>
</head>
<body>
<?php
function finish($error = null) {
	global $migrational, $blogid, $database;
?>
	<script type="text/javascript">
		//<![CDATA[
<?php
	if ($error) {
?>
			//pi.style.backgroundColor = "red";
			alert("<?php echo $error;?>");
<?php
	} else {
?>
			alert("<?php echo ($migrational ? _t('성공적으로 이주되었습니다.') : _t('성공적으로 복원되었습니다.'));?>");
<?php
	}
?>
			window.parent.document.getElementById("progressDialog").style.display = "none";
			window.parent.document.getElementById("progressDialogTitle").innerHTML = "";
			window.parent.document.getElementById("progressText").innerHTML = "";
			window.parent.document.getElementById("progressTextSub").innerHTML = "";
		//]]>
	</script>
<?php
	$activeEditors = POD::queryColumn("SELECT DISTINCT contenteditor FROM {$database}Entries WHERE blogid = $blogid");
	$activeFormatters = POD::queryColumn("SELECT DISTINCT contentformatter FROM {$database}Entries WHERE blogid = $blogid");
	if(!empty($activeEditors)) {foreach($activeEditors as $editor) activatePlugin($editor);}
	if(!empty($activeFormatters)) {foreach($activeFormatters as $formatter) activatePlugin($formatter);}
	echo _t('완료.');
?>
</body>
</html>
<?php
	exit;
}
$lastProgress = 0;
$lastProgressText = null;
$lastProgressTextSub = null;

function setProgress($progress, $text = null, $sub = null) {
	global $lastProgress, $lastProgressText, $lastProgressTextSub;
	$progress = intval($progress);
	$diff = '';
	if (isset($progress) && ($progress != $lastProgress)) {
		$lastProgress = $progress;
		$diff .= 'pi.style.width = "' . $progress . '%";';
	}
	if (isset($text) && ($text != $lastProgressText)) {
		$lastProgressText = $text;
		$diff .= 'pt.innerHTML = "' . $text . '";';
		if (!isset($sub)) {
			$lastProgressTextSub = '';
			$diff .= 'pts.innerHTML = "";';
		}
	}
	if (isset($sub) && ($sub != $lastProgressTextSub)) {
		$lastProgressTextSub = $sub;
		$diff .= 'pts.innerHTML = "(' . $sub . ')";';
	}
	if (!empty($diff)) {
?>
<script type="text/javascript">
	//<![CDATA[
		<?php echo $diff;?>
	//]]>
</script>
<?php
		flush();
	}
}
switch (@$_POST['importFrom']) {
	default:
		finish(_t('잘못된 요청입니다.'));
		break;
	case 'server':
		$backup = __TEXTCUBE_CACHE_DIR__."/backup/$blogid.xml";
		break;
	case 'uploaded':
		if (@$_FILES['backupPath']['error'] !== 0)
			finish(_t('업로드가 취소되었거나 업로드 용량 제한을 초과하였습니다.'));
		$backup = $_FILES['backupPath']['tmp_name'];
		break;
	case 'web':
		if (!file_exists(__TEXTCUBE_CACHE_DIR__.'/import')) {
			mkdir(__TEXTCUBE_CACHE_DIR__.'/import');
			@chmod(__TEXTCUBE_CACHE_DIR__.'/import', 0777);
		}
		if (!is_dir(__TEXTCUBE_CACHE_DIR__.'/import')) {
			finish(_t('백업파일을 저장할 공간에 권한이 없습니다.'));
		}
		$request = new HTTPRequest($_POST['backupURL']);
		$backup = __TEXTCUBE_CACHE_DIR__."/import/$blogid.xml";
		$request->pathToSave = $backup;
		if (!$request->send()) {
			finish(_t('백업파일이 손상되었거나 가져올 수 없습니다.'));
		}
		break;
}
$migrational = false;
$items = 0;
$item = 0;
$xmls = new XMLStruct();
set_time_limit(0);
setProgress(0, _t('백업파일을 확인하고 있습니다.'));
$xmls->setStream('/blog/setting/banner/content');
$xmls->setStream('/blog/post/attachment/content');
$xmls->setStream('/blog/notice/attachment/content');
$xmls->setStream('/blog/keyword/attachment/content');
$xmls->setConsumer('scanner');
if (!$xmls->openFile($backup, Validator::getBool(@$_POST['correctData']))) {
	finish(_f('백업파일의 %1번째 줄이 올바르지 않습니다.', $xmls->error['line']));
}
$xmls->close();
if ($items == 0)
	finish(_t('백업파일에 복원할 데이터가 없습니다.'));
if (!$migrational) {
	setProgress(0, _t('복원 위치를 준비하고 있습니다.'));
	DataMaintenance::removeAll(false);
	CacheControl::flushAll();
}
$xmls->setConsumer('importer');
if (!$xmls->openFile($backup, Validator::getBool(@$_POST['correctData']))) {
	finish(_t('백업파일이 올바르지 않습니다.'));
}

$xmls->close();
if (file_exists(__TEXTCUBE_CACHE_DIR__."/import/$blogid.xml"))
	@unlink(__TEXTCUBE_CACHE_DIR__."/import/$blogid.xml");
setProgress(100, _t('완료되었습니다.'));
finish();

/*@callback@*/
function scanner($path, $node, $line) {
	global $migrational, $items;
	switch ($path) {
		case '/blog':
			if (!preg_match('/^tattertools\/1\.[01]$/', @$node['.attributes']['type'])
			 && !preg_match('/^textcube\/1\.[01]$/', @$node['.attributes']['type']))
				finish(_t('지원하지 않는 백업파일입니다.'));
			$migrational = Validator::getBool(@$node['.attributes']['migrational']);
			return true;
		case '/blog/setting/banner/content':
		case '/blog/post/attachment/content':
		case '/blog/notice/attachment/content':
		case '/blog/keyword/attachment/content':
			if (!empty($node['.stream'])) {
				fclose($node['.stream']);
				unset($node['.stream']);
			}
			return true;
		case '/blog/setting':
		case '/blog/category':
		case '/blog/post':
		case '/blog/notice':
		case '/blog/keyword':
		case '/blog/link':
		case '/blog/logs/referer':
		case '/blog/statistics/referer':
		case '/blog/statistics/visits':
		case '/blog/statistics/daily':
		case '/blog/skin':
		case '/blog/plugin':
		case '/blog/commentNotified/comment':
		case '/blog/commentNotifiedSiteInfo/site':
		case '/blog/guestbook/comment':
		case '/blog/filter':
		case '/blog/feed':
		case '/blog/line':
			$items++;
			if (!strpos($path, 'referer'))
				setProgress(null, _t('백업파일을 확인하고 있습니다.'), $line);
			return true;
		case '/blog/personalization':
		case '/blog/userSetting':
			// skip
			return true;
	}
}

/*@callback@*/
function importer($path, $node, $line) {
	global $blogid, $migrational, $items, $item;
	switch ($path) {
		case '/blog/setting':
			setProgress($item++ / $items * 100, _t('블로그 설정을 복원하고 있습니다.'));
			$setting = new BlogSetting();
			if (isset($node['title'][0]['.value']))
				$setting->title = $node['title'][0]['.value'];
			if (isset($node['description'][0]['.value']))
				$setting->description = $node['description'][0]['.value'];
			if (isset($node['banner'][0]['name'][0]['.value']))
				$setting->banner = $node['banner'][0]['name'][0]['.value'];
			if (isset($node['useSloganOnPost'][0]['.value']))
				$setting->useSloganOnPost = $node['useSloganOnPost'][0]['.value'];
			if (isset($node['postsOnPage'][0]['.value']))
				$setting->postsOnPage = $node['postsOnPage'][0]['.value'];
			if (isset($node['postsOnList'][0]['.value']))
				$setting->postsOnList = $node['postsOnList'][0]['.value'];
			if (isset($node['postsOnFeed'][0]['.value']))
				$setting->postsOnFeed = $node['postsOnFeed'][0]['.value'];
			if (isset($node['publishWholeOnFeed'][0]['.value']))
				$setting->publishWholeOnFeed = $node['publishWholeOnFeed'][0]['.value'];
			if (isset($node['acceptGuestComment'][0]['.value']))
				$setting->acceptGuestComment = $node['acceptGuestComment'][0]['.value'];
			if (isset($node['acceptcommentOnGuestComment'][0]['.value']))
				$setting->acceptcommentOnGuestComment = $node['acceptcommentOnGuestComment'][0]['.value'];
			if (isset($node['language'][0]['.value']))
				$setting->language = $node['language'][0]['.value'];
			if (isset($node['timezone'][0]['.value']))
				$setting->timezone = $node['timezone'][0]['.value'];
			if (!$setting->save())
				user_error(__LINE__ . $setting->error);
			if (!empty($setting->banner) && !empty($node['banner'][0]['content'][0]['.stream'])) {
				Attachment::confirmFolder();
				Utils_Base64Stream::decode($node['banner'][0]['content'][0]['.stream'], Path::combine(ROOT, 'attach', $blogid, $setting->banner));
				Attachment::adjustPermission(Path::combine(ROOT, 'attach', $blogid, $setting->banner));
				fclose($node['banner'][0]['content'][0]['.stream']);
				unset($node['banner'][0]['content'][0]['.stream']);
			}
			return true;
		case '/blog/category':
			setProgress($item++ / $items * 100, _t('분류를 복원하고 있습니다.'));
			$category = new Category();
			$category->name = $node['name'][0]['.value'];
			$category->priority = $node['priority'][0]['.value'];
			if (isset($node['root'][0]['.value'])) $category->id = 0;
			if (!$category->add())
				user_error(__LINE__ . $category->error);
			if (isset($node['category'])) {
				for ($i = 0; $i < count($node['category']); $i++) {
					$childCategory = new Category();
					$childCategory->parent = $category->id;
					$cursor = & $node['category'][$i];
					$childCategory->name = $cursor['name'][0]['.value'];
					$childCategory->priority = $cursor['priority'][0]['.value'];
					if (!$childCategory->add()) {
						user_error(__LINE__ . $childCategory->error);
					}
				}
			}
			return true;
		case '/blog/post':
			setProgress($item++ / $items * 100, _t('글을 복원하고 있습니다.'));
			$post = new Post();
			$post->id = $node['id'][0]['.value'];
			$post->slogan = @$node['.attributes']['slogan'];
			$post->visibility = $node['visibility'][0]['.value'];
			if(isset($node['starred'][0]['.value']))
				$post->starred = $node['starred'][0]['.value'];
			else $post->starred = 0;
			$post->title = $node['title'][0]['.value'];
			$post->content = $node['content'][0]['.value'];
			$post->contentformatter = isset($node['content'][0]['.attributes']['formatter']) ? $node['content'][0]['.attributes']['formatter'] : 'ttml';
			$post->contenteditor = isset($node['content'][0]['.attributes']['editor']) ? $node['content'][0]['.attributes']['editor'] : 'modern';
			$post->location = $node['location'][0]['.value'];
			$post->password = isset($node['password'][0]['.value']) ? $node['password'][0]['.value'] : null;
			$post->acceptcomment = $node['acceptComment'][0]['.value'];
			$post->accepttrackback = $node['acceptTrackback'][0]['.value'];
			$post->published = $node['published'][0]['.value'];
			if (isset($node['longitude'][0]['.value']))
				$post->longitude = $node['longitude'][0]['.value'];
			if (isset($node['latitude'][0]['.value']))
				$post->latitude = $node['latitude'][0]['.value'];
			$post->created = @$node['created'][0]['.value'];
			$post->modified = @$node['modified'][0]['.value'];
			if (($post->visibility == 'private' && intval($post->published) > $_SERVER['REQUEST_TIME']) ||
				(!empty($node['appointed'][0]['.value']) && $node['appointed'][0]['.value'] == 'true')) // for compatibility of appointed entries
				$post->visibility = 'appointed';
			if ($post->slogan == '') $post->slogan = 'Untitled'.$post->id;
			if (!empty($node['category'][0]['.value']))
				$post->category = Category::getId($node['category'][0]['.value']);
			if (isset($node['tag'])) {
				$post->tags = array();
				for ($i = 0; $i < count($node['tag']); $i++) {
					if (!empty($node['tag'][$i]['.value']))
						array_push($post->tags, $node['tag'][$i]['.value']);
				}
			}
			if (floatval(Setting::getServiceSettingGlobal('newlineStyle')) >= 1.1 && floatval(@$node['.attributes']['format']) < 1.1)
				$post->content = nl2brWithHTML($post->content);
			if (!$post->add())
				user_error(__LINE__ . $post->error);
			if (isset($node['attachment'])) {
				for ($i = 0; $i < count($node['attachment']); $i++) {
					$attachment = new Attachment();
					$attachment->parent = $post->id;
					$cursor = & $node['attachment'][$i];
					$attachment->name = $cursor['name'][0]['.value'];
					$attachment->label = $cursor['label'][0]['.value'];
					$attachment->mime = @$cursor['.attributes']['mime'];
					$attachment->size = $cursor['.attributes']['size'];
					$attachment->width = $cursor['.attributes']['width'];
					$attachment->height = $cursor['.attributes']['height'];
					$attachment->enclosure = @$cursor['enclosure'][0]['.value'];
					$attachment->attached = $cursor['attached'][0]['.value'];
					$attachment->downloads = @$cursor['downloads'][0]['.value'];
					if (!$attachment->add()) {
						user_error(__LINE__ . $attachment->error);
					} else if ($cursor['name'][0]['.value'] != $attachment->name) {
						$post2 = new Post();
						if ($post2->open($post->id, 'id, content')) {
							$post2->content = str_replace($cursor['name'][0]['.value'], $attachment->name, $post2->content);
                            $post2->loadTags();
							$post2->update();
							$post2->close();
						}
						unset($post2);
					}
					if (!empty($cursor['content'][0]['.stream'])) {
						Utils_Base64Stream::decode($cursor['content'][0]['.stream'], Path::combine(ROOT, 'attach', $blogid, $attachment->name));
						Attachment::adjustPermission(Path::combine(ROOT, 'attach', $blogid, $attachment->name));
						fclose($cursor['content'][0]['.stream']);
						unset($cursor['content'][0]['.stream']);
					}
				}
			}
			if (isset($node['comment'])) {
				for ($i = 0; $i < count($node['comment']); $i++) {
					$comment = new Comment();
					$comment->entry = $post->id;
					$cursor = & $node['comment'][$i];
					$comment->name = $cursor['commenter'][0]['name'][0]['.value'];
					if (!empty($cursor['id'][0]['.value']))
						$comment->id = $cursor['id'][0]['.value'];
					if (!empty($cursor['commenter'][0]['.attributes']['id']))
						$comment->commenter = $cursor['commenter'][0]['.attributes']['id'];
					if (!empty($cursor['commenter'][0]['homepage'][0]['.value']))
						$comment->homepage = $cursor['commenter'][0]['homepage'][0]['.value'];
					if (!empty($cursor['commenter'][0]['ip'][0]['.value']))
						$comment->ip = $cursor['commenter'][0]['ip'][0]['.value'];
					if (!empty($cursor['commenter'][0]['openid'][0]['.value']))
						$comment->openid = $cursor['commenter'][0]['openid'][0]['.value'];
					$comment->password = $cursor['password'][0]['.value'];
					$comment->secret = $cursor['secret'][0]['.value'];
					$comment->written = $cursor['written'][0]['.value'];
					if (isset($cursor['longitude'][0]['.value']))
						$comment->longitude = $cursor['longitude'][0]['.value'];
					if (isset($cursor['latitude'][0]['.value']))
						$comment->latitude = $cursor['latitude'][0]['.value'];
					$comment->content = $cursor['content'][0]['.value'];
					if (!empty($cursor['isFiltered'][0]['.value']))
				    	$comment->isfiltered = $cursor['isFiltered'][0]['.value'];
					if (!$comment->add())
						user_error(__LINE__ . $comment->error);
					if (isset($node['comment'][$i]['comment'])) {
						for ($j = 0; $j < count($node['comment'][$i]['comment']); $j++) {
							$childComment = new Comment();
							$childComment->entry = $post->id;
							$childComment->parent = $comment->id;
							$cursor = & $node['comment'][$i]['comment'][$j];
							if (!empty($cursor['id'][0]['.value']))
								$childComment->id = $cursor['id'][0]['.value'];
							if (!empty($cursor['commenter'][0]['.attributes']['id']))
								$childComment->commenter = $cursor['commenter'][0]['.attributes']['id'];
							$childComment->name = $cursor['commenter'][0]['name'][0]['.value'];
							if (!empty($cursor['commenter'][0]['homepage'][0]['.value']))
								$childComment->homepage = $cursor['commenter'][0]['homepage'][0]['.value'];
							if (!empty($cursor['commenter'][0]['ip'][0]['.value']))
								$childComment->ip = $cursor['commenter'][0]['ip'][0]['.value'];
							if (!empty($cursor['commenter'][0]['openid'][0]['.value']))
								$childComment->openid = $cursor['commenter'][0]['openid'][0]['.value'];
							$childComment->password = $cursor['password'][0]['.value'];
							$childComment->secret = $cursor['secret'][0]['.value'];
							$childComment->written = $cursor['written'][0]['.value'];
							if (isset($cursor['longitude'][0]['.value']))
								$comment->longitude = $cursor['longitude'][0]['.value'];
							if (isset($cursor['latitude'][0]['.value']))
								$comment->latitude = $cursor['latitude'][0]['.value'];
							$childComment->content = $cursor['content'][0]['.value'];
							if (!empty($cursor['isFiltered'][0]['.value']))
					    		$childComment->isfiltered = $cursor['isFiltered'][0]['.value'];
							if (!$childComment->add())
								user_error(__LINE__ . $childComment->error);
						}
					}
				}
			}
			if (isset($node['trackback'])) {
				for ($i = 0; $i < count($node['trackback']); $i++) {
					$trackback = new Trackback();
					$trackback->entry = $post->id;
					$cursor = & $node['trackback'][$i];
					$trackback->url = $cursor['url'][0]['.value'];
					$trackback->site = $cursor['site'][0]['.value'];
					$trackback->title = $cursor['title'][0]['.value'];
					$trackback->excerpt = @$cursor['excerpt'][0]['.value'];
					if (!empty($cursor['ip'][0]['.value']))
						$trackback->ip = $cursor['ip'][0]['.value'];
					if (!empty($cursor['received'][0]['.value']))
						$trackback->received = $cursor['received'][0]['.value'];
					if (!empty($cursor['isFiltered'][0]['.value']))
					    	$trackback->isFiltered = $cursor['isFiltered'][0]['.value'];
					if (!$trackback->add())
						user_error(__LINE__ . $trackback->error);
				}
			}
			if (isset($node['logs'][0]['trackback'])) {
				for ($i = 0; $i < count($node['logs'][0]['trackback']); $i++) {
					$log = new TrackbackLog();
					$log->entry = $post->id;
					$cursor = & $node['logs'][0]['trackback'][$i];
					$log->url = $cursor['url'][0]['.value'];
					if (!empty($cursor['sent'][0]['.value']))
						$log->sent = $cursor['sent'][0]['.value'];
					if (!$log->add())
						user_error(__LINE__ . $log->error);
				}
			}
			return true;
		case '/blog/page':
			setProgress($item++ / $items * 100, _t('페이지를 복원하고 있습니다.'));
			$page = new Page();
			$page->id = $node['id'][0]['.value'];
			$page->slogan = @$node['.attributes']['slogan'];
			$page->visibility = $node['visibility'][0]['.value'];
			if(isset($node['starred'][0]['.value']))
				$page->starred = $node['starred'][0]['.value'];
			else $page->starred = 0;
			$page->title = $node['title'][0]['.value'];
			$page->content = $node['content'][0]['.value'];
			$page->contentformatter = isset($node['content']['.attributes']['formatter']) ? $node['content']['.attributes']['formatter'] : getDefaultFormatter();
			$page->contenteditor = isset($node['content']['.attributes']['editor']) ? $node['content']['.attributes']['editor'] : getDefaultEditor();
			$page->published = $node['published'][0]['.value'];
			$page->created = @$node['created'][0]['.value'];
			$page->modified = @$node['modified'][0]['.value'];
			if (floatval(Setting::getServiceSettingGlobal('newlineStyle')) >= 1.1 && floatval(@$node['.attributes']['format']) < 1.1)
				$page->content = nl2brWithHTML($page->content);
			if (!$page->add())
				user_error(__LINE__ . $page->error);
			if (isset($node['attachment'])) {
				for ($i = 0; $i < count($node['attachment']); $i++) {
					$attachment = new Attachment();
					$attachment->parent = $page->id;
					$cursor = & $node['attachment'][$i];
					$attachment->name = $cursor['name'][0]['.value'];
					$attachment->label = $cursor['label'][0]['.value'];
					$attachment->mime = @$cursor['.attributes']['mime'];
					$attachment->size = $cursor['.attributes']['size'];
					$attachment->width = $cursor['.attributes']['width'];
					$attachment->height = $cursor['.attributes']['height'];
					$attachment->enclosure = @$cursor['enclosure'][0]['.value'];
					$attachment->attached = $cursor['attached'][0]['.value'];
					$attachment->downloads = @$cursor['downloads'][0]['.value'];
					if (Attachment::doesExist($attachment->name)) {
						if (!$attachment->add())
							user_error(__LINE__ . $attachment->error);
						$page2 = new Page();
						if ($page2->open($page->id, 'id, content')) {
							$page2->content = str_replace($cursor['name'][0]['.value'], $attachment->name, $page2->content);
							$page2->update();
							$page2->close();
						}
						unset($page2);
					} else {
						if (!$attachment->add())
							user_error(__LINE__ . $attachment->error);
					}
					if (!empty($cursor['content'][0]['.stream'])) {
						Utils_Base64Stream::decode($cursor['content'][0]['.stream'], Path::combine(ROOT, 'attach', $blogid, $attachment->name));
						Attachment::adjustPermission(Path::combine(ROOT, 'attach', $blogid, $attachment->name));
						fclose($cursor['content'][0]['.stream']);
						unset($cursor['content'][0]['.stream']);
					}
				}
			}
			return true;
		case '/blog/notice':
			setProgress($item++ / $items * 100, _t('공지를 복원하고 있습니다.'));
			$notice = new Notice();
			$notice->id = $node['id'][0]['.value'];
			$notice->slogan = @$node['.attributes']['slogan'];
			$notice->visibility = $node['visibility'][0]['.value'];
			if(isset($node['starred'][0]['.value']))
				$notice->starred = $node['starred'][0]['.value'];
			else $notice->starred = 0;
			$notice->title = $node['title'][0]['.value'];
			$notice->content = $node['content'][0]['.value'];
			$notice->contentformatter = isset($node['content'][0]['.attributes']['formatter']) ? $node['content'][0]['.attributes']['formatter'] : getDefaultFormatter();
			$notice->contenteditor = isset($node['content'][0]['.attributes']['editor']) ? $node['content'][0]['.attributes']['editor'] : getDefaultEditor();
			$notice->published = intval($node['published'][0]['.value']);
			$notice->created = @$node['created'][0]['.value'];
			$notice->modified = @$node['modified'][0]['.value'];
			if (floatval(Setting::getServiceSettingGlobal('newlineStyle')) >= 1.1 && floatval(@$node['.attributes']['format']) < 1.1)
				$notice->content = nl2brWithHTML($notice->content);
			if (!$notice->add())
				user_error(__LINE__ . $notice->error);
			if (isset($node['attachment'])) {
				for ($i = 0; $i < count($node['attachment']); $i++) {
					$attachment = new Attachment();
					$attachment->parent = $notice->id;
					$cursor = & $node['attachment'][$i];
					$attachment->name = $cursor['name'][0]['.value'];
					$attachment->label = $cursor['label'][0]['.value'];
					$attachment->mime = @$cursor['.attributes']['mime'];
					$attachment->size = $cursor['.attributes']['size'];
					$attachment->width = $cursor['.attributes']['width'];
					$attachment->height = $cursor['.attributes']['height'];
					$attachment->enclosure = @$cursor['enclosure'][0]['.value'];
					$attachment->attached = $cursor['attached'][0]['.value'];
					$attachment->downloads = @$cursor['downloads'][0]['.value'];
					if (Attachment::doesExist($attachment->name)) {
						if (!$attachment->add())
							user_error(__LINE__ . $attachment->error);
						$notice2 = new Notice();
						if ($notice2->open($notice->id, 'id, content')) {
							$notice2->content = str_replace($cursor['name'][0]['.value'], $attachment->name, $notice2->content);
							$notice2->update();
							$notice2->close();
						}
						unset($notice2);
					} else {
						if (!$attachment->add())
							user_error(__LINE__ . $attachment->error);
					}
					if (!empty($cursor['content'][0]['.stream'])) {
						Utils_Base64Stream::decode($cursor['content'][0]['.stream'], Path::combine(ROOT, 'attach', $blogid, $attachment->name));
						Attachment::adjustPermission(Path::combine(ROOT, 'attach', $blogid, $attachment->name));
						fclose($cursor['content'][0]['.stream']);
						unset($cursor['content'][0]['.stream']);
					}
				}
			}
			return true;
		case '/blog/keyword':
			setProgress($item++ / $items * 100, _t('키워드를 복원하고 있습니다.'));
			$keyword = new Keyword();
			$keyword->id = $node['id'][0]['.value'];
			$keyword->visibility = $node['visibility'][0]['.value'];
			if(isset($node['starred'][0]['.value']))
				$keyword->starred = $node['starred'][0]['.value'];
			else $keyword->starred = 0;
			$keyword->name = $node['name'][0]['.value'];
			$keyword->description = $node['description'][0]['.value'];
			$keyword->descriptionEditor = isset($node['description'][0]['.attributes']['editor']) ? $node['description'][0]['.attributes']['editor'] : getDefaultEditor();
			$keyword->descriptionFormatter = isset($node['description'][0]['.attributes']['formatter']) ? $node['description'][0]['.attributes']['formatter'] : getDefaultFormatter();
			$keyword->published = intval($node['published'][0]['.value']);
			$keyword->created = @$node['created'][0]['.value'];
			$keyword->modified = @$node['modified'][0]['.value'];
			if (floatval(Setting::getServiceSettingGlobal('newlineStyle')) >= 1.1 && floatval(@$node['.attributes']['format']) < 1.1)
				$keyword->description = nl2brWithHTML($keyword->description);
			if (!$keyword->add())
				user_error(__LINE__ . $keyword->error);
			if (isset($node['attachment'])) {
				for ($i = 0; $i < count($node['attachment']); $i++) {
					$attachment = new Attachment();
					$attachment->parent = $keyword->id;
					$cursor = & $node['attachment'][$i];
					$attachment->name = $cursor['name'][0]['.value'];
					$attachment->label = $cursor['label'][0]['.value'];
					$attachment->mime = @$cursor['.attributes']['mime'];
					$attachment->size = $cursor['.attributes']['size'];
					$attachment->width = $cursor['.attributes']['width'];
					$attachment->height = $cursor['.attributes']['height'];
					$attachment->enclosure = @$cursor['enclosure'][0]['.value'];
					$attachment->attached = $cursor['attached'][0]['.value'];
					$attachment->downloads = @$cursor['downloads'][0]['.value'];
					if (Attachment::doesExist($attachment->name)) {
						if (!$attachment->add())
							user_error(__LINE__ . $attachment->error);
						$keyword2 = new Keyword();
						if ($keyword2->open($keyword->id, 'id, content')) {
							$keyword2->content= str_replace($cursor['name'][0]['.value'], $attachment->name, $keyword2->content);
							$keyword2->update();
							$keyword2->close();
						}
						unset($keyword2);
					} else {
						if (!$attachment->add())
							user_error(__LINE__ . $attachment->error);
					}
					if (!empty($cursor['content'][0]['.stream'])) {
						Utils_Base64Stream::decode($cursor['content'][0]['.stream'], Path::combine(ROOT, 'attach', $blogid, $attachment->name));
						Attachment::adjustPermission(Path::combine(ROOT, 'attach', $blogid, $attachment->name));
						fclose($cursor['content'][0]['.stream']);
						unset($cursor['content'][0]['.stream']);
					}
				}
			}
			return true;
		case '/blog/linkCategories':
			setProgress($item++ / $items * 100, _t('링크 카테고리를 복원하고 있습니다.'));
			$linkCategory = new LinkCategories();
			$linkCategory->name = $node['name'][0]['.value'];
			$linkCategory->priority = $node['priority'][0]['.value'];
			$linkCategory->visibility = !isset($node['visibility'][0]['.value']) || empty($node['visibility'][0]['.value'])
				? 2 : $node['visibility'][0]['.value'];

			$linkCategory->id = LinkCategories::getId($linkCategory->name);
			if ($linkCategory->id) {
				if (!$linkCategory->update())
					user_error(__LINE__ . $linkCategory->error);
			} else {
				if (!$linkCategory->add())
					user_error(__LINE__ . $linkCategory->error);
			}
			return true;
		case '/blog/link':
			setProgress($item++ / $items * 100, _t('링크를 복원하고 있습니다.'));
			$link = new Link();
			$link->category = empty($node['category'][0]['.value']) ? 0 : $node['category'][0]['.value'];
			$link->url = $node['url'][0]['.value'];
			$link->title = $node['title'][0]['.value'];
			if (!empty($node['feed'][0]['.value']))
				$link->feed = $node['feed'][0]['.value'];
			if (!empty($node['registered'][0]['.value']))
				$link->registered = $node['registered'][0]['.value'];
			if (!empty($node['xfn'][0]['.value']))
				$link->xfn = $node['xfn'][0]['.value'];
			$link->id = Link::getId($link->url);
			if ($link->id) {
				if (!$link->update())
					user_error(__LINE__ . $link->error);
			} else {
				if (!$link->add())
					user_error(__LINE__ . $link->error);
			}
			return true;
		case '/blog/logs/referer':
			setProgress($item++ / $items * 100, _t('리퍼러 로그를 복원하고 있습니다.'));
			$log = new RefererLog();
			if (isset($node['path'][0]['.value']))
				$log->url = $node['path'][0]['.value'];
			else
				$log->url = $node['url'][0]['.value'];
			$log->referred = $node['referred'][0]['.value'];
			if (!$log->add(false))
				user_error(__LINE__ . $log->error);
			return true;
		case '/blog/commentsNotified/comment':
			setProgress($item++ / $items * 100, _t('댓글 알리미 내용을 복원하고 있습니다.'));
			$cmtNotified = new CommentNotified();
			$cmtNotified->id = $node['id'][0]['.value'];
			$cursor = & $node['commenter'][0];
			$cmtNotified->name = $cursor['name'][0]['.value'];
			$cmtNotified->homepage = $cursor['homepage'][0]['.value'];
			$cmtNotified->ip = $cursor['ip'][0]['.value'];
			$cmtNotified->entry = $node['entry'][0]['.value'];
			$cmtNotified->password = $node['password'][0]['.value'];
			$cmtNotified->content = $node['content'][0]['.value'];
			$cmtNotified->parent = $node['parent'][0]['.value'];
			$cmtNotified->secret = $node['secret'][0]['.value'];
			$cmtNotified->written = $node['written'][0]['.value'];
			$cmtNotified->modified = $node['modified'][0]['.value'];
			$cmtNotified->url = $node['url'][0]['.value'];
			$cmtNotified->isnew = $node['isNew'][0]['.value'];
			$site = new CommentNotifiedSiteInfo();
			if (!$site->open("url = '{$node['site'][0]['.value']}'")) {
				$site->title = '';
				$site->name = '';
				$site->modified = 31536000;
				$site->url = $node['site'][0]['.value'];
				$site->add();
			}
			$cmtNotified->siteid = $site->id;
			$site->close();
			$cmtNotified->remoteid = $node['remoteId'][0]['.value'];
			$cmtNotified->entrytitle = (!isset($node['entryTitle'][0]['.value']) || empty($node['entryTitle'][0]['.value'])) ? 'No title' : $node['entryTitle'][0]['.value'];
			$cmtNotified->entryurl = $node['entryUrl'][0]['.value'];
			if (!$cmtNotified->add())
				user_error(__LINE__ . $cmtNotified->error);
			return true;
		case '/blog/commentsNotifiedSiteInfo/site':
			setProgress($item++ / $items * 100, _t('댓글 알리미 내용을 복원하고 있습니다.'));
			$cmtNotifiedSite = new CommentNotifiedSiteInfo();
			if ($cmtNotifiedSite->open("url = '{$node['url'][0]['.value']}'")) {
				if (intval($node['modified'][0]['.value']) > intval($cmtNotifiedSite->modified)) {
					$cmtNotifiedSite->title = $node['title'][0]['.value'];
					$cmtNotifiedSite->name = $node['name'][0]['.value'];
					$cmtNotifiedSite->modified = $node['modified'][0]['.value'];
				}
				if (!$cmtNotifiedSite->update())
					user_error(__LINE__ . $cmtNotifiedSite->error);
			} else {
				$cmtNotifiedSite->url = $node['url'][0]['.value'];
				$cmtNotifiedSite->title = $node['title'][0]['.value'];
				$cmtNotifiedSite->name = $node['name'][0]['.value'];
				$cmtNotifiedSite->modified = $node['modified'][0]['.value'];
				if (!$cmtNotifiedSite->add())
					user_error(__LINE__ . $cmtNotifiedSite->error);
			}
			return true;
		case '/blog/statistics/referer':
			setProgress($item++ / $items * 100, _t('리퍼러 통계를 복원하고 있습니다.'));
			$statistics = new RefererStatistics();
			$statistics->host = $node['host'][0]['.value'];
			$statistics->count = $node['count'][0]['.value'];
			if (!$statistics->add())
				user_error(__LINE__ . $statistics->error);
			return true;
		case '/blog/statistics/visits':
			setProgress($item++ / $items * 100, _t('블로그 통계 정보를 복원하고 있습니다.'));
			$statistics = new BlogStatistics();
			$statistics->visits = $node['.value'];
			if (!$statistics->add())
				user_error(__LINE__ . $statistics->error);
			return true;
		case '/blog/statistics/daily':
			setProgress($item++ / $items * 100, _t('일별 통계 정보를 복원하고 있습니다.'));
			$statistics = new DailyStatistics();
			$statistics->date = $node['date'][0]['.value'];
			$statistics->visits = $node['visits'][0]['.value'];
			if (!$statistics->add())
				user_error(__LINE__ . $statistics->error);
			return true;
		case '/blog/skin':
			setProgress($item++ / $items * 100, _t('스킨 설정을 복원하고 있습니다.'));
			$setting = new SkinSetting();
			if (false) {
				$setting->skin = $node['name'][0]['.value'];
				if (!$setting->save())
					user_error(__LINE__ . $setting->error);
				$setting->skin = null;
			}
			$setting->entriesOnRecent = $node['entriesOnRecent'][0]['.value'];
			$setting->commentsOnRecent = $node['commentsOnRecent'][0]['.value'];
			$setting->trackbacksOnRecent = $node['trackbacksOnRecent'][0]['.value'];
			$setting->commentsOnGuestbook = $node['commentsOnGuestbook'][0]['.value'];
			$setting->tagsOnTagbox = $node['tagsOnTagbox'][0]['.value'];
			$setting->alignOnTagbox = $node['alignOnTagbox'][0]['.value'];
			$setting->expandComment = $node['expandComment'][0]['.value'];
			$setting->expandTrackback = $node['expandTrackback'][0]['.value'];
			if (!empty($node['recentNoticeLength'][0]['.value']))
				$setting->recentNoticeLength = $node['recentNoticeLength'][0]['.value'];
			$setting->recentEntryLength = $node['recentEntryLength'][0]['.value'];
			$setting->recentTrackbackLength = $node['recentTrackbackLength'][0]['.value'];
			$setting->linkLength = $node['linkLength'][0]['.value'];
			$setting->showListOnCategory = $node['showListOnCategory'][0]['.value'];
			$setting->showListOnArchive = $node['showListOnArchive'][0]['.value'];
			if (isset($node['tree'])) {
				$cursor = & $node['tree'][0];
				$setting->tree = $cursor['name'][0]['.value'];
				$setting->colorOnTree = $cursor['color'][0]['.value'];
				$setting->bgcolorOnTree = $cursor['bgColor'][0]['.value'];
				$setting->activecolorOnTree = $cursor['activeColor'][0]['.value'];
				$setting->activebgcolorOnTree = $cursor['activeBgColor'][0]['.value'];
				$setting->labelLengthOnTree = $cursor['labelLength'][0]['.value'];
				$setting->showValueOnTree = $cursor['showValue'][0]['.value'];
			}
			if (!$setting->save())
				user_error(__LINE__ . $setting->error);
			return true;
		case '/blog/plugin':
//			setProgress($item++ / $items * 100, _t('플러그인 설정을 복원하고 있습니다.'));
//			$setting = new PluginSetting();
//			$setting->name = $node['name'][0]['.value'];
//			$setting->setting = $node['setting'][0]['.value'];
//			if (!$setting->add())
//				user_error(__LINE__ . $setting->error);
			return true;
		case '/blog/personalization':
//			setProgress($item++ / $items * 100, _t('사용자 편의 설정을 복원하고 있습니다.'));
//			$setting = new UserSetting();
//			$setting->name = 'rowsPerPage';
//			$setting->value = $node['rowsPerPage'][0]['.value'];
//			if (!$setting->add())
//				user_error(__LINE__ . $setting->error);
//			$setting->name = 'readerPannelVisibility';
//			$setting->value = $node['readerPannelVisibility'][0]['.value'];
//			if (!$setting->add())
//				user_error(__LINE__ . $setting->error);
//			$setting->name = 'readerPannelHeight';
//			$setting->value = $node['readerPannelHeight'][0]['.value'];
//			if (!$setting->add())
//				user_error(__LINE__ . $setting->error);
//			$setting->name = 'lastVisitNotifiedPage';
//			$setting->value = $node['lastVisitNotifiedPage'][0]['.value'];
//			if (!$setting->add())
//				user_error(__LINE__ . $setting->error);
			return true;
		case '/blog/userSetting':
//			setProgress($item++ / $items * 100, _t('사용자 편의 설정을 복원하고 있습니다'));
//			$setting = new UserSetting();
//			$setting->name = $node['name'][0]['.value'];
//			$setting->value = $node['value'][0]['.value'];
//			if (!$setting->add())
//				user_error(__LINE__ . $setting->error);
			return true;
		case '/blog/guestbook/comment':
			setProgress($item++ / $items * 100, _t('방명록을 복원하고 있습니다.'));
			$comment = new GuestComment();
			$comment->name = $node['commenter'][0]['name'][0]['.value'];
			if (!empty($node['id'][0]['.value']))
				$comment->id = $node['id'][0]['.value'];
			if (!empty($node['commenter'][0]['.attributes']['id']))
				$comment->commenter = $node['commenter'][0]['.attributes']['id'];
			if (!empty($node['commenter'][0]['homepage'][0]['.value']))
				$comment->homepage = $node['commenter'][0]['homepage'][0]['.value'];
			if (!empty($node['commenter'][0]['ip'][0]['.value']))
				$comment->ip = $node['commenter'][0]['ip'][0]['.value'];
			if (!empty($node['commenter'][0]['openid'][0]['.value']))
				$comment->openid = $node['commenter'][0]['openid'][0]['.value'];
			$comment->password = $node['password'][0]['.value'];
			$comment->secret = @$node['secret'][0]['.value'];
			$comment->written = $node['written'][0]['.value'];
			$comment->content = $node['content'][0]['.value'];
			if (!$comment->add())
				user_error(__LINE__ . $comment->error);
			if (isset($node['comment'])) {
				for ($j = 0; $j < count($node['comment']); $j++) {
					$childComment = new GuestComment();
					$childComment->parent = $comment->id;
					$cursor = & $node['comment'][$j];
					$childComment->name = $cursor['commenter'][0]['name'][0]['.value'];
					if (!empty($cursor['id'][0]['.value']))
						$comment->id = $cursor['id'][0]['.value'];
					if (!empty($cursor['commenter'][0]['.attributes']['id']))
						$childComment->commenter = $cursor['commenter'][0]['.attributes']['id'];
					if (!empty($cursor['commenter'][0]['homepage'][0]['.value']))
						$childComment->homepage = $cursor['commenter'][0]['homepage'][0]['.value'];
					if (!empty($cursor['commenter'][0]['ip'][0]['.value']))
						$childComment->ip = $cursor['commenter'][0]['ip'][0]['.value'];
					if (!empty($cursor['commenter'][0]['openid'][0]['.value']))
						$childComment->openid = $cursor['commenter'][0]['openid'][0]['.value'];
					$childComment->password = $cursor['password'][0]['.value'];
					$childComment->secret = @$cursor['secret'][0]['.value'];
					$childComment->written = $cursor['written'][0]['.value'];
					$childComment->content = $cursor['content'][0]['.value'];
					if (!$childComment->add())
						user_error(__LINE__ . $childComment->error);
				}
			}
			return true;
		case '/blog/filter':
			setProgress($item++ / $items * 100, _t('필터 설정을 복원하고 있습니다.'));
			$filter = new Filter();
			$filter->type = $node['.attributes']['type'];
			$filter->pattern = $node['pattern'][0]['.value'];
			if (!$filter->add())
				user_error(__LINE__ . $filter->error);
			return true;
		case '/blog/feed':
			setProgress($item++ / $items * 100, _t('리더 데이터를 복원하고 있습니다.'));
			$feed = new Feed();
			if (!empty($node['group'][0]['.value']))
				$feed->group = FeedGroup::getId($node['group'][0]['.value'], true);
			$feed->url = $node['url'][0]['.value'];
			if (!$feed->add())
				user_error(__LINE__ . $feed->error);
			return true;
		case '/blog/line':
			setProgress($item++ / $items * 100, _t('라인을 복원하고 있습니다.'));
			$line = Model_Line::getInstance();
			$line->reset();
			if (!empty($node['author'][0]['.value']))
				$line->author = $node['author'][0]['.value'];
			if (!empty($node['category'][0]['.value']))
				$line->category = $node['category'][0]['.value'];
			if (!empty($node['root'][0]['.value']))
				$line->root = $node['root'][0]['.value'];
			if (!empty($node['permalink'][0]['.value']))
				$line->permalink = $node['permalink'][0]['.value'];
			if (!empty($node['content'][0]['.value']))
				$line->content = $node['content'][0]['.value'];
			if (!empty($node['created'][0]['.value']))
				$line->created = intval($node['created'][0]['.value']);
			if ($line->add()) {
				return true;
			} else {
				user_error(__LINE__ . $line->_error);
			}
	}
}

?>
