<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/includeForBlog.php';
require ROOT . '/library/model/blog.skin.php';

requireModel('common.setting');
requireModel('blog.entry');

if(!file_exists(ROOT . '/cache/CHECKUP')) $currentVersion = _text('첫번째 점검');
else $currentVersion = file_get_contents(ROOT . '/cache/CHECKUP');

function setBlogSettingForMigration($blogid, $name, $value, $mig = null) {
	global $database;
	$name = POD::escapeString($name);
	$value = POD::escapeString($value);
	if($mig === null) 
		return POD::execute("REPLACE INTO {$database['prefix']}BlogSettingsMig VALUES('$blogid', '$name', '$value')");
	else
		return POD::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES('$blogid', '$name', '$value')");
}

function getBlogSettingForMigration($blogid, $name, $default = null) {
	global $database;
	$value = POD::queryCell("SELECT value 
		FROM {$database['prefix']}BlogSettingsMig 
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
	global $database, $changed, $errorlog;
	static $isCleared = false;
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

	echo '<li>', _textf('공지사항 캐시를 초기화합니다.'), ': ';
	if(POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name like 'TextcubeNotice%'"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else echo '<span class="result fail">', _text('실패'), '</span></li>';
	$isCleared = true;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo _text('텍스트큐브를 점검합니다.');?></title>
	<link rel="stylesheet" media="screen" type="text/css" href="<?php echo $service['path']?>/style/setup/style.css" />
</head>
<body>
	<div id="container">
		<form id="checkup">
			<div id="title">
				<h1><img src="<?php echo $service['path']?>/style/setup/image/title.gif" width="253" height="44" alt="Textcube를 점검합니다." /></h1>
			</div>

			<div id="inner">
				<h2><?php echo _text('텍스트큐브 점검을 시작합니다.');?></h2>
				
				<div id="content">
					<h3><?php echo _text('버전 검사');?></h3>
					
					<ul class="version">
						<li><?php echo _textf('기존 버전 - %1',$currentVersion);?></li>
						<li><?php echo _textf('현재 버전 - %1',TEXTCUBE_VERSION);?></li>
					</ul>
					
					<h3><?php echo _text('변경 중');?></h3>
					
					<ul id="processList">
<?php
$changed = false;
global $succeed;
$succeed = true;
if($currentVersion != TEXTCUBE_VERSION) {
	// From 1.6
	if (POD::queryCell("DESC {$database['prefix']}CommentsNotified id", 'Extra') == 'auto_increment') {
		$changed = true;
		echo '<li>', _text('데이터베이스 호환성을 위하여 댓글 테이블의 자동 증가 설정을 제거합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Comments CHANGE id id int(11) NOT NULL")
			&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotified CHANGE id id int(11) NOT NULL")
			&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotifiedQueue CHANGE id id int(11) NOT NULL")
			&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotifiedSiteInfo CHANGE id id int(11) NOT NULL"))
			showCheckupMessage(true);
		else {
			showCheckupMessage(false);
		}
	}

	if (POD::queryCell("DESC {$database['prefix']}Trackbacks id", 'Extra') == 'auto_increment') {
		$changed = true;
		echo '<li>', _text('데이터베이스 호환성을 위하여 트랙백 테이블의 자동 증가 설정을 제거합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Trackbacks CHANGE id id int(11) NOT NULL")
			&& POD::execute("ALTER TABLE {$database['prefix']}TrackbackLogs CHANGE id id int(11) NOT NULL"))
			showCheckupMessage(true);
		else {
			showCheckupMessage(false);
		}
	}

	if (POD::queryCell("DESC {$database['prefix']}Comments blogid", 'Key') != 'PRI') {
		$changed = true;
		echo '<li>', _text('데이터베이스 호환성을 위하여 댓글 테이블의 인덱스 설정을 변경합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Comments DROP PRIMARY KEY, ADD PRIMARY KEY (blogid, id)")
			&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotified DROP PRIMARY KEY, ADD PRIMARY KEY (blogid, id)")
			&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotifiedQueue DROP PRIMARY KEY, ADD PRIMARY KEY (blogid, id)")
			&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotifiedSiteInfo DROP INDEX id"))
			showCheckupMessage(true);
		else {
			showCheckupMessage(false);
		}
	}

	if (!doesExistTable($database['prefix'] . 'EntriesArchive')) {
		$changed = true;
		echo '<li>', _text('글 버전 관리및 비교를 위한 테이블을 추가합니다.'), ': ';
		$query = "
		CREATE TABLE {$database['prefix']}EntriesArchive (
			blogid int(11) NOT NULL default '0',
			userid int(11) NOT NULL default '0',
			id int(11) NOT NULL,
			visibility tinyint(4) NOT NULL default '0',
			category int(11) NOT NULL default '0',
			title varchar(255) NOT NULL default '',
			slogan varchar(255) NOT NULL default '',
			content mediumtext NOT NULL,
			contentFormatter varchar(32) DEFAULT '' NOT NULL,
			contentEditor varchar(32) DEFAULT '' NOT NULL,
			location varchar(255) NOT NULL default '/',
			password varchar(32) default NULL,
			created int(11) NOT NULL default '0',
			PRIMARY KEY (blogid, id, created),
			KEY visibility (visibility),
			KEY blogid (blogid, id),
			KEY userid (userid, blogid)
			) TYPE=MyISAM
		";
		if (POD::execute($query . ' DEFAULT CHARSET=utf8') || POD::execute($query))
			showCheckupMessage(true);
		else {
			showCheckupMessage(false);
		}
	}

	if (!doesExistTable($database['prefix'] . 'OpenIDUsers')) {
		$changed = true;
		echo '<li>', _text('오픈아이디 사용자 테이블을 만듭니다'), ': ';
		$query = "
		CREATE TABLE `{$database['prefix']}OpenIDUsers` (
		  blogid int(11) NOT NULL default '0',
		  openid varchar(128) NOT NULL,
		  delegatedid varchar(128) default NULL,
		  firstLogin int(11) default NULL,
		  lastLogin int(11) default NULL,
		  loginCount int(11) default NULL,
		  data text,
		  PRIMARY KEY  (blogid,openid)
		) TYPE=MyISAM
		";
		if (POD::execute($query . ' DEFAULT CHARSET=utf8') || POD::execute($query))
			showCheckupMessage(true);
		else {
			showCheckupMessage(false);
		}
	}

	if (!POD::queryExistence("DESC {$database['prefix']}Comments openid")) {
		$changed = true;
		echo '<li>', _text('Comments 테이블에 openid 필드를 추가합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Comments ADD openid varchar(128) NOT NULL DEFAULT '' AFTER id"))
			showCheckupMessage(true);
		else {
			showCheckupMessage(false);
		}
	}

	if (doesExistTable($database['prefix'] . 'OpenIDComments')) {
		$changed = true;
		echo '<li>', _text('오픈아이디 댓글 테이블을 기존 댓글 테이블에 병합합니다'), ': ';
		if (POD::execute("UPDATE `{$database['prefix']}Comments` AS A,`{$database['prefix']}OpenIDComments` AS B SET `A`.`openid` = `B`.`openid` WHERE `A`.`id` = `B`.`id`" )) {
		} else {
			$openids = POD::queryAll( "SELECT * from `{$database['prefix']}OpenIDComments`" );
			foreach( $openids as $rec ) {
				$_oid = POD::escapeString( $rec['openid'] );
				POD::execute( "UPDATE `{$database['prefix']}Comments` SET `openid`='$_oid' WHERE `id`={$rec['id']}" );
			}
		}
		if (POD::execute("DROP TABLE `{$database['prefix']}OpenIDComments`" ) )
			showCheckupMessage(true);
		else {
			showCheckupMessage(false);
		}
	}

	if (POD::queryExistence("DESC {$database['prefix']}Links visible")) {
		$changed = true;
		echo '<li>', _text('Links 테이블의 공개 여부 설정 필드의 속성을 변경합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Links CHANGE visible visibility tinyint(4) NOT NULL DEFAULT 2")) 
			showCheckupMessage(true);
		else {
			showCheckupMessage(false);
		}
	}

	if (!POD::queryExistence("DESC {$database['prefix']}Links visibility")) {
		$changed = true;
		echo '<li>', _text('Links 테이블에 공개 여부 설정 필드와 XFN 마이크로포맷을 위한 필드를 추가합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Links ADD visibility tinyint(4) NOT NULL DEFAULT 2") &&
		   POD::execute("ALTER TABLE {$database['prefix']}Links ADD xfn varchar(128) NOT NULL DEFAULT ''"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}

	if (POD::queryCell("DESC {$database['prefix']}Sessions updated", 'Key') != 'MUL') {
		$changed = true;
		echo '<li>', _text('동시 접속자 관리를 위하여 세션 테이블의 인덱스 설정을 변경합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Sessions ADD INDEX updated (updated)"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}

	if (!POD::queryExistence("DESC {$database['prefix']}RemoteResponses blogid") &&
		POD::queryCell("DESC {$database['prefix']}Trackbacks blogid", 'Key') != 'PRI') {
		$changed = true;
		echo '<li>', _text('트랙백 불러오기 속도를 개선하기 위하여 트랙백 테이블의 인덱스 설정을 변경합니다.'), ': ';
		POD::execute("ALTER TABLE {$database['prefix']}Trackbacks DROP INDEX written");
		POD::execute("ALTER TABLE {$database['prefix']}Trackbacks DROP INDEX blogid");
		POD::execute("ALTER TABLE {$database['prefix']}TrackbackLogs DROP INDEX id");
		if (POD::execute("ALTER TABLE {$database['prefix']}Trackbacks 
				DROP PRIMARY KEY, ADD PRIMARY KEY (blogid, id),
				ADD INDEX blogid (blogid, isFiltered, written)")
			&&POD::execute("ALTER TABLE {$database['prefix']}TrackbackLogs
				DROP PRIMARY KEY, ADD PRIMARY KEY (blogid, entry, id), ADD UNIQUE id (blogid, id)"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}

	if (POD::queryExistence("DESC {$database['prefix']}SessionVisits blog")) {
		$changed = true;
		echo '<li>', _text('SessionVisits 테이블의 블로그 정보 필드 이름을 변경합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}SessionVisits CHANGE blog blogid int(11) NOT NULL DEFAULT 0") && 
		(POD::execute("ALTER TABLE {$database['prefix']}SessionVisits DROP PRIMARY KEY, ADD PRIMARY KEY (id,address,blogid)")))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}

	if (POD::queryCell("DESC {$database['prefix']}BlogSettings name", 'Key') != 'PRI') {
		$changed = true;
		echo '<li>', _text('블로그 설정 불러오기 속도를 개선하기 위하여 블로그 설정 테이블의 인덱스 설정을 변경합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}BlogSettings ADD INDEX name (name,value (32))"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}

	if (!POD::queryExistence("DESC {$database['prefix']}SkinSettings showListOnAuthor")) {
		$changed = true;
		echo '<li>', _text('스킨 설정 테이블에 저자별 페이지 출력 설정을 위한 필드를 추가합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}SkinSettings ADD showListOnAuthor TINYINT(4) DEFAULT 1 NOT NULL AFTER showListOnTag"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}

	if (POD::queryCell("DESC {$database['prefix']}Entries draft", 'Key') != 'PRI') {
		$changed = true;
		echo '<li>', _text('엔트리 테이블의 주 인덱스에 draft를 추가합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Entries DROP PRIMARY KEY, ADD PRIMARY KEY (`blogid`,`id`,`draft`,`category`,`published`)"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}
	/* FROM Textcube 1.7 */
	if (!POD::queryExistence("DESC {$database['prefix']}Entries starred")) {
		$changed = true;
		echo '<li>', _text('본문 테이블에 별표 및 작성 중 글 표시를 위한 필드를 추가합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Entries ADD starred TINYINT(4) DEFAULT 1 NOT NULL AFTER visibility"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}
	
	if (POD::queryCell("DESC {$database['prefix']}Users name", 'Key') != 'UNI') {
		$changed = true;
		echo '<li>', _text('id의 도용을 막기 위하여 같은 사용자 id를 사용할 수 없도록 합니다.'), ': ';
		if(!is_null($users = POD::queryAll("SELECT userid, name FROM {$database['prefix']}Users"))) {
			// 1 : rename duplicate names.
			foreach($users as $user) {
				$duplicates = POD::queryAll("SELECT userid, name FROM {$database['prefix']}Users WHERE name = '".POD::escapeString($user['name'])."' AND userid != {$user['userid']}");
				if(!empty($duplicates)) {
					$count = 1;
					foreach($duplicates as $dup) {
						POD::query("UPDATE {$database['prefix']}Users SET name = '".POD::escapeString($user['name'])."-".$count."' WHERE userid = {$user['userid']}");
						$count++;
					}
				}
				unset($duplicates);
			}
			// 2: set name as unique field
			if (POD::execute("ALTER TABLE {$database['prefix']}Users ADD UNIQUE name (name)"))
				showCheckupMessage(true);
			else
				showCheckupMessage(false);
		} else {
			showCheckupMessage(false);
		}
	}
	if (!doesExistTable($database['prefix'] . 'LinkCategories')) {
		$changed = true;
		echo '<li>', _text('링크 카테고리 테이블을 만듭니다'), ': ';
		$query = "
		CREATE TABLE `{$database['prefix']}LinkCategories` (
		  pid int(11) NOT NULL default '0',
		  blogid int(11) NOT NULL default '0',
		  id int(11) NOT NULL default '0',
		  name varchar(128) NOT NULL,
		  priority int(11) NOT NULL default '0',
		  visibility tinyint(4) NOT NULL default '2',
		  PRIMARY KEY (pid),
		  UNIQUE KEY blogid (blogid, id)
		) TYPE=MyISAM
		";
		if (POD::execute($query . ' DEFAULT CHARSET=utf8') || POD::execute($query)) {
			if (POD::execute("ALTER TABLE {$database['prefix']}Links 
					ADD category int(11) NOT NULL DEFAULT 0 AFTER id,
					ADD pid int(11) NOT NULL DEFAULT 0 FIRST,
					CHANGE id id int(11) NOT NULL default '0'") &&
				POD::execute("UPDATE {$database['prefix']}Links 
					SET pid = id") &&
				POD::execute("ALTER TABLE {$database['prefix']}Links 
					DROP PRIMARY KEY,
					ADD PRIMARY KEY (pid)")) {
				showCheckupMessage(true);
			} else {
				@POD::execute("DROP TABLE {$database['prefix']}LinkCategories");
				showCheckupMessage(false);
			}
		} else {
			showCheckupMessage(false);
		}
	}
	/* FROM Textcube 1.7.3 */
	if (!is_null($notices = POD::queryAll("SELECT blogid, id, title, slogan
		FROM {$database['prefix']}Entries 
		WHERE category = -2
			AND slogan = ''")) && !empty($notices)) {
		$changed = true;
		echo '<li>', _text('fancyURL이 적용되지 않는 공지 글에 슬로건을 추가합니다.'), ': ';
		foreach($notices as $notice) :
			$notice['slogan'] = getSlogan($notice['title']);
			$succeed = POD::execute("UPDATE {$database['prefix']}Entries
				SET slogan = '".POD::escapeString($notice['slogan'])."'
				WHERE blogid = {$notice['blogid']}
				AND id = {$notice['id']}");
		endforeach;
		if($succeed)
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}
	/* From Textcube 1.7.6 */
	if (!strpos(POD::queryCell("DESC {$database['prefix']}Filters type", 'Type'),'whiteurl')) {
		$changed = true;
		echo '<li>', _text('필터 테이블에 예외 목록을 추가하기 위하여 필드 속성을 변경합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Filters CHANGE type type ENUM('content','ip','name','url','whiteurl') NOT NULL DEFAULT 'content'"))
			showCheckupMessage(true);
		else
			showCheckupMessage(false);
	}
	/* From Textcube 1.7.9 */
	if (DBQuery::queryCell("DESC {$database['prefix']}Attachments name", 'Type') == 'varchar(32)') {
		$changed = true;
		echo '<li>', _text('티스토리 데이터 백업 호환성을 위하여 첨부파일 이름 필드 크기를 확장합니다.'), ': ';
		if (POD::execute("ALTER TABLE {$database['prefix']}Attachments CHANGE name name varchar(64) NOT NULL DEFAULT ''"))
			showCheckupMessage(true);
		else {
			showCheckupMessage(false);
		}
	}	
}

/***** Common parts. *****/
if(doesHaveOwnership()) clearCache();

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
RewriteBase ".$service['path']."/
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

if (((!file_exists(ROOT . '/cache/CHECKUP')) || (file_get_contents(ROOT . '/cache/CHECKUP') != TEXTCUBE_VERSION)) && ($succeed == true)) {
	if ($fp = fopen(ROOT . '/cache/CHECKUP', 'w')) {
		fwrite($fp, TEXTCUBE_VERSION);
		fclose($fp);
		@chmod(ROOT . '/cache/CHECKUP', 0666);
		clearCache();
	}
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
					<a href="<?php echo $blogURL.'/owner/center/dashboard';?>"><img src="<?php echo $service['path']?>/style/setup/image/icon_ok.gif" width="74" height="24" alt="돌아가기" /></a>
				</div>
			</div>
		</form>
	</div>
</body>
</html>
