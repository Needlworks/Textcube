<?php
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 'on');

define('CRLF', "\n");
define('ROOT', ".");
define('PATH', dirname($_SERVER['PHP_SELF']));
define('DEBUG', "on");
define('TATTERTOOLS_NAME', 'Tattertools');
define('TATTERTOOLS_VERSION', '1.1a2 development branch');
define('TATTERTOOLS_COPYRIGHT', 'Copyright ⓒ 2004-2006. Tatter &amp; Company / Tatter &amp; Friends.');
define('TATTERTOOLS_HOMEPAGE', 'http://www.tattertools.com');
define('TATTERTOOLS_MANUAL', 'http://manual.tattertools.com');

require 'components/Eolin.PHP.Core.php';

$gWebConfigFile = ".htaccess";
$gUserConfigFile = "config.php";
$gAttachDir = "attach";
$gCacheDir = "cache";
$gRollbackFile = "rollback.data";
$grgTablesAsVersion = array();
$grgTablesAsVersion['0.96'] = <<<EOS
t3_{identifier}
t3_{identifier}_cnt_log
t3_{identifier}_count
t3_{identifier}_ct1
t3_{identifier}_ct2
t3_{identifier}_files
t3_{identifier}_guest
t3_{identifier}_guest_icon
t3_{identifier}_guest_reply
t3_{identifier}_keyword
t3_{identifier}_keyword_files
t3_{identifier}_link
t3_{identifier}_referlog
t3_{identifier}_referstat
t3_{identifier}_reply
t3_{identifier}_rss
t3_{identifier}_rss_group
t3_{identifier}_rss_item
t3_{identifier}_setting
t3_{identifier}_tblog
t3_{identifier}_trackback
EOS;
$grgTablesAsVersion['0.97'] = <<<EOS
t3_{identifier}_10ofmg
t3_{identifier}_10ofmg_cnt_log
t3_{identifier}_10ofmg_count
t3_{identifier}_10ofmg_ct1
t3_{identifier}_10ofmg_ct2
t3_{identifier}_10ofmg_files
t3_{identifier}_10ofmg_guest
t3_{identifier}_10ofmg_guest_icon
t3_{identifier}_10ofmg_guest_reply
t3_{identifier}_10ofmg_keyword
t3_{identifier}_10ofmg_keyword_files
t3_{identifier}_10ofmg_link
t3_{identifier}_10ofmg_notice_log
t3_{identifier}_10ofmg_notice_queue
t3_{identifier}_10ofmg_referlog
t3_{identifier}_10ofmg_referstat
t3_{identifier}_10ofmg_reply
t3_{identifier}_10ofmg_rss
t3_{identifier}_10ofmg_rss_group
t3_{identifier}_10ofmg_rss_item
t3_{identifier}_10ofmg_setting
t3_{identifier}_10ofmg_spam_filter
t3_{identifier}_10ofmg_tag
t3_{identifier}_10ofmg_tblog
t3_{identifier}_10ofmg_trackback
EOS;
$grgTablesAsVersion['1.0.b2'] = <<<EOS
{identifier}Attachments
{identifier}BlogSettings
{identifier}BlogStatistics
{identifier}Categories
{identifier}ContentFilters
{identifier}DailyStatistics
{identifier}Entries
{identifier}GuestFilters
{identifier}HostFilters
{identifier}Links
{identifier}MonthlyStatistics
{identifier}RefererLogs
{identifier}RefererStatistics
{identifier}Replies
{identifier}ReservedWords
{identifier}ServiceSetting
{identifier}SessionVisits
{identifier}Sessions
{identifier}SkinSettings
{identifier}TagRelations
{identifier}Tags
{identifier}TrackbackLogs
{identifier}Trackbacks
{identifier}URLFilters
{identifier}Users
EOS;
$grgTablesAsVersion['1.0.0'] = <<<EOS
{identifier}Attachments
{identifier}BlogSettings
{identifier}BlogStatistics
{identifier}Categories
{identifier}Comments
{identifier}CommentsNotified
{identifier}CommentsNotifiedQueue
{identifier}CommentsNotifiedSiteInfo
{identifier}ContentFilters
{identifier}DailyStatistics
{identifier}Entries
{identifier}FeedGroupRelations
{identifier}FeedGroups
{identifier}FeedItems
{identifier}FeedOwners
{identifier}FeedReads
{identifier}Feeds
{identifier}FeedSettings
{identifier}FeedStarred
{identifier}GuestFilters
{identifier}HostFilters
{identifier}Links
{identifier}MonthlyStatistics
{identifier}Personalization
{identifier}Plugins
{identifier}RefererLogs
{identifier}RefererStatistics
{identifier}ReservedWords
{identifier}Sessions
{identifier}SessionVisits
{identifier}SkinSettings
{identifier}TagRelations
{identifier}Tags
{identifier}TrackbackLogs
{identifier}Trackbacks
{identifier}URLFilters
{identifier}Users
EOS;
$grgTablesAsVersion['1.0.2'] = <<<EOS
{identifier}Attachments
{identifier}BlogSettings
{identifier}BlogStatistics
{identifier}Categories
{identifier}Comments
{identifier}CommentsNotified
{identifier}CommentsNotifiedQueue
{identifier}CommentsNotifiedSiteInfo
{identifier}DailyStatistics
{identifier}Entries
{identifier}FeedGroupRelations
{identifier}FeedGroups
{identifier}FeedItems
{identifier}FeedReads
{identifier}Feeds
{identifier}FeedSettings
{identifier}FeedStarred
{identifier}Filters
{identifier}Links
{identifier}Personalization
{identifier}Plugins
{identifier}RefererLogs
{identifier}RefererStatistics
{identifier}ReservedWords
{identifier}Sessions
{identifier}SessionVisits
{identifier}SkinSettings
{identifier}TagRelations
{identifier}Tags
{identifier}TrackbackLogs
{identifier}Trackbacks
{identifier}Users
EOS;
$grgTablesAsVersion['1.1.a2'] = <<<EOS
{identifier}Attachments
{identifier}BlogSettings
{identifier}BlogStatistics
{identifier}Categories
{identifier}Comments
{identifier}CommentsNotified
{identifier}CommentsNotifiedQueue
{identifier}CommentsNotifiedSiteInfo
{identifier}DailyStatistics
{identifier}Entries
{identifier}FeedGroupRelations
{identifier}FeedGroups
{identifier}FeedItems
{identifier}FeedReads
{identifier}Feeds
{identifier}FeedSettings
{identifier}FeedStarred
{identifier}Filters
{identifier}Links
{identifier}Plugins
{identifier}RefererLogs
{identifier}RefererStatistics
{identifier}ReservedWords
{identifier}ServiceSettings
{identifier}Sessions
{identifier}SessionVisits
{identifier}SkinSettings
{identifier}TagRelations
{identifier}Tags
{identifier}TrackbackLogs
{identifier}Trackbacks
{identifier}Users
{identifier}UserSettings
EOS;
	
/* 셋업 프로세스 시작 ************************************************************************************************/

// 변수 선언.
$grgProcessOrder = array();
empty($_POST['backupHead']) ? $_POST['backupHead'] = "backup_".date("YmdHis")."_" : NULL;

// $_POST['process'] 가공. IE의 'input type="image"' 처리 방식 오류를 해결하기 위한 방법임.
foreach ($_POST as $key => $value) {
	if (eregi("^([a-z]+)_([a-z]+)_([a-z0-9]+)_(x|y)$", $key, $rgTemp)) {
		$_POST[$rgTemp[1]] = $rgTemp[3];
		unset($_POST[$rgTemp[0]]);
		unset($_POST[$rgTemp[1]."_".$rgTemp[2]."_".$rgTemp[3]]);
	}
}

// 모드가 선택되지 않았으면 basic 모드로 지정.
if (empty($_POST['mode'])) {
	$_POST['mode'] = "basic";
	$_POST['process'] = 0;
} else {
	settype($_POST['process'], "integer");
}

// 언어 설정.
if (!empty($_POST['lang']))
	$gBaseLanguage = $_POST['lang'];
else if (!empty($_GET['lang']))
	$gBaseLanguage = $_GET['lang'];
else
	$gBaseLanguage = 'ko';
if (Locale::setDirectory('language'))
	Locale::set($gBaseLanguage);

// 모드별 프로세스 순서.
switch ($_POST['mode']) {
	case "basic":
		$grgProcessOrder[0] = array('step' => 1, 'title' => "시작 스크린");
		$grgProcessOrder[1] = array('step' => 2, 'title' => "셋업 타입 선택");
		break;
	case "install":
		$grgProcessOrder[] = array('step' => 3, 'title' => "데이터베이스 정보 입력 받기");
		$grgProcessOrder[] = array('step' => 3, 'title' => "입력받은 데이터베이스 정보 재확인");
		$grgProcessOrder[] = array('step' => 4, 'title' => "설치 시작 알림");
		$grgProcessOrder[] = array('step' => 4, 'title' => "PHP 함수 검사");
		$grgProcessOrder[] = array('step' => 4, 'title' => "mySQL의 UTF-8 지원 검사");
		$grgProcessOrder[] = array('step' => 4, 'title' => "데이터베이스 테이블 권한 검사");
		$grgProcessOrder[] = array('step' => 4, 'title' => "파일 시스템 권한 검사");
		$grgProcessOrder[] = array('step' => 4, 'title' => "첨부 파일 디렉토리 생성");
		$grgProcessOrder[] = array('step' => 4, 'title' => "캐시 디렉토리 생성");
		$grgProcessOrder[] = array('step' => 4, 'title' => "사용자 수정 스킨 디렉토리 생성");
		$grgProcessOrder[] = array('step' => 5, 'title' => "블로그 타입 선택");
		$grgProcessOrder[] = array('step' => 5, 'title' => "관리자 정보 입력 받기");
		$grgProcessOrder[] = array('step' => 5, 'title' => "입력받은 관리자 정보 재확인");
		$grgProcessOrder[] = array('step' => 5, 'title' => "데이터베이스 테이블 생성");
		$grgProcessOrder[] = array('step' => 5, 'title' => "웹 설정파일 생성");
		$grgProcessOrder[] = array('step' => 5, 'title' => "사용자 설정파일 생성");
		$grgProcessOrder[] = array('step' => 6, 'title' => "설치 완료");
		$grgProcessOrder[] = array('step' => 7, 'title' => "블로그 메인으로 페이지 이동");
		$grgProcessOrder[100] = array('step' => 6, 'title' => "설치 프로세스 중단");
		$grgProcessOrder[101] = array('step' => 6, 'title' => "셋업 메인으로 페이지 이동");
		break;
	case "optimize":
		$grgProcessOrder[] = array('step' => 4, 'title' => "데이터베이스 테이블 최적화");
		$grgProcessOrder[] = array('step' => 6, 'title' => "최적화 완료");
		$grgProcessOrder[] = array('step' => 7, 'title' => "셋업 메인으로 페이지 이동");
		$grgProcessOrder[100] = array('step' => 6, 'title' => "최적화 프로세스 중단");
		$grgProcessOrder[101] = array('step' => 6, 'title' => "셋업 메인으로 페이지 이동");
		break;
	case "repair":
		$grgProcessOrder[] = array('step' => 4, 'title' => "데이터베이스 테이블 수리");
		$grgProcessOrder[] = array('step' => 5, 'title' => "수리 완료");
		$grgProcessOrder[] = array('step' => 6, 'title' => "셋업 메인으로 페이지 이동");
		$grgProcessOrder[100] = array('step' => 5, 'title' => "수리 프로세스 중단");
		$grgProcessOrder[101] = array('step' => 6, 'title' => "셋업 메인으로 페이지 이동");
		break;
	case "reset":
		$grgProcessOrder[] = array('step' => 4, 'title' => "사용자 설정파일 재설정");
		$grgProcessOrder[] = array('step' => 6, 'title' => "재설정 완료");
		$grgProcessOrder[] = array('step' => 7, 'title' => "셋업 메인으로 페이지 이동");
		$grgProcessOrder[100] = array('step' => 6, 'title' => "재설정 프로세스 중단");
		$grgProcessOrder[101] = array('step' => 6, 'title' => "셋업 메인으로 페이지 이동");
		break;
	case "uninstall":
		$grgProcessOrder[] = array('step' => 4, 'title' => "설치 삭제 시작 알림");
		$grgProcessOrder[] = array('step' => 4, 'title' => "설치된 요소 삭제");
		$grgProcessOrder[] = array('step' => 5, 'title' => "설치 삭제 완료");
		$grgProcessOrder[] = array('step' => 6, 'title' => "태터툴즈 홈페이지로 페이지 이동");
		$grgProcessOrder[100] = array('step' => 5, 'title' => "설치 삭제 중단");
		$grgProcessOrder[101] = array('step' => 6, 'title' => "셋업 메인으로 페이지 이동");
		break;
}

$rgKeys = array_keys($grgProcessOrder);

foreach ($rgKeys as $iCount) {
	if ($iCount >= $_POST['process']) {
		
		// 대화상자 클래스 인스턴스 생성.
		if (is_object($objSetupWindow)) {
			$objSetupWindow->initialize();
		} else {
			$objSetupWindow = new CSetupDialog();
			$objSetupWindow->baseLanguage = $gBaseLanguage;
		}
		
		// 권한 검사.
		$gCheckLogin = checkLoginStatus();
		if ($gCheckLogin === -2) {
			$objSetupWindow->callSetupDialog("권한 검사 실패");
			exit;
		} else if ($gCheckLogin === -3) {
			$objSetupWindow->callSetupDialog("로그인 검사 실패");
			exit;
		}
		
		switch ($grgProcessOrder[$iCount]['title']) {
			/* BASIC *************************************************************************************************/
			// *시작 화면.
			case "시작 스크린":
				$objSetupWindow->callSetupWindow("시작 스크린");
				exit;
			// *설치타입 선택 화면.
			case "셋업 타입 선택":
				$objSetupWindow->callSetupWindow("셋업 타입 선택");
				exit;
			
			/* INSTALL **********************************************************************************************/
			// *데이터베이스 정보 입력 받기.
			case "데이터베이스 정보 입력 받기":
				if ($_POST['check'] == "true") {
					$iCheckResult = checkInputValue("데이터베이스 정보");
					if ($iCheckResult === true) {
						$_POST['process']++;
					} else {
						$objSetupWindow->callSetupWindow("데이터베이스 정보 입력 받기", $iCheckResult);
						exit;
					}
				} else {
					$objSetupWindow->callSetupWindow("데이터베이스 정보 입력 받기");
					exit;
				}
				break;
			// *입력된 데이터베이스 정보 재확인.
			case "입력받은 데이터베이스 정보 재확인":
				$objSetupWindow->callSetupWindow("입력받은 데이터베이스 정보 재확인");
				exit;
			// *설치시작 알림 대화상자.
			case "설치 시작 알림":
				$objSetupWindow->callSetupWindow("설치 시작 알림");
				exit;
			// *필수 PHP 함수 체크.
			case "PHP 함수 검사":
				list($bCheckResult, $rgOmittedFunctions) = checkRequiredFunctions();
				if ($bCheckResult == false) {
					$objSetupWindow->callSetupDialog("PHP 함수 검사 실패", $rgOmittedFunctions);
					exit;
				} else {
					$_POST['process']++;
				}
				break;
			// *mySQL의 UTF-8 지원여부 검사.
			case "mySQL의 UTF-8 지원 검사":
				$iCheckResult = checkEncodingOfDB();
				if ($iCheckResult === true) {
					$_POST['process']++;
				} else if ($iCheckResult === 0) {
					$objSetupWindow->callSetupDialog("UTF-8 미지원");
					exit;
				} else if ($iCheckResult === -1) {
					$objSetupWindow->callSetupDialog("데이터베이스 연결 실패");
					exit;
				}
				break;
			// *mySQL 테이블 생성권한 검사.
			case "데이터베이스 테이블 권한 검사":
				$iCheckResult = checkTablePrivileges();
				if ($iCheckResult === true) {
					$_POST['process']++;
				} else if ($iCheckResult === 0) {
					$objSetupWindow->callSetupDialog("데이터베이스 테이블 권한 없음");
					exit;
				} else if ($iCheckResult === -1) {
					$objSetupWindow->callSetupDialog("데이터베이스 연결 실패");
					exit;
				}
				break;
			// *파일 시스템 권한 검사.
			case "파일 시스템 권한 검사":
				if (checkFileSystemPrivileges() == false) {
					$objSetupWindow->callSetupDialog("파일 시스템 권한 없음");
					exit;
				} else {
					// Rollback 인스턴스 생성.
					$objRollback = new CRollback($gRollbackFile);
					$objRollback->backupHead = $_POST['backupHead'];
					$_POST['process']++;
				}
				break;
			// *attach 디렉토리 생성.
			case "첨부 파일 디렉토리 생성":
				if (!createDir($gAttachDir, _t('첨부파일 디렉토리'), $objRollback))
					exit;
				else
					$_POST['process']++;
				break;
			// *cache 디렉토리 생성.
			case "캐시 디렉토리 생성":
				if (!createDir($gCacheDir, _t('캐시 디렉토리'), $objRollback))
					exit;
				else
					$_POST['process']++;
				break;
			// *skin/customize 디렉토리 생성.
			case "사용자 수정 스킨 디렉토리 생성":
				if (!createDir("skin/customize", _t('사용자 수정 스킨 디렉토리'), $objRollback))
					exit;
				else
					$_POST['process']++;
				break;
			// *블로그 타입 선택하기.
			case "블로그 타입 선택":
				$iRewrite = checkRewriteMod();
				if ($iRewrite > 0) {
					$objSetupWindow->callSetupWindow("블로그 타입 선택", $iRewrite);
					exit;
				} else {
					$objSetupWindow->callSetupDialog("mod_rewrite 오류");
					exit;
				}
				break;
			// *관리자 정보 입력 받기.
			case "관리자 정보 입력 받기":
				if ($_POST['check'] == "true") {
					$iCheckResult = checkInputValue("관리자 정보");
					if ($iCheckResult === true) {
						$_POST['process']++;
					} else {
						$objSetupWindow->callSetupWindow("관리자 정보 입력 받기", $iCheckResult);
						exit;
					}
				} else {
					$objSetupWindow->callSetupWindow("관리자 정보 입력 받기");
					exit;
				}
				break;
			// *입력받은 관리자 정보 재확인.
			case "입력받은 관리자 정보 재확인":
				$objSetupWindow->callSetupWindow("입력받은 관리자 정보 재확인");
				exit;
			// *mySQL table 생성.
			case "데이터베이스 테이블 생성":
				if ($_POST['overwriteThis'] == "true")
					$strTempMode = "overwrite";
				else
					$strTempMode = "new";
				
				$iCreateResult = createTables($strTempMode, $_POST['backupHead']);
				
				if ($iCreateResult === true) {
					$_POST['process']++;
				} else if ($iCreateResult === false) {
					$objSetupWindow->callSetupDialog("데이터베이스 연결 실패");
					exit;
				} else if ($iCreateResult === -1) {
					$objSetupWindow->callSetupDialog("Rollback 오류");
					exit;
				} else if ($iCreateResult === -2) {
					$objSetupWindow->callSetupDialog("데이터베이스 테이블 생성 오류");
					exit;
				} else if ($iCreateResult === -3) {
					$objSetupWindow->callSetupDialog("기존 테이블이 존재함");
					exit;
				}
				unset($_POST['overwriteThis']);
				break;
			// *.htaccess 파일 생성.
			case "웹 설정파일 생성":
				if (!createFile($gWebConfigFile, _t('웹 설정파일'), $objRollback))
					exit;
				else
					$_POST['process']++;
				break;
			// *config.php 파일 생성.
			case "사용자 설정파일 생성":
				if (!createFile($gUserConfigFile, _t('사용자 설정파일'), $objRollback))
					exit;
				else
					$_POST['process']++;
				break;
			// *설치 중단.
			case "설치 프로세스 중단":
				if (!is_object($objRollback)) {
					$objRollback = new CRollback($gRollbackFile);
					$objRollback->backupHead = $_POST['backupHead'];
				}
				$objRollback->executeRollback();
				$objSetupWindow->callSetupWindow("프로세스 중단", "install");
				exit;
			case "설치 완료":
				if (!is_object($objRollback)) {
					$objRollback = new CRollback($gRollbackFile);
					$objRollback->backupHead = $_POST['backupHead'];
				}
				$objRollback->cancelRollback();
				$objSetupWindow->callSetupWindow("프로세스 완료", "install");
				exit;
			
			/* OPTIMIZE **********************************************************************************************/
			// *테이블 최적화.
			case "데이터베이스 테이블 최적화":
				if ($gCheckLogin === true) {
					optimizeTables($database);
					$_POST['process']++;
				} else if ($gCheckLogin === -1) {
					$objSetupWindow->callSetupDialog("사용자 설정파일 검사 실패");
					exit;
				} else if ($gCheckLogin === -2) {
					$objSetupWindow->callSetupDialog("권한 검사 실패");
					exit;
				} else if ($gCheckLogin === -3) {
					$objSetupWindow->callSetupDialog("로그인 검사 실패");
					exit;
				}
				break;
			// *최적화 중단.
			case "최적화 프로세스 중단":
				$objSetupWindow->callSetupWindow("프로세스 중단", "optimize");
				exit;
			// *최적화 완료 메세지.
			case "최적화 완료":
				$objSetupWindow->callSetupWindow("프로세스 완료", "optimize");
				exit;
			
			/* RESET *************************************************************************************************/
			// *사용자 설정파일 재설정.
			case "사용자 설정파일 재설정":
				if ($gCheckLogin === true) {
					resetConfig();
					$_POST['process']++;
				} else if ($gCheckLogin === -1) {
					$objSetupWindow->callSetupDialog("사용자 설정파일 검사 실패");
					exit;
				} else if ($gCheckLogin === -2) {
					$objSetupWindow->callSetupDialog("권한 검사 실패");
					exit;
				} else if ($gCheckLogin === -3) {
					$objSetupWindow->callSetupDialog("로그인 검사 실패");
					exit;
				}
				break;
			// *재설정 중단.
			case "재설정 프로세스 중단":
				$objSetupWindow->callSetupWindow("프로세스 중단", "reset");
				exit;
			// *재설정 완료 메세지.
			case "재설정 완료":
				$objSetupWindow->callSetupWindow("프로세스 완료", "reset");
				exit;
			
			/* REPAIR ************************************************************************************************/
			// *설치시 생성된 파일/디렉토리/데이터베이스 테이블 삭제.
			case "데이터베이스 테이블 수리":
				if ($gCheckLogin === true) {
					repairTables($database);
					$_POST['process']++;
				} else if ($gCheckLogin === -1) {
					$objSetupWindow->callSetupDialog("사용자 설정파일 검사 실패");
					exit;
				} else if ($gCheckLogin === -2) {
					$objSetupWindow->callSetupDialog("권한 검사 실패");
					exit;
				} else if ($gCheckLogin === -3) {
					$objSetupWindow->callSetupDialog("로그인 검사 실패");
					exit;
				}
				break;
			// *테이블 수리 중단.
			case "수리 프로세스 중단":
				$objSetupWindow->callSetupWindow("프로세스 중단", "repair");
				exit;
			// *수리 완료 메세지.
			case "수리 완료":
				$objSetupWindow->callSetupWindow("프로세스 완료", "repair");
				exit;
			
			/* UNINSTALL *********************************************************************************************/
			// *삭제 공지 메세지.
			case "설치 삭제 시작 알림":
				$objSetupWindow->callSetupWindow("설치 삭제 시작 알림");
				exit;
			// *설치시 생성된 파일/디렉토리/데이터베이스 테이블 삭제.
			case "설치된 요소 삭제":
				if ($gCheckLogin === true) {
					if (!deleteFiles() || !deleteDirs() || !removeDatabaseTables())
						exit;
					$_POST['process']++;
				} else if ($gCheckLogin === -1) {
					$objSetupWindow->callSetupDialog("사용자 설정파일 검사 실패");
					exit;
				} else if ($gCheckLogin === -2) {
					$objSetupWindow->callSetupDialog("권한 검사 실패");
					exit;
				} else if ($gCheckLogin === -3) {
					$objSetupWindow->callSetupDialog("로그인 검사 실패");
					exit;
				}
				break;
			// *삭제 중단.
			case "설치 삭제 프로세스 중단":
				if (!is_object($objRollback)) {
					$objRollback = new CRollback($gRollbackFile);
					$objRollback->backupHead = $_POST['backupHead'];
				}
				$objRollback->executeRollback();
				$objSetupWindow->callSetupWindow("프로세스 중단", "uninstall");
				exit;
			// *삭제 완료 메세지.
			case "설치 삭제 완료":
				if (!is_object($objRollback)) {
					$objRollback = new CRollback($gRollbackFile);
					$objRollback->backupHead = $_POST['backupHead'];
				}
				$objRollback->cancelRollback();
				$objSetupWindow->callSetupWindow("프로세스 완료", "uninstall");
				exit;
			
			/* COMMON ************************************************************************************************/
			case "블로그 메인으로 페이지 이동":
				$strBlogURL = $_POST['blogType'] == 'path' ? PATH."/{$_POST['blog']}" : PATH;
				header("Location: ".$strBlogURL);
				exit;
			case "셋업 메인으로 페이지 이동":
				header("Location: ".PATH."/setup.php");
				exit;	
			case "태터툴즈 홈페이지로 페이지 이동":
				header("Location: http://www.tattertools.com");
				exit;
		}
	}
}

/* 함수 **************************************************************************************************************/

// addHeadToFileName()
function addHeadToFileName($argFilePath, $argHead) {
	if (empty($argFilePath) || $argFilePath == "." || $argFilePath == "..")
		return false;
	
	$rgName = explode("/", $argFilePath);
	$strFileName = array_pop($rgName);
	
	if (empty($strFileName)) {
		return false;
	} else if (eregi("^\.([a-z0-9_]+)$", $strFileName, $rgTemp)) {
		$strNewName = ".".$argHead.$rgTemp[1];
	} else {
		$strNewName = $argHead.$strFileName;
	}
	
	array_push($rgName, $strNewName);
	return implode("/", $rgName);
}

// checkDir()
function checkDir($argDirPath) {
	if (file_exists($argDirPath)) {
		if (!is_dir($argDirPath)) {
			return -1;
		} else if (!is_writable($argDirPath)) {
			return -2;
		} else {
			return -3;
		}
	} else {
		return true;
	}
}

// checkEncodingOfDB()
function checkEncodingOfDB() {
	global $_POST;
	
	if (connectDatabase($_POST['dbServer'], $_POST['dbUser'], $_POST['dbPassword'], $_POST['dbName'])) {
		if (DBQuery::execute("SET CHARACTER SET 'utf8'") && DBQuery::execute("SET SESSION collation_connection = 'utf8_general_ci'")) {
			return true;
		} else {
			return 0;
		}
	} else {
		return -1;
	}
}

// checkFile()
function checkFile($argFilePath) {
	if (file_exists($argFilePath)) {
		if (!is_file($argFilePath)) {
			return -1;
		} else if (!is_writable($argFilePath)) {
			return -2;
		} else {
			return -3;
		}
	} else {
		return true;
	}
}

// checkFileSystemPrivileges()
function checkFileSystemPrivileges() {
	if (!is_writable(ROOT.'/')) {
		return false;
	} else {
		return true;
	}
}

// checkInputValue()
function checkInputValue($argType) {
	if ($argType == "데이터베이스 정보") {
		if (!empty($_POST['dbServer']) && !empty($_POST['dbName']) && !empty($_POST['dbUser']) && isset($_POST['dbPassword']) && isset($_POST['dbPrefix'])) {
			if (!mysql_connect($_POST['dbServer'], $_POST['dbUser'], $_POST['dbPassword'])) {
				return -1;
			} else if (!mysql_select_db($_POST['dbName'])) {
				return -2;
			} else if (!empty($_POST['dbPrefix']) && !ereg('^[[:alnum:]_]+$', $_POST['dbPrefix'])) {
				return -3;
			} else {
				return true;
			}
		} else {
			return -4;
		}
	} else if ($argType == "관리자 정보") {
		if (empty($_POST['email'])) {
			return -1;
		} else if (empty($_POST['password']) || empty($_POST['password2'])) {
			return -2;
		} else if ($_POST['password'] != $_POST['password2']) {
			return -3;
		} else if (empty($_POST['blog'])) {
			return -4;
		} else if (empty($_POST['name'])) {
			return -5;
		} else {
			return true;
		}
	}
}

// checkInstalledVersion()
function checkInstalledVersion() {
	global $gUserConfigFile;
	
	// config.php 파일과 DB가 존재하면 에러.
	if (file_exists(ROOT."/$gUserConfigFile")) {
		return true;
	}
}

// checkLoginStatus()
function checkLoginStatus() {
	global $gUserConfigFile;
	
	if (!file_exists($gUserConfigFile)) {
		// config.php가 없음.
		return -1;
	}
	
	include_once $gUserConfigFile;
	session_name('TSSESSION');
	setSession();
	session_cache_expire(1);
	session_set_cookie_params(0, '/', $service['domain']);
	session_start();
	
	return doesHaveAdminship();
}

// checkRequiredFunctions()
function checkRequiredFunctions() {
	$rgOmittedFunctions = array();
	foreach (getRequiredFunctions() as $strTempFunction) {
		// 누락된 함수를 저장.
		if (!function_exists($strTempFunction)) {
			array_push($rgOmittedFunctions, $strTempFunction);
		}
	}
	
	if (count($rgOmittedFunctions) > 0) {
		return array(false, $rgOmittedFunctions);
	} else {
		return array(true, NULL);
	}
}

// checkRewriteMod()
function checkRewriteMod() {
	global $_SERVER, $_POST, $gWebConfigFile;
	
	if (file_exists(ROOT."/".$gWebConfigFile))
		rename(ROOT."/".$gWebConfigFile, addHeadToFileName(ROOT."/".$gWebConfigFile, $_POST['backupHead']));
	
	$pFile = fopen(ROOT."/".$gWebConfigFile, 'w+');
	fwrite($pFile, "RewriteEngine On\nRewriteBase ".PATH."/\nRewriteRule ^testrewrite$ setup.php [L]");
	fclose($pFile);
	chmod(ROOT."/".$gWebConfigFile, 0666);
	
	if (checkWebConfig(substr(getFingerPrint(), 0, 6).substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.')), PATH.'/testrewrite?test=now', $_SERVER['SERVER_PORT']))
		$iRewrite = 3;
	else if (checkWebConfig(substr(getFingerPrint(), 0, 6).'.'.$_SERVER['HTTP_HOST'], PATH.'/testrewrite?test=now', $_SERVER['SERVER_PORT']))
		$iRewrite = 2;
	else if (checkWebConfig($_SERVER['HTTP_HOST'], PATH.'/testrewrite?test=now', $_SERVER['SERVER_PORT']))
		$iRewrite = 1;
	else
		$iRewrite = 0;
	
	unlink(ROOT."/".$gWebConfigFile);
	
	if (file_exists(addHeadToFileName(ROOT."/".$gWebConfigFile, $_POST['backupHead'])))
		rename(addHeadToFileName(ROOT."/".$gWebConfigFile, $_POST['backupHead']), ROOT."/".$gWebConfigFile);
	
	return $iRewrite;
}

// checkTablePrivileges()
function checkTablePrivileges() {
	global $_POST;
	
	if (connectDatabase($_POST['dbServer'], $_POST['dbUser'], $_POST['dbPassword'], $_POST['dbName'])) {
		$strTestTable = date("YmdHis");
		if (DBQuery::execute("CREATE TABLE `test_$strTestTable` (a INT NOT NULL)")) {
			DBQuery::execute("DROP TABLE `test_$strTestTable`");
			return true;
		} else {
			return 0;
		}
	} else {
		return -1;
	}
}

// checkWebConfig()
function checkWebConfig($argHost, $argPath, $argPort) {
	$pSocket = @fsockopen($argHost, $argPort, $iErrNo, $strErr, 10);
	if ($pSocket === false)
	    return false;
	
	fputs($pSocket, "GET $argPath HTTP/1.1\r\n");
	fputs($pSocket, "Host: $argHost\r\n");
	fputs($pSocket, "User-Agent: Mozilla/4.0 (compatible; Tattertools Setup)\r\n");
	fputs($pSocket, "Connection: close\r\n");
	fputs($pSocket, "\r\n");
	
	$strResponse = '';
	while (!feof($pSocket))
	    $strResponse .= fgets($pSocket, 128);
	fclose($pSocket);
	
	return strstr($strResponse, "Select Default Language") ? true : false;
}

// connectDatabase()
function connectDatabase($argDbServer, $argDbUser, $argDbPassword, $argDbName) {
	if (mysql_connect($argDbServer, $argDbUser, $argDbPassword) && mysql_select_db($argDbName))
		return true;
	else
		return false;
}

// createDir()
function createDir($argDirName, $argDirKorName) {
	global $_POST, $gBaseLanguage, $gRollbackFile;
	
	$objSetupWindow = new CSetupDialog();
	$objSetupWindow->baseLanguage = $gBaseLanguage;
	$argDirPath = ROOT."/$argDirName";
	$iCheckResult = checkDir($argDirPath);
	
	// 디렉토리가 존재하면.
	if ($iCheckResult === -1) {
		// 되돌려 받은 선택 값이 "덮어쓰기"라면 덮어쓰기.
		if ($_POST['overwriteThis'] == "true" || $_POST['cleanInstall'] == "true") {
			if (makeDirMod("overwrite", $argDirPath, $_POST['backupHead'])) {
				$objRollback = new CRollback($gRollbackFile);
				$objRollback->backupHead = $_POST['backupHead'];
				if (!$objRollback->addToList("overwrite", "dir", $argDirPath) && $_POST['ignoreRollback'] != "true") {
					$objSetupWindow->callSetupDialog("Rollback 오류");
					return false;
				}
			} else {
				$objSetupWindow->callSetupDialog("디렉토리 덮어쓰기 실패", $argDirKorName);
				return false;
			}
		// 되돌려 받은 선택 값이 "덮어쓰지 말기"라면 오류 출력.
		} else if ($_POST['overwriteThis'] == "false" && $_POST['cleanInstall'] != "true") {
			$objSetupWindow->callSetupDialog("디렉토리를 덮어쓸 것인지 재확인", $argDirKorName);
			return false;
		// 첫 화면이면 선택 대화상자를 띄움.
		} else {
			$objSetupWindow->callSetupDialog("파일을 덮어쓸 것인지 재확인", $argDirKorName);
			return false;
		}
	} else if ($iCheckResult === -2) {
		$objSetupWindow->callSetupDialog("디렉토리 수정 권한 없음.", $argDirKorName);
		return false;
	} else if ($iCheckResult === -3) {
		// 되돌려 받은 선택 값이 "덮어쓰기"라면 정상적으로 덮어쓰기.
		if ($_POST['overwriteThis'] == "true" || $_POST['cleanInstall'] == "true") {
			if (makeDirMod("overwrite", $argDirPath, $_POST['backupHead'])) {
				$objRollback = new CRollback($gRollbackFile);
				$objRollback->backupHead = $_POST['backupHead'];
				if (!$objRollback->addToList("overwrite", "dir", $argDirPath) && $_POST['ignoreRollback'] != "true") {
					$objSetupWindow->callSetupDialog("Rollback 오류");
					return false;
				}
			} else {
				$objSetupWindow->callSetupDialog("디렉토리 덮어쓰기 실패", $argDirKorName);
				return false;
			}
		// 통과.
		} else if ($_POST['overwriteThis'] == "false" && $_POST['cleanInstall'] != "true") {
			// 재사용.
		// 첫 접속이므로 새로 생성한다는 대화상자 출력.
		} else {
			$objSetupWindow->callSetupDialog("디렉토리를 덮어쓸 것인지 재확인", $argDirKorName);
			return false;
		}
	// 디렉토리가 존재하지 아니면.
	} else {
		// 새 디렉토리 생성.
		if (makeDirMod("new", $argDirPath, $_POST['backupHead'])) {
			$objRollback = new CRollback($gRollbackFile);
			$objRollback->backupHead = $_POST['backupHead'];
			if (!$objRollback->addToList("create", "Dir", $argDirPath) && $_POST['ignoreRollback'] != "true") {
				$objSetupWindow->callSetupDialog("Rollback 오류");
				return false;
			}
		// 실패.
		} else {
			$objSetupWindow->callSetupDialog("디렉토리 생성 실패", $argDirKorName);
			return false;
		}
	}
	
	unset($_POST['overwriteThis']);
	return true;
}

// createFile()
function createFile($argFileName, $argFileKorName, &$objRollback) {
	global $_POST, $gBaseLanguage, $gRollbackFile;
	
	$objSetupWindow = new CSetupDialog();
	$objSetupWindow->baseLangauge = $gBaseLanguage;
	$objRollback = new CRollback($gRollbackFile);
	$objRollback->backupHead = $_POST['backupHead'];
	
	$argFilePath = ROOT."/$argFileName";
	$iCheckResult = checkFile($argFilePath);
	
	if ($iCheckResult === -1) {
		// 되돌려 받은 선택 값이 "덮어쓰기"라면 정상적으로 덮어쓰기.
		if ($_POST['overwriteThis'] == "true" || $_POST['cleanInstall'] == "true") {
			if (makeFileMod("overwrite", $argFilePath, $_POST['backupHead'])) {
				if (!$objRollback->addToList("overwrite", "file", $argFilePath) && $_POST['ignoreRollback'] != "true") {
					$objSetupWindow->callSetupDialog("Rollback 오류");
					return false;
				}
			} else {
				$objSetupWindow->callSetupDialog("파일 덮어쓰기 실패", $argFileKorName);
				return false;
			}
		// 되돌려 받은 선택 값이 "덮어쓰지 말기"라면 오류 출력.
		} else if ($_POST['overwriteThis'] == "false" && $_POST['cleanInstall'] != "true") {
			$objSetupWindow->callSetupDialog("파일을 덮어쓸 것인지 재확인", $argFileKorName);
			return false;
		// 첫 화면이면 선택 대화상자를 띄움.
		} else {
			$objSetupWindow->callSetupDialog("디렉토리를 덮어쓸 것인지 재확인", $argFileKorName);
			return false;
		}
	// 쓰기 권한이 없는 경우.
	} else if ($iCheckResult === -2) {
		$objSetupWindow->callSetupDialog("파일 수정 권한 없음.", $argFileKorName);
		return false;
	// $argFilePath 파일이 있고, 쓰기 권한도 있는 경우.
	} else if ($iCheckResult === -3) {
		// 되돌려 받은 선택 값이 "덮어쓰기"라면 정상적으로 덮어쓰기.
		if ($_POST['overwriteThis'] == "true" || $_POST['cleanInstall'] == "true") {
			if (makeFileMod("overwrite", $argFilePath, $_POST['backupHead'])) {
				if (!$objRollback->addToList("overwrite", "file", $argFilePath) && $_POST['ignoreRollback'] != "true") {
					$objSetupWindow->callSetupDialog("Rollback 오류");
					return false;
				}
			} else {
				$objSetupWindow->callSetupDialog("파일 덮어쓰기 실패", $argFileKorName);
				return false;
			}
		// 되돌려 받은 선택 값이 "덮어쓰지 말기"라면 통과.
		} else if ($_POST['overwriteThis'] == "false" && $_POST['cleanInstall'] != "true") {
			// 통과. 재사용.
		// 첫 접속이므로 새로 생성한다는 대화상자 출력.
		} else {
			$objSetupWindow->callSetupDialog("파일을 덮어쓸 것인지 재확인", $argFileKorName);
			return false;
		}
	} else {
		// 새 파일 생성.
		if (makeFileMod("new", $argFilePath, $_POST['backupHead'])) {
			if (!$objRollback->addToList("create", "file", $argFilePath) && $_POST['ignoreRollback'] != "true") {
				$objSetupWindow->callSetupDialog("Rollback 오류");
				return false;
			}
		} else {
			$objSetupWindow->callSetupDialog("파일 생성 실패", $argFileKorName);
			return false;
		}
	}
	
	unset($_POST['overwriteThis']);
	return true;
}

// createTables()
function createTables($argType, $argHead=NULL) {
	global $_POST, $gBaseLanguage, $gRollbackFile;
	
	if (!connectDatabase($_POST['dbServer'], $_POST['dbUser'], $_POST['dbPassword'], $_POST['dbName'])) {
		return false;
	}
	
	list($strTemp, $rgTablesOfLastVersion) = getTablesOfLastVersion($_POST['dbPrefix']);
	
	$rgAllTables = DBQuery::queryAll("SHOW TABLES");
	
	foreach ($rgAllTables as $strTempTable) {
		if (in_array($strTempTable[0], $rgTablesOfLastVersion)) {
			if ($argType == "overwrite") {
				// 기존 테이블을 백업한다.
				if (in_array($argHead.strtolower($strTempTable[0]), $rgAllTables)) {
					DBQuery::execute("DROP TABLE `".$argHead.strtolower($strTempTable[0])."`");
				}
				DBQuery::execute("RENAME TABLE `".strtolower($strTempTable[0])."` TO `".$argHead.strtolower($strTempTable[0])."`");
			} else {
				return -3;
			}
		}
	}
	
	if (!DBQuery::execute("SET CHARACTER SET 'utf8'")) {
		$strCharset = "TYPE=MyISAM";
	} else {
		$strCharset = "TYPE=MyISAM DEFAULT CHARSET=utf8";
	}
	DBQuery::execute("SET SESSION collation_connection = 'utf8_general_ci'");
	$strCreationQuery = 
		"CREATE TABLE `{$_POST['dbPrefix']}Attachments` (
			`owner` int(11) NOT NULL default '0',
			`parent` int(11) NOT NULL default '0',
			`name` varchar(32) NOT NULL default '',
			`label` varchar(64) NOT NULL default '',
			`mime` varchar(32) NOT NULL default '',
			`size` int(11) NOT NULL default '0',
			`width` int(11) NOT NULL default '0',
			`height` int(11) NOT NULL default '0',
			`attached` int(11) NOT NULL default '0',
			`downloads` int(11) NOT NULL default '0',
			`enclosure` tinyint(1) NOT NULL default '0',
			PRIMARY KEY (`owner`,`name`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}BlogSettings` (
			`owner` int(11) NOT NULL default '0',
			`name` varchar(32) NOT NULL default '',
			`secondaryDomain` varchar(64) NOT NULL default '',
			`defaultDomain` int(1) NOT NULL default '0',
			`url` varchar(80) NOT NULL default '',
			`title` varchar(255) NOT NULL default '',
			`description` varchar(255) NOT NULL default '',
			`logo` varchar(64) NOT NULL default '',
			`logoLabel` varchar(255) NOT NULL default '',
			`logoWidth` int(11) NOT NULL default '0',
			`logoHeight` int(11) NOT NULL default '0',
			`useSlogan` int(1) NOT NULL default '1',
			`entriesOnPage` int(11) NOT NULL default '10',
			`entriesOnList` int(11) NOT NULL default '10',
			`entriesOnRSS` int(11) NOT NULL default '10',
			`publishWholeOnRSS` int(1) NOT NULL default '1',
			`publishEolinSyncOnRSS` int(1) NOT NULL default '1',
			`allowWriteOnGuestbook` int(1) NOT NULL default '1',
			`allowWriteDoubleCommentOnGuestbook` char(1) NOT NULL default '1',
			`language` VARCHAR(5) NOT NULL DEFAULT 'en',
			`blogLanguage` VARCHAR(5) NOT NULL DEFAULT 'en',
			`timezone` VARCHAR(32) NOT NULL DEFAULT 'GMT',
			PRIMARY KEY (`owner`),
			UNIQUE KEY name (name)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}BlogStatistics` (
			`owner` int(11) NOT NULL default '0',
			`visits` int(11) NOT NULL default '0',
			PRIMARY KEY (`owner`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Categories` (
			`owner` int(11) NOT NULL default '0',
			`id` int(11) NOT NULL auto_increment,
			`parent` int(11) default NULL,
			`name` varchar(127) NOT NULL default '',
			`priority` int(11) NOT NULL default '0',
			`entries` int(11) NOT NULL default '0',
			`entriesInLogin` int(11) NOT NULL default '0',
			`label` varchar(255) NOT NULL default '',
			`visibility` tinyint(4) NOT NULL default '2',
			`bodyId` varchar(20) default NULL,
			PRIMARY KEY (`id`),
			KEY `owner` (`owner`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Comments` (
			`owner` int(11) NOT NULL default '0',
			`replier` int(11) default NULL,
			`id` int(11) NOT NULL auto_increment,
			`entry` int(11) NOT NULL default '0',
			`parent` int(11) default NULL,
			`name` varchar(80) NOT NULL default '',
			`password` varchar(32) NOT NULL default '',
			`homepage` varchar(80) NOT NULL default '',
			`email` varchar(80) NULL default '',
			`secret` int(1) NOT NULL default '0',
			`comment` text NOT NULL,
			`ip` varchar(15) NOT NULL default '',
			`written` int(11) NOT NULL default '0',
			`isFiltered` int(1) NOT NULL default '0',
			PRIMARY KEY (id),
			KEY `owner` (`owner`),
			KEY `entry` (`entry`),
			KEY `parent` (`parent`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}CommentsNotified` (
			`owner` int(11) NOT NULL default '0',
			`replier` int(11) default NULL,
			`id` int(11) NOT NULL auto_increment,
			`entry` int(11) NOT NULL default '0',
			`parent` int(11) default NULL,
			`name` varchar(80) NOT NULL default '',
			`password` varchar(32) NOT NULL default '',
			`homepage` varchar(80) NOT NULL default '',
			`secret` int(1) NOT NULL default '0',
			`comment` text NOT NULL,
			`ip` varchar(15) NOT NULL default '',
			`written` int(11) NOT NULL default '0',
			`modified` int(11) NOT NULL default '0',
			`siteId` int(11) NOT NULL default '0',
			`isNew` int(1) NOT NULL default '1',
			`url` varchar(255) NOT NULL default '',
			`remoteId` int(11) NOT NULL default '0',
			`entryTitle` varchar(255) NOT NULL default '',
			`entryUrl` varchar(255) NOT NULL default '',
			PRIMARY KEY (`id`),
			KEY `owner` (`owner`),
			KEY `entry` (`entry`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}CommentsNotifiedQueue` (
			`owner` int(11) NOT NULL default '0',
			`id` int(11) NOT NULL auto_increment,
			`commentId` int(11) NOT NULL default '0',
			`sendStatus` int(1) NOT NULL default '0',
			`checkDate` int(11) NOT NULL default '0',
			`written` int(11) NOT NULL default '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `commentId` (`commentId`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}CommentsNotifiedSiteInfo` (
			`id` int(11) NOT NULL auto_increment,
			`title` varchar(255) NOT NULL default '',
			`name` varchar(255) NOT NULL default '',
			`url` varchar(255) NOT NULL default '',
			`modified` int(11) NOT NULL default '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `url` (`url`),
			UNIQUE KEY `id` (`id`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}DailyStatistics` (
			`owner` int(11) NOT NULL default '0',
			`date` int(11) NOT NULL default '0',
			`visits` int(11) NOT NULL default '0',
			PRIMARY KEY (`owner`,`date`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Entries` (
			`owner` int(11) NOT NULL default '0',
			`id` int(11) NOT NULL auto_increment,
			`draft` tinyint(1) NOT NULL default '0',
			`visibility` tinyint(4) NOT NULL default '0',
			`category` int(11) NOT NULL default '0',
			`title` varchar(255) NOT NULL default '',
			`slogan` varchar(255) NOT NULL default '',
			`content` mediumtext NOT NULL,
			`location` varchar(255) NOT NULL default '/',
			`password` varchar(32) default NULL,
			`acceptComment` int(1) NOT NULL default '1',
			`acceptTrackback` int(1) NOT NULL default '1',
			`published` int(11) NOT NULL default '0',
			`created` int(11) NOT NULL default '0',
			`modified` int(11) NOT NULL default '0',
			`comments` int(11) NOT NULL default '0',
			`trackbacks` int(11) NOT NULL default '0',
			PRIMARY KEY (`owner`, `id`, `draft`),
			KEY `owner` (`owner`),
			KEY `category` (`category`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}FeedGroupRelations` (
			`owner` int(11) NOT NULL default '0',
			`feed` int(11) NOT NULL default '0',
			`groupId` int(11) NOT NULL default '0',
			PRIMARY KEY (`owner`,`feed`,`groupId`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}FeedGroups` (
			`owner` int(11) NOT NULL default '0',
			`id` int(11) NOT NULL default '0',
			`title` varchar(255) NOT NULL default '',
			PRIMARY KEY (`owner`,`id`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}FeedItems` (
			`id` int(11) NOT NULL auto_increment,
			`feed` int(11) NOT NULL default '0',
			`author` varchar(255) NOT NULL default '',
			`permalink` varchar(255) NOT NULL default '',
			`title` varchar(255) NOT NULL default '',
			`description` text NOT NULL,
			`tags` varchar(255) NOT NULL default '',
			`enclosure` varchar(255) NOT NULL default '',
			`written` int(11) NOT NULL default '0',
			PRIMARY KEY (`id`),
			KEY `feed` (`feed`),
			KEY `written` (`written`),
			KEY `permalink` (`permalink`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}FeedReads` (
			`owner` int(11) NOT NULL default '0',
			`item` int(11) NOT NULL default '0',
			PRIMARY KEY (`owner`,`item`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}FeedSettings` (
			`owner` int(11) NOT NULL default '0',
			`updateCycle` int(11) NOT NULL default '120',
			`feedLife` int(11) NOT NULL default '30',
			`loadImage` int(11) NOT NULL default '1',
			`allowScript` int(11) NOT NULL default '1',
			`newWindow` int(11) NOT NULL default '1',
			PRIMARY KEY (`owner`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}FeedStarred` (
			`owner` int(11) NOT NULL default '0',
			`item` int(11) NOT NULL default '0',
			PRIMARY KEY (`owner`,`item`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Feeds` (
			`id` int(11) NOT NULL auto_increment,
			`xmlURL` varchar(255) NOT NULL default '',
			`blogURL` varchar(255) NOT NULL default '',
			`title` varchar(255) NOT NULL default '',
			`description` varchar(255) NOT NULL default '',
			`language` varchar(5) NOT NULL default 'en-US',
			`modified` int(11) NOT NULL default '0',
			PRIMARY KEY (`id`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Filters` (
			`id` int(11) NOT NULL auto_increment,
			`owner` int(11) NOT NULL default '0',
			`type` enum('content','ip','name','url') NOT NULL default 'content',
			`pattern` varchar(255) NOT NULL default '',
			PRIMARY KEY (`id`),
			UNIQUE KEY `owner` (`owner`,`type`,`pattern`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Links` (
			`owner` int(11) NOT NULL default '0',
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(255) NOT NULL default '',
			`url` varchar(255) NOT NULL default '',
			`rss` varchar(255) NOT NULL default '',
			`written` int(11) NOT NULL default '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `owner` (`owner`,`url`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Plugins` (
			`owner` int(11) NOT NULL default '0',
			`name` varchar(255) NOT NULL default '',
			`settings` text,
			PRIMARY KEY (`owner`,`name`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}RefererLogs` (
			`owner` int(11) NOT NULL default '0',
			`host` varchar(64) NOT NULL default '',
			`url` varchar(255) NOT NULL default '',
			`referred` int(11) NOT NULL default '0'
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}RefererStatistics` (
			`owner` int(11) NOT NULL default '0',
			`host` varchar(64) NOT NULL default '',
			`count` int(11) NOT NULL default '0',
			PRIMARY KEY (`owner`,`host`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}ReservedWords` (
			`word` varchar(16) NOT NULL default '',
			PRIMARY KEY (`word`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}ServiceSettings` (
			`name` varchar(32) NOT NULL default '',
			`value` varchar(255) NOT NULL default '',
			PRIMARY KEY (`name`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}SessionVisits` (
			`id` varchar(32) NOT NULL default '',
			`address` varchar(15) NOT NULL default '',
			`blog` int(11) NOT NULL default '0',
			PRIMARY KEY (`id`,`address`,`blog`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Sessions` (
			`id` varchar(32) NOT NULL default '',
			`address` varchar(15) NOT NULL default '',
			`userid` int(11) default NULL,
			`preexistence` int(11) default NULL,
			`data` text default NULL,
			`server` varchar(64) NOT NULL default '',
			`request` varchar(255) NOT NULL default '',
			`referer` varchar(255) NOT NULL default '',
			`timer` float NOT NULL default '0',
			`created` int(11) NOT NULL default '0',
			`updated` int(11) NOT NULL default '0',
			PRIMARY KEY (`id`,`address`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}SkinSettings` (
			`owner` int(11) NOT NULL default '0',
			`skin` varchar(32) NOT NULL default 'Tattertools_skyline_ko',
			`entriesOnRecent` int(11) NOT NULL default '10',
			`commentsOnRecent` int(11) NOT NULL default '10',
			`commentsOnGuestbook` int(11) NOT NULL default '5',
			`archivesOnPage` int(11) NOT NULL default '5',
			`tagsOnTagbox` tinyint(4) NOT NULL default '10',
			`tagboxAlign` tinyint(4) NOT NULL default '1',
			`trackbacksOnRecent` int(11) NOT NULL default '5',
			`expandComment` int(1) NOT NULL default '1',
			`expandTrackback` int(1) NOT NULL default '1',
			`recentNoticeLength` int(11) NOT NULL default '30',
			`recentEntryLength` int(11) NOT NULL default '30',
			`recentCommentLength` int(11) NOT NULL default '30',
			`recentTrackbackLength` int(11) NOT NULL default '30',
			`linkLength` int(11) NOT NULL default '30',
			`showListOnCategory` int(1) NOT NULL default '1',
			`showListOnArchive` int(1) NOT NULL default '1',
			`tree` varchar(32) NOT NULL default 'base',
			`colorOnTree` varchar(6) NOT NULL default '000000',
			`bgColorOnTree` varchar(6) NOT NULL default '',
			`activeColorOnTree` varchar(6) NOT NULL default 'FFFFFF',
			`activeBgColorOnTree` varchar(6) NOT NULL default '00ADEF',
			`labelLengthOnTree` int(11) NOT NULL default '30',
			`showValueOnTree` int(1) NOT NULL default '1',
			PRIMARY KEY (`owner`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}TagRelations` (
			`owner` int(11) NOT NULL default '0',
			`tag` int(11) NOT NULL default '0',
			`entry` int(11) NOT NULL default '0',
			PRIMARY KEY (`owner`,`tag`,`entry`),
			KEY `owner` (`owner`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Tags` (
			`id` int(11) NOT NULL auto_increment,
			`name` varchar(255) NOT NULL default '',
			PRIMARY KEY (`id`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}TrackbackLogs` (
			`owner` int(11) NOT NULL default '0',
			`id` int(11) NOT NULL auto_increment,
			`entry` int(11) NOT NULL default '0',
			`url` varchar(255) NOT NULL default '',
			`written` int(11) NOT NULL default '0',
			PRIMARY KEY (`id`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Trackbacks` (
			`id` int(11) NOT NULL auto_increment,
			`owner` int(11) NOT NULL default '0',
			`entry` int(11) NOT NULL default '0',
			`url` varchar(255) NOT NULL default '',
			`writer` int(11) default NULL,
			`site` varchar(255) NOT NULL default '',
			`subject` varchar(255) NOT NULL default '',
			`excerpt` varchar(255) NOT NULL default '',
			`ip` varchar(15) NOT NULL default '',
			`written` int(11) NOT NULL default '0',
			`isFiltered` int(1) NOT NULL default '0',
			PRIMARY KEY (`id`),
			UNIQUE KEY `owner` (`owner`,`entry`,`url`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}Users` (
			`userid` int(11) NOT NULL auto_increment,
			`loginid` varchar(64) NOT NULL default '',
			`password` varchar(32) default NULL,
			`name` varchar(32) NOT NULL default '',
			`created` int(11) NOT NULL default '0',
			`lastLogin` int(11) NOT NULL default '0',
			`host` int(11) NOT NULL default '0',
			PRIMARY KEY (`userid`),
			UNIQUE KEY `loginid` (`loginid`)
		) $strCharset;
		CREATE TABLE `{$_POST['dbPrefix']}UserSettings` (
			`user` int(11) NOT NULL default '0',
			`name` varchar(32) NOT NULL default '',
			`value` varchar(255) NOT NULL default '',
			PRIMARY KEY (`user`,`name`)
		) $strCharset;";
	
	$rgQuery = split(';(\n|\t)*', trim($strCreationQuery));
	
	$rgCreatedTables = array();
	foreach ($rgQuery as $strTempQuery) {
		$strTempQuery = trim($strTempQuery);
		
		if (!empty($strTempQuery)) {
			// 테이블 중 하나라도 생성에 실패하면.
			if (!DBQuery::execute($strTempQuery)) {
				// 여지껏 생성된 테이블을 지운다.
				DBQuery::execute("DROP TABLE `{$_POST['dbPrefix']}".implode("`,`{$_POST['dbPrefix']}", $rgCreatedTables)."`");
				
				if ($argType == "overwrite") {
					// 백업된 테이블을 원래대로 돌려놓는다.
					$rgAllTables = DBQuery::queryAll("SHOW TABLES");
					foreach ($rgAllTables as $strTempTable) {
						if (eregi("^$argHead", $strTempTable[0], $rgTemp)) {
							DBQuery::execute("RENAME `".$strTempTable[0]."` TO `".eregi_replace("^$argHead", "", $strTempTable[0])."`");
						}
					}
				}
				
				return -2;
			// 성공하면 성공 테이블 array에 등록.
			} else {
				eregi("^CREATE TABLE `({$_POST['dbPrefix']}[a-z]+)`", $strTempQuery, $rgTemp);
				if (!empty($rgTemp[1])) {
					array_push($rgCreatedTables, $rgTemp[1]);
				}
			}
		}
	}
	
	$strLoginid = mysql_escape_string($_POST['email']);
	$strPassword = $_POST['password'];
	$strName = mysql_escape_string($_POST['name']);
	$strBaseLanguage = mysql_escape_string($gBaseLanguage);
	$strBaseTimezone = mysql_escape_string(substr(_t('default:Asia/Seoul'),8));
	$strBlog = mysql_escape_string($_POST['blog']);
	
	$strInsertQuery = 
	"INSERT INTO `{$_POST['dbPrefix']}Users` VALUES (1, '$strLoginid', '$strPassword', '$strName', UNIX_TIMESTAMP(), 0, 0);
	INSERT INTO `{$_POST['dbPrefix']}BlogSettings` (`owner`, `name`, `language`, `timezone`) VALUES (1, '$strBlog', '$strBaseLanguage', '$strBaseTimezone');
	INSERT INTO `{$_POST['dbPrefix']}SkinSettings` (`owner`) VALUES (1);
	INSERT INTO `{$_POST['dbPrefix']}FeedSettings` (`owner`) VALUES (1);
	INSERT INTO `{$_POST['dbPrefix']}FeedGroups` (`owner`) VALUES (1)";
	
	$rgQuery = split(';(\n|\t)*', trim($strInsertQuery));
	foreach ($rgQuery as $strTempQuery) {
		DBQuery::execute($strTempQuery);
	}
	
	$objRollback = new CRollback($gRollbackFile);
	$objRollback->backupHead = $_POST['backupHead'];
	if (!$objRollback->addToList("create", "new", "database") && $_POST['ignoreRollback'] != "true") {
		return -1;
	} else {
		return true;
	}
}

// existsErrorInDatabase()
function existsErrorInDatabase() {
	global $gUserConfigFile;
	
	if (file_exists(ROOT."/".$gUserConfigFile))
		include_once(ROOT."/".$gUserConfigFile);
	else
		return false;
	
	if (!connectDatabase($database['server'], $database['username'], $database['password'], $database['database']))
		return true;
	
	$iOptimizeCount = 0;
	$rgObjectTables = array();
	list($strTemp, $rgTablesOfLastVersion) = getTablesOfLastVersion($database['prefix']);
	$rgAllTablesInDB = DBQuery::queryAll("SHOW TABLE STATUS");
	foreach ($rgAllTablesInDB as $strTempTable) {
		// dbPrefix가 붙은 테이블인지 체크.
		if (in_array($strTempTable['Name'], $rgTablesOfLastVersion)) {
			array_push($rgObjectTables, $strTempTable['Name']);
			if ($strTempTable['Data_free'] > 0) {
				$iOptimizeCount++;
			}
		}
	}
	
	if (count($rgObjectTables) == count($rgTablesOfLastVersion)) {
		// 테이블 에러 체크.
		$rgCheckResults = DBQuery::queryAll("CHECK TABLES `".implode("`, `", $rgObjectTables)."` EXTENDED");
		foreach ($rgCheckResults as $rgResult) {
			if ($rgResult['Msg_text'] != "OK" && $rgResult['Msg_text'] != "Found row where the auto_increment column has the value 0") {
				return "broken tables";
			}
		}
		
		// 테이블 오버헤드 체크.
		if ($iOptimizeCount > 0) {
			return "overhead tables";
		}
	} else {
		return false;
	}
}

// getFingerPrint()
function getFingerPrint() {
	return md5($_SERVER['SERVER_SOFTWARE'].$_SERVER['SERVER_SIGNATURE'].$_SERVER['SCRIPT_FILENAME'].phpversion());
}

// getRequiredFunctions()
function getRequiredFunctions() {
	$rgFunctions = <<<EOS
addslashes
array_flip
array_key_exists
array_pop
array_push
array_shift
array_slice
base64_encode
ceil
checkdate
closedir
copy
count
dechex
dir
ereg
ereg_replace
eregi
eregi_replace
explode
fclose
feof
fgets
file_exists
file_get_contents
filesize
fopen
fputs
fread
fsockopen
function_exists
fwrite
get_magic_quotes_gpc
getimagesize
gmdate
gmmktime
gmstrftime
header
html_entity_decode
htmlspecialchars
implode
ini_set
intval
is_dir
is_file
is_null
is_numeric
is_writable
ksort
ltrim
mail
max
md5
microtime
min
mkdir
mktime
move_uploaded_file
mysql_affected_rows
mysql_connect
mysql_error
mysql_escape_string
mysql_fetch_array
mysql_fetch_row
mysql_insert_id
mysql_num_rows
mysql_query
mysql_result
mysql_select_db
nl2br
number_format
ob_end_clean
ob_get_contents
ob_start
opendir
ord
parse_url
preg_match
preg_replace
rand
rawurlencode
readdir
rmdir
rtrim
session_cache_expire
session_destroy
session_id
session_name
session_set_cookie_params
session_set_save_handler
session_start
setcookie
sizeof
sprintf
str_replace
strftime
stripslashes
strlen
strncasecmp
strncmp
strpos
strrev
strtolower
strval
substr
substr_count
substr_replace
time
trim
unlink
urlencode
xml_get_error_code
xml_parse
xml_parser_create
xml_parser_free
xml_parser_set_option
xml_set_character_data_handler
xml_set_default_handler
xml_set_element_handler
xml_set_object
EOS;
	return explode("\n", str_replace("\r", '', trim($rgFunctions)));
}

// getTablesOfLastVersion()
function getTablesOfLastVersion($argPrefix=NULL) {
	global $grgTablesAsVersion;
	
	$rgKeys = array_keys($grgTablesAsVersion);
	sort($rgKeys);
	$strLastVersion = array_pop($rgKeys);
	
	$grgTablesAsVersion[$strLastVersion] = str_replace("{identifier}", $argPrefix, strtolower($grgTablesAsVersion[$strLastVersion]));
	return array($strLastVersion, split("\n", str_replace("\r", '', trim($grgTablesAsVersion[$strLastVersion]))));
}

// makeDirMod()
function makeDirMod($argType, $strDirPath, $argHead) {
	$strTempDir = str_replace("/", "", basename($strDirPath));
	
	switch ($argType) {
		case "overwrite":
			rename($strDirPath, eregi_replace("/".$strTempDir."$", "/".$argHead.$strTempDir, $strDirPath));
		case "new":
			if (mkdir($strDirPath)) {
				chmod($strDirPath, 0776);
				return true;
			} else {
				return false;
			}
			break;
	}
	
	return false;
}

// makeFileMod()
function makeFileMod($argType, $argFilePath, $argHead) {
	global $_POST, $gWebConfigFile, $gUserConfigFile;
	
	switch ($argType) {
		case "overwrite":
			$strTempFile = addHeadToFileName($argFilePath, $argHead);
			if (file_exists($strTempFile))
				unlink($strTempFile);
			rename($argFilePath, $strTempFile);
		case "new":
			if ($argFilePath == ROOT."/$gWebConfigFile") {
				$pFile = fopen($argFilePath, 'w+');
				if ($pFile == true) {
					fwrite($pFile, $_POST['type'] == 'path' ?
"<IfModule mod_url.c>
CheckURL Off
</ifModule>
RewriteEngine On
RewriteBase ".PATH."/
RewriteCond %{ENV:REDIRECT_SURI} !^$
RewriteRule (.*) - [L]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^(.+[^/])$ $1/ [L]
RewriteRule ^$ blog/index.php [E=SURI:1,L]
RewriteRule ^[[:alnum:]]+/*$ blog/index.php [E=SURI:1,L]
RewriteRule ^[[:alnum:]]+/+[0-9]+$ blog/item.php [E=SURI:1,L]
RewriteRule ^[[:alnum:]]+/+favicon\.ico$ blog/favicon.ico.php [E=SURI:1,L]
RewriteRule ^[[:alnum:]]+/+index\.gif$ blog/index.gif.php [E=SURI:1,L]
RewriteCond %{QUERY_STRING} (^|&)pl=([0-9]+)
RewriteRule ^([[:alnum:]]+)/+index\.php$ $1/%2 [NE,L]
RewriteRule ^[[:alnum:]]+/+index\.php$ blog/index.php [E=SURI:1,L]
RewriteRule ^[[:alnum:]]+/+index\.xml$ blog/rss/index.php [E=SURI:1,L]
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule !^(blog|cache)/ - [L]
RewriteRule ^[[:alnum:]]+/+(thumbnail)/([0-9]+/.+) cache/$1/$2 [E=SURI:1,L]
RewriteRule ^[[:alnum:]]+/+(entry|attachment|category|keylog|tag|search|plugin)/? blog/$1/index.php [E=SURI:1,L]
RewriteRule ^[[:alnum:]]+/+(.+)/[0-9]+$ blog/$1/item.php [E=SURI:1,L]
RewriteRule ^[[:alnum:]]+/+(.+)$ blog/$1/index.php [E=SURI:1,L]
"
                    :
"<IfModule mod_url.c>
CheckURL Off
</IfModule>
RewriteEngine On
RewriteBase ".PATH."/
RewriteCond %{ENV:REDIRECT_SURI} !^$
RewriteRule (.*) - [L]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^(.+[^/])$ $1/ [L]
RewriteRule ^$ blog/index.php [E=SURI:1,L]
RewriteRule ^[0-9]+$ blog/item.php [E=SURI:1,L]
RewriteRule ^favicon\.ico$ blog/favicon.ico.php [E=SURI:1,L]
RewriteRule ^index\.gif$ blog/index.gif.php [E=SURI:1,L]
RewriteCond %{QUERY_STRING} (^|&)pl=([0-9]+)
RewriteRule ^index\.php$ %2 [NE,L]
RewriteRule ^index\.php$ blog/index.php [E=SURI:1,L]
RewriteRule ^index\.xml$ blog/rss/index.php [E=SURI:1,L]
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule !^(blog|cache)/ - [L]
RewriteRule ^(thumbnail)/([0-9]+/.+) cache/$1/$2 [E=SURI:1,L]
RewriteRule ^(entry|attachment|category|keylog|tag|search|plugin)/? blog/$1/index.php [E=SURI:1,L]
RewriteRule ^(.+)/[0-9]+$ blog/$1/item.php [E=SURI:1,L]
RewriteRule ^(.+)$ blog/$1/index.php [E=SURI:1,L]
"
					);
		            fclose($pFile);
		            chmod($argFilePath, 0666);
		            
		            return true;
				} else {
					return false;
				}
			} else if ($argFilePath == ROOT."/$gUserConfigFile") {
				$pFile = fopen($argFilePath, 'w+');
				if ($pFile == true) {
					fwrite($pFile,
"<?php
// ".date("Y-m-d H:i:s")."
ini_set('display_errors', 'off');
\$database['server'] = '{$_POST['dbServer']}';
\$database['database'] = '{$_POST['dbName']}';
\$database['username'] = '{$_POST['dbUser']}';
\$database['password'] = '{$_POST['dbPassword']}';
\$database['prefix'] = '{$_POST['dbPrefix']}';
\$service['type'] = '{$_POST['blogType']}';
\$service['domain'] = '{$_POST['domain']}';
\$service['path'] = '".PATH."';
\$service['skin'] = 'Tattertools_skyline_ko';
?>"
					);
		            fclose($pFile);
		            chmod($argFilePath, 0666);
					return true;
				} else {
					return false;
				}
			}
			break;
	}
	
	return false;
}

// optimizeTables()
function optimizeTables($database) {
	if (!connectDatabase($database['server'], $database['username'], $database['password'], $database['database'])) {
		return false;
	}
	
	$bOptimizeFlag = true;
	$rgObjectTables = array();
	list($strTemp, $rgTablesOfLastVersion) = getTablesOfLastVersion($database['prefix']);
	$rgAllTablesInDB = DBQuery::queryAll("SHOW TABLE STATUS");
	foreach ($rgAllTablesInDB as $strTempTable) {
		// dbPrefix가 붙은 테이블인지 체크.
		if (in_array($strTempTable['Name'], $rgTablesOfLastVersion)) {
			if ($strTempTable['Data_free'] > 0) {
				if (!DBQuery::execute("OPTIMIZE TABLE {$strTempTable['Name']}"))
					$bOptimizeFlag = false;
			}
		}
	}
	
	if ($bOptimizeFlag == true)
		return true;
	else
		return -1;
}

// resetConfig()
function resetConfig()
{
	
}

// repairTables()
function repairTables($database) {
	if (!connectDatabase($database['server'], $database['username'], $database['password'], $database['database'])) {
		return false;
	}
	
	$bOptimizeFlag = true;
	$rgObjectTables = array();
	list($strTemp, $rgTablesOfLastVersion) = getTablesOfLastVersion($database['prefix']);
	$rgAllTablesInDB = DBQuery::queryAll("SHOW TABLE STATUS");
	foreach ($rgAllTablesInDB as $strTempTable) {
		// dbPrefix가 붙은 테이블인지 체크.
		if (in_array($strTempTable['Name'], $rgTablesOfLastVersion)) {
			if ($strTempTable['Data_free'] > 0) {
				if (!DBQuery::execute("REPAIR TABLE {$strTempTable['Name']}"))
					$bOptimizeFlag = false;
			}
		}
	}
	
	if ($bOptimizeFlag == true)
		return true;
	else
		return -1;
}

// Session 관련 함수 ----------------------------------------------------------------------------------------------
function doesHaveAdminship() {
	global $owner;
	if (empty($_SESSION['userid'])) 
		return -3;
	else if ($_SESSION['userid'] != 1)
		return -2;
	else
		return true;
}

function getAnonymousSession() {
	global $database;
	$strResult = DBQuery::queryRow("SELECT `id` FROM `{$database['prefix']}Sessions` WHERE `address` = '{$_SERVER['REMOTE_ADDR']}' AND `userid` IS NULL AND `preexistence` IS NULL");
	return $strResult;
}

function isSessionAuthorized($id) {
	global $database;
	$strResult = DBQuery::queryRow("SELECT `id` FROM `{$database['prefix']}Sessions` WHERE `id` = '$id' AND `address` = '{$_SERVER['REMOTE_ADDR']}' AND (`userid` IS NOT NULL OR `preexistence` IS NOT NULL)");
	if ($strResult != false && count($strResult) == 1)
		return true;
	return false;
}

function newAnonymousSession() {
	global $database;
	for ($i = 0; $i < 100; $i++) {
		if (($id = getAnonymousSession()) !== false)
			return $id;
		$id = dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF)) . dechex(rand(0x10000000, 0x7FFFFFFF));
		mysql_query("INSERT INTO {$database['prefix']}Sessions(id, address, created, updated) VALUES('$id', '{$_SERVER['REMOTE_ADDR']}', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
		if (mysql_affected_rows() > 0)
			return $id;
	}
	return false;
}

function setSession() {
	$id = empty($_COOKIE[session_name()]) ? '' : $_COOKIE[session_name()];
	if ((strlen($id) < 32) || !isSessionAuthorized($id))
		setSessionAnonymous($id);
}

function setSessionAnonymous($currentId) {
	$id = getAnonymousSession();
	if ($id !== false) {
		if ($id != $currentId)
			session_id($id);
		return true;
	}
	$id = newAnonymousSession();
	if ($id !== false) {
		session_id($id);
		return true;
	}
	return false;
}



// class CRollback
class CRollback {
	var $filePath = NULL;
	var $backupHead = NULL;
	
	function CRollback($argFilePath) {
		$this->filePath = $argFilePath;
	}
	
	function initialize() {
		@unlink($this->filePath);
		$pRollbackDataFile = fopen($this->filePath, "a");
		if ($pRollbackDataFile != false) {
			fclose($pRollbackDataFile);
			return true;
		} else {
			return false;
		}
	}
	
	function executeRollback() {
		global $_POST;
		
		if (file_exists($this->filePath)) {
			$rgContent = array_map('rtrim', file($this->filePath));
			foreach ($rgContent as $line) {
				$rgCommand = explode("\t", $line);
				
				switch ($rgCommand[1]) {
					case "file":
						if ($rgCommand[0] == "create") {
							unlink($rgCommand[2]);
						} else if ($rgCommand[0] == "overwrite") {
							unlink($rgCommand[2]);
							$strBaseName = basename($rgCommand[2]);
							rename(eregi_replace($strBaseName."$", $this->backupHead.$strBaseName, $rgCommand[2]), $rgCommand[2]);
						}
						break;
					case "dir":
						if ($rgCommand[0] == "create") {
							unlink($rgCommand[2]);
						} else if ($rgCommand[0] == "overwrite") {
							unlink($rgCommand[2]);
							$strDirName = str_replace("/", "", dirname($rgCommand[2]));
							rename(eregi_replace($strDirName."$", $this->backupHead.$strDirName, $rgCommand[2]), $rgCommand[2]);
						}
					case "DB":
						$rgAllTables = DBQuery::queryRow("SHOW TABLES");
						foreach ($rgAllTables as $strTempTable) {
							if (eregi("^".$_POST['dbPrefix'], $strTempTable[0], $rgTemp)) {
								DBQuery::execute("DROP TABLE `".$strTempTable[0]."`");
							}
							if (eregi("^".$this->backupHead, $strTempTable[0], $rgTemp)) {
								DBQuery::execute("RENAME `".$strTempTable[0]."` TO `".eregi_replace("^".$this->backupHead, "", $strTempTable[0])."`");
							}
						}
						break;
				}
			}
			
			unlink($this->filePath);
		}
	}
	
	function cancelRollback() {
		$rgContent = array_map('rtrim', file($this->filePath));
		
		foreach ($rgContent as $line) {
			$rgCommand = explode("\t", $line);
			
			switch ($rgCommand[1]) {
				case "file":
					if ($rgCommand[0] == "overwrite") {
						$strBaseName = basename($rgCommand[2]);
						unlink(eregi_replace($strBaseName."$", $this->backupHead.$strBaseName, $rgCommand[2]));
					}
					break;
				case "dir":
					if ($rgCommand[0] == "overwrite") {
						unlink($rgCommand[2]);
						$strDirName = str_replace("/", "", dirname($rgCommand[2]));
						rename(eregi_replace($strDirName."$", $this->backupHead.$strDirName, $rgCommand[2]), $rgCommand[2]);
					}
					break;
				case "DB":
					$rgAllTables = DBQuery::queryRow("SHOW TABLES");
					foreach ($rgAllTables as $strTempTable) {
						if (eregi("^".$this->backupHead, $strTempTable[0], $rgTemp)) {
							DBQuery::execute("DROP TABLE `".$strTempTable[0]."`");
						}
					}
					break;
			}
		}
		
		unlink($this->filePath);
	}
	
	function addToList($command, $type, $obj, $etc=NULL) {
		$pRollbackDataFile = fopen($this->filePath, "a");
		
		if ($pRollbackDataFile != false) {
			fwrite($pRollbackDataFile, "command|$command\t$type\t$obj".CRLF);
			fclose($pRollbackDataFile);
			return true;
		} else {
			return false;
		}
	}
}



// class CSetupDialog
class CSetupDialog {
	var $baseLanguage = "ko";
	var $type = "normal";
	var $linkToManual = NULL;
	var $message = NULL;
	var $title = NULL;
	var $fields = array();
	var $buttons = array();
	var $errors = array();
	
	var $srcIconAttention = "style/setup/image/icon_attention.gif";
	var $srcIconNormal = "style/setup/image/icon_normal.gif";
	var $srcIconWarning = "style/setup/image/icon_warning.gif";
	var $srcButtonOk = "style/setup/image/icon_ok.gif";
	var $srcButtonCancel = "style/setup/image/icon_cancel.gif";
	var $srcButtonNext = "style/setup/image/icon_next.gif";
	var $srcButtonPrev = "style/setup/image/icon_prev.gif";
	var $srcButtonPrevDisabled = "style/setup/image/icon_prev_disabled.gif";
	var $srcButtonNo = "style/setup/image/icon_no.gif";
	var $srcButtonYes = "style/setup/image/icon_yes.gif";
	
	var $textNextButton = "다음 단계로 이동합니다.";
	var $textPrevButton = "이전 단계로 이동합니다.";
	var $textStopButton = "작업을 중단합니다.";
	var $textRetryButton = "작업을 재시도합니다.";
	var $textContinueButton = "작업을 진행합니다.";
	var $textNewCreateButton = "예. 새로 생성합니다.";
	var $textReuseButton = "아니요. 재사용합니다.";
	var $textAskStopButton = "작업을 중단하시겠습니까?";
	
	// initialize();
	function initialize($argObject=array()) {
		if (in_array("type", $argObject) || empty($argObject))
			$this->type = "normal";
		if (in_array("linkToManual", $argObject) || empty($argObject))
			$this->linkToManual = NULL;
		if (in_array("message", $argObject) || empty($argObject))
			$this->message = NULL;
		if (in_array("title", $argObject) || empty($argObject))
			$this->title = NULL;
		if (in_array("fields", $argObject) || empty($argObject))
			$this->fields = array();
		if (in_array("buttons", $argObject) || empty($argObject))
			$this->buttons = array();
		if (in_array("errors", $argObject) || empty($argObject))
			$this->errors = array();
	}
	
	// callSetupDialog()
	function callSetupDialog($argType, $argNotes=NULL) {
		global $_POST, $grgProcessOrder;
		
		$this->_header();
		
		switch ($_POST['mode']) {
			case "install":
				$this->title = _t('설치를 진행중입니다.');
				break;
		}
		
		switch ($argType) {
			case "권한 검사 실패":
				break;
			case "기존 테이블이 존재함":
				$this->type = "warning";
				$this->message = _t('기존에 사용하던 태터툴즈의 데이터베이스 테이블이 남아 있습니다. 삭제 후 재생성하시겠습니까?');
				$this->linkToManual = _t('데이터베이스 테이블 생성 실패');
				
				$this->_createNewField("hidden", array("name" => "overwriteThis", "value" => "true"));
				$this->_createNewButton("NO", array("name" => "process", "value" => 100, "title" => _t($this->textReuseButton)));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'], "title" => _t($this->textNewCreateButton)));
				break;
			case "데이터베이스 연결 실패":
				$this->type = "warning";
				$this->message = _t('데이터베이스에 연결하지 못했습니다. 재시도하시겠습니까?');
				$this->linkToManual = _t('데이터베이스 연결 실패');
				
				$this->_createNewButton("NO", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'], "title" => _t($this->textRetryButton)));
				break;
			case "데이터베이스 테이블 권한 없음":
				$this->type = "warning";
				$this->message = _f('mySQL의 데이터베이스 "%1"에 테이블을 생성할 권한이 없습니다. 재시도하시겠습니까?', $_POST['dbName']);
				$this->linkToManual = _t('테이블 생성 권한 없음');
				
				$this->_createNewButton("NO", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'], "title" => _t($this->textRetryButton)));
				break;
			case "데이터베이스 테이블 생성 오류":
				$this->type = "warning";
				$this->message = _t('데이터베이스 테이블을 생성하지 못했습니다. 재시도하시겠습니까?');
				$this->linkToManual = _t('데이터베이스 테이블 생성 실패');
				
				$this->_createNewField("hidden", array("name" => "overwriteThis", "value" => "true"));
				$this->_createNewButton("NO", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'], "title" => _t($this->textRetryButton)));
				break;
			case "디렉토리 덮어쓰기 실패":
				$this->type = "warning";
				$this->message = _f('새 %1를 생성하지 못했습니다. 설치를 중단합니다.', $argNotes);
				$this->linkToManual = _f('%1 덮어쓰기 실패', $argNotes);
				
				$this->_createNewButton("OK", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				break;
			case "디렉토리 생성 실패":
				$this->type = "warning";
				$this->message = _f('새 %1를 생성하지 못했습니다. 설치를 중단합니다.', $argNotes);
				$this->linkToManual = _f('%1 생성 실패', $argNotes);
				
				$this->_createNewButton("OK", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				break;
			case "디렉토리 수정 권한 없음.":
				$this->type = "warning";
				$this->message = _f('%1를 수정할 권한이 없습니다. 해당 디렉토리의 권한을 0777로 변경하시고 다시 시도하십시오. 설치를 중단합니다.', $argNotes);
				$this->linkToManual = _f('%1 수정 권한 없음', $argNotes);
				
				$this->_createNewButton("OK", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				break;
			case "디렉토리를 덮어쓸 것인지 재확인":
				$this->type = "attention";
				$this->message = _f('%1가 이미 있습니다. 삭제 후 새 %2를 생성하시겠습니까?', $argNotes, $argNotes);
				
				$this->_createNewField("hidden", array("name" => "process", "value" => $_POST['process']));
				$this->_createNewButton("NO", array("name" => "overwriteThis", "value" => "false", "title" => _t($this->textReuseButton)));
				$this->_createNewButton("YES", array("name" => "overwriteThis", "value" => "true", "title" => _t($this->textNewCreateButton)));
				break;
			case "로그인 검사 실패":
				$this->type = "warning";
				$this->message = _t('관리자 로그인 상태가 아닙니다. 로그인하셔야 셋업 기능을 사용하실 수 있습니다.');
				
				if (empty($POST['referer'])) {
					$this->_createNewField("hidden", array("name" => "referer", "value" => $_SERVER['HTTP_REFERER']));
					$this->_createNewButton("OK", array("name" => "onclick", "value" => "window.location.href='{$_SERVER['HTTP_REFERER']}'", "title" => _t('이전 페이지로 이동합니다.')));
				} else {
					$this->_createNewField("hidden", array("name" => "referer", "value" => $POST['referer']));
					$this->_createNewButton("OK", array("name" => "onclick", "value" => "window.location.href='{$POST['referer']}'", "title" => _t('이전 페이지로 이동합니다.')));
				}
				break;
			case "사용자 설정파일 검사 실패":
				break;
			case "파일 덮어쓰기 실패":
				$this->type = "warning";
				$this->message = _f('새 %1를 생성하지 못했습니다. 설치를 중단합니다.', $argNotes);
				$this->linkToManual = _f('%1 덮어쓰기 실패', $argNotes);
				
				$this->_createNewButton("OK", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				break;
			case "파일 생성 실패":
				$this->type = "warning";
				$this->message = _f('새 %1을 생성하지 못했습니다. 설치를 중단합니다.', $argNotes);
				$this->linkToManual = _f('%1 생성 실패', $argNotes);
				
				$this->_createNewButton("OK", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				break;
			case "파일 수정 권한 없음.":
				break;
			case "파일 시스템 권한 없음":
				$this->type = "warning";
				$this->message = _t('설치할 디렉토리에 쓰기권한이 없습니다. 해당 디렉토리의 권한을 0777로 변경하셔야 합니다. 재시도하시겠습니까?');
				$this->linkToManual = _t('파일 시스템 권한 없음');
				
				$this->_createNewButton("NO", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'], "title" => _t($this->textRetryButton)));
				break;
			case "파일을 덮어쓸 것인지 재확인":
				$this->type = "attention";
				$this->message = _f('%1 명을 사용하는 다른 파일이 있습니다. 삭제 후 새 %2를 생성하시겠습니까?', $argNotes, $argNotes);
				
				$this->_createNewField("hidden", array("name" => "overwriteThis", "value" => "true"));
				$this->_createNewButton("NO", array("name" => "process", "value" => 100, "title" => _t('아니요. 새로 생성하지 않습니다.')));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'] + 1, "title" => _t($this->textNewCreateButton)));
				break;
			case "mod_rewrite 오류":
				$this->type = "warning";
				$this->message = _t('Rewrite를 사용할 수 없습니다.');
				$this->linkToManual = _t('mod_rewrite 오류');
				
				$this->_createNewButton("OK", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				break;
			case "PHP 함수 검사 실패":
				$this->type = "warning";
				$this->message = _t('태터툴즈의 동작에 필요한 몇몇 PHP 함수가 설치되어 있지 않습니다. 재시도하시겠습니까?');
				$this->linkToManual = _t('PHP 함수 미설치');
				
				$this->_createNewButton("NO", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'], "title" => _t($this->textRetryButton)));
				break;
			case "Rollback 오류":
				$this->type = "attention";
				$this->message = _t('Rollback 기능이 제대로 동작하지 않고 있습니다. 이 기능은 설치중단시 그 때까지 진행된 설치를 원래 상태로 되돌려주는 역할을 합니다. 이 기능이 동작하지 않아도 설치된 블로그는 정상적으로 동작하지만, 계정과 데이터베이스에 불필요한 파일이 남아 있을 수 있습니다.');
				
				$this->_createNewField("hidden", array("name" => "ignoreRollback", "value" => "true"));
				$this->_createNewButton("OK", array("name" => "process", "value" => $_POST['process'] + 1, "title" => _t($this->textContinueButton)));
				break;
			case "UTF-8 미지원":
				$this->type = "attention";
				$this->message = _t('태터툴즈의 동작에 필요한 UTF-8을 지원하지 않는 mySQL 버전입니다. 설치 후 검색 등의 몇몇 블로그 기능이 비정상적으로 동작할 수 있습니다. 재시도하시겠습니까?');
				$this->linkToManual = _t('UTF-8 미지원');
				
				$this->_createNewButton("NO", array("name" => "process", "value" => 100, "title" => _t($this->textStopButton)));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'], "title" => _t($this->textRetryButton)));
				break;
		}
?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo $this->title;?></h2>
<?php
				$this->_createFields();
?>
				<div id="<?php echo $this->type;?>DialogBox" class="dialog-box">
					<div class="message"><?php echo $this->message;?></div>
					<div class="buttons">
<?php
				$this->_createDialogButtons();
?>
					</div>
				</div>
			</div>
			
			<hr class="hidden" />
			
			<div id="layoutFoot"></div>
		</form>
<?php
		
		$this->_footer();
	}
	
	// callSetupWindow()
	function callSetupWindow($argType, $argNotes=NULL) {
		global $_SERVER, $_POST, $gUserConfigFile, $gWebConfigFile, $grgProcessOrder, $gCheckLogin;
		
		$this->_header();
		
		switch ($argType) {
			// *관리자 정보 입력 받기.
			case "관리자 정보 입력 받기":
				$this->linkToManual = _t('관리자 정보 입력하기');
				
				switch ($argNotes) {
					case -1:
						$this->message = _t('이메일 정보를 입력하지 않으셨습니다. 이메일은 관리자 로그인 시 아이디로 사용되므로 반드시 입력주시기 바랍니다.');
						$argNotes = array('email');
						break;
					case -2:
						$this->message = _f('비밀번호를 입력하지 않으셨습니다. 비밀번호를 정확하게 입력해 주시기 바랍니다.', $_POST['dbName']);
						$argNotes = array('password', 'password2');
						break;
					case -3:
						$this->message = _t('비밀번호를 확인하지 못했습니다. 확인을 위해 비밀번호를 2회 정확하게 입력해 주시기 바랍니다.');
						$argNotes = array('password', 'password2');
						break;
					case -4:
						$this->message = _t('블로그 식별자를 입력하지 않으셨습니다. 블로그 식별자는 다른 이용자와 구분에 사용됩니다. 주소창에 표시되는 부분이오니 신중히 입력해 주시기 바랍니다.');
						$argNotes = array('blog');
						break;
					case -5:
						$this->message = _t('닉네임을 입력하지 않으셨습니다. 닉네임은 블로그 상에서 자신의 이름으로 사용됩니다.');
						$argNotes = array('name');
						break;
					default:
						$this->message = _t('관리자 정보를 입력해 주시기 바랍니다. 이 정보를 이용하여 관리자 정보를 생성합니다.');
						break;
				}
				$this->_createNewButton("PREV", array("name" => "process", "value" => $_POST['process'] - 1, "title" => _t($this->textPrevButton)));
				$this->_createNewButton("NEXT", array("name" => "process", "value" => $_POST['process'], "title" => _t($this->textNextButton)));
?>
		<form name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo _t('관리자 정보를 입력해 주십시오.');?></h2>
				
<?php
				$this->_createFields();
?>
				<input type="hidden" name="check" value="true" />
				
				<div class="content-box">
					<table>
						<tbody>
							<tr>
								<td class="head"><label for="email"><?php echo _t('이메일');?></label></td>
								<td>
									<input type="text" id="email" name="email" value="<?php echo (isset($_POST['email']) ? $_POST['email'] : '');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('email', $argNotes) ? ' input-error' : '');?>" />
								</td>
							</tr>
							<tr>
								<td class="head"><label for="password"><?php echo _t('비밀번호');?></label></td>
								<td>
									<input type="password" id="password" name="password" value="<?php echo (isset($_POST['password']) ? $_POST['password'] : '');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('password', $argNotes) ? ' input-error' : '');?>" />
								</td>
							</tr>
							<tr>
								<td class="head"><label for="password2"><?php echo _t('비밀번호 확인');?></label></td>
								<td>
									<input type="password" id="password2" name="password2" value="<?php echo (isset($_POST['password2']) ? $_POST['password2'] : '');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('password2', $argNotes) ? ' input-error' : '');?>" />
								</td class="head">
							</tr>
<?php
				if ($_POST['blogType'] == "domain" || $_POST['blogType'] == "path") {
?>
							<tr>
								<td class="head"><label for="blog"><?php echo _t('블로그 식별자');?></label></td>
								<td>
									<input type="text" id="blog" name="blog" value="<?php echo (isset($_POST['blog']) ? htmlspecialchars($_POST['blog']) : '');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('blog', $argNotes) ? ' input-error' : '');?>" />
								</td>
							</tr>
<?php
				}
?>
							<tr>
								<td class="head"><label for="name"><?php echo _t('필명');?></label></td>
								<td>
									<input type="text" id="name" name="name" value="<?php echo (isset($_POST['name']) ? $_POST['name'] : '');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('name', $argNotes) ? ' input-error' : '');?>" />
								</td>
							</tr>
						</tbody>
					</table>
				</div>
<?php
				$this->_createHelpMenu();
				
				if (!empty($argNotes['title'])) {
?>
				<div id="messageBox" class="warning-message-box">
					<?php echo $argNotes['title'].CRLF;?>
				</div>
<?php
				} else if (!empty($this->message)) {
?>
				<div id="messageBox">
					<?php echo $this->message.CRLF;?>
				</div>
<?php
				}
?>
			</div>
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *테이터베이스 정보 입력 받기.
			case "데이터베이스 정보 입력 받기":
				$this->title = _t('작업 정보를 입력해 주십시오.');
				
				switch ($argNotes) {
					case -1:
						$this->linkToManual = _t('데이터베이스 서버연결 불가능');
						$this->message = _t('데이터베이스 서버에 연결할 수 없습니다. 데이터베이스 서버에 연결하기 위해서는 데이터베이스 서버명, 데이터베이스 사용자 아이디와 암호가 필요합니다. 해당 정보에 관해서는 이용 중인 호스팅 업체에 문의하십시오.');
						$argNotes = array('dbServer', 'dbUser', 'dbPassword');
						break;
					case -2:
						$this->linkToManual = _t('데이터베이스 사용 불가능');
						$this->message = _f('데이터베이스를 사용할 수 없습니다. 데이터베이스 서버에 명칭이 "%1"인 데이터베이스가 존재하지 않습니다. 데이터베이스 이름을 확인하신 후, 다시 입력해 주십시오.', $_POST['dbName']);
						$argNotes = array('dbName');
						break;
					case -3:
						$this->linkToManual = _t('잘못된 테이블 식별자');
						$this->message = _t('테이블 식별자가 올바르지 않습니다. 테이블 식별자에는 알파벳, 숫자, 언더바(_)만 사용가능합니다. 다시 입력해 주십시오.');
						$argNotes = array('dbPrefix');
						break;
					case -4:
						$this->linkToManual = _t('데이터베이스 정보 입력하기');
						$this->message = _t('입력된 정보가 부족합니다. 테이블 식별자는 입력되지 않아도 사용할 수 있으나, 나머지 입력란은 반드시 기입되어야 합니다.');
						$argNotes = array('dbServer', 'dbUser', 'dbPassword', 'dbName');
						break;
					default:
						$this->linkToManual = _t('데이터베이스 정보 입력하기');
						$this->message = _t('<p>데이터베이스 이름은 보통 해당 호스팅 서비스의 계정 아이디와 동일합니다. 데이터베이스 서버와 테이블 식별자는 로딩시 기입된 내용을 그대로 사용하셔도 무방합니다.</p>');
						break;
				}
				
				$this->_createNewButton("PREV", array("name" => "process", "value" => $_POST['process'] - 1, "title" => _t($this->textPrevButton)));
				$this->_createNewButton("NEXT", array("name" => "process", "value" => $_POST['process'], "title" => _t($this->textNextButton)));
?>
		<form name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo $this->title;?></h2>
				
<?php
				$this->_createFields();
?>
				<input type="hidden" name="check" value="true" />
				
				<div class="content-box">
					<table>
						<tbody>
							<tr>
								<td class="head"><label for="dbServer"><?php echo _t('데이터베이스 서버');?></label></td>
								<td>
									<input type="text" id="dbServer" name="dbServer" value="<?php echo (isset($_POST['dbServer']) ? $_POST['dbServer'] : 'localhost');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('dbServer', $argNotes) ? ' input-error' : '');?>" />
								</td>
							</tr>
							<tr>
								<td class="head"><label for="dbName"><?php echo _t('데이터베이스 이름');?></label></td>
								<td>
									<input type="text" id="dbName" name="dbName" value="<?php echo (isset($_POST['dbName']) ? $_POST['dbName'] : '');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('dbName', $argNotes) ? ' input-error' : '');?>" />
								</td>
							</tr>
							<tr>
								<td class="head"><label for="dbUser"><?php echo _t('데이터베이스 사용자 아이디');?></label></td>
								<td>
									<input type="text" id="dbUser" name="dbUser" value="<?php echo (isset($_POST['dbUser']) ? $_POST['dbUser'] : '');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('dbUser', $argNotes) ? ' input-error' : '');?>" />
								</td class="head">
							</tr>
							<tr>
								<td class="head"><label for="dbPassword"><?php echo _t('데이터베이스 사용자 암호');?></label></td>
								<td>
									<input type="password" id="dbPassword" name="dbPassword" value="<?php echo (isset($_POST['dbPassword']) ? htmlspecialchars($_POST['dbPassword']) : '');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('dbPassword', $argNotes) ? ' input-error' : '');?>" />
								</td>
							</tr>
							<tr>
								<td class="head"><label for="dbPrefix"><?php echo _t('테이블 식별자');?></label></td>
								<td>
									<input type="text" id="dbPrefix" name="dbPrefix" value="<?php echo (isset($_POST['dbPrefix']) ? $_POST['dbPrefix'] : 'tt_');?>" class="input-text<?php echo ($_POST['check'] == 'true' && in_array('dbPrefix', $argNotes) ? ' input-error' : '');?>" />
								</td>
							</tr>
						</tbody>
					</table>
				</div>
<?php
				$this->_createHelpMenu();
?>
				<p id="messageBox" class="warning-message-box">
					<?php echo $this->message.CRLF;?>
				</p>
			</div>
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *블로그 타입 선택.
			case "블로그 타입 선택":
				$this->linkToManual = _t('블로그 타입 선택');
				$this->_createNewButton("PREV", array("disabled" => "disabled"));
				$this->_createNewButton("NEXT", array("name" => "process", "value" => $_POST['process'] + 1, "title" => _t($this->textContinueButton)));
				
				$strDomain = ($argNotes == 3 ? substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.') + 1) : $_SERVER['HTTP_HOST']);
				$this->_createNewField("hidden", array("name" => "domain", "value" => $strDomain));
?>
		<form name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo _t('블로그 타입을 선택해 주십시오.');?></h2>
				
<?php
				$this->_createFields();
?>
				<div class="content-box">
					<dl id="singleGroup" class="group">
						<dt><input type="radio" class="radio-input" id="blogSingleType" name="blogType" value="single"<?php echo $gbIsWindows != true ? ' style="vertical-align: middle;"' : '';?> checked="checked" /> <label for="blogSingleType"><?php echo _t('단일 블로그.');?></label></dt>
						<dd>
							<ul>
								<li><samp>http://<?php echo $strDomain;?><?php echo $_SERVER['SERVER_PORT'] == 80 ? '' : ":{$_SERVER['SERVER_PORT']}";?><?php echo PATH;?>/</samp></li>
							</ul>
						</dd>
					</dl>
<?php
				if ($iRewrite >= 2) {
?>
					<dl id="domainGroup" class="group">
						<dt><input type="radio" class="radio-input" id="blogDomainType" name="blogType" value="domain"<?php echo $gbIsWindows != true ? ' style="vertical-align: middle;"' : '';?> /> <label for="blogDomainType"><?php echo _t('도메인네임(DNS)으로 블로그 식별');?></label></dt>
						<dd>
							<ul>
								<li><samp>http://<strong>blog1</strong>.<?php echo $strDomain;?><?php echo ($_SERVER['SERVER_PORT'] == 80 ? '' : ":{$_SERVER['SERVER_PORT']}");?><?php echo PATH;?>/</samp></li>
								<li><samp>http://<strong>blog2</strong>.<?php echo $strDomain;?><?php echo ($_SERVER['SERVER_PORT'] == 80 ? '' : ":{$_SERVER['SERVER_PORT']}");?><?php echo PATH;?>/</samp></li>
							</ul>
						</dd>
					</dl>
<?php
				}
?>
					<dl id="pathGroup" class="group">
						<dt><input type="radio" class="radio-input" id="blogMultiType" name="blogType" value="path"<?php echo $gbIsWindows != true ? ' style="vertical-align: middle;"' : '';?> /> <label for="blogMultiType"><?php echo _t('다중 사용자. 하위 경로(Path)로 블로그 식별.');?></label></dt>
						<dd>
							<ul>
								<li><samp>http://<?php echo $strDomain;?><?php echo ($_SERVER['SERVER_PORT'] == 80 ? '' : ":{$_SERVER['SERVER_PORT']}");?><?php echo PATH;?>/<strong>blog1</strong></samp></li>
								<li><samp>http://<?php echo $strDomain;?><?php echo ($_SERVER['SERVER_PORT'] == 80 ? '' : ":{$_SERVER['SERVER_PORT']}");?><?php echo PATH;?>/<strong>blog2</strong></samp></li>
							</ul>
						</dd>
					</dl>
				</div>
<?php
				$this->_createHelpMenu();
?>
			</div>
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *설치 삭제 시작 알림.
			case "설치 삭제 시작 알림":
?>
		<form name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo $this->title;?></h2>
<?php
				$this->_createFields();
?>
				
				<div id="messageBox" class="<?php echo $this->type;?>-message-box">
					<?php echo $this->message;?>
				</div>
			</div>
			
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *설치 시작 알림.
			case "설치 시작 알림":
				$this->_createNewButton("PREV", array("disabled" => "disabled"));
				$this->_createNewButton("NEXT", array("name" => "process", "value" => $_POST['process'] + 1, "title" => _t($this->textContinueButton)));
?>
		<form name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo _t('설치를 시작합니다.');?></h2>
<?php
				$this->_createFields();
				//$this->_createHelpMenu();
?>
				<div id="<?php echo $strErrorID;?>ContentBox" class="content-box">
				</div>

				<p id="messageBox" class="<?php echo $this->type;?>-message-box">
					<?php echo _t('NEXT 버튼을 누르시면 태터툴즈 설치를 시작합니다.').CRLF;?>
				</p>
			</div>
			
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *셋업 타입 선택.
			case "셋업 타입 선택":
?>
		<div id="layoutBody">
			<h2><span class="step"><?php echo _t('2단계');?></span> : <?php echo _t('작업 유형을 선택해 주십시오.');?></h2>
			
			<table id="formGroup" cellpadding="0" cellspacing="0" border="0">
				<tbody>
					<tr>
						<td align="center" valign="middle">

<?php
				if ($gCheckLogin === -1 && !checkInstalledVersion()) {
?>
							<form name="setup1" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
								<input type="hidden" name="mode" value="install" />
								<input type="hidden" name="process" value="0" />
								<input type="hidden" name="lang" value="<?php echo $this->baseLanguage;?>" />
								
								<input type="submit" class="button-input" value="<?php echo _t('태터툴즈를 설치합니다');?>" />
								<p><?php echo _t('태터툴즈 설치를 시작합니다. 데이터베이스를 설치하고 설정파일을 작성합니다.');?></p>
							</form>
							
							<form name="setup2" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
								<input type="submit" class="button-input" value="<?php echo _t('태터툴즈를 재설정합니다');?>"  disabled="disabled" />
								<p><?php echo _t('설치된 태터툴즈가 없습니다. 재설정 기능은 비활성화 됩니다.');?></p>
							</form>
<?php
				} else {
?>
							<form name="setup4" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
								<input type="hidden" name="mode" value="uninstall" />
								<input type="hidden" name="process" value="0" />
								<input type="hidden" name="lang" value="<?php echo $this->baseLanguage;?>" />
								
								<input type="submit" class="button-input" value="<?php echo _t('태터툴즈를 제거합니다');?>" />
								<p><?php echo _t('태터툴즈를 제거합니다. 모든 컨텐츠가 삭제되오니 반드시 백업 후 진행하시기 바랍니다.');?></p>
							</form>
							
							<form name="setup2" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
								<input type="hidden" name="mode" value="reset" />
								<input type="hidden" name="process" value="0" />
								<input type="hidden" name="lang" value="<?php echo $this->baseLanguage;?>" />
								
								<input type="submit" class="button-input" value="<?php echo _t('태터툴즈를 재설정합니다');?>" />
								<p><?php echo _t('설정과 사용자 정보를 재설정합니다. 이외의 블로그 데이터는 영향을 받지 않습니다.');?></p>
							</form>
<?php
				}
?>	
							
<?php
				$bCheckResult = existsErrorInDatabase();
				if ($bCheckResult == false) {
?>
							<form name="setup3" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
								<input type="submit" class="button-input" value="<?php echo _t('태터툴즈를 복구합니다');?>"  disabled="disabled" />
								<p><?php echo _t('이 버튼은 태터툴즈 데이터베이스에서 오류가 발견되면 자동으로 활성화 됩니다.');?></p>
							</form>
<?php
				} else if ($bCheckResult == "broken tables") {
?>
							<form name="setup3" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
								<input type="hidden" name="mode" value="repair" />
								<input type="hidden" name="process" value="0" />
								<input type="hidden" name="lang" value="<?php echo $this->baseLanguage;?>" />
								
								
								<input type="submit" class="button-input" value="<?php echo _t('태터툴즈를 복구합니다');?>" />
								<p><?php echo _t('태터툴즈 데이터베이스의 오류를 복구합니다. 이유 없이 로그인 되지 않을 경우 사용합니다.');?></p>
							</form>
<?php
				} else if ($bCheckResult == "overhead tables") {
?>
							<form name="setup3" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
								<input type="hidden" name="mode" value="optimize" />
								<input type="hidden" name="process" value="0" />
								<input type="hidden" name="lang" value="<?php echo $this->baseLanguage;?>" />
								
								
								<input type="submit" class="button-input" value="<?php echo _t('태터툴즈를 최적화합니다');?>" />
								<p><?php echo _t('태터툴즈 데이터가 일부 비효율적인 상태입니다.');?></p>
							</form>
<?php
				}
?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
<?php
				break;
			// *시작 스크린.
			case "시작 스크린":
				$this->_createNewButton("NEXT", array("name" => "process", "value" => 1, "title" => _t($this->textNextButton)));
?>
		<div id="layoutBody">
			<h2><span class="step"><?php echo _t('1단계');?></span> : <?php echo _t('태터툴즈 설치를 시작합니다.');?></h2>
			
			<form id="languageSelect" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
				<?php $this->_drowSetLang($this->baseLanguage, 'normal');?>
			</form>
			
			<div id="info">
				<strong><?php echo TATTERTOOLS_VERSION;?></strong><br />
				<?php echo TATTERTOOLS_COPYRIGHT;?><br />
				Homepage: <a href="<?php echo TATTERTOOLS_HOMEPAGE;?>"><?php echo TATTERTOOLS_HOMEPAGE;?></a>
			</div>
			<div id="content">
				<ol>
					<li><?php echo _t('소스를 포함한 소프트웨어에 포함된 모든 저작물(이하, 태터툴즈)의 저작권자는 Tatter &amp; Company와 Tatter &amp; Friends입니다.');?></li>
					<li><?php echo _t('태터툴즈는 <a href="http://www.gnu.org/licenses/gpl.html" title="GNU GPL 문서로 이동합니다.">GPL 라이센스</a>로 제공되며, 모든 사람이 자유롭게 이용할 수 있습니다.');?></li>
					<li><?php echo _t('프로그램 사용에 대한 유지 및 보수 등의 의무와, 사용 중 데이터 손실 등에 대한 사고책임은 모두 사용자에게 있습니다.');?></li>
					<li><?php echo _t('스킨 및 트리, 플러그인의 저작권은 각 제작자에게 있습니다.');?></li>
				</ol>
			</div>
		</div>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<input type="hidden" name="mode" value="basic" />
			<input type="hidden" name="lang" value="<?php echo $this->baseLanguage;?>" />
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *입력받은 관리자 정보 재확인.
			case "입력받은 관리자 정보 재확인":
				$this->_createNewButton("NO", array("name" => "process", "value" => $_POST['process'] - 1, "title" => _t($this->textPrevButton)));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'] + 1, "title" => _t($this->textNextButton)));
?>
		<form name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo _t('입력하신 관리자 정보를 확인합니다.');?></h2>
				
<?php
				$this->_createFields(array("password"));
?>
				<input type="hidden" name="password" value="<?php echo md5($_POST['password']);?>" class="readonly-input" readonly="readonly" />

				<div class="content-box">
					<table>
						<tbody>
							<tr>
								<td class="head"><?php echo _t('이메일');?></td>
								<td>
									<input type="text" value="<?php echo $_POST['email'];?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
							<tr>
								<td class="head"><?php echo _t('비밀번호');?></td>
								<td>
									<input type="password" value="<?php echo str_repeat('*', strlen($_POST['password']));?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
							<tr>
								<td class="head"><?php echo _t('비밀번호 확인');?></td>
								<td>
									<input type="password" value="<?php echo str_repeat('*', strlen($_POST['password2']));?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
<?php
				if ($_POST['blogType'] == "domain" || $_POST['blogType'] == "path") {
?>
							<tr>
								<td class="head"><?php echo _t('블로그 식별자');?></td>
								<td>
									<input type="text" value="<?php echo $_POST['blog'];?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
<?php
				}
?>
							<tr>
								<td class="head"><?php echo _t('필명');?></td>
								<td>
									<input type="text" value="<?php echo $_POST['name'];?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
						</tbody>
					</table>
				</div>
<?php
				$this->_createHelpMenu();
?>
				<div id="messageBox" class="normal-message-box">
					<?php echo $this->message.CRLF;?>
				</div>
			</div>
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *입력받은 데이터베이스 정보 재확인.
			case "입력받은 데이터베이스 정보 재확인":
				$this->_createNewButton("NO", array("name" => "process", "value" => $_POST['process'] - 1, "title" => _t($this->textPrevButton)));
				$this->_createNewButton("YES", array("name" => "process", "value" => $_POST['process'] + 1, "title" => _t($this->textNextButton)));
?>
		<form name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo _t('입력하신 데이터베이스 정보를 확인합니다.');?></h2>
				
<?php
				$this->_createFields();
?>
				<div class="content-box">
					<table>
						<tbody>
							<tr>
								<td class="head"><?php echo _t('데이터베이스 서버');?></td>
								<td>
									<input type="text" value="<?php echo $_POST['dbServer'];?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
							<tr>
								<td class="head"><?php echo _t('데이터베이스 이름');?></td>
								<td>
									<input type="text" value="<?php echo $_POST['dbName'];?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
							<tr>
								<td class="head"><?php echo _t('데이터베이스 사용자명');?></td>
								<td>
									<input type="text" value="<?php echo $_POST['dbUser'];?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
							<tr>
								<td class="head"><?php echo _t('데이터베이스 암호');?></td>
								<td>
									<input type="password" value="<?php echo $_POST['dbPassword'];?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
							<tr>
								<td class="head"><?php echo _t('테이블 식별자');?></td>
								<td>
									<input type="text" value="<?php echo $_POST['dbPrefix'];?>" class="readonly-input" readonly="readonly" />
								</td>
							</tr>
						</tbody>
					</table>
				</div>
<?php
				$this->_createHelpMenu();
?>
				<p id="messageBox" class="normal-message-box align-center">
					<?php echo _t('이 정보를 이용하여 설치를 시작하시겠습니까?').CRLF;?>
				</p>
			</div>
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *프로세스 완료.
			case "프로세스 완료":
				switch ($argNotes) {
					case "install":
						$this->message = _t('설치가 완료되었습니다.');
						$strTempTitle = _t('블로그 메인 화면으로 이동합니다.');
						break;
					case "optimize":
						$this->message = _t('데이터베이스 최적화가 완료되었습니다.');
						$strTempTitle = _t('셋업 메인 화면으로 이동합니다.');
						break;
					case "repair":
						$this->message = _t('데이터베이스 수리가 완료되었습니다.');
						$strTempTitle = _t('셋업 메인 화면으로 이동합니다.');
						break;
					case "reset":
						$this->message = _t('재설정이 완료되었습니다.');
						$strTempTitle = _t('셋업 메인화면으로 이동합니다.');
						break;
					case "uninstall": // 사용할 상황이 없을 듯.
						$this->message = _t('설치 삭제가 완료되었습니다.');
						$strTempTitle = _t('태터툴즈 홈페이지로 이동합니다.');
						break;
				}
				
				$this->_createNewButton("OK", array("name" => "process", "value" => $_POST['process'] + 1, "title" => _t('셋업 메인 화면으로 이동합니다.')));
?>
		<form name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo $this->title;?></h2>
<?php
				$this->_createFields();
?>
				
				<div id="messageBox" class="<?php echo $this->type;?>-message-box">
					<?php echo $this->message;?>
				</div>
			</div>
			
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *프로세스 중단.
			case "프로세스 중단":
				switch ($argNotes) {
					case "install":
						$this->message = _t('설치가 중단되었습니다.');
						break;
					case "optimize":
						$this->message = _t('데이터베이스 최적화가 중단되었습니다.');
						break;
					case "repair":
						$this->message = _t('데이터베이스 수리가 중단되었습니다.');
						break;
					case "reset":
						$this->message = _t('재설정이 중단되었습니다.');
						break;
					case "uninstall": // 사용할 상황이 없을 듯.
						$this->message = _t('설치 삭제가 중단되었습니다.');
						break;
				}
				
				$this->_createNewButton("OK", array("name" => "process", "value" => 101, "title" => _t('셋업 메인 화면으로 이동합니다.')));
					
?>
		<form name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="layoutBody">
				<h2><span class="step"><?php echo _t($grgProcessOrder[$_POST['process']]['step'].'단계');?></span> : <?php echo $this->title;?></h2>
				
<?php
				$this->_createFields();
?>
				
				<div id="messageBox" class="<?php echo $this->type;?>-message-box">
					<?php echo $this->message;?>
				</div>
			</div>
			
<?php
				$this->_createButtonBox();
?>
		</form>
<?php
				break;
			// *디폴트.
			default:
				// 존재하지 않는 페이지입니다.
				break;
		}
		
		$this->_footer();
	}
	
	
	/* 클래스 공통 함수 ************************************************************************************************************/
	
	// _header()
	function _header() {
		global $grgProcessOrder;
?>
<!DOCTYPE html PUBLIC "-//W3C//XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo TATTERTOOLS_NAME." ".TATTERTOOLS_VERSION;?> Setup</title>
	<link rel="stylesheet" media="screen" type="text/css" href="style/setup/style.developing.css" />
	<script type="text/javascript">
		//<![CDATA[
			function current(){ 
				document.getElementById("languageSelect").submit() ; 
			}
		//]]>
	</script>
</head>
<body>
	<div id="container" class="step-<?php echo $grgProcessOrder[$_POST['process']]['step'];?>">
		<div id="layoutHead">
			<h1><?php echo _t('태터툴즈 설치 매니저');?></h1>
		</div>
		
		<hr class="hidden" />
		
<?php
	}
	
	// _footer()
	function _footer() {
		global $_POST;
?>
	</div>
<?php
		if (defined("DEBUG")) {
?>
	<div id="debuger" style="text-align: right; position: absolute; top: 0; right: 0; padding: 0 5px; background-color: #FFFFFF;">
		<script type="text/javascript">
			function toggleLayer(obj) {
				object = document.getElementById(obj);
				if (object.style.display == "none") {
					object.style.display = "block"
				} else {
					object.style.display = "none";
				}
			}
		</script>
		<a href="#" onclick="toggleLayer('phpinfo')" style="font-family: arial; font-weight: bold; font-size: 8pt;"><strong>PHP INFO</strong></a>
		<div id="phpinfo" style="text-align: left; display: none; width: 300px; height: 300px; overflow: auto;">
<?php
		echo '<pre style="font-family: \'Courier New\'; font-size: 9pt;">';
		var_dump($_POST);
		var_dump($_SESSION);
		echo '</pre>';
?>
		</div>
	</div>
<?php
		}
?>
</body>
</html>
<?php
	}
	
	// _drowSetLang()
	function _drowSetLang($argCurrentLang="ko" ,$argCurrentPosition="normal") { 
		if (Locale::setDirectory("language"))
			$availableLanguages = Locale::getSupportedLocales(); 
		else
			return false; 
?> 
	Select Default Language :
	<select id="lang" name="lang" onchange="current();"> 
<?php
		foreach ($availableLanguages as $key => $value) 
			print('<option value="'.$key.'"'.($key == $argCurrentLang ? ' selected="selected"' : '').' >'.$value.'</option>'.CRLF); 
?>
	</select>
<?php 
		return true;
	}
	
	// _createDialogButtons()
	function _createDialogButtons() {
		foreach ($this->buttons as $rgButton) {
			if (isset($rgButton['attribute']['name']))
				$rgButton['attribute']['name'] = $rgButton['attribute']['name']."_".strtolower($rgButton['type'])."_".$rgButton['attribute']['value'];
			
			$rgAttributes = array();
			$rgKeys = array_keys($rgButton['attribute']);
			for ($iCount=0; $iCount<count($rgKeys); $iCount++) {
				$strCurrentKey = $rgKeys[$iCount];
				if ($strCurrentKey == "name")
					$strValueOfName = $rgButton['attribute'][$strCurrentKey];
				array_push($rgAttributes, $strCurrentKey.'="'.htmlspecialchars($rgButton['attribute'][$strCurrentKey]).'"');
			}
			
			switch (strtoupper($rgButton['type'])) {
				case "CANCEL":
					$srcImage = $this->srcButtonCancel;
					break;
				case "NEXT":
					$srcImage = $this->srcButtonNext;
					break;
				case "NO":
					$srcImage = $this->srcButtonNo;
					break;
				case "OK":
					$srcImage = $this->srcButtonOk;
					break;
				case "PREV":
					$srcImage = $this->srcButtonPrev;
					break;
				case "YES":
					$srcImage = $this->srcButtonYes;
					break;
			}
			
			echo '<input type="image" src="'.$srcImage.'" '.eregi_replace('name="process"', 'name=""', implode(" ", $rgAttributes)).' alt="'._t($rgButton['type']).'" />'.CRLF;
		}
	}
	
	// _createNewButton()
	function _createNewButton($argType, $argAttributes) {
		$this->buttons[count($this->buttons)] = array("type" => $argType, "attribute" => $argAttributes);
		return true;
	}
	
	// _createNewField()
	function _createNewField($argType, $argAttributes) {
		$this->fields[count($this->fields)] = array("type" => $argType, "attribute" => $argAttributes);
		return true;
	}
	
	// _createButtonBox()
	function _createButtonBox() {
		echo '<hr class="hidden" />'.CRLF;
		echo '<div id="layoutFoot">'.CRLF;
		
		foreach ($this->buttons as $rgButton) {
			if (isset($rgButton['attribute']['name']))
				$rgButton['attribute']['name'] = $rgButton['attribute']['name']."_".strtolower($rgButton['type'])."_".$rgButton['attribute']['value'];
			
			$rgAttributes = array();
			$rgKeys = array_keys($rgButton['attribute']);
			for ($iCount=0; $iCount<count($rgKeys); $iCount++) {
				$strCurrentKey = $rgKeys[$iCount];
				if ($strCurrentKey == "name")
					$strValueOfName = $rgButton['attribute'][$strCurrentKey];
				array_push($rgAttributes, $strCurrentKey.'="'.htmlspecialchars($rgButton['attribute'][$strCurrentKey]).'"');
			}
			
			switch (strtoupper($rgButton['type'])) {
				case "CANCEL":
					$srcImage = $this->srcButtonCancel;
					break;
				case "NEXT":
					$srcImage = $this->srcButtonNext;
					break;
				case "NO":
					$srcImage = $this->srcButtonNo;
					break;
				case "OK":
					$srcImage = $this->srcButtonOk;
					break;
				case "PREV":
					if ($rgButton['attribute']['disabled'] == "disabled")
						$srcImage = $this->srcButtonPrevDisabled;
					else
						$srcImage = $this->srcButtonPrev;
					break;
				case "YES":
					$srcImage = $this->srcButtonYes;
					break;
			}
			
			if ($rgButton['attribute']['disabled'] == "disabled")
				echo '<img src="'.$srcImage.'" alt="'._f('%1(작동불가)', $rgButton['type']).'" />'.CRLF;
			else
				echo '<input type="image" src="'.$srcImage.'" '.eregi_replace('name="process"', 'name=""', implode(" ", $rgAttributes)).' alt="'._t($rgButton['type']).'" />'.CRLF;
		}
		
		echo '</div>'.CRLF;
	}
	
	// _createFields()
	function _createFields($argExcepts=array()) {
		global $_POST;
		
		if (isset($_POST['mode']))
			$this->_createNewField("hidden", array("name" => "mode", "value" => $_POST['mode']));
		if (isset($_POST['lang']))
			$this->_createNewField("hidden", array("name" => "lang", "value" => $_POST['lang']));
		
		if (isset($_POST['dbServer']))
			$this->_createNewField("hidden", array("name" => "dbServer", "value" => $_POST['dbServer']));
		if (isset($_POST['dbName']))
			$this->_createNewField("hidden", array("name" => "dbName", "value" => $_POST['dbName']));
		if (isset($_POST['dbUser']))
			$this->_createNewField("hidden", array("name" => "dbUser", "value" => $_POST['dbUser']));
		if (isset($_POST['dbPassword']))
			$this->_createNewField("hidden", array("name" => "dbPassword", "value" => $_POST['dbPassword']));
		if (isset($_POST['dbPrefix']))
			$this->_createNewField("hidden", array("name" => "dbPrefix", "value" => $_POST['dbPrefix']));
		
		if (isset($_POST['ignoreRollback']))
			$this->_createNewField("hidden", array("name" => "ignoreRollback", "value" => $_POST['ignoreRollback']));
		if (isset($_POST['overwriteThis']))
			$this->_createNewField("hidden", array("name" => "overwriteThis", "value" => $_POST['overwriteThis']));
		if (isset($_POST['cleanInstall']))
			$this->_createNewField("hidden", array("name" => "cleanInstall", "value" => $_POST['cleanInstall']));
		if (isset($_POST['backupHead']))
			$this->_createNewField("hidden", array("name" => "backupHead", "value" => $_POST['backupHead']));
		if (isset($_POST['blogType']))
			$this->_createNewField("hidden", array("name" => "blogType", "value" => $_POST['blogType']));
		
		if (isset($_POST['email']))
			$this->_createNewField("hidden", array("name" => "email", "value" => $_POST['email']));
		if (isset($_POST['password']))
			$this->_createNewField("hidden", array("name" => "password", "value" => $_POST['password']));
		if (isset($_POST['blog']))
			$this->_createNewField("hidden", array("name" => "blog", "value" => $_POST['blog']));
		if (isset($_POST['name']))
			$this->_createNewField("hidden", array("name" => "name", "value" => $_POST['name']));
		
		if (isset($_POST['domain']))
			$this->_createNewField("hidden", array("name" => "domain", "value" => $_POST['domain']));
		
		foreach ($this->fields as $rgButton) {
			$bExcept = false;
			$rgAttributes = array();
			$rgKeys = array_keys($rgButton['attribute']);
			
			for ($iCount=0; $iCount<count($rgKeys); $iCount++) {
				$strCurrentKey = $rgKeys[$iCount];
				if (in_array($rgButton['attribute'][$strCurrentKey], $argExcepts))
					$bExcept = true;
				else
					array_push($rgAttributes, $strCurrentKey.'="'.htmlspecialchars($rgButton['attribute'][$strCurrentKey]).'"');
			}
			
			if ($bExcept == false)
				echo str_repeat("\t", 4).'<input type="'.$rgButton['type'].'" '.implode(" ", $rgAttributes).' />'.CRLF;
		}
	}
	
	// _createHelpMenu()
	function _createHelpMenu() {
?>
				<p id="helpBox">
<?php
		if (!empty($this->linkToManual)) {
?>
					<a id="help" href="<?php echo $this->linkToManual;?>" title="<?php echo _t('이 대화상자에 관련된 도움말을 보여줍니다.');?>"><?php echo _t('도움말');?></a>
<?php
		} else {
?>
					<span id="help" class="text"><?php echo _t('도움말');?></span>
<?php
		}
?>
				</p>
<?php
	}
}