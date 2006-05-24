<?
define('ROOT', '../..');
require ROOT . '/lib/include.php';
if (!file_exists(ROOT . '/cache/CHECKUP') || (file_get_contents(ROOT . '/cache/CHECKUP') != TATTERTOOLS_VERSION)) {
	if ($fp = fopen(ROOT . '/cache/CHECKUP', 'w')) {
		fwrite($fp, TATTERTOOLS_VERSION);
		fclose($fp);
		@chmod(ROOT . '/cache/CHECKUP', 0666);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=_t('태터툴즈를 점검합니다')?>...</title>
<script type="text/javascript">
//<![CDATA[
//]]>
</script>
<style type="text/css" media="screen">
	body{
		font:12px/1.5 Verdana, Gulim;
		color:#333;
	}
	h3 {
		color:#0099FF;
		padding-bottom:5px;
	}
</style>
</head>
<body>
<h3><?=_t('태터툴즈를 점검합니다')?>...</h3>
<p>
<ul>
<?
$changed = false;
if (!DBQuery::queryExistence("DESC {$database['prefix']}SkinSettings recentNoticeLength")) { // Since 1.0.1
	$changed = true;
	echo '<li>', _t('스킨 설정 테이블에 공지 길이 제한 필드를 추가합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings ADD recentNoticeLength INT DEFAULT 30 NOT NULL AFTER expandTrackback"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryExistence("DESC {$database['prefix']}Categories `order`")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('분류 테이블의 우선순위 필드명을 변경합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories CHANGE `order` priority INT NOT NULL DEFAULT 0"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryExistence("DESC {$database['prefix']}Users `database`")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('사용자 테이블의 미사용 필드를 삭제합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Users DROP server, DROP `database`"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}RefererLogs url")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('레퍼러 로그 테이블의 구조를 변경합니다'), ': ';
	if (DBQuery::execute("UPDATE {$database['prefix']}RefererLogs SET path = CONCAT('http://', host, path)") && DBQuery::execute("ALTER TABLE {$database['prefix']}RefererLogs CHANGE path url VARCHAR(255) NOT NULL") && DBQuery::execute("ALTER TABLE {$database['prefix']}RefererLogs CHANGE written referred INT NOT NULL"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("SELECT count(*) FROM {$database['prefix']}Filters WHERE owner = 0")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('필터와 관련된 구조를 변경합니다'), ': ';
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
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	} else {
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
	}
}
if (DBQuery::queryExistence("SELECT count(*) FROM {$database['prefix']}FeedOwners WHERE owner = 0")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('리더와 관련된 구조를 변경합니다'), ': ';
	if (DBQuery::execute("DROP TABLE {$database['prefix']}FeedOwners"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryExistence("SELECT count(*) FROM {$database['prefix']}MonthlyStatistics WHERE owner = 0")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('통계와 관련된 구조를 변경합니다'), ': ';
	if (DBQuery::execute("DROP TABLE {$database['prefix']}MonthlyStatistics"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryExistence("SELECT * FROM {$database['prefix']}Users WHERE name = ''")) { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('사용자 이름 누락 정보를 보완합니다'), ': ';
	if (DBQuery::execute("UPDATE {$database['prefix']}Users SET name = IF(LEFT(loginid, POSITION('@' IN loginid) - 1) = '', loginid, LEFT(loginid, POSITION('@' IN loginid) - 1)) WHERE name = ''"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Entries owner", 'Key') != 'PRI') { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('엔트리 테이블의 인덱스를 수정합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Entries DROP PRIMARY KEY, ADD PRIMARY KEY(owner, id, draft)"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}TagRelations owner", 'Key') != 'PRI') { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('태그관계 테이블의 인덱스를 수정합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}TagRelations DROP PRIMARY KEY, ADD PRIMARY KEY(owner, tag, entry)"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Trackbacks owner", 'Key') != 'MUL') { // Since 1.0.2
	$changed = true;
	echo '<li>', _t('트랙백 테이블의 인덱스를 수정합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Trackbacks DROP INDEX entry, ADD UNIQUE owner (owner, entry, url)"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Comments parent", 'Key') != 'MUL') { // Since 1.0.3
	$changed = true;
	echo '<li>', _t('댓글 테이블에 인덱스를 추가합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Comments ADD INDEX parent (parent)"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (!Validator::getBool(DBQuery::queryCell("DESC {$database['prefix']}Sessions data", 'Null'))) { // Since 1.0.3
	$changed = true;
	echo '<li>', _t('세션 테이블의 필드 속성을 변경합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Sessions CHANGE data data TEXT DEFAULT NULL"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Categories name", 'Type') == 'varchar(32)') { // Since 1.0.3
	$changed = true;
	echo '<li>', _t('분류 테이블의 이름 필드 속성을 변경합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories CHANGE name name VARCHAR(127) NOT NULL"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}Categories label", 'Type') == 'varchar(80)') { // Since 1.0.3
	$changed = true;
	echo '<li>', _t('분류 테이블의 라벨 필드 속성을 변경합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Categories CHANGE label label VARCHAR(255) NOT NULL"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}BlogSettings timezone", 'Type') != 'varchar(32)') { // Since 1.0.5
	$changed = true;
	echo '<li>', _t('블로그 설정 테이블의 시간대 필드 속성을 변경합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}BlogSettings CHANGE timezone timezone VARCHAR(32) NOT NULL DEFAULT 'GMT'")) {
		DBQuery::execute("UPDATE {$database['prefix']}BlogSettings SET timezone = 'GMT' WHERE timezone <> '32400' AND timezone <> '-18000'");
		DBQuery::execute("UPDATE {$database['prefix']}BlogSettings SET timezone = 'Asia/Seoul' WHERE timezone = '32400'");
		DBQuery::execute("UPDATE {$database['prefix']}BlogSettings SET timezone = 'America/New_York' WHERE timezone = '-18000'");
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	} else {
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
	}
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}SkinSettings archivesOnPage")) {
	$changed = true;
	echo '<li>', _t('스킨 설정 테이블에 아카이브 출력 설정 필드를 추가합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}SkinSettings ADD archivesOnPage INT DEFAULT 5 NOT NULL AFTER commentsOnGuestbook"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}BlogSettings publishEolinSyncOnRSS")) {
	$changed = true;
	echo '<li>', _t('블로그 설정 테이블에 RSS 공개 정도 설정 필드를 추가합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}BlogSettings ADD publishEolinSyncOnRSS INT(1) DEFAULT 0 NOT NULL AFTER publishWholeOnRSS"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}Trackbacks isFiltered")) {
	$changed = true;
	echo '<li>', _t('트랙백 테이블에 광고 및 스팸 분류를 위한 휴지통 필드를 추가합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Trackbacks ADD isFiltered INT(1) DEFAULT 0 NOT NULL AFTER written"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (!DBQuery::queryExistence("DESC {$database['prefix']}Comments isFiltered")) {
	$changed = true;
	echo '<li>', _t('덧글및 방명록 테이블에 광고 및 스팸 분류를 위한 휴지통 필드를 추가합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}Comments ADD isFiltered INT(1) DEFAULT 0 AFTER written"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
if (DBQuery::queryCell("DESC {$database['prefix']}BlogSettings language", 'Type') != 'varchar(5)') { // Since 1.0.5
	$changed = true;
	echo '<li>', _t('블로그 설정 테이블의 언어 필드 속성을 변경합니다'), ': ';
	if (DBQuery::execute("ALTER TABLE {$database['prefix']}BlogSettings CHANGE language language VARCHAR(5) NOT NULL DEFAULT 'en'"))
		echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
	else
		echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
}
?>
</ul>
<?=($changed ? _t('완료되었습니다.') : _t('확인되었습니다.'))?>
</p>
</body>
</html>
