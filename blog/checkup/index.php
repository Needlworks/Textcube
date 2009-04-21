<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

define('ROOT','../..');
define('__TEXTCUBE_LOGIN__',true);

require ROOT . '/library/preprocessor.php';
require ROOT . '/library/model/blog.skin.php';

requireModel('common.setting');
requireModel('blog.entry');

if (!file_exists(ROOT . '/cache/CHECKUP') || (file_get_contents(ROOT . '/cache/CHECKUP') != TEXTCUBE_VERSION)) {
	if ($fp = fopen(ROOT . '/cache/CHECKUP', 'w')) {
		fwrite($fp, '1.5.4');
		fclose($fp);
		@chmod(ROOT . '/cache/CHECKUP', 0666);
	}
}

function setBlogSettingForMigration($blogid, $name, $value, $mig = null) {
	global $database;
	$name = mysql_tt_escape_string($name);
	$value = mysql_tt_escape_string($value);
	if($mig === null) 
		return DBQuery::execute("REPLACE INTO {$database['prefix']}BlogSettingsMig VALUES('$blogid', '$name', '$value')");
	else
		return DBQuery::execute("REPLACE INTO {$database['prefix']}BlogSettings VALUES('$blogid', '$name', '$value')");
}

function getBlogSettingForMigration($blogid, $name, $default = null) {
	global $database;
	$value = DBQuery::queryCell("SELECT value 
		FROM {$database['prefix']}BlogSettingsMig 
		WHERE blogid = '$blogid'
		AND name = '".mysql_tt_escape_string($name)."'");
	return ($value === null) ? $default : $value;
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
		<form id="setup">
			<div id="title">
				<h1><img src="<?php echo $service['path']?>/style/setup/image/title.gif" width="253" height="44" alt="Textcube를 점검합니다." /></h1>
			</div>

			<div id="inner">
				<h2><?php echo _text('텍스트큐브 점검을 시작합니다.');?></h2>

				<div id="content">
					<h3><?php echo _text('버전 검사');?></h3>
					
					<ul class="message">
						<li><?php echo _textf('기존 버전 - %1',$currentVersion);?></li>
						<li><?php echo _textf('현재 버전 - %1',TEXTCUBE_VERSION);?></li>
					</ul>
					
					<h3><?php echo _text('변경 중');?></h3>
					
					<ul id="processList">
<?php
$changed = false;
if (!DBQuery::queryExistence("DESC {$database['prefix']}SkinSettings recentNoticeLength")) { // Since 1.0.1
	$changed = true;
	echo '<li>', _text('스킨 설정 테이블에 공지 길이 제한 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings ADD recentNoticeLength INT DEFAULT 30 NOT NULL AFTER expandTrackback"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryExistence("DESC {$database['prefix']}Categories `order`")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('분류 테이블의 우선순위 필드명을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories CHANGE `order` priority INT NOT NULL DEFAULT 0"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryExistence("DESC {$database['prefix']}Users `database`")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('사용자 테이블의 미사용 필드를 삭제합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Users DROP server, DROP `database`"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}RefererLogs url")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('리퍼러 로그 테이블의 구조를 변경합니다.'), ': ';
	if (DBQuery::execute("UPDATE {$database['prefix']}RefererLogs SET path = CONCAT('http://', host, path)") && DBQuery::execute("ALTER TABLE {$database['prefix']}RefererLogs CHANGE path url VARCHAR(255) NOT NULL") && DBQuery::execute("ALTER TABLE {$database['prefix']}RefererLogs CHANGE written referred INT NOT NULL"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!doesExistTable($database['prefix'] . 'Filters')) { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('필터와 관련된 구조를 변경합니다.'), ': ';
	$query = "
		CREATE TABLE {$database['prefix']}Filters (
		  id int(11) NOT NULL auto_increment,
		  owner int(11) NOT NULL default '0',
		  type enum('content','ip','name','url') NOT NULL default 'content',
		  pattern varchar(255) NOT NULL default '',
		  PRIMARY KEY (id),
		  UNIQUE KEY owner (owner, type, pattern)
		) TYPE=MyISAM
	";
	if (DBQuery::execute($query . ' DEFAULT CHARSET=utf8') || DBQuery::execute($query)) {
		if (DBQuery::execute("INSERT INTO {$database['prefix']}Filters(owner, type, pattern) SELECT owner, 'content', word FROM {$database['prefix']}ContentFilters"))
			DBQuery::execute("DROP TABLE {$database['prefix']}ContentFilters");
		if (DBQuery::execute("INSERT INTO {$database['prefix']}Filters(owner, type, pattern) SELECT owner, 'name', name FROM {$database['prefix']}GuestFilters"))
			DBQuery::execute("DROP TABLE {$database['prefix']}GuestFilters");
		if (DBQuery::execute("INSERT INTO {$database['prefix']}Filters(owner, type, pattern) SELECT owner, 'ip', address FROM {$database['prefix']}HostFilters"))
			DBQuery::execute("DROP TABLE {$database['prefix']}HostFilters");
		if (DBQuery::execute("INSERT INTO {$database['prefix']}Filters(owner, type, pattern) SELECT owner, 'url', url FROM {$database['prefix']}URLFilters"))
			DBQuery::execute("DROP TABLE {$database['prefix']}URLFilters");
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}
if (doesExistTable($database['prefix'] . 'FeedOwners')) { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('리더와 관련된 구조를 변경합니다.'), ': ';
	if (DBQuery::execute("DROP TABLE {$database['prefix']}FeedOwners"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (doesExistTable($database['prefix'] . 'MonthlyStatistics')) { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('통계와 관련된 구조를 변경합니다.'), ': ';
	if (DBQuery::execute("DROP TABLE {$database['prefix']}MonthlyStatistics"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryExistence("SELECT * FROM {$database['prefix']}Users WHERE name = ''")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('사용자 이름 누락 정보를 보완합니다.'), ': ';
	if (DBQuery::execute("UPDATE {$database['prefix']}Users SET name = IF(LEFT(loginid, POSITION('@' IN loginid) - 1) = '', loginid, LEFT(loginid, POSITION('@' IN loginid) - 1)) WHERE name = ''"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Entries owner", 'Key') != 'PRI'
	&& DBQuery::queryCell("DESC {$database['prefix']}Entries blogid", 'Key') != 'PRI') { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('엔트리 테이블의 인덱스를 수정합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Entries DROP PRIMARY KEY, ADD PRIMARY KEY(owner, id, draft)"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}TagRelations owner", 'Key') != 'PRI'
	&& DBQuery::queryCell("DESC {$database['prefix']}TagRelations blogid", 'Key') != 'PRI') { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('태그관계 테이블의 인덱스를 수정합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}TagRelations DROP PRIMARY KEY, ADD PRIMARY KEY(owner, tag, entry)"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Trackbacks owner", 'Key') != 'MUL'
	&& DBQuery::queryCell("DESC {$database['prefix']}Trackbacks blogid", 'Key') != 'MUL') { // Since 1.0.2
	$changed = true;
	echo '<li>', _text('걸린글 테이블의 인덱스를 수정합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Trackbacks DROP INDEX entry, ADD UNIQUE owner (owner, entry, url)"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Comments parent", 'Key') != 'MUL') { // Since 1.0.3
	$changed = true;
	echo '<li>', _text('댓글 테이블에 인덱스를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Comments ADD INDEX parent (parent)"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!Validator::getBool(DBQuery::queryCell("DESC {$database['prefix']}Sessions data", 'Null'))) { // Since 1.0.3
	$changed = true;
	echo '<li>', _text('세션 테이블의 필드 속성을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Sessions CHANGE data data TEXT DEFAULT NULL"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Categories name", 'Type') == 'varchar(32)') { // Since 1.0.3
	$changed = true;
	echo '<li>', _text('분류 테이블의 이름 필드 속성을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories CHANGE name name VARCHAR(127) NOT NULL"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Categories label", 'Type') == 'varchar(80)') { // Since 1.0.3
	$changed = true;
	echo '<li>', _text('분류 테이블의 라벨 필드 속성을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories CHANGE label label VARCHAR(255) NOT NULL"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}BlogSettings timezone", 'Type') != 'varchar(32)'
	&& !DBQuery::queryExistence("DESC {$database['prefix']}BlogSettings value")) { // Since 1.0.5
	$changed = true;
	echo '<li>', _text('블로그 설정 테이블의 시간대 필드 속성을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}BlogSettings CHANGE timezone timezone VARCHAR(32) NOT NULL DEFAULT 'GMT'")) {
		DBQuery::execute("UPDATE {$database['prefix']}BlogSettings SET timezone = 'GMT' WHERE timezone <> '32400' AND timezone <> '-18000'");
		DBQuery::execute("UPDATE {$database['prefix']}BlogSettings SET timezone = 'Asia/Seoul' WHERE timezone = '32400'");
		DBQuery::execute("UPDATE {$database['prefix']}BlogSettings SET timezone = 'America/New_York' WHERE timezone = '-18000'");
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}
if (DBQuery::queryCell("DESC {$database['prefix']}BlogSettings language", 'Type') != 'varchar(5)'
	&& !DBQuery::queryExistence("DESC {$database['prefix']}BlogSettings value")) { // Since 1.0.6
	$changed = true;
	echo '<li>', _text('블로그 설정 테이블의 언어 필드 속성을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}BlogSettings CHANGE language language VARCHAR(5) NOT NULL DEFAULT 'en'"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}SkinSettings archivesOnPage")) { // Since 1.1
	$changed = true;
	echo '<li>', _text('스킨 설정 테이블에 아카이브 출력 설정 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings ADD archivesOnPage INT DEFAULT 5 NOT NULL AFTER commentsOnGuestbook"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}BlogSettings publishEolinSyncOnRSS")
	&& !DBQuery::queryExistence("DESC {$database['prefix']}BlogSettings value")) {
	$changed = true;
	echo '<li>', _text('블로그 설정 테이블에 RSS 공개 정도 설정 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}BlogSettings ADD publishEolinSyncOnRSS INT(1) DEFAULT 1 NOT NULL AFTER publishWholeOnRSS"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}Trackbacks isFiltered")) {
	$changed = true;
	echo '<li>', _text('걸린글 테이블에 광고 및 스팸 분류를 위한 휴지통 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Trackbacks ADD isFiltered INT(11) DEFAULT 0 NOT NULL AFTER written"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}Comments isFiltered")) {
	$changed = true;
	echo '<li>', _text('덧글및 방명록 테이블에 광고 및 스팸 분류를 위한 휴지통 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Comments ADD isFiltered INT(11) DEFAULT 0 NOT NULL AFTER written"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (DBQuery::queryExistence("DESC {$database['prefix']}Trackbacks sender")) {
	$changed = true;
	echo '<li>', _text('걸린글 테이블의 미사용 필드를 삭제합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Trackbacks DROP sender"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}Categories visibility")) {
	$changed = true;
	echo '<li>', _text('카테고리 테이블에 비공개 카테고리 설정을 위한 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories ADD visibility TINYINT(4) DEFAULT 2 NOT NULL AFTER label"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}Categories bodyId")) {
	$changed = true;
	echo '<li>', _text('카테고리 테이블에 Body Id 설정을 위한 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories ADD bodyId varchar(20) DEFAULT null AFTER visibility"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!doesExistTable($database['prefix'] . 'ServiceSettings')) {
	$changed = true;
	echo '<li>', _text('서비스 설정을 위한 테이블을 추가합니다.'), ': ';
	$query = "
		CREATE TABLE {$database['prefix']}ServiceSettings (
			name varchar(32) NOT NULL default '',
			value varchar(255) NOT NULL default '',
			PRIMARY KEY (name)
		) TYPE=MyISAM
	";
	if (DBQuery::execute($query . ' DEFAULT CHARSET=utf8') || DBQuery::execute($query))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
if (!doesExistTable($database['prefix'] . 'UserSettings')) { // Since 1.0.7
	$changed = true;
	echo '<li>', _t('사용자 설정값과 관련된 구조를 변경합니다'), ': ';
	$query = "
		CREATE TABLE {$database['prefix']}UserSettings (
		  user int(11) NOT NULL default '0',
		  name varchar(32) NOT NULL default '',
		  value text NOT NULL default '',
		  PRIMARY KEY (user,name)
		) TYPE=MyISAM
	";
	if (DBQuery::execute($query . ' DEFAULT CHARSET=utf8') || DBQuery::execute($query)) {
		DBQuery::execute("INSERT INTO {$database['prefix']}UserSettings(user, name, value) SELECT owner, 'rowsPerPage', rowsPerPage FROM {$database['prefix']}Personalization");
		DBQuery::execute("INSERT INTO {$database['prefix']}UserSettings(user, name, value) SELECT owner, 'readerPannelVisibility', readerPannelVisibility FROM {$database['prefix']}Personalization");
		DBQuery::execute("INSERT INTO {$database['prefix']}UserSettings(user, name, value) SELECT owner, 'readerPannelHeight', readerPannelHeight FROM {$database['prefix']}Personalization");
		DBQuery::execute("INSERT INTO {$database['prefix']}UserSettings(user, name, value) SELECT owner, 'lastVisitNotifiedPage', lastVisitNotifiedPage FROM {$database['prefix']}Personalization");
		DBQuery::execute("DROP TABLE {$database['prefix']}Personalization");
		echo '<span class="result success">', _t('성공'), '</span></li>';
	} else {
		echo '<span class="result fail">', _t('실패'), '</span></li>';
	}
}

if (!DBQuery::queryExistence("SELECT value FROM {$database['prefix']}ServiceSettings WHERE name = 'newlineStyle' AND value >= 1.1")) { // Since 1.0.7
	$query = new TableQuery($database['prefix'] . 'Entries');
	if($query->doesExist()) {
		$changed = true;
		echo '<li>', _t('[HTML][/HTML] 블럭을 제거합니다'), ': ';
		if ($entries = $query->getAll('owner, id, draft')) {
			foreach($entries as $entry) {
				$query->setQualifier('owner', $entry['owner']);
				$query->setQualifier('id', $entry['id']);
				$query->setQualifier('draft', $entry['draft']);
				$originalEntry = $query->getCell('content');
				$newContent = mysql_tt_escape_string(nl2brWithHTML($originalEntry));
				DBQuery::execute("UPDATE {$database['prefix']}Entries SET content = '$newContent' WHERE owner = {$entry['owner']} AND id = {$entry['id']} AND draft = {$entry['draft']}");
				$query->resetQualifiers();
			}
			echo '<span class="result success">', _t('성공'), '</span></li>';
			unset($entries);
		} else {
			echo '<span class="result fail">', _t('실패'), '</span></li>';
		}
	}
	setServiceSetting('newlineStyle', '1.1');
}

if (!DBQuery::queryExistence("DESC {$database['prefix']}BlogSettings blogLanguage")
	&& !DBQuery::queryExistence("DESC {$database['prefix']}BlogSettings value")) {
	$changed = true;
	echo '<li>', _text('설정 테이블에 블로그 언어 설정을 위한 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}BlogSettings ADD blogLanguage varchar(5) not null default 'en' after language")) {
		DBQuery::execute("UPDATE {$database['prefix']}BlogSettings SET blogLanguage = language");
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryExistence("DESC {$database['prefix']}SkinSettings NoCommentMessage")) {
	$changed = true;
	echo '<li>', _text('스킨 관련 테이블에 댓글 및 글걸기 메세지 설정을 위한 필드를 삭제합니다.'), ': ';
	if(DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings DROP NoCommentMessage") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings DROP SingleCommentMessage") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings DROP MultipleCommentMessage") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings DROP NoTrackbackMessage") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings DROP SingleTrackbackMessage") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings DROP MultipleTrackbackMessage"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("DESC {$database['prefix']}Tags name" , 'Key') != 'UNI') {
	$changed = true;
	echo '<li>', _text('태그 테이블에 인덱스 키를 추가합니다.'), ': ';
	requireComponent('Textcube.Data.Post');
	Post::correctTagsAll();
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Tags ADD UNIQUE INDEX name (name)")) {
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		echo '<span class="result fail">', _text('실패'), '</span>';
		echo '<span class="result success">', _text('관리자 화면의 환경 설정에서 데이터 교정을 수행하시기 바랍니다.'), '</span></li>';
	}
}

if (DBQuery::queryCell("DESC {$database['prefix']}UserSettings value", 'Type') != 'text') { // Since 1.1
	$changed = true;
	echo '<li>', _text('사용자 설정값 테이블의 필드 속성을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}UserSettings CHANGE value value text NOT NULL DEFAULT ''")) {
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}

if (DBQuery::queryCell("DESC {$database['prefix']}Comments isFiltered", 'Type') != 'int(11)') {
	$changed = true;
	echo '<li>', _text('휴지통 테이블의 필드 속성을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Comments CHANGE isFiltered isFiltered int(11) NOT NULL DEFAULT 0")) {
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}

if (DBQuery::queryCell("DESC {$database['prefix']}Trackbacks isFiltered", 'Type') != 'int(11)') {
	$changed = true;
	echo '<li>', _text('휴지통 테이블의 필드 속성을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Trackbacks CHANGE isFiltered isFiltered int(11) NOT NULL DEFAULT 0")) {
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}

// Since 1.1.1
$indexes = DBQuery::queryAll("Show index from {$database['prefix']}Entries");
$idkey = FALSE;
foreach($indexes as $index)
	if($index['Column_name']=='id' && $index['Key_name']=='id') $idkey = TRUE;
if ($idkey == FALSE) {
	$changed = true;
	echo '<li>', _text('본문 테이블에 태그 검색 향상을 위한 인덱스를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Entries ADD INDEX id (id)"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("DESC {$database['prefix']}Comments isFiltered", 'Key') != 'MUL') {
	$changed = true;
	echo '<li>', _text('댓글 테이블에 필터 인덱스를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Comments ADD INDEX isFiltered (isFiltered)"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("DESC {$database['prefix']}Trackbacks isFiltered", 'Key') != 'MUL') {
	$changed = true;
	echo '<li>', _text('글걸기 테이블에 필터 인덱스를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Trackbacks ADD INDEX isFiltered (isFiltered)"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (!DBQuery::queryExistence("DESC {$database['prefix']}SkinSettings showListOnTag")) {
	$changed = true;
	echo '<li>', _text('스킨 설정 테이블에 태그 출력시 목록 및 글 출력 설정을 위한 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings ADD showListOnTag INT(1) DEFAULT 1 NOT NULL AFTER showListOnArchive"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (!DBQuery::queryExistence("DESC {$database['prefix']}SkinSettings showListOnSearch")) { // Since 1.1.1.1
	$changed = true;
	echo '<li>', _text('스킨 설정 테이블에 검색 결과 출력시 목록 및 글 출력 설정을 위한 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings ADD showListOnSearch INT(1) DEFAULT 1 NOT NULL AFTER showListOnTag"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("DESC {$database['prefix']}Categories owner", 'Key') != 'PRI'
	&& DBQuery::queryCell("DESC {$database['prefix']}Categories blogid", 'Key') != 'PRI') { // Since 1.1.2
	$changed = true;
	echo '<li>', _text('최상위 카테고리 이름 수정을 위하여 카테고리 테이블의 인덱스를 수정합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories DROP PRIMARY KEY, ADD PRIMARY KEY(owner, id)"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("DESC {$database['prefix']}Categories id", 'Extra') == 'auto_increment') {
	$changed = true;
	echo '<li>', _text('최상위 카테고리 이름 수정을 위하여 카테고리 테이블의 자동 증가 설정을 제거합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories CHANGE id id int(11) NOT NULL"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("DESC {$database['prefix']}Entries id", 'Extra') == 'auto_increment') {
	$changed = true;
	echo '<li>', _text('글번호의 교정을 위하여 본문 테이블의 자동 증가 설정을 제거합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Entries CHANGE id id int(11) NOT NULL"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (!DBQuery::queryExistence("DESC {$database['prefix']}Entries contentFormatter")) { // Since 1.5
	$changed = true;
	echo '<li>', _text('글을 쓸 때 사용할 편집기와 포매터를 선택하는 필드를 추가합니다.'), ': ';
	$defaultformatter = 'ttml';
	$defaulteditor = 'modern';
	$result =
		DBQuery::execute("ALTER TABLE {$database['prefix']}Entries ADD contentEditor VARCHAR(32) DEFAULT '' NOT NULL AFTER content, ADD contentFormatter VARCHAR(32) DEFAULT '' NOT NULL AFTER content") &&
		DBQuery::execute("UPDATE {$database['prefix']}Entries SET contentEditor = '".mysql_tt_escape_string($defaulteditor)."', contentFormatter = '".mysql_tt_escape_string($defaultformatter)."'");
	if ($result)
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("select count(*) FROM {$database['prefix']}Entries WHERE contentFormatter = ''") != 0) { 
	$changed = true;
	echo '<li>', _text('글 테이블의 편집기와 포매터 필드를 갱신합니다.'), ': ';
	$defaultformatter = 'ttml';
	$defaulteditor = 'modern';
	$result = DBQuery::execute("UPDATE {$database['prefix']}Entries SET contentEditor = '".mysql_tt_escape_string($defaulteditor)."', contentFormatter = '".mysql_tt_escape_string($defaultformatter)."'");
	if ($result)
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (!doesExistTable($database['prefix'] . 'Teamblog')) {
	$changed = true;
	echo '<li>', _text('팀블로그 기능을 위한 테이블을 추가합니다.'), ': ';
	$query = "
		CREATE TABLE {$database['prefix']}Teamblog (
			blogid int(11) NOT NULL default 1,
			userid int(11) NOT NULL default 1,
			acl	int(11) NOT NULL default 0,
			created int(11) NOT NULL default 0,
			lastLogin int(11) NOT NULL default 0,
			PRIMARY KEY (blogid,userid)
		) TYPE=MyISAM
	";
	if (DBQuery::execute($query . ' DEFAULT CHARSET=utf8') || DBQuery::execute($query)) {
		$query = new TableQuery($database['prefix'] . 'Users');
		if($query->doesExist()) {
			$changed = true;
			if ($users = $query->getAll('userid, name, created')) {
				foreach($users as $user) {
					DBQuery::execute("INSERT INTO `{$database['prefix']}Teamblog` (blogid,userid,acl,created,lastLogin) VALUES('".$user['userid']."', '".$user['userid']."','16','".$user['created']."', '0')");
				}
			}
			unset($users);
			echo '<span class="result success">', _text('성공'), '</span></li>';
		}
	} else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (!doesExistTable($database['prefix'] . 'XMLRPCPingSettings')) {
	$changed = true;
	echo '<li>', _text('XML-RPC ping 설정을 위한 테이블을 추가합니다.'), ': ';
	$query = "
		CREATE TABLE {$database['prefix']}XMLRPCPingSettings (
			owner int(11) NOT NULL default 0,
			url varchar(255) NOT NULL default '',
			type varchar(32) NOT NULL default 'xmlrpc',
			PRIMARY KEY (owner)
		) TYPE=MyISAM
	";
	if (DBQuery::execute($query . ' DEFAULT CHARSET=utf8') || DBQuery::execute($query))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryExistence("DESC {$database['prefix']}Teamblog enduser")) {
	$changed = true;
	echo '<li>', _text('팀블로그 테이블의 유저 출력 설정 필드를 삭제합니다.'), ': ';
	if(DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog DROP logo") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog DROP enduser") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog DROP admin") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog DROP posting") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog DROP font_style") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog DROP font_color") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog DROP font_size") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog DROP font_bold") &&
	DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog ADD acl int(11) not null AFTER userid"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryExistence("DESC {$database['prefix']}BlogSettings defaultDomain")) {
	$changed = true;
	echo '<li>', _text('블로그 설정 테이블과 사용자 설정 테이블의 구조를 변경합니다.'), ': ';
	$query = "
		CREATE TABLE {$database['prefix']}BlogSettingsMig (
			blogid int(11) NOT NULL default 0,
			name varchar(32) NOT NULL default '',
			value text NOT NULL,
			PRIMARY KEY (blogid,name)
		) TYPE=MyISAM
	";
	if (DBQuery::execute($query . ' DEFAULT CHARSET=utf8') || DBQuery::execute($query)) {
		$query = new TableQuery($database['prefix'] . 'BlogSettings');
		if($query->doesExist()) {
			$changed = true;
			$defaultformatter = 'ttml';
			$defaulteditor = 'modern';
			if ($blogSettings = $query->getAll('owner, name, secondaryDomain, defaultDomain, url, title, description, logo, logoLabel, logoWidth, logoHeight, useSlogan, entriesOnPage, entriesOnList, entriesOnRSS, publishWholeOnRSS, publishEolinSyncOnRSS, allowWriteOnGuestbook, allowWriteDoubleCommentOnGuestbook, language, blogLanguage,timezone')) {
				$fieldnames = array('owner', 'name', 'secondaryDomain', 'defaultDomain', 'url', 'title', 'description', 'logo', 'logoLabel', 'logoWidth', 'logoHeight', 'useSlogan', 'entriesOnPage', 'entriesOnList', 'entriesOnRSS', 'publishWholeOnRSS', 'publishEolinSyncOnRSS', 'allowWriteOnGuestbook', 'language', 'blogLanguage','timezone');
				foreach($blogSettings as $blogSetting) {
					foreach($fieldnames as $fieldname) {
						setBlogSettingForMigration($blogSetting['owner'],$fieldname,$blogSetting[$fieldname]);
					}
					setBlogSettingForMigration($blogSetting['owner'],'defaultEditor',$defaulteditor);
					setBlogSettingForMigration($blogSetting['owner'],'defaultFormatter',$defaultformatter);
					setBlogSettingForMigration($blogSetting['owner'],'allowWriteDblCommentOnGuestbook',$blogSetting['allowWriteDoubleCommentOnGuestbook']);
				}
				$checked = true;
				foreach($blogSettings as $blogSetting) {
					foreach($fieldnames as $fieldname) {
						if(getBlogSettingForMigration($blogSetting['owner'],$fieldname) != $blogSetting[$fieldname]) {$checked = false;break;}
					}
					if(getBlogSettingForMigration($blogSetting['owner'],'allowWriteDblCommentOnGuestbook') != $blogSetting['allowWriteDoubleCommentOnGuestbook']) {$checked = false;break;}
				}
				unset($blogSettings);
				if($checked == false) {
					DBQuery::execute("DROP TABLE {$database['prefix']}BlogSettingsMig");
					echo '<span class="result fail">', _text('실패'), '</span></li>';
				} else {
					// Change Table
					DBQuery::execute("DROP TABLE {$database['prefix']}BlogSettings");
					DBQuery::execute("RENAME TABLE {$database['prefix']}BlogSettingsMig TO {$database['prefix']}BlogSettings");
					// Migrate UserSettings
					$query = new TableQuery($database['prefix'] . 'UserSettings');
					if($query->doesExist()) {
						$oldUserSettings = $query->getAll('user, name, value');
						foreach($oldUserSettings as $oldUserSetting) {
							setBlogSettingForMigration($oldUserSetting['user'],$oldUserSetting['name'],$oldUserSetting['value'],true);
						}
						DBQuery::execute("DROP TABLE {$database['prefix']}UserSettings");
						$query = "
							CREATE TABLE {$database['prefix']}UserSettings (
							userid int(11) NOT NULL default 0,
							name varchar(32) NOT NULL default '',
							value text NOT NULL,
							PRIMARY KEY (userid,name)
						) TYPE=MyISAM
						";
						if (DBQuery::execute($query . ' DEFAULT CHARSET=utf8') || DBQuery::execute($query)) {
							echo '<span class="result success">', _text('성공'), '</span></li>';
						} else echo '<span class="result fail">', _text('실패'), '</span></li>';
					} else echo '<span class="result fail">', _text('실패'), '</span></li>';
				}
			} else echo '<span class="result fail">', _text('실패'), '</span></li>';
		}
	} else echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (!DBQuery::queryExistence("DESC {$database['prefix']}Entries userid")) {
	$changed = true;
	echo '<li>', _text('본문 테이블에 작성자 정보를 위한 필드를 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Entries ADD userid INT(11) DEFAULT 0 NOT NULL AFTER owner")) {
		if($blogids = DBQuery::queryColumn("SELECT DISTINCT owner FROM {$database['prefix']}Entries")) {
			foreach($blogids as $blogid) {
				DBQuery::execute("UPDATE {$database['prefix']}Entries 
					SET userid = '".$blogid['owner']."'
					WHERE owner = '".$blogid['owner']."'");
			}
			DBQuery::execute("DROP TABLE {$database['prefix']}TeamEntryRelations");
			echo '<span class="result success">', _text('성공'), '</span></li>';
		} else {
			echo '<span class="result fail">', _text('실패'), '</span></li>';
		}
	} else {
		echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}

if (DBQuery::queryCell("DESC {$database['prefix']}Entries published", 'Key') != 'PRI') {
	$changed = true;
	echo '<li>', _text('본문 테이블의 인덱스를 수정합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Entries 
		DROP PRIMARY KEY, 
		DROP INDEX owner, 
		DROP INDEX id, 
		ADD PRIMARY KEY (owner,id,category,published), 
		ADD index visibility (visibility),
		ADD index published (published),
		ADD index userid (userid),
		ADD index id (id, category, visibility),
		ADD index owner (owner, published)"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryExistence("DESC {$database['prefix']}Teamblog teams")) {
	$changed = true;
	echo '<li>', _text('팀블로그 테이블의 필드 이름을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog CHANGE teams blogid int(11) NOT NULL DEFAULT 0"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}


if (DBQuery::queryExistence("DESC {$database['prefix']}Teamblog profile")) {
	$changed = true;
	echo '<li>', _text('팀블로그 테이블의 사용자 이름 필드를 삭제합니다.'), ': ';
	if(DBQuery::execute("ALTER TABLE {$database['prefix']}Teamblog DROP profile"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryExistence("DESC {$database['prefix']}SkinSettings owner")) {
	$changed = true;
	echo '<li>', _text('스킨 테이블의 필드 이름을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings CHANGE owner blogid int(11) NOT NULL DEFAULT 0"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryExistence("DESC {$database['prefix']}Entries owner")) {
	$changed = true;
	echo '<li>', _text('본문 테이블의 필드 이름을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Entries CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Attachments CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}BlogStatistics CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Categories CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Comments CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}CommentsNotified CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}CommentsNotifiedQueue CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}DailyStatistics CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}FeedGroupRelations CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}FeedGroups CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}FeedReads CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}FeedSettings CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}FeedStarred CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Filters CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Links CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Plugins CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}RefererLogs CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}RefererStatistics CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}TagRelations CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Trackbacks CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}TrackbackLogs CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}XMLRPCPingSettings CHANGE owner blogid int(11) NOT NULL DEFAULT 0")
	)
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("DESC {$database['prefix']}Filters blogid", 'Key') != 'MUL') {
	$changed = true;
	echo '<li>', _text('테이블의 필드 인덱스를 변경합니다.'), ': ';
	if (
	   DBQuery::execute("ALTER TABLE {$database['prefix']}Categories DROP INDEX owner, ADD INDEX blogid (blogid)")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Comments DROP INDEX owner, ADD INDEX blogid (blogid)")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}CommentsNotified DROP INDEX owner, ADD INDEX blogid (blogid)")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Entries DROP INDEX owner, ADD INDEX blogid (blogid, published)")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Filters DROP INDEX owner, ADD INDEX blogid (blogid, type, pattern)")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Links DROP INDEX owner, ADD INDEX blogid (blogid, url)")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}RefererLogs ADD INDEX blogid (blogid, referred)")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}TagRelations DROP INDEX owner, ADD INDEX blogid (blogid)")
	   && DBQuery::execute("ALTER TABLE {$database['prefix']}Trackbacks DROP INDEX owner, ADD INDEX blogid (blogid, entry, url)")
	)
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}
	
	
if (!doesExistTable($database['prefix'] . 'PageCacheLog')) {
	$changed = true;
	echo '<li>', _text('페이지 캐싱을 위한 테이블을 추가합니다.'), ': ';
	$query = "
		CREATE TABLE {$database['prefix']}PageCacheLog (
			blogid int(11) NOT NULL default 0,
			name varchar(255) NOT NULL default '',
			PRIMARY KEY (blogid,name)
		) TYPE=MyISAM
	";
	if (DBQuery::execute($query . ' DEFAULT CHARSET=utf8') || DBQuery::execute($query))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("DESC {$database['prefix']}SkinSettings skin", 'Default') != 'coolant') {
	$changed = true;
	echo '<li>', _text('기본 스킨을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings CHANGE skin skin varchar(32) NOT NULL DEFAULT 'coolant'")) {
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}



// Plugin Table update.
$likeEscape = array ( '/_/' , '/%/' );
$likeReplace = array ( '\\_' , '\\%' );
$escapename = preg_replace($likeEscape, $likeReplace, $database['prefix']);
$query = "show tables like '{$escapename}%'";
$dbtables = DBQuery::queryColumn($query);

$result = DBQuery::queryRow("show variables like 'lower_case_table_names'");
$dbCaseInsensitive = ($result['Value'] == 1) ? true : false;

$definedTables = getDefinedTableNames();

$dbtables = array_values(array_diff($dbtables, $definedTables));
if ($dbCaseInsensitive == true) {
	$tempTables = $definedTables;
	$definedTables = array();
	foreach($tempTables as $table) {
		$table = strtolower($table);
		array_push($definedTables, $table);
	}
	$tempTables = $dbtables;
	$dbtables = array();
	foreach($tempTables as $table) {
		$table = strtolower($table);
		array_push($dbtables, $table);
	}
	$dbtables = array_values(array_diff($dbtables, $definedTables));
}

$query = "select name, value from {$database['prefix']}ServiceSettings WHERE name like 'Database\\_%'";
$plugintablesraw = DBQuery::queryAll($query);
$plugintables = array();
foreach($plugintablesraw as $table) {
	$dbname = $database['prefix'] . substr($table['name'], 9);
	$values = explode('/', $table['value'], 2);

	$plugin = $values[0];
	$version = $values[1];
	if (!array_key_exists($plugin .'/'. $version, $plugintables)) {
		$plugintables[$plugin .'/'. $version] = array('plugin' => $plugin, 'version' => $version, 'tables' => array());
	}
	array_push($plugintables[$plugin .'/'. $version]['tables'], $dbname);
	
	if ($dbCaseInsensitive == true) $dbname = strtolower($dbname);
	if(DBQuery::queryExistence("DESC $dbname owner")) {
		echo '<li>', _textf('플러그인이 생성한 %1 테이블의 owner 필드를 변경합니다.',$table['name']), ': ';
		if(DBQuery::execute("ALTER TABLE $dbname CHANGE owner blogid int(11) NOT NULL DEFAULT 0"))
			echo '<span class="result success">', _text('성공'), '</span></li>';
		else
			echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}

if (DBQuery::queryCell("SELECT acl FROM {$database['prefix']}Teamblog WHERE blogid = 1 AND userid = 1") == '0') {
	$changed = true;
	echo '<li>', _text('팀블로그 테이블의 소유 관계를 정의합니다.'), ': ';
	if (DBQuery::execute("UPDATE {$database['prefix']}Teamblog SET acl = 16
		WHERE blogid = userid"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if (DBQuery::queryCell("DESC {$database['prefix']}ServiceSettings value", 'Type') != 'text') {
	$changed = true;
	echo '<li>', _text('서비스 설정값 테이블의 필드 속성을 변경합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}ServiceSettings CHANGE value value text NOT NULL")) {
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		echo '<span class="result fail">', _text('실패'), '</span></li>';
	}
}

if (!DBQuery::queryExistence("DESC {$database['prefix']}PageCacheLog value")) {
	$changed = true;
	echo '<li>', _text('페이지 캐싱을 위한 테이블을 추가합니다.'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}PageCacheLog ADD value text NOT NULL AFTER name"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else
		echo '<span class="result fail">', _text('실패'), '</span></li>';
}


// Common parts.
if(doesHaveOwnership() && $blogids = DBQuery::queryColumn("SELECT blogid FROM {$database['prefix']}PageCacheLog")) {
	$changed = true;
	$errorlog = false;
	echo '<li>', _textf('페이지 캐시를 초기화합니다.'), ': ';
	foreach($blogids as $ids) {
		if(CacheControl::flushAll($ids) == false) $errorlog = true; 
	}
	if($errorlog == false) echo '<span class="result success">', _text('성공'), '</span></li>';
	else echo '<span class="result fail">', _text('실패'), '</span></li>';
}

if(doesHaveOwnership()){
	echo '<li>', _textf('공지사항 캐시를 초기화합니다.'), ': ';
	if(DBQuery::execute("DELETE FROM {$database['prefix']}ServiceSettings WHERE name = 'Textcube_Notice'"))
		echo '<span class="result success">', _text('성공'), '</span></li>';
	else echo '<span class="result fail">', _text('실패'), '</span></li>';
}

$filename = ROOT . '/.htaccess';
$fp = fopen($filename, "r");
$content = fread($fp, filesize($filename));
fclose($fp);
if ((preg_match('@rewrite\.php@', $content) == 0 ) || (strpos($content,'[OR]') !== false)) {
	$fp = fopen($filename, "w");
	echo '<li>', _textf('htaccess 규칙을 수정합니다.'), ': ';
	$content = 
"#<IfModule mod_url.c>
#CheckURL Off
#</IfModule>
#SetEnv PRELOAD_CONFIG 1
RewriteEngine On
RewriteBase ".$service['path']."/
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^(.+[^/])$ $1/ [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ rewrite.php [L,QSA]
";
	$fp = fopen($filename, "w");
	if(fwrite($fp, $content)) {
		fclose($fp);
		echo '<span class="result success">', _text('성공'), '</span></li>';
	} else {
		fclose($fp);
		echo '<span class="result fail">', _text('실패'), '</span></li>';
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
