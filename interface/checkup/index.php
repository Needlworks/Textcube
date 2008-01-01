<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
require ROOT . '/lib/includeForBlog.php';
require ROOT . '/lib/model/blog.skin.php';

requireModel('common.setting');

if (!file_exists(ROOT . '/cache/CHECKUP') || (file_get_contents(ROOT . '/cache/CHECKUP') != TEXTCUBE_VERSION)) {
	if ($fp = fopen(ROOT . '/cache/CHECKUP', 'w')) {
		fwrite($fp, TEXTCUBE_VERSION);
		fclose($fp);
		@chmod(ROOT . '/cache/CHECKUP', 0666);
	}
}

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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo _text('텍스트큐브를 점검합니다.');?></title>
	<style type="text/css" media="screen">
	/*<![CDATA[*/
		body
		{
			font                : 12px/1.5 Verdana, Gulim;
			color               : #333;
		}
		h3
		{
			color               :#0099FF;
			padding-bottom      :5px;
		}
	/*]]>*/
	</style>
</head>
<body>
	<h3><?php echo _text('텍스트큐브를 점검합니다.');?></h3>
	
	<p>
		<ul>
<?php
$changed = false;

// From 1.6
if (POD::queryCell("DESC {$database['prefix']}CommentsNotified id", 'Extra') == 'auto_increment') {
	$changed = true;
	echo '<li>', _text('데이터베이스 호환성을 위하여 댓글 테이블의 자동 증가 설정을 제거합니다.'), ': ';
	if (POD::execute("ALTER TABLE {$database['prefix']}Comments CHANGE id id int(11) NOT NULL")
		&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotified CHANGE id id int(11) NOT NULL")
		&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotifiedQueue CHANGE id id int(11) NOT NULL")
		&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotifiedSiteInfo CHANGE id id int(11) NOT NULL"))
		echo '<span style="color:#33CC33;">', _text('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _text('실패'), '</span></li>';
}

if (POD::queryCell("DESC {$database['prefix']}Comments blogid", 'Key') != 'PRI') {
	$changed = true;
	echo '<li>', _text('데이터베이스 호환성을 위하여 댓글 테이블의 인덱스 설정을 변경합니다.'), ': ';
	if (POD::execute("ALTER TABLE {$database['prefix']}Comments DROP PRIMARY KEY, ADD PRIMARY KEY (blogid, id)")
		&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotified DROP PRIMARY KEY, ADD PRIMARY KEY (blogid, id)")
		&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotifiedQueue DROP PRIMARY KEY, ADD PRIMARY KEY (blogid, id)")
		&& POD::execute("ALTER TABLE {$database['prefix']}CommentsNotifiedSiteInfo DROP INDEX id"))
		echo '<span style="color:#33CC33;">', _text('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _text('실패'), '</span></li>';
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
		echo '<span style="color:#33CC33;">', _text('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _text('실패'), '</span></li>';
}

if (POD::queryExistence("DESC {$database['prefix']}Links visible")) {
	$changed = true;
	echo '<li>', _text('Links 테이블의 공개 여부 설정 필드의 속성을 변경합니다.'), ': ';
	if (POD::execute("ALTER TABLE {$database['prefix']}Links CHANGE visible visibility tinyint(4) NOT NULL DEFAULT 2")) 
		echo '<span style="color:#33CC33;">', _text('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _text('실패'), '</span></li>';
}

if (!POD::queryExistence("DESC {$database['prefix']}Links visibility")) {
	$changed = true;
	echo '<li>', _text('Links 테이블에 공개 여부 설정 필드와 XFN 마이크로포맷을 위한 필드를 추가합니다.'), ': ';
	if (POD::execute("ALTER TABLE {$database['prefix']}Links ADD visibility tinyint(4) NOT NULL DEFAULT 2") &&
	   POD::execute("ALTER TABLE {$database['prefix']}Links ADD xfn varchar(128) NOT NULL DEFAULT ''"))
		echo '<span style="color:#33CC33;">', _text('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _text('실패'), '</span></li>';
}

if (POD::queryCell("DESC {$database['prefix']}Sessions updated", 'Key') != 'MUL') {
	$changed = true;
	echo '<li>', _text('동시 접속자 관리를 위하여 세션 테이블의 인덱스 설정을 변경합니다.'), ': ';
	if (POD::execute("ALTER TABLE {$database['prefix']}Comments ADD KEY updated (updated)"))
		echo '<span style="color:#33CC33;">', _text('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _text('실패'), '</span></li>';
}

/***** Common parts. *****/
if(doesHaveOwnership() && $blogids = POD::queryColumn("SELECT blogid FROM {$database['prefix']}PageCacheLog")) {
	$changed = true;
	$errorlog = false;
	echo '<li>', _textf('페이지 캐시를 초기화합니다.'), ': ';
	foreach($blogids as $ids) {
		if(CacheControl::flushAll($ids) == false) $errorlog = true; 
	}
	if($errorlog == false) echo '<span style="color:#33CC33;">', _text('성공'), '</span></li>';
	else echo '<span style="color:#FF0066;">', _text('실패'), '</span></li>';
}

if(doesHaveOwnership()){
	echo '<li>', _textf('공지사항 캐시를 초기화합니다.'), ': ';
	if(POD::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name = 'Textcube_Notice'"))
		echo '<span style="color:#33CC33;">', _text('성공'), '</span></li>';
	else echo '<span style="color:#FF0066;">', _text('실패'), '</span></li>';
}
?>
</ul>
<?php
	reloadSkin(1);
?>
<?php echo ($changed ? _text('완료되었습니다.') : _text('확인되었습니다.'));?>
</p>
<p>
<a href="<?php echo $blogURL.'/owner/center/dashboard';?>"><?php echo _text('되돌아가기');?></a>
</p>
</body>
</html>
