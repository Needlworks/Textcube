<?php
ini_set('display_errors', 'off');
define('ROOT', '../../../..');
$IV = array(
	'POST' => array(
		'importFrom' => array(array('server', 'uploaded', 'web')),
		'backupURL' => array('url', 'mandatory' => false),
		'correctData' => array(array('on'), 'mandatory' => false)
	),
	'FILES' => array(
		'backupPath' => array('file', 'mandatory' => false)
	)
);
require ROOT . '/lib/includeForOwner.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title>Tattertools Data Importing</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
	global $migrational;
?>
	<script type="text/javascript">
		//<![CDATA[
<?php
	if ($error) {
?>
			//pi.style.backgroundColor = "red";
			alert("<?php echo $error?>");
<?php
	} else {
?>
			alert("<?php echo ($migrational ? _t('성공적으로 이주되었습니다.') : _t('성공적으로 복원되었습니다.'))?>");
<?php
	}
?>
			window.parent.document.getElementById("progressDialog").style.display = "none";
			window.parent.document.getElementById("progressDialogTitle").innerHTML = "";
			window.parent.document.getElementById("progressText").innerHTML = "";
			window.parent.document.getElementById("progressTextSub").innerHTML = "";
		//]]>
	</script>
	<?php echo _t('완료.')?>
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
		<?php echo $diff?>
	//]]>
</script>
<?php
		flush();
	}
}
requireComponent('Eolin.PHP.OutputWriter');
requireComponent('Eolin.PHP.Base64Stream');
switch (@$_POST['importFrom']) {
	default:
		finish(_t('잘못된 요청입니다.'));
		break;
	case 'server':
		$backup = ROOT . "/cache/backup/$owner.xml";
		break;
	case 'uploaded':
		if (@$_FILES['backupPath']['error'] !== 0)
			finish(_t('업로드가 취소되었거나 업로드 용량 제한을 초과하였습니다.'));
		$backup = $_FILES['backupPath']['tmp_name'];
		break;
	case 'web':
		if (!file_exists(ROOT . '/cache/import')) {
			mkdir(ROOT . '/cache/import');
			@chmod(ROOT . '/cache/import', 0777);
		}
		if (!is_dir(ROOT . '/cache/import')) {
			finish(_t('백업파일을 저장할 공간에 권한이 없습니다.'));
		}
		requireComponent('Eolin.PHP.HTTPRequest');
		$request = new HTTPRequest($_POST['backupURL']);
		$backup = ROOT . "/cache/import/$owner.xml";
		$request->pathToSave = $backup;
		if (!$request->send()) {
			finish(_t('백업파일이 손상되었거나 가져올 수 없습니다.'));
		}
		break;
}
requireComponent('Tattertools.Data.DataMaintenance');
requireComponent('Tattertools.Data.BlogSetting');
requireComponent('Tattertools.Data.Category');
requireComponent('Tattertools.Data.Post');
requireComponent('Tattertools.Data.Attachment');
requireComponent('Tattertools.Data.Tag');
requireComponent('Tattertools.Data.Comment');
requireComponent('Tattertools.Data.Trackback');
requireComponent('Tattertools.Data.TrackbackLog');
requireComponent('Tattertools.Data.Notice');
requireComponent('Tattertools.Data.Keyword');
requireComponent('Tattertools.Data.Link');
requireComponent('Tattertools.Data.RefererLog');
requireComponent('Tattertools.Data.RefererStatistics');
requireComponent('Tattertools.Data.BlogStatistics');
requireComponent('Tattertools.Data.DailyStatistics');
requireComponent('Tattertools.Data.SkinSetting');
requireComponent('Tattertools.Data.PluginSetting');
requireComponent('Tattertools.Data.Filter');
requireComponent('Tattertools.Data.GuestComment');
requireComponent('Tattertools.Data.Feed');
requireComponent('Tattertools.Data.ServiceSetting');
requireComponent('Tattertools.Data.UserSetting');
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
}
$acceptNewLineOnEditor = false;
$xmls->setConsumer('importer');
if (!$xmls->openFile($backup, Validator::getBool(@$_POST['correctData']))) {
	finish(_t('백업파일이 올바르지 않습니다.'));
}
if(!$acceptNewLineOnEditor) {
	$query = new TableQuery($database['prefix'] . 'Entries');
	if ($entries = $query->getAll('owner, id, draft, content')) {
		foreach($entries as $entry) {
			$newContent = mysql_escape_string(nl2brWithHTML($entry['content']));
			DBQuery::execute("UPDATE {$database['prefix']}Entries SET content = '$newContent' WHERE owner = {$entry['owner']} AND id = {$entry['id']} AND draft = {$entry['draft']}");
		}
	}
	setServiceSetting('acceptNewLineOnEditor', '1');
}
$xmls->close();
if (file_exists(ROOT . "/cache/import/$owner.xml"))
	@unlink(ROOT . "/cache/import/$owner.xml");
setProgress(100, _t('완료되었습니다.'));
finish();

/*@callback@*/
function scanner($path, $node, $line) {
	global $migrational, $items, $acceptNewLineOnEditor;
	switch ($path) {
		case '/blog':
			if (@$node['.attributes']['type'] != 'tattertools/1.0')
				finish(_t('지원하지 않는 백업파일입니다.'));
			$migrational = Validator::getBool(@$node['.attributes']['migrational']);
			return true;
		case '/blog/setting/banner/content':case '/blog/post/attachment/content':case '/blog/notice/attachment/content':case '/blog/keyword/attachment/content':
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
		case '/blog/personalization':
		case '/blog/guestbook/comment':
		case '/blog/filter':
		case '/blog/feed':
		case '/blog/userSetting':
		case '/blog/serviceSetting':		
			$items++;
			if (!strpos($path, 'referer'))
				setProgress(null, _t('백업파일을 확인하고 있습니다.'), $line);
			return true;
	}
}

/*@callback@*/
function importer($path, $node, $line) {
	global $owner, $migrational, $items, $item;
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
			if (isset($node['useSlogan'][0]['.value']))
				$setting->useSlogan = $node['useSlogan'][0]['.value'];
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
			if (isset($node['acceptCommentOnGuestComment'][0]['.value']))
				$setting->acceptCommentOnGuestComment = $node['acceptCommentOnGuestComment'][0]['.value'];
			if (isset($node['language'][0]['.value']))
				$setting->language = $node['language'][0]['.value'];
			if (isset($node['timezone'][0]['.value']))
				$setting->timezone = $node['timezone'][0]['.value'];
			if (!$setting->save())
				user_error(__LINE__ . $setting->error);
			if (!empty($setting->banner) && !empty($node['banner'][0]['content'][0]['.stream'])) {
				Attachment::confirmFolder();
				Base64Stream::decode($node['banner'][0]['content'][0]['.stream'], Path::combine(ROOT, 'attach', $owner, $setting->banner));
				Attachment::adjustPermission(Path::combine(ROOT, 'attach', $owner, $setting->banner));
				fclose($node['banner'][0]['content'][0]['.stream']);
				unset($node['banner'][0]['content'][0]['.stream']);
			}
			return true;
		case '/blog/category':
			setProgress($item++ / $items * 100, _t('분류를 복원하고 있습니다.'));
			$category = new Category();
			$category->name = $node['name'][0]['.value'];
			$category->priority = $node['priority'][0]['.value'];
			if (!$category->add())
				user_error(__LINE__ . $category->error);
			if (isset($node['category'])) {
				for ($i = 0; $i < count($node['category']); $i++) {
					$childCategory = new Category();
					$childCategory->parent = $category->id;
					$cursor = & $node['category'][$i];
					$childCategory->name = $cursor['name'][0]['.value'];
					$childCategory->priority = $cursor['priority'][0]['.value'];
					if (!$childCategory->add())
						user_error(__LINE__ . $childCategory->error);
				}
			}
			return true;
		case '/blog/post':
			setProgress($item++ / $items * 100, _t('글을 복원하고 있습니다.'));
			$post = new Post();
			$post->id = $node['id'][0]['.value'];
			$post->slogan = @$node['.attributes']['slogan'];
			$post->visibility = $node['visibility'][0]['.value'];
			$post->title = $node['title'][0]['.value'];
			$post->content = $node['content'][0]['.value'];
			$post->location = $node['location'][0]['.value'];
			$post->password = isset($node['password'][0]['.value']) ? $node['password'][0]['.value'] : null;
			$post->acceptComment = $node['acceptComment'][0]['.value'];
			$post->acceptTrackback = $node['acceptTrackback'][0]['.value'];
			$post->published = $node['published'][0]['.value'];
			$post->created = @$node['created'][0]['.value'];
			$post->modified = @$node['modified'][0]['.value'];
			if (!empty($node['category'][0]['.value']))
				$post->category = Category::getId($node['category'][0]['.value']);
			if (!$post->add())
				user_error(__LINE__ . $post->error);
			if (isset($node['tag'])) {
				$post->tags = array();
				for ($i = 0; $i < count($node['tag']); $i++) {
					if (!empty($node['tag'][$i]['.value']))
						array_push($post->tags, $node['tag'][$i]['.value']);
				}
				if (!empty($post->tags)) {
					if (!$post->saveTags())
						user_error(__LINE__ . $post->error);
				}
			}
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
							$post2->update();
							$post2->close();
						}
						unset($post2);
					}
					if (!empty($cursor['content'][0]['.stream'])) {
						Base64Stream::decode($cursor['content'][0]['.stream'], Path::combine(ROOT, 'attach', $owner, $attachment->name));
						Attachment::adjustPermission(Path::combine(ROOT, 'attach', $owner, $attachment->name));
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
					if (!empty($cursor['commenter'][0]['.attributes']['id']) && !empty($cursor['commenter'][0]['.attributes']['email'])) {
						if (User::getEmail($cursor['commenter'][0]['.attributes']['id']) == $cursor['commenter'][0]['.attributes']['email'])
							$comment->commenter = $cursor['commenter'][0]['.attributes']['id'];
					}
					$comment->name = $cursor['commenter'][0]['name'][0]['.value'];
					if (!empty($cursor['commenter'][0]['homepage'][0]['.value']))
						$comment->homepage = $cursor['commenter'][0]['homepage'][0]['.value'];
					if (!empty($cursor['commenter'][0]['ip'][0]['.value']))
						$comment->ip = $cursor['commenter'][0]['ip'][0]['.value'];
					$comment->password = $cursor['password'][0]['.value'];
					$comment->secret = $cursor['secret'][0]['.value'];
					$comment->written = $cursor['written'][0]['.value'];
					$comment->content = $cursor['content'][0]['.value'];
					if (!empty($cursor['isFiltered'][0]['.value']))
					    	$comment->isFiltered = $cursor['isFiltered'][0]['.value'];
					if (!$comment->add())
						user_error(__LINE__ . $comment->error);
					if (isset($node['comment'][$i]['comment'])) {
						for ($j = 0; $j < count($node['comment'][$i]['comment']); $j++) {
							$childComment = new Comment();
							$childComment->entry = $post->id;
							$childComment->parent = $comment->id;
							$cursor = & $node['comment'][$i]['comment'][$j];
							if (!empty($cursor['commenter'][0]['.attributes']['id']) && !empty($cursor['commenter'][0]['.attributes']['email'])) {
								if (User::getEmail($cursor['commenter'][0]['.attributes']['id']) == $cursor['commenter'][0]['.attributes']['email'])
									$childComment->commenter = $cursor['commenter'][0]['.attributes']['id'];
							}
							$childComment->name = $cursor['commenter'][0]['name'][0]['.value'];
							if (!empty($cursor['commenter'][0]['homepage'][0]['.value']))
								$childComment->homepage = $cursor['commenter'][0]['homepage'][0]['.value'];
							if (!empty($cursor['commenter'][0]['ip'][0]['.value']))
								$childComment->ip = $cursor['commenter'][0]['ip'][0]['.value'];
							$childComment->password = $cursor['password'][0]['.value'];
							$childComment->secret = $cursor['secret'][0]['.value'];
							$childComment->written = $cursor['written'][0]['.value'];
							$childComment->content = $cursor['content'][0]['.value'];
							if (!empty($cursor['isFiltered'][0]['.value']))
					    			$childComment->isFiltered = $cursor['isFiltered'][0]['.value'];
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
		case '/blog/notice':
			setProgress($item++ / $items * 100, _t('공지를 복원하고 있습니다.'));
			$notice = new Notice();
			$notice->visibility = $node['visibility'][0]['.value'];
			$notice->title = $node['title'][0]['.value'];
			$notice->content = $node['content'][0]['.value'];
			$notice->published = $node['published'][0]['.value'];
			$notice->created = @$node['created'][0]['.value'];
			$notice->modified = @$node['modified'][0]['.value'];
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
						unset($post2);
					} else {
						if (!$attachment->add())
							user_error(__LINE__ . $attachment->error);
					}
					if (!empty($cursor['content'][0]['.stream'])) {
						Base64Stream::decode($cursor['content'][0]['.stream'], Path::combine(ROOT, 'attach', $owner, $attachment->name));
						Attachment::adjustPermission(Path::combine(ROOT, 'attach', $owner, $attachment->name));
						fclose($cursor['content'][0]['.stream']);
						unset($cursor['content'][0]['.stream']);
					}
				}
			}
			return true;
		case '/blog/keyword':
			setProgress($item++ / $items * 100, _t('키워드를 복원하고 있습니다.'));
			$keyword = new Keyword();
			$keyword->visibility = $node['visibility'][0]['.value'];
			$keyword->name = $node['name'][0]['.value'];
			$keyword->description = $node['description'][0]['.value'];
			$keyword->published = $node['published'][0]['.value'];
			$keyword->created = @$node['created'][0]['.value'];
			$keyword->modified = @$node['modified'][0]['.value'];
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
							$keyword2->content = str_replace($cursor['name'][0]['.value'], $attachment->name, $keyword2->content);
							$keyword2->update();
							$keyword2->close();
						}
						unset($post2);
					} else {
						if (!$attachment->add())
							user_error(__LINE__ . $attachment->error);
					}
					if (!empty($cursor['content'][0]['.stream'])) {
						Base64Stream::decode($cursor['content'][0]['.stream'], Path::combine(ROOT, 'attach', $owner, $attachment->name));
						Attachment::adjustPermission(Path::combine(ROOT, 'attach', $owner, $attachment->name));
						fclose($cursor['content'][0]['.stream']);
						unset($cursor['content'][0]['.stream']);
					}
				}
			}
			return true;
		case '/blog/link':
			setProgress($item++ / $items * 100, _t('링크을 복원하고 있습니다.'));
			$link = new Link();
			$link->url = $node['url'][0]['.value'];
			$link->title = $node['title'][0]['.value'];
			if (!empty($node['feed'][0]['.value']))
				$link->feed = $node['feed'][0]['.value'];
			if (!empty($node['registered'][0]['.value']))
				$link->registered = $node['registered'][0]['.value'];
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
				$setting->bgColorOnTree = $cursor['bgColor'][0]['.value'];
				$setting->activeColorOnTree = $cursor['activeColor'][0]['.value'];
				$setting->activeBgColorOnTree = $cursor['activeBgColor'][0]['.value'];
				$setting->labelLengthOnTree = $cursor['labelLength'][0]['.value'];
				$setting->showValueOnTree = $cursor['showValue'][0]['.value'];
			}
			if (!$setting->save())
				user_error(__LINE__ . $setting->error);
			return true;
		case '/blog/plugin':
			setProgress($item++ / $items * 100, _t('플러그인 설정을 복원하고 있습니다.'));
			$setting = new PluginSetting();
			$setting->name = $node['name'][0]['.value'];
			$setting->setting = $node['setting'][0]['.value'];
			if (!$setting->add())
				user_error(__LINE__ . $setting->error);
			return true;
		case '/blog/personalization':
			setProgress($item++ / $items * 100, _t('사용자 편의설정을 복원하고 있습니다.'));
			$setting = new UserSetting();
			$setting->name = 'rowsPerPage';
			$setting->value = $node['rowsPerPage'][0]['.value'];
			if (!$setting->add())
				user_error(__LINE__ . $setting->error);
			$setting->name = 'readerPannelVisibility';
			$setting->value = $node['readerPannelVisibility'][0]['.value'];
			if (!$setting->add())
				user_error(__LINE__ . $setting->error);
			$setting->name = 'readerPannelHeight';
			$setting->value = $node['readerPannelHeight'][0]['.value'];
			if (!$setting->add())
				user_error(__LINE__ . $setting->error);
			$setting->name = 'lastVisitNotifiedPage';
			$setting->value = $node['lastVisitNotifiedPage'][0]['.value'];
			if (!$setting->add())
				user_error(__LINE__ . $setting->error);
			return true;
		case '/blog/userSetting':
			setProgress($item++ / $items * 100, _t('사용자 편의 설정을 복원하고 있습니다'));
			$setting = new UserSetting();
			$setting->name = $node['name'][0]['.value'];
			$setting->value = $node['value'][0]['.value'];
			if (!$setting->add())
				user_error(__LINE__ . $setting->error);
			return true;
		case '/blog/serviceSetting':
			setProgress($item++ / $items * 100, _t('서비스 설정을 복원하고 있습니다'));
			$setting = new ServiceSetting();
			$setting->name = $node['name'][0]['.value'];
			$setting->value = $node['value'][0]['.value'];
			if($setting->name == 'acceptNewLineOnEditor' && $setting->value == '1')
				$acceptNewLineOnEditor = true;
			if (!$setting->add())
				user_error(__LINE__ . $setting->error);
			return true;
		case '/blog/guestbook/comment':
			setProgress($item++ / $items * 100, _t('방명록을 복원하고 있습니다.'));
			$comment = new GuestComment();
			if (!empty($node['commenter'][0]['.attributes']['id']) && !empty($node['commenter'][0]['.attributes']['email'])) {
				if (User::getEmail($node['commenter'][0]['.attributes']['id']) == $node['commenter'][0]['.attributes']['email'])
					$comment->commenter = $node['commenter'][0]['.attributes']['id'];
			}
			$comment->name = $node['commenter'][0]['name'][0]['.value'];
			if (!empty($node['commenter'][0]['homepage'][0]['.value']))
				$comment->homepage = $node['commenter'][0]['homepage'][0]['.value'];
			if (!empty($node['commenter'][0]['ip'][0]['.value']))
				$comment->ip = $node['commenter'][0]['ip'][0]['.value'];
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
					if (!empty($cursor['commenter'][0]['.attributes']['id']) && !empty($cursor['commenter'][0]['.attributes']['email'])) {
						if (User::getEmail($cursor['commenter'][0]['.attributes']['id']) == $cursor['commenter'][0]['.attributes']['email'])
							$childComment->commenter = $cursor['commenter'][0]['.attributes']['id'];
					}
					$childComment->name = $cursor['commenter'][0]['name'][0]['.value'];
					if (!empty($cursor['commenter'][0]['homepage'][0]['.value']))
						$childComment->homepage = $cursor['commenter'][0]['homepage'][0]['.value'];
					if (!empty($cursor['commenter'][0]['ip'][0]['.value']))
						$childComment->ip = $cursor['commenter'][0]['ip'][0]['.value'];
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
			$feed->group = FeedGroup::getId($node['group'][0]['.value'], true);
			$feed->url = $node['url'][0]['.value'];
			if (!$feed->add())
				user_error(__LINE__ . $feed->error);
			return true;
	}
}

/// Optimizer Directives!
if (false) {
	scanner();
	importer();
}
?>
