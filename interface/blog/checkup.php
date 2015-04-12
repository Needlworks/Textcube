<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/preprocessor.php';
$codeCache = new CodeCache();
$codeCache->flush();

importlib('blogskin');
importlib('model.blog.skin');
importlib('model.common.setting');
importlib('model.blog.entry');
importlib('model.blog.trash');
importlib('model.blog.version');

$currentVersion = getBlogVersion();

function setSkinSettingForMigration($blogid, $name, $value, $mig = null) {
	global $database;
	$name = POD::escapeString($name);
	$value = POD::escapeString($value);
	if($mig === null)
		return POD::execute("REPLACE INTO {$database['prefix']}SkinSettingsMig VALUES('$blogid', '$name', '$value')");
	else
		return POD::execute("REPLACE INTO {$database['prefix']}SkinSettings VALUES('$blogid', '$name', '$value')");
}

function getSkinSettingForMigration($blogid, $name, $default = null) {
	global $database;
	$value = POD::queryCell("SELECT value
		FROM {$database['prefix']}SkinSettingsMig
		WHERE blogid = '$blogid'
		AND name = '".POD::escapeString($name)."'");
	return ($value === null) ? $default : $value;
}

function showCheckupMessage($stat = true) {
	global $succeed;
	if($stat) {
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		$succeed = false;
		echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}

function clearCache() {
	global $database, $changed, $errorlog, $memcache;
	static $isCleared = false;
	$context = Model_Context::getInstance();
	if($isCleared == true) return;
	if(!is_null($blogids = POD::queryColumn("SELECT blogid FROM {$database['prefix']}PageCacheLog"))) {
		$changed = true;
		$errorlog = false;
		echo '<li>', _textf('페이지 캐시를 초기화합니다.'), ': ';
		foreach($blogids as $ids) {
			if(CacheControl::flushAll($ids) == false) $errorlog = true;
		}
		if($errorlog == false) echo '<span class="result success">', _text('성공'), '</span></li>';
		else echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
	if($context->getProperty('service.codecache',false)) {
		$changed = true;
		$errorlog = false;
		echo '<li>', _textf('코드 캐시를 초기화합니다.'), ': ';
		$code = new CodeCache();
		$code->flush();
		if($errorlog == false) echo '<span class="result success">', _text('성공'), '</span></li>';
		else echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
	if(!is_null($memcache)) {
		echo '<li>', _textf('Memcached 캐시를 초기화합니다.'), ': ';
		if($memcache->flush())  echo '<span class="result success">', _text('성공'), '</span></li>';
		else echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
	echo '<li>', _textf('공지사항 캐시를 초기화합니다.'), ': ';
	if(POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name like 'TextcubeNotice%'"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else echo '<span class="result fail">', _text('실패'), '</span></li>';
	if(!is_null($blogids = POD::queryColumn("SELECT DISTINCT blogid FROM {$database['prefix']}BlogSettings"))) {
		$changed = true;
		$errorlog = false;
		echo '<li>', _textf('댓글 및 트랙백 휴지통을 비웁니다.'), ': ';
		foreach($blogids as $ids) {
			emptyTrash(true,$ids);
			emptyTrash(false,$ids);
		}
		if($errorlog == false) echo '<span class="result success">', _text('성공'), '</span></li>';
		else echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
	$isCleared = true;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo _text('텍스트큐브를 점검합니다.');?></title>
	<link rel="stylesheet" media="screen" type="text/css" href="<?php echo $context->getProperty('service.path')?>/resources/style/setup/style.css" />
</head>
<body>
	<div id="container">
		<form id="checkup">
			<div id="title">
				<h1><img src="<?php echo $context->getProperty('service.path')?>/resources/style/setup/image/title.gif" width="253" height="44" alt="텍스트큐브를 점검합니다." /></h1>
			</div>

			<div id="inner">
				<h2><?php echo _text('텍스트큐브 점검을 시작합니다.');?></h2>

				<div id="content">
					<h3><?php echo _text('버전 검사');?></h3>

					<ul class="version">
						<li><?php echo _textf('기존 버전 - %1',$currentVersion);?></li>
						<li><?php echo _textf('현재 버전 - %1',TEXTCUBE_VERSION);?></li>
					</ul>
<?php
	if(version_compare($currentVersion,'1.9.0','<')) {
?>
					<h3><?php echo _text('업그레이드 안내');?></h3>
					<ul id="upgradeInstruction">
						<li class="instruction"><?php echo _text('텍스트큐브 2.0으로 업그레이드하기 위해서는 먼저 텍스트큐브 1.9 이상의 버전으로 업그레이드해야 합니다.');?></li>
						<li class="detail"><?php echo _textf('설치된 버전 : %1',$currentVersion);?></li>
					</ul>

<?php
	}
?>
					<h3><?php echo _text('변경 중');?></h3>

					<ul id="processList">
<?php
$changed = false;
global $succeed;
$succeed = true;
if($currentVersion != TEXTCUBE_VERSION && in_array(POD::dbms(),array('MySQL','MySQLi'))) {
	/* From Textcube 1.9 */
	if (version_compare($currentVersion, '1.9.1','<')) {
		$changed = true;
		echo '<li>', _text('기본 에디터를 변경합니다.'), ': ';
		$query = DBModel::getInstance();
		$query->reset('BlogSettings');
		$query->setQualifier('name','equals','defaultEditor',true);
		$query->setQualifier('value','equals','modern',true);
		$query->setAttribute('value','tinyMCE',true);
		if($query->update())
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}
	/* From Textcube 2.0 */
	$result = DBAdapter::queryAll("SELECT blogid, userid, id FROM {$database['prefix']}Entries WHERE contentformatter='ttml' and contenteditor='modern'");
	if ($result) {
		$changed = true;
		echo '<li>', _text('기존 에디터로 작성된 글을 새 에디터로 편집 가능하도록 이전합니다.'), ': ';
		if (DBAdapter::execute("UPDATE {$database['prefix']}Entries SET contentformatter='ttml', contenteditor='tinyMCE' WHERE contentformatter='ttml' and contenteditor='modern'"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}

	if (!DBAdapter::queryExistence("DESC {$database['prefix']}Sessions expires")) {
		$changed = true;
		echo '<li>', _text('자동 로그인을 위해 세션 테이블 구조를 수정합니다.'), ': ';
		if (DBAdapter::execute("ALTER TABLE {$database['prefix']}Sessions ADD expires int(11) NOT NULL DEFAULT 0 AFTER updated"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}
}

/***** Common parts. *****/
if(doesHaveOwnership()) clearCache();

if (!defined('__TEXTCUBE_GAE__')) {
	$filename = ROOT . '/.htaccess';
	$fp = fopen($filename, "r");
	$content = fread($fp, filesize($filename));
	fclose($fp);
	if ((preg_match('@rewrite\.php@', $content) == 0 ) ||
			(strpos($content,'[OR]') !== false) ||
			(strpos($content,' -d') == false) ||
			(strpos($content,'(cache|xml|txt|log)') == false)
			) {
		echo '<li>', _textf('htaccess 규칙을 수정합니다.'), ': ';
		$fp = fopen($filename.'_backup_'.Timestamp::format('%Y%m%d'), "w");
		fwrite($fp,$content);
		fclose($fp);
		$content =
"#<IfModule mod_url.c>
#CheckURL Off
#</IfModule>
#SetEnv PRELOAD_CONFIG 1
RewriteEngine On
RewriteBase ".$context->getProperty('service.path')."/
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^(cache)+/+(.+[^/])\\.(cache|xml|txt|log)$ - [NC,F,L]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^(.+[^/])$ $1/ [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(thumbnail)/([0-9]+/.+)$ cache/$1/$2 [L]
RewriteRule ^(.*)$ rewrite.php [L,QSA]
";
		$fp = fopen($filename, "w");
		if(fwrite($fp, $content)) {
			fclose($fp);
			showCheckupMessage(true);
		} else {
			fclose($fp);
			showCheckupMessage(false);
		}
	}
}

if ($currentVersion != TEXTCUBE_VERSION && $succeed == true) {
	setBlogVersion();
	clearCache();
}
?>
					</ul>

					<p id="lastMessage">
						<?php
	reloadSkin(1);
	echo ($changed ? _text('완료되었습니다.') : _text('확인되었습니다.'));
?>
					</p>
				</div>

				<div id="navigation">
					<a href="<?php echo $context->getProperty('uri.blog').'/owner/center/dashboard';?>"><img src="<?php echo $context->getProperty('service.path')?>/resources/style/setup/image/icon_ok.gif" width="74" height="24" alt="돌아가기" /></a>
				</div>
			</div>
		</form>
	</div>
</body>
</html>
