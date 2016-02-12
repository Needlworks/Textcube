<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('__TEXTCUBE_SETUP__',true);
header('Content-Type: text/html; charset=utf-8');
ini_set('display_errors', 'on');

define('ROOT','.');
//if (!defined('__TEXTCUBE_CACHE_DIR__')) {
	define('__TEXTCUBE_CACHE_DIR__', ROOT . '/user/cache');
//}
require ROOT.'/framework/id/textcube/config.default.php';

if (version_compare(PHP_VERSION,'5.4.0', '<')) {
	if(!isset($service['forceinstall']) || $service['forceinstall'] != true) {
	    header('HTTP/1.1 503 Service Unavailable');
		echo "PHP Version mismatch. You need at least PHP 5.4.0 to install this version of Textcube.";
		exit;
	}
}
$bootFiles = array();
foreach (new DirectoryIterator(ROOT.'/framework/boot') as $fileInfo) {
	if($fileInfo->isFile()) array_push($bootFiles, $fileInfo->getPathname());
}
sort($bootFiles);
foreach ($bootFiles as $bf) {
	require_once($bf);
}
unset($bootFiles);
if (get_magic_quotes_gpc()) {
    foreach ($_GET as $key => $value)
        $_GET[$key] = stripslashes($value);
    foreach ($_POST as $key => $value)
        $_POST[$key] = stripslashes($value);
    foreach ($_COOKIE as $key => $value)
        $_COOKIE[$key] = stripslashes($value);
}
$host = explode(':', $_SERVER['HTTP_HOST']);
if (count($host) > 1) {
	$_SERVER['HTTP_HOST'] = $host[0];
	$_SERVER['SERVER_PORT'] = $host[1];
}
unset($host);
if(empty($accessInfo)) {
	$root = substr($_SERVER['SCRIPT_FILENAME'], 0, strlen($_SERVER['SCRIPT_FILENAME']) - 10);
	$path = stripPath(substr($_SERVER['PHP_SELF'], 0, strlen($_SERVER['PHP_SELF']) - 10));
} else {
	$root = substr($_SERVER['SCRIPT_FILENAME'], 0, strlen($_SERVER['SCRIPT_FILENAME']) - 12);
	$path = stripPath(substr($_SERVER['PHP_SELF'], 0, strlen($_SERVER['PHP_SELF']) - 12));
}
$_SERVER['PHP_SELF'] = rtrim($_SERVER['PHP_SELF'], '/');
// Set default table prefix.
if (isset($_POST['dbPrefix']) && $_POST['dbPrefix'] == '') {
	$_POST['dbPrefix'] == 'tc_';
}
$context = Model_Context::getInstance();
$context->setProperty('import.library', array(
	'function.string',
	'function.time',
	'function.javascript',
	'function.html',
	'function.xml',
	'function.mail'));
if(isset($_POST['dbms'])) $database['dbms'] = $_POST['dbms'];
require ROOT.'/library/include.php';

importlib('model.blog.blogSetting');
importlib('model.blog.entry');
importlib('auth');

if (!empty($_GET['test'])) {
	echo getFingerPrint();
	exit;
}
$baseLanguage = 'ko';
if( !empty($_POST['Lang']) ) $baseLanguage = $_POST['Lang'];
$locale = Locales::getInstance();
$locale->setDomain('setup');
if( $locale->setDirectory(ROOT.'/resources/locale/setup') ) $locale->set( $baseLanguage , "setup");

if (file_exists($root . '/config.php') && (filesize($root . '/config.php') > 0)) {
    header('HTTP/1.1 503 Service Unavailable');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo TEXTCUBE_NAME;?> <?php echo TEXTCUBE_VERSION;?> - Setup</title>
<link rel="stylesheet" media="screen" type="text/css" href="resources/style/setup/style.css" />
<script type="text/javascript">
//<![CDATA[
	function current(){
		document.getElementById("setup").submit();
	}
//]]>
</script>
</head>
<body>
	<div id="container">
		<form id="setup" name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
			<div id="title">
				<h1><img src="./resources/style/setup/image/title.gif" width="253" height="44" alt="Textcube를 점검합니다." /></h1>
			</div>

			<div id="inner">
				<p class="message"><?php echo _t('다시 설정하시려면 config.php를 먼저 삭제하셔야 합니다.');?></p>

				<p class="message">
<?php
	if( $locale->setDirectory(ROOT.'/resources/locale/setup')) {
		$currentLang = isset($_REQUEST['Lang']) ? $_REQUEST['Lang'] : '';
		$availableLanguages =   $locale->getSupportedLocales();
?>
Select Language : <select name="Lang" id = "Lang" onchange= "current();" >
<?php
		foreach( $availableLanguages as $key => $value)
			print('<option value="'.$key.'" '.( $key == $currentLang ? ' selected="selected" ' : '').' >'.$value.'</option>');
?></select>
<?php
	}
?>
				</p>
			</div>
		</form>
	</div>
</body>
</html>
<?php
    exit;
}

if (array_key_exists('phpinfo',$_GET)) {
	phpinfo();
	exit;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo TEXTCUBE_NAME;?> <?php echo TEXTCUBE_VERSION;?> - Setup</title>
<link rel="stylesheet" media="screen" type="text/css" href="./resources/style/setup/style.css" />
<script type="text/javascript">
//<![CDATA[
	function init() {
	}

	function previous() {
	}

	function current(){
		document.getElementById("step").value ="";
		document.getElementById("setup").submit();
	}

	function next(type) {
		if (type != undefined)
			document.getElementById("setupMode").value = type;
		document.getElementById("setup").submit();
	}

	function show(id) {
		if (document.getElementById("typeDomain"))
			document.getElementById("typeDomain").style.display = "none";
		if (document.getElementById("typePath"))
			document.getElementById("typePath").style.display = "none";
		if (document.getElementById("typeSingle"))
			document.getElementById("typeSingle").style.display = "none";
		if (document.getElementById(id))
			document.getElementById(id).style.display = "block";
	}
//]]>
</script>
</head>
<body onload="init()">
<div id="container">
<form id="setup" name="setup" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
	<div id="title">
		<h1><img src="./resources/style/setup/image/title.gif" width="253" height="44" alt="<?php echo TEXTCUBE_NAME;?> <?php echo TEXTCUBE_VERSION;?> Setup" /></h1>
	</div>
	<input type="hidden" name="Lang" id="Lang" value="<?php echo $baseLanguage;?>" />
<?php
if (empty($_POST['step'])) {
?>
	<div id="inner">
		<input type="hidden" id="step" name="step" value="1" />
		<h2><span class="step"><?php echo _f('%1단계', 1);?></span> : <?php echo _t('텍스트큐브 설치를 시작합니다.');?></h2>
		<div id="langSel" >
		<?php drawSetLang( $baseLanguage, 'Norm');?>
		</div>
		<div id="info"><b><?php echo TEXTCUBE_VERSION;?></b><br />
			<?php echo TEXTCUBE_COPYRIGHT;?><br />
			Homepage: <a href="<?php echo TEXTCUBE_HOMEPAGE;?>"><?php echo TEXTCUBE_HOMEPAGE;?></a>
		</div>
		<div id="content">
			<ol>
				<li><?php echo _t('소스를 포함한 소프트웨어에 포함된 모든 저작물(이하, 텍스트큐브)의 저작권자는 Needlworks / TNF 입니다.');?></li>
				<li><?php echo _t('텍스트큐브는 GPL 라이선스로 제공되며, 모든 사람이 자유롭게 이용할 수 있습니다.');?></li>
				<li><?php echo _t('프로그램 사용에 대한 유지 및 보수 등의 의무와, 사용 중 데이터 손실 등에 대한 사고책임은 모두 사용자에게 있습니다.');?></li>
				<li><?php echo _t('스킨 및 트리, 플러그인의 저작권은 각 제작자에게 있습니다.');?></li>
			</ol>
		</div>
		<div id="navigation">
			<a href="#" onclick="next(); return false;" title="<?php echo _t('다음');?>"><img src="./resources/style/setup/image/icon_next.gif" width="74" height="24" alt="<?php echo _t('다음');?>" /></a>
		</div>
	</div>
<?php
}
else if ($_POST['step'] == 7) {
	checkStep(8, false);
} else {

	for ($i = 1; $i <= $_POST['step']; $i ++) {
        if (!checkStep($i))
            break;
    }
    if ($i > $_POST['step'])
        checkStep($_POST['step'] + 1, false);
}

function checkStep($step, $check = true) {
    global $root, $path;
    $error = 0;
    if ($step == 1) {
        if ($check)
            return true;
    }
    else if ($step == 2) {
        if ($check) {
			if (!empty($_POST['mode'])) {
				switch ($_POST['mode']) {
					case 'install':
					case 'setup':
					case 'uninstall':
						return true;
				}
			}
        }
?>
  <input type="hidden" name="step" value="2" />
  <input id="setupMode" type="hidden" name="mode" value="" />
  <div id="inner">
    <h2><span class="step"><?php echo _f('%1단계', 2);?></span> : <?php echo _t('작업 유형을 선택해 주십시오.');?></h2>
    <div style="text-align:center">
      <div style="width:100%; padding:40px 0px 40px 0px">
        <div style="margin:20px;"><input type="button" value="<?php echo _t('새로운 텍스트큐브를 설정합니다');?>" style="width:100%; height:40px; font-size:14px" onclick="next('install');return false;" /></div>
        <div style="margin:20px;"><input type="button" value="<?php echo _t('텍스트큐브를 다시 설정합니다');?>" style="width:100%; height:40px; font-size:14px" onclick="next('setup');return false;" /></div>
        <div style="margin:20px;"><input type="button" value="<?php echo _t('텍스트큐브 테이블을 삭제합니다');?>" style="width:100%; height:40px; font-size:14px" onclick="next('uninstall');return false;" /></div>
      </div>
    </div>
  </div>
<?php
    }
    else if ($step == 3) {
        if ($check) {
			switch ($_POST['mode']) {
				case 'install':
				case 'setup':
					if (!empty($_POST['dbServer']) && !empty($_POST['dbName']) && !empty($_POST['dbUser']) && isset($_POST['dbPassword']) && isset($_POST['dbPrefix'])) {
						$dbTemp = array('server'=>$_POST['dbServer'],'username'=>$_POST['dbUser'],'password'=>$_POST['dbPassword'],'port'=>$_POST['dbPort']);
						if(!empty($_POST['dbName'])) $dbTemp['database'] = $_POST['dbName'];
						global $dbms;
						$dbms = $_POST['dbms'];
						if (!POD::bind($dbTemp))
							$error = 1;
//						else if (!POD::select_db($_POST['dbName']))	// select_db is deprecated.
//							$error = 2;
						else if (!empty($_POST['dbPrefix']) && !preg_match('/^[a-zA-Z0-9_]+$/', $_POST['dbPrefix']))
							$error = 3;
						else
							return true;
					}
					break;
				case 'uninstall':
					if (!empty($_POST['dbServer']) && !empty($_POST['dbName']) && !empty($_POST['dbUser']) && isset($_POST['dbPassword']) && !empty($_POST['dbPort'])) {
						$dbTemp = array('server'=>$_POST['dbServer'],'username'=>$_POST['dbUser'],'password'=>$_POST['dbPassword'],'port'=>$_POST['dbPort']);
						if(!empty($_POST['dbName'])) $dbTemp['database'] = $_POST['dbName'];
						global $dbms;
						$dbms = $_POST['dbms'];
						if (!POD::bind($dbTemp))
							$error = 1;
//						else if (!POD::select_db($_POST['dbName']))	// select_db is deprecated.
//							$error = 2;
						else
							return true;
					}
					break;
            }
        }
?>
  <input type="hidden" name="step" value="3" />
  <input type="hidden" name="mode" value="<?php echo $_POST['mode'];?>" />
  <script type="text/javascript">
    //<![CDATA[
     function suggestDefaultPort(db) {
		switch(db) {
			case 'MySQLi':
			default:
				port = 3306;
				break;
			case 'Cubrid':
				port = 30000;
				break;
			case 'PostgreSQL':
				port = 5432;
				break;
			default:
				port = '';
				break;
		}
		document.getElementById('dbPort').value = port;
		document.getElementById('dbms'+db).checked = checked;
		return true;
	 }
    //]]>
  </script>
  <div id="inner">
    <h2><span class="step"><?php echo _f('%1단계', 3);?></span> : <?php echo _t('작업 정보를 입력해 주십시오.');?></h2>
    <div id="userinput">
    <table class="inputs">
	  <tr>
        <th><?php echo _t('데이터베이스 관리 시스템');?> :</th>
        <td>
<?php
$dbmsSupport = array();
if(function_exists('mysqli_connect')) array_push($dbmsSupport,'MySQLi');
if(function_exists('pg_connect')) array_push($dbmsSupport,'PostgreSQL');
if(class_exists('SQLite3')) array_push($dbmsSupport,'SQLite3');
if(function_exists('cubrid_connect')) array_push($dbmsSupport,'Cubrid');
foreach($dbmsSupport as $dbms) {
?>
	      <input type="radio" id="dbms<?php echo $dbms;?>" name="dbms" value="<?php echo $dbms;?>" <?php echo (((isset($_POST['dbms']) && $_POST['dbms'] == $dbms)||(!isset($_POST['dbms']) && $dbms == $dbmsSupport[0])) ? 'checked' : '');?> onclick="suggestDefaultPort('<?php echo $dbms;?>');return false;" /> <?php echo $dbms;?>
<?php
}
?>
         </td>
      </tr>
	  <tr>
        <th><?php echo _t('데이터베이스 서버');?> :</th>
        <td>
          <input type="text" name="dbServer" value="<?php echo (isset($_POST['dbServer']) ? $_POST['dbServer'] : 'localhost');?>" class="input<?php echo ($check && (empty($_POST['dbServer']) || ($error == 1)) ? ' input_error' : '');?>" />
        </td>
      </tr>
      <tr>
        <th><?php echo _t('데이터베이스 포트');?> :</th>
        <td>
          <input type="text" id="dbPort" name="dbPort" value="<?php echo (isset($_POST['dbPort']) ? $_POST['dbPort'] :
		  '3306'
		  );?>" class="input<?php echo ($check && (empty($_POST['dbPort']) || ($error == 1)) ? ' input_error' : '');?>" />
        </td>
      </tr>
	  <tr>
        <th><?php echo _t('데이터베이스 이름');?> :</th>
        <td>
          <input type="text" name="dbName" value="<?php echo (isset($_POST['dbName']) ? $_POST['dbName'] : NULL);?>" class="input<?php echo ($check && (empty($_POST['dbName']) || ($error == 2)) ? ' input_error' : '');?>" />
        </td>
      </tr>
      <tr>
        <th><?php echo _t('데이터베이스 사용자명');?> :</th>
        <td>
          <input type="text" name="dbUser" value="<?php echo (isset($_POST['dbUser']) ? $_POST['dbUser'] : '');?>" class="input<?php echo ($check && (empty($_POST['dbUser']) || $error) ? ' input_error' : '');?>" />
        </td>
      </tr>
      <tr>
        <th><?php echo _t('데이터베이스 암호');?> :</th>
        <td>
          <input type="password" name="dbPassword" value="<?php echo (isset($_POST['dbPassword']) ? htmlspecialchars($_POST['dbPassword']) : '');?>" class="input<?php echo ($check && ($error == 1) ? ' input_error' : '');?>" />
        </td>
      </tr>
<?php
		switch ($_POST['mode']) {
			case 'install':
			case 'setup':
?>
      <tr>
        <th><?php echo _t('테이블 식별자');?> :</th>
        <td>
          <input type="text" name="dbPrefix" value="<?php echo (isset($_POST['dbPrefix']) ? $_POST['dbPrefix'] : 'tc_');?>" class="input <?php echo ($check && ($error == 3) ? ' input_error' : '');?>" />
        </td>
      </tr>
<?php
				break;
			case 'uninstall':
				break;
		}
?>
    </table>
    </div>
    <div id="content">
      <ol>
        <li><?php echo _t('데이터베이스가 해당 호스트에 먼저 생성되어 있어야 합니다.');?></li>
		<li><?php echo _t('테이블식별자는 텍스트큐브가 사용하는 테이블이름 앞에 붙는 문자열입니다. 데이터 베이스내에 다른 어플리케이션이 사용하는 테이블이 있을 경우 구별하기 위해 사용합니다');?> <?php echo _t('테이블식별자를 입력하지 않을 경우 자동으로 tc_ 를 사용합니다.');?></li>
      </ol>
    </div>
    <div id="warning"><?php
        if ($error == 1)
           echo _t('데이터베이스 서버에 연결할 수 없습니다. 정보를 다시 입력해 주십시오.');
        else if ($error == 2)
           echo _t('데이터베이스를 사용할 수가 없습니다. 정보를 다시 입력해 주십시오.');
        else if ($error == 3)
           echo _t('테이블 식별자가 올바르지 않습니다. 다시 입력해 주십시오.');
        else if ($error == 6)
           echo _t('데이터베이스에 연결할 수 없습니다.');
        else if ($error == 7)
           echo _t('데이터베이스에 접근할 수 없습니다.');
        else if ($error == 8)
           echo _t('새로운 테이블 식별자가 올바르지 않습니다. 다시 입력해 주십시오.');
        else if ($check)
           echo _t('표시된 정보가 부족합니다.');
        else
           echo '&nbsp;';
?></div>
  <div id="navigation">
    <a href="#" onclick="window.history.back()" title="<?php echo _t('이전');?>"><img src="./resources/style/setup/image/icon_prev.gif" width="74" height="24" alt="<?php echo _t('이전');?>" /></a>
    <a href="#" onclick="next(); return false;" title="<?php echo _t('다음');?>"><img src="./resources/style/setup/image/icon_next.gif" width="74" height="24" alt="<?php echo _t('다음');?>" /></a>
  </div>
  </div>
<?php
    }
    else if (($step == 4) || ($step == 33)) {
        if ($check) {
			if ($_POST['mode'] == 'uninstall') {
				if (empty($_POST['target'])) {
					checkStep(2, false);
					return false;
				}
				else {
					checkStep(205, false);
					return false;
				}
			}
            if (!empty($_POST['checked']) && $_POST['checked'] == 'yes')
                return true;
        }
		if ($_POST['mode'] == 'uninstall')
			return checkStep(204, false);
?>
  <input type="hidden" name="step" value="4" />
  <input type="hidden" name="mode" value="<?php echo $_POST['mode'];?>" />
  <input type="hidden" name="dbms" value="<?php echo (isset($_POST['dbms']) ? $_POST['dbms'] : '');?>" />
  <input type="hidden" name="dbServer" value="<?php echo (isset($_POST['dbServer']) ? $_POST['dbServer'] : '');?>" />
  <input type="hidden" name="dbName" value="<?php echo (isset($_POST['dbName']) ? $_POST['dbName'] : '');?>" />
  <input type="hidden" name="dbPort" value="<?php echo (isset($_POST['dbPort']) ? $_POST['dbPort'] : '');?>" />
  <input type="hidden" name="dbUser" value="<?php echo (isset($_POST['dbUser']) ? $_POST['dbUser'] : '');?>" />
  <input type="hidden" name="dbPassword" value="<?php echo (isset($_POST['dbPassword']) ? htmlspecialchars($_POST['dbPassword']) : '');?>" />
  <input type="hidden" name="dbPrefix" value="<?php echo (isset($_POST['dbPrefix']) ? $_POST['dbPrefix'] : '');?>" />
  <input type="hidden" name="disableRewrite" value="<?php echo (isset($_POST['disableRewrite']) ? $_POST['disableRewrite'] : '');?>" />
  <div id="inner">
    <h2><span class="step"><?php echo _f('%1단계', 4);?></span> : <?php echo _t('설치 요구 사항을 확인하고 있습니다.');?> </h2>
    <div id="content-box">
    <h3><?php echo _t('환경');?></h3>
    <ul>
      <li><?php echo _t('하드웨어');?>: <?php echo @exec('uname -mp');?></li>
      <li><?php echo _t('운영체제');?>: <?php echo @exec('uname -sir');?></li>
      <li><?php echo _t('웹서버');?>: <?php echo $_SERVER['SERVER_SOFTWARE'];?> <?php echo isset($_SERVER['SERVER_SIGNATURE']) ? $_SERVER['SERVER_SIGNATURE'] : '(no signature)';?></li>
      <li><?php echo _t('PHP 버전');?>: <?php echo phpversion();?></li>
      <li><?php echo _t('데이터베이스 종류');?>: <?php echo POD::dbms();?></li>
      <li><?php echo _f('%1 버전',POD::dbms());?>: <?php echo POD::version();?></li>
    </ul>
    <h3>PHP</h3>
    <ul>
<?php
        $functions = "
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
max
md5
microtime
min
mkdir
mktime
move_uploaded_file
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
";
        $required = array();
        foreach (explode("\n", str_replace("\r", '', trim($functions))) as $function) {
            if (!function_exists($function))
                array_push($required, $function);
        }
		if (version_compare(PHP_VERSION, '5.4.0') === -1 && ( !isset( $service['forceinstall'] ) || $service['forceinstall']==false) ) {
			$error = 4;
?>
                <span style="color:red"><?php echo _f('PHP 버전이 낮습니다. 설치를 위해서는 최소한 %1 이상의 버전이 필요합니다.','5.4.0');?></span>
<?php
		} else if (count($required) == 0) {
?>
                  <li>OK</li>
<?php
        } else {
            $error = 4;
?>
                <span style="color:red"><?php echo _t('함수가 설치되어야 합니다.');?></span>
<?php
            foreach ($required as $function) {
?>
                  <li style="color:red"><?php echo $function;?></li>
<?php
            }
        }
?>
    </ul>
    <h3><?php echo POD::dbms();?></h3>
    <ul>
<?php
        if (POD::charset() == 'utf8')
           echo '<li>Character Set: OK</li>';
        else {
           echo '<li style="color:navy">Character Set: ', _t('UTF8 미지원 (경고: 한글 지원이 불완전할 수 있습니다.)'), '</li>';
        }
        if (POD::query("CREATE TABLE {$_POST['dbPrefix']}Setup (a INT NOT NULL)")) {
            POD::query("DROP TABLE {$_POST['dbPrefix']}Setup");
           echo '<li>', _t('테이블 생성 권한'), ': OK</li>';
        }
        else {
            $error = 6;
           echo '<li style="color:red">', _t('테이블 생성 권한'), ': ', _t('없음'), '</li>';
        }
?>
    </ul>
<?php
        $tables = array();
        if ($result = POD::tableList()) {
            foreach($result as $table) {
                if (strncmp($table, $_POST['dbPrefix'], strlen($_POST['dbPrefix'])))
                    continue;
                switch (strtolower(substr($table, strlen($_POST['dbPrefix'])))) {
                    case 'attachments':
                    case 'blogsettings':
                    case 'blogstatistics':
                    case 'categories':
					case 'comments':
					case 'commentsnotified':
					case 'commentsnotifiedqueue':
					case 'commentsnotifiedsiteinfo':
					case 'dailystatistics':
					case 'entries':
					case 'entriesarchive':
					case 'feedgrouprelations':
					case 'feedgroups':
					case 'feeditems':
					case 'feedreads':
					case 'feedsettings':
					case 'feedstarred':
					case 'feeds':
					case 'filters':
                    case 'linkcategories':
                    case 'links':
					case 'openidusers':
					case 'pagecachelog':
					case 'plugins':
                    case 'refererlogs':
                    case 'refererstatistics':
                    case 'reservedwords':
					case 'servicesetting':
                    case 'sessionvisits':
                    case 'sessions':
                    case 'skinsettings':
                    case 'tagrelations':
                    case 'tags':
                    case 'teamblog':
                    case 'trackbacklogs':
                    case 'trackbacks':
					case 'usersettings':
                    case 'users':
                    case 'xmlrpcpingsettings':
                        $tables[count($tables)] = $table;
                        break;
                }
            }
        }

		switch ($_POST['mode']) {
			case 'install':
				echo '<h3>', _t('새 데이터베이스 테이블'), '</h3>';
				if (count($tables) == 0) {
					echo '<ul><li>OK</li></ul>';
				} else {
					$error = 7;
					echo '<ul style="color:red">', _t('테이블이 이미 존재합니다.');
					foreach ($tables as $table)
						echo '<li>', $table, '</li>';
					echo '</ul>';
				}
				break;
			case 'setup':
				echo '<h3>', _t('데이터베이스 테이블 확인'), '</h3>';
				if (((count($tables) < 40) && (count($tables) > 35)) || ((count($tables) == 35) && !in_array('Filters', $tables))) {
					echo '<ul><li>OK</li></ul>';
				} else {
					$error = 7;
					echo '<ul style="color:red">', _t('테이블이 존재하지 않습니다.');
					foreach ($tables as $table)
						echo '<li>', $table, '</li>';
					echo '</ul>';
				}
		}
?>
    <h3><?php echo _t('파일 시스템 권한');?></h3>
    <ul>
<?php
        $commands = array();
        $filename = $root . '/.htaccess';
        if (file_exists($filename)) {
            if (is_writable($filename)) {
                if (filesize($filename))
                   echo '<li style="color:navy">', _f('설정 파일: OK (경고: "%1" 파일을 덮어 쓰게 됩니다.)', $filename), '</li>';
                else
                   echo '<li>', _t('웹 설정 파일'), ': OK</li>';
            }
            else {
                $error = 8;
                echo '<li style="color:red">', _t('웹 설정 파일'), ': ', _f('"%1"에 접근할 수 없습니다. 퍼미션을 %2(으)로 수정해 주십시오.', $filename, '0666'), '</li>';
                array_push($commands, 'chmod 0666 '.$filename);
            }
        }
        else if (is_writable($root))
            echo '<li>', _t('웹 설정 파일'), ': OK</li>';
        else {
            $error = 9;
            echo '<li style="color:red">', _t('웹 설정 파일'), ': ', _f('"%1"에 %2 파일을 생성할 수 없습니다. "%1"의 퍼미션을 %3(으)로 수정해 주십시오.', $root, '.htaccess', '0777'), '</li>';
            array_push($commands, 'chmod 0777 '.$root);
        }

        $filename = $root . '/config.php';
        if (file_exists($filename)) {
            if (is_writable($filename)) {
                if (filesize($filename))
                   echo '<li style="color:navy">', _f('설정 파일: OK (경고: "%1" 파일을 덮어 쓰게 됩니다.)', $filename), '</li>';
                else
                   echo '<li>', _t('설정 파일'), ': OK</li>';
            }
            else {
                $error = 10;
                echo '<li style="color:red">', _t('설정 파일'), ': ', _f('"%1"에 접근할 수 없습니다. 퍼미션을 %2(으)로 수정해 주십시오.', $filename, '0666'), '</li>';
                array_push($commands, 'chmod 0666 '.$filename);
            }
        }
        else if (is_writable($root))
           echo '<li>', _t('설정 파일'), ': OK</li>';
        else {
            $error = 11;
            echo '<li style="color:red">', _t('설정 파일'), ': ', _f('"%1"에 %2 파일을 생성할 수 없습니다. "%1"의 퍼미션을 %3(으)로 수정해 주십시오.', $root, 'config.php', '0777'), '</li>';
            array_push($commands, 'chmod 0777 '.$root);
        }

        $filename = $root . '/user';
        if (file_exists($filename)) {
            if (is_dir($filename) && is_writable($filename))
               echo '<li>', _t('사용자 데이터 디렉토리'), ': OK</li>';
            else {
                $error = 12;
                echo '<li style="color:red">', _t('사용자 데이터 디렉토리'), ': ', _f('"%1"에 접근할 수 없습니다. 퍼미션을 %2(으)로 수정해 주십시오.', $filename, '0777'), '</li>';
                array_push($commands, 'chmod 0777 '.$filename);
            }
        } else if (mkdir($filename)) {
            @chmod($filename, 0777);
            echo '<li>', _t('사용자 데이터 디렉토리'), ': OK</li>';
        } else {
            $error = 13;
            echo '<li style="color:red">', _t('사용자 데이터 디렉토리'), ': ', _f('"%1"에 %2 디렉토리를 생성할 수 없습니다. "%1"의 퍼미션을 %3(으)로 수정해 주십시오.', $root, 'user', '0777'), '</li>';
            array_push($commands, 'chmod 0777 '.$root);
        }

        $filename = $root . '/user/attach';
        if (file_exists($filename)) {
            if (is_dir($filename) && is_writable($filename))
               echo '<li>', _t('첨부 디렉토리'), ': OK</li>';
            else {
                $error = 12;
                echo '<li style="color:red">', _t('첨부 디렉토리'), ': ', _f('"%1"에 접근할 수 없습니다. 퍼미션을 %2(으)로 수정해 주십시오.', $filename, '0777'), '</li>';
                array_push($commands, 'chmod 0777 '.$filename);
            }
        } else if (mkdir($filename)) {
            @chmod($filename, 0777);
            echo '<li>', _t('첨부 디렉토리'), ': OK</li>';
        } else {
            $error = 13;
            echo '<li style="color:red">', _t('첨부 디렉토리'), ': ', _f('"%1"에 %2 디렉토리를 생성할 수 없습니다. "%1"의 퍼미션을 %3(으)로 수정해 주십시오.', $root, 'attach', '0777'), '</li>';
            array_push($commands, 'chmod 0777 '.$root);
        }

        $filename = $root . '/user/cache';
        if (is_dir($filename)) {
            if (is_writable($filename))
               echo '<li>', _t('캐시 디렉토리'), ': OK</li>';
            else {
                $error = 12;
                    echo '<li style="color:red">', _t('캐시 디렉토리'), ': ', _f('"%1"에 접근할 수 없습니다. 퍼미션을 %2(으)로 수정해 주십시오.', $filename, '0777'), '</li>';
                    array_push($commands, 'chmod 0777 '.$filename);
            }
        } else if (mkdir($filename)) {
            @chmod($filename, 0777);
            echo '<li>', _t('캐시 디렉토리'), ': OK</li>';
        } else {
            $error = 13;
            echo '<li style="color:red">', _t('캐시 디렉토리'), ': ', _f('"%1"에 %2 디렉토리를 생성할 수 없습니다. "%1"의 퍼미션을 %3(으)로 수정해 주십시오.', $root, 'cache', '0777'), '</li>';
            array_push($commands, 'chmod 0777 '.$root);
        }

/*        $filename = $root . '/remote';
        if (is_dir($filename)) {
            if (is_writable($filename))
               echo '<li>', _t('원격 설치 디렉토리'), ': OK</li>';
            else {
                $error = 12;
               echo '<li style="color:red">', _t('원격 설치 디렉토리'), ': ', _f('"%1"에 접근할 수 없습니다. 퍼미션을 %2(으)로 수정해 주십시오.', $filename, '0777'), '</li>';
            }
        } else if (mkdir($filename)) {
			@chmod($filename, 0777);
           echo '<li>', _t('원격 설치 디렉토리'), ': OK</li>';
        } else {
            $error = 13;
           echo '<li style="color:red">', _t('원격 설치 디렉토리'), ': ', _f('"%1"에 %2 디렉토리를 생성할 수 없습니다. "%1"의 퍼미션을 %3(으)로 수정해 주십시오.', $root, 'cache', '0777'), '</li>';
        }*/

        $filename = $root . '/user/skin/blog/customize';
        if (is_dir($filename)) {
            if (is_writable($filename))
               echo '<li>', _t('스킨 디렉토리'), ': OK</li>';
            else {
                $error = 14;
                echo '<li style="color:red">', _t('스킨 디렉토리'), ': ', _f('"%1"에 접근할 수 없습니다. 퍼미션을 %2(으)로 수정해 주십시오.', $filename, '0777'), '</li>';
                array_push($commands, 'chmod 0777 '.$filename);
            }
        } else if (mkdir($filename)) {
			@chmod($filename, 0777);
           echo '<li>', _t('스킨 디렉토리'), ': OK</li>';
        } else {
            $error = 15;
            echo '<li style="color:red">', _t('스킨 디렉토리'), ': ', _f('"%1"에 %2 디렉토리를 생성할 수 없습니다. "%1"의 퍼미션을 %3(으)로 수정해 주십시오.', "$root/user/skin/blog", 'customize', '0777'), '</li>';
            array_push($commands, 'chmod 0777 '."$root/user/skin/blog");
        }

?>
    </ul>
<?php
        if (!empty($commands)) {
			echo '<span class="instruction">'._t("퍼미션 수정은 FTP 프로그램을 사용하시거나 다음의 명령을 터미널에 붙여 넣으시면 됩니다.")."</span>";
            echo '<ul class="instruction">';
            $commands = array_unique($commands);
            foreach($commands as $command) {
                echo "<li>" . $command . "</li>";
            }
            echo '</ul>';
        }
        if ($step == 33) {
            $error = 16;
            if (checkIIS()) {
?>
	<h3><?php echo _t('IIS Rewrite Module');?></h3>
	<ul style="color:red">
		<li><?php echo _t('현재 IIS에서의 설치는 실험적으로만 지원하고 있으며 별도의 Rewrite 모듈을 사용해야 합니다.').' '._t('만약 이 페이지를 보고 계시다면 Apache mod_rewrite와 호환되지 않는 Rewrite 모듈을 사용 중이거나 아예 모듈이 없는 경우입니다.'); ?></li>
		<li><?php echo _t('IIS 7.0을 사용하시는 경우 공식 URL Rewrite Module을 사용하려면 <a href="http://www.iis.net/extensions/URLRewrite">이곳에서 다운로드</a>받아 설치하시고, 계속 진행·설치 후 생성되는 <b>.htaccess</b> 파일 내용을 그대로 import해주시면 됩니다.'); ?></li>
		<li><?php echo _t('IIS 6.0 이전 버전을 사용하시는 경우 Rewrite 모듈을 설치하려면, 오픈스소 무료 모듈을 제공하고 있는 <a href="http://www.codeplex.com/IIRF" target="_blank">Ionics Isapi Rewrite Filter 홈페이지</a>를 방문하여 설치하신 후, 계속 진행·설치 후 생성되는 <b>.htaccess</b> 파일의 내용을 위 모듈의 설정파일(<b>IsapiRewrite4.ini</b>)에 복사하시기 바랍니다.'); ?></li>
	</ul>
	<p>
		<input type="radio" name="rewriteIIS" value="IISRewrite" id="rewriteIIS_Option1"><label for="rewriteIIS_Option1"><?php echo _t('IIS 7.0용 공식 URL Rewrite 모듈을 사용합니다.'); ?></label><br />
		<input type="radio" name="rewriteIIS" value="ISAPI" id="rewriteIIS_Option2"><label for="rewriteIIS_Option2"><?php echo _t('IIS 6.0 및 그 이전 버전을 위한 오픈소스 Rewrite 모듈을 사용합니다.'); ?></label>
	</p>
<?php
				$error = 0;
			} else {
?>
    <h3><?php echo _t('Apache Rewrite Engine');?></h3>
    <ul style="color:red">
      <li><?php echo _t('Rewrite를 사용할 수 없습니다.');?><br /><span style="color:black"><?php echo _t('다음 항목을 확인하십시오.');?></span></li>
      <input type="checkbox" id="disableRewrite" name="disableRewrite" />
	  <label for="disableRewrite"><?php echo _t('rewrite 모듈을 사용하지 않습니다.').' '._t('만약 rewrite 모듈 설정을 올바르게 했는데도 모듈 사용 여부의 검사에 문제가 있는 경우 rewrite 모듈을 사용하지 않음을 선택하시고 이 부분을 건너 뛰시기 바랍니다.').' '._t('지금 설정하지 않아도 설치 이후에 관리 패널의 서비스설정-서버 에서 rewrite 관련 설정을 할 수 있습니다.');?></label>
      <ol style="color:blue">
        <li><?php echo _t('웹서버 설정에 <b>mod_rewrite</b>의 로딩이 포함되어야 합니다.');?><br />
          <samp><?php echo _t('예: LoadModule <b>rewrite_module</b> modules/<b>mod_rewrite</b>.so');?></samp>
        </li>
        <li><?php echo _t('웹서버 설정의 이 디렉토리에 대한 <em>Options</em> 항목에 <b>FollowSymLinks</b>가 포함되거나 <b>All</b>이어야 합니다.');?>
          <samp><br /><?php echo _t('예: Options <b>FollowSymLinks</b>');?></samp>
          <samp><br /><?php echo _t('예: Options <b>All</b>');?></samp>
        </li>
        <li><?php echo _t('웹서버 설정의 이 디렉토리에 대한 <em>AllowOverride</em> 항목에 <b>FileInfo</b>가 포함되거나 <b>All</b>이어야 합니다.');?>
          <samp><br /><?php echo _t('예: AllowOverride <b>FileInfo</b>');?></samp>
          <samp><br /><?php echo _t('예: AllowOverride <b>All</b>');?></samp>
        </li>
        <li><b><?php echo _t('위 2와 3의 문제는 아래 내용을 웹서버 설정에 포함시켜 해결할 수 있습니다.');?></b>
          <samp style="color:black"><br />
          &lt;Directory &quot;<?php echo $root;?>&quot;&gt;<br />
          &nbsp;&nbsp;Options FollowSymLinks<br />
          &nbsp;&nbsp;AllowOverride FileInfo<br />
          &lt;/Directory&gt;
          </samp>
        </li>
      </ul>
    </ul>
<?php
			}
        }
?>
  </div>
  <div id="navigation">
    <a href="#" onclick="window.history.back()" title="<?php echo _t('이전');?>"><img src="./resources/style/setup/image/icon_prev.gif" width="74" height="24" alt="<?php echo _t('이전');?>" /></a>
    <a href="#" onclick="next(); return false;" title="<?php echo _t('다음');?>"><img src="./resources/style/setup/image/icon_next.gif" width="74" height="24" alt="<?php echo _t('다음');?>" /></a>
  </div>
  </div>
  <input type="hidden" name="checked" value="<?php echo ($error > 0 ? 'no' : 'yes');?>" />
<?php
    }
    else if ($step == 5) {
        if ($check) {
            if (!empty($_POST['domain']) && !empty($_POST['type']))
                return true;
        }
		// mod_rewrite routine.
		if(empty($_POST['disableRewrite']) && empty($_POST['rewriteIIS'])) {
	        $filename = $root . '/.htaccess';
    	    $fp = fopen($filename, 'w+');
	        if (!$fp) {
    	        checkStep($step - 1, false);
	            return false;
	        }
    	    fwrite($fp,
"RewriteEngine On
RewriteBase $path/
RewriteRule ^testrewrite$ setup.php [L]"
        	);
	        fclose($fp);
			@chmod($filename, 0666);

        	if (testMyself('blog' . substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.')), $path . '/testrewrite?test=now', $_SERVER['SERVER_PORT']))
            	$rewrite = 3;
	        else if (testMyself('blog.' . $_SERVER['HTTP_HOST'], $path . '/testrewrite?test=now', $_SERVER['SERVER_PORT']))
    	        $rewrite = 2;
	        else if (testMyself($_SERVER['HTTP_HOST'], $path . '/testrewrite?test=now', $_SERVER['SERVER_PORT']))
    	        $rewrite = 1;
	        else {
    	        $rewrite = 0;
				@unlink($filename);
				checkStep(33, false);
				return false;
			}
			@unlink($filename);
		} else if (!empty($_POST['rewriteIIS'])) {
			switch ($_POST['rewriteIIS']) {
			case 'ISAPI':
				$rewrite = -1;
				break;
			case 'IISRewrite':
			default:
				$rewrite = -2;
			}
		} else {
			$rewrite = 0;
		}
    	$domain = $rewrite == 3 ? substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.') + 1) : $_SERVER['HTTP_HOST'];

        $blogProtocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $blogDefaultPort = isset($_SERVER['HTTPS']) ? 443 : 80;
?>
  <input type="hidden" name="step" value="<?php echo $step;?>" />
  <input type="hidden" name="mode" value="<?php echo $_POST['mode'];?>" />
  <input type="hidden" name="dbms" value="<?php echo (isset($_POST['dbms']) ? $_POST['dbms'] : '');?>" />
  <input type="hidden" name="dbServer" value="<?php echo (isset($_POST['dbServer']) ? $_POST['dbServer'] : '');?>" />
  <input type="hidden" name="dbPort" value="<?php echo (isset($_POST['dbPort']) ? $_POST['dbPort'] : '');?>" />
  <input type="hidden" name="dbName" value="<?php echo (isset($_POST['dbName']) ? $_POST['dbName'] : '');?>" />
  <input type="hidden" name="dbUser" value="<?php echo (isset($_POST['dbUser']) ? $_POST['dbUser'] : '');?>" />
  <input type="hidden" name="dbPassword" value="<?php echo (isset($_POST['dbPassword']) ? htmlspecialchars($_POST['dbPassword']) : '');?>" />
  <input type="hidden" name="dbPrefix" value="<?php echo (isset($_POST['dbPrefix']) ? $_POST['dbPrefix'] : '');?>" />
  <input type="hidden" name="checked" value="<?php echo (isset($_POST['checked']) ? $_POST['checked'] : '');?>" />
  <input type="hidden" name="domain" value="<?php echo $domain;?>" />
  <input type="hidden" name="disableRewrite" value="<?php echo (isset($_POST['disableRewrite']) ? $_POST['disableRewrite'] : '');?>" />
  <input type="hidden" name="rewriteMode" value="<?php echo ($rewrite <= -1) ? $_POST['rewriteIIS'] : 'mod_rewrite';?>" />
  <div id="inner">
  <h2><span class="step"><?php echo _f('%1단계', $step);?></span> : <?php echo _t('사용 가능한 운영 방법은 다음과 같습니다. 선택하여 주십시오.');?></h2>
  <div id="userinput">
    <table class="inputs">
<?php
        if ($rewrite != 0) {
?>
      <tr>
        <th width="120"><strong><?php echo _t('다중 사용자');?> : </strong></th>
        <td>
<?php
            if ($rewrite >= 2) {
?>
        <label for="type1"><input type="radio" id="type1" name="type" value="domain" checked="checked" onclick="show('typeDomain');" />
                      <?php echo _t('도메인네임(DNS)으로 블로그 식별');?></label>
        <br />
<?php
            }
?>
        <label for="type2"><input type="radio" id="type2" name="type" value="path"<?php echo (($rewrite == 1 || $rewrite == -1) ? ' checked="checked"' : '');?> onclick="show('typePath');" />
        <?php echo _t('하위 경로(Path)로 블로그 식별');?></label></td>
      </tr>
<?php
		}
?>
      <tr>
        <th style="padding-top:10px"><strong><?php echo _t('단일 사용자');?> : </strong></th>
        <td style="padding-top:10px">
          <label for="type3"><input type="radio" id="type3" name="type" value="single" onclick="show('typeSingle');" <?php echo (empty($_POST['disableRewrite']) ? '' : 'checked="checked"');?> /><?php echo _t('단일 블로그');?></label></td>
      </tr>
      <tr>
        <th style="padding-top:20px"><?php echo _t('블로그 주소 예시');?></th>
        <td style="padding-top:20px; height:100px">
        <ul id="typeDomain"<?php echo ($rewrite >= 2 ? '' : ' style="display:none"');?>>
          <li><?php echo $blogProtocol;?>://<b>blog1</b>.<?php echo $domain;?><?php echo ($_SERVER['SERVER_PORT'] == $blogDefaultPort ? '' : ":{$_SERVER['SERVER_PORT']}");?><?php echo $path;?>/</li>
          <li><?php echo $blogProtocol;?>://<b>blog2</b>.<?php echo $domain;?><?php echo ($_SERVER['SERVER_PORT'] == $blogDefaultPort ? '' : ":{$_SERVER['SERVER_PORT']}");?><?php echo $path;?>/</li>
        </ul>
        <ul id="typePath"<?php echo ($rewrite == 1 ? '' : ' style="display:none"');?>>
          <li><?php echo $blogProtocol;?>://<?php echo $domain;?><?php echo ($_SERVER['SERVER_PORT'] == $blogDefaultPort ? '' : ":{$_SERVER['SERVER_PORT']}");?><?php echo $path;?>/<b>blog1</b></li>
          <li><?php echo $blogProtocol;?>://<?php echo $domain;?><?php echo ($_SERVER['SERVER_PORT'] == $blogDefaultPort ? '' : ":{$_SERVER['SERVER_PORT']}");?><?php echo $path;?>/<b>blog2</b></li>
        </ul>
        <ul id="typeSingle" <?php echo (empty($_POST['disableRewrite']) ? 'style="display:none"' : '');?>>
          <li><?php echo $blogProtocol;?>://<?php echo $domain;?><?php echo ($_SERVER['SERVER_PORT'] == $blogDefaultPort ? '' : ":{$_SERVER['SERVER_PORT']}");?><?php echo $path;?>/<?php echo (empty($_POST['disableRewrite']) ? '' : 'blog/');?></li>
        </ul>
        </td>
      </tr>
    </table>
  </div>
  <div id="navigation">
    <a href="#" onclick="window.history.back()" title="<?php echo _t('이전');?>"><img src="./resources/style/setup/image/icon_prev.gif" width="74" height="24" alt="<?php echo _t('이전');?>" /></a>
    <a href="#" onclick="next(); return false;" title="<?php echo _t('다음');?>"><img src="./resources/style/setup/image/icon_next.gif" width="74" height="24" alt="<?php echo _t('다음');?>" /></a>
  </div>
  </div>
<?php
    }
    else if ($step == 6) {
        if ($check) {
            if (!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['password2']) && (($_POST['type'] == 'single') || !empty($_POST['blog'])) && isset($_POST['name'])) {
                if (!preg_match('/^[^@]+@([-a-zA-Z0-9]+\.)+[-a-zA-Z0-9]+$/', $_POST['email']))
                    $error = 51;
                else if ($_POST['password'] != $_POST['password2'])
                    $error = 52;
                else if (($_POST['type'] != 'single') && !preg_match('/^[a-zA-Z0-9]+$/', $_POST['blog']))
                    $error = 53;
                else if (strlen($_POST['password']) < 6 || strlen($_POST['password2']) < 6)
					$error = 54;
                else
                    return true;
            }
        } else {
			@POD::query('SET CHARACTER SET utf8');
			if ($result = @POD::query("SELECT loginid, password, name FROM {$_POST['dbPrefix']}Users WHERE userid = 1")) {
				@list($_POST['email'], $_POST['password'], $_POST['name']) = POD::fetch($result,'row');
				$_POST['password2'] = $_POST['password'];
				POD::free($result);
			}
			if ($result = @POD::queryCell("SELECT value FROM {$_POST['dbPrefix']}BlogSettings
						WHERE blogid = 1
							AND name = 'name'")) {
				$_POST['blog'] = $result;
			}
		}
?>
  <input type="hidden" name="step" value="<?php echo $step;?>" />
  <input type="hidden" name="mode" value="<?php echo $_POST['mode'];?>" />
  <input type="hidden" name="dbms" value="<?php echo (isset($_POST['dbms']) ? $_POST['dbms'] : '');?>" />
  <input type="hidden" name="dbServer" value="<?php echo (isset($_POST['dbServer']) ? $_POST['dbServer'] : '');?>" />
  <input type="hidden" name="dbPort" value="<?php echo (isset($_POST['dbPort']) ? $_POST['dbPort'] : '');?>" />
  <input type="hidden" name="dbName" value="<?php echo (isset($_POST['dbName']) ? $_POST['dbName'] : '');?>" />
  <input type="hidden" name="dbUser" value="<?php echo (isset($_POST['dbUser']) ? $_POST['dbUser'] : '');?>" />
  <input type="hidden" name="dbPassword" value="<?php echo (isset($_POST['dbPassword']) ? htmlspecialchars($_POST['dbPassword']) : '');?>" />
  <input type="hidden" name="dbPrefix" value="<?php echo (isset($_POST['dbPrefix']) ? $_POST['dbPrefix'] : '');?>" />
  <input type="hidden" name="checked" value="<?php echo (isset($_POST['checked']) ? $_POST['checked'] : '');?>" />
  <input type="hidden" name="domain" value="<?php echo (isset($_POST['domain']) ? $_POST['domain'] : '');?>" />
  <input type="hidden" name="disableRewrite" value="<?php echo (isset($_POST['disableRewrite']) ? $_POST['disableRewrite'] : '');?>" />
  <input type="hidden" name="rewriteMode" value="<?php echo (isset($_POST['rewriteMode']) ? $_POST['rewriteMode'] : '');?>" />
  <input type="hidden" name="type" value="<?php echo (isset($_POST['type']) ? $_POST['type'] : '');?>" />
  <div id="inner">
    <h2><span class="step"><?php echo _f('%1단계', $step);?></span> : <?php echo _t('관리자 정보 입력');?></h2>
    <div id="userinput">
      <table class="inputs">
        <tr>
          <th style="width:100px"><?php echo _t('이메일');?> : </th>
          <td>
            <input type="text" id="email" name="email" value="<?php echo (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '');?>" class="input_email"<?php echo ($check && (empty($_POST['email']) || ($error == 51)) ? ' style="border-color:red"' : '');?> />
          </td>
        </tr>
        <tr>
          <th><?php echo _t('비밀번호');?> : </th>
          <td>
            <input type="password" name="password" value="<?php echo (isset($_POST['password']) ? htmlspecialchars($_POST['password']) : '');?>" class="input_password"<?php echo ($check && empty($_POST['password']) ? ' style="border-color:red"' : '');?> /><br />
			<em class="password"><?php echo _t('비밀번호는 최소 6자 이상이어야 합니다.');?></em>
          </td>
        </tr>
        <tr>
          <th><?php echo _t('비밀번호 확인');?> : </th>
          <td>
            <input type="password" name="password2" value="<?php echo (isset($_POST['password2']) ? htmlspecialchars($_POST['password2']) : '');?>" class="input_password"<?php echo ($check && empty($_POST['password2']) ? ' style="border-color:red"' : '');?> />
          </td>
        </tr>
        <tr>
          <th><?php echo _t('블로그 식별자');?> : </th>
          <td>
            <input type="text" name="blog" value="<?php echo (isset($_POST['blog']) ? htmlspecialchars($_POST['blog']) : '');?>" class="input_password"<?php echo ($check && (empty($_POST['blog']) || ($error == 53)) ? ' style="border-color:red"' : '');?> />
          </td>
        </tr>
        <tr>
          <th><?php echo _t('필명');?> : </th>
          <td>
            <input type="text" name="name" value="<?php echo (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '');?>" class="input_password" />
          </td>
        </tr>
      </table>
      <div id="warning"><?php
        if ($error == 51)
           echo _t('이메일이 올바르지 않습니다.');
        else if ($error == 52)
           echo _t('비밀번호가 일치하지 않습니다.');
        else if ($error == 53)
           echo _t('블로그 식별자가 올바르지 않습니다.');
        else if ($error == 54)
           echo _t('비밀번호는 최소 6자 이상이어야 합니다.');
        else if ($check)
           echo _t('표시된 정보가 부족합니다.');
        else
           echo '&nbsp;';
?></div>
    </div>
  <div id="navigation">
    <a href="#" onclick="window.history.back()" title="<?php echo _t('이전');?>"><img src="./resources/style/setup/image/icon_prev.gif" width="74" height="24" alt="<?php echo _t('이전');?>" /></a>
    <a href="#" onclick="next(); return false;" title="<?php echo _t('다음');?>"><img src="./resources/style/setup/image/icon_next.gif" width="74" height="24" alt="<?php echo _t('다음');?>" /></a>
  </div>
  </div>
  <script type="text/javascript">
    //<![CDATA[
      document.getElementById('email').focus();
    //]]>
  </script>
<?php
    }
    else if ($step == 7) {
        if ($check)
            return true;

?>
  <input type="hidden" name="step" value="<?php echo $step;?>" />
  <input type="hidden" name="mode" value="<?php echo $_POST['mode'];?>" />
  <input type="hidden" name="dbms" value="<?php echo (isset($_POST['dbms']) ? $_POST['dbms'] : '');?>" />
  <input type="hidden" name="dbServer" value="<?php echo (isset($_POST['dbServer']) ? $_POST['dbServer'] : '');?>" />
  <input type="hidden" name="dbPort" value="<?php echo (isset($_POST['dbPort']) ? $_POST['dbPort'] : '');?>" />
  <input type="hidden" name="dbName" value="<?php echo (isset($_POST['dbName']) ? $_POST['dbName'] : '');?>" />
  <input type="hidden" name="dbUser" value="<?php echo (isset($_POST['dbUser']) ? $_POST['dbUser'] : '');?>" />
  <input type="hidden" name="dbPassword" value="<?php echo (isset($_POST['dbPassword']) ? htmlspecialchars($_POST['dbPassword']) : '');?>" />
  <input type="hidden" name="dbPrefix" value="<?php echo (isset($_POST['dbPrefix']) ? $_POST['dbPrefix'] : '');?>" />
  <input type="hidden" name="checked" value="<?php echo (isset($_POST['checked']) ? $_POST['checked'] : '');?>" />
  <input type="hidden" name="domain" value="<?php echo (isset($_POST['domain']) ? $_POST['domain'] : '');?>" />
  <input type="hidden" name="disableRewrite" value="<?php echo (isset($_POST['disableRewrite']) ? $_POST['disableRewrite'] : false);?>" />
  <input type="hidden" name="rewriteMode" value="<?php echo (isset($_POST['rewriteMode']) ? $_POST['rewriteMode'] : '');?>" />
  <input type="hidden" name="type" value="<?php echo (isset($_POST['type']) ? $_POST['type'] : '');?>" />
  <input type="hidden" name="blog" value="<?php echo (isset($_POST['blog']) ? $_POST['blog'] : '');?>" />
  <div id="inner">
    <h2><span class="step"><?php echo _f('%1단계', $step);?></span> : <?php echo _t('데이터베이스를 준비하고 있습니다. 잠시만 기다려 주십시오.');?></h2>
    <div id="content-box" style="text-align:center">
	<p></p>
    </div>
  </div><!-- inner -->
  </form>
</div><!-- container -->
<?php
		function fail($msg) {
			flush();
			if ($_POST['mode'] == 'install') {
			}
			exit;
		}

		$loginid = POD::escapeString($_POST['email']);
		$password = md5($_POST['password']);
		$name = POD::escapeString($_POST['name']);
		$blog = POD::escapeString($_POST['blog']);
		$baseLanguage = POD::escapeString( $_POST['Lang']);
		$baseTimezone = POD::escapeString( substr(_t('default:Asia/Seoul'),8));

		if(POD::dbms() == 'MySQLi') {
	        $charset = 'DEFAULT CHARSET=utf8';
//    	    if (!@POD::query('SET CHARACTER SET utf8'))
  //      	    $charset = 'TYPE=MyISAM';
	    //    @POD::query('SET SESSION collation_connection = \'utf8_general_ci\'');
		} else {
			$charset = '';
		}

        if ($_POST['mode'] == 'install') {
			$schema = '';
			// Compatibility layer load
			if(file_exists(ROOT.'/resources/setup/compatibility.'.POD::dbms().'.sql')) {
				$schema = file_get_contents(ROOT.'/resources/setup/compatibility.'.POD::dbms().'.sql');
            	$query = explode(';', trim($schema));
            	foreach ($query as $sub) {
					@POD::query($sub);
				}
				$schema = '';
				$query = array();
			}
            // Loading create schema from sql file. (DBMS specific)
			if(POD::dbms() == 'MySQLi') $dbSelector = 'MySQL';
			else $dbSelector = POD::dbms();
			$schema .= file_get_contents(ROOT.'/resources/setup/initialize.'.$dbSelector.'.sql');
			$schema = str_replace('[##_dbPrefix_##]',$_POST['dbPrefix'],$schema);
			$schema = str_replace('[##_charset_##]',$charset,$schema);

            $schema .= "
INSERT INTO {$_POST['dbPrefix']}Users VALUES (1, '$loginid', '$password', '$name', ".Timestamp::getUNIXtime().", 0, 0);
INSERT INTO {$_POST['dbPrefix']}Privileges VALUES (1, 1, 16, ".Timestamp::getUNIXtime().", 0);
INSERT INTO {$_POST['dbPrefix']}ServiceSettings VALUES ('newlineStyle', '1.1');
INSERT INTO {$_POST['dbPrefix']}ServiceSettings VALUES ('useNewPluginSetting', 1);
INSERT INTO {$_POST['dbPrefix']}BlogSettings VALUES (1, 'name', '$blog');
INSERT INTO {$_POST['dbPrefix']}BlogSettings VALUES (1, 'language', '$baseLanguage');
INSERT INTO {$_POST['dbPrefix']}BlogSettings VALUES (1, 'blogLanguage', '$baseLanguage');
INSERT INTO {$_POST['dbPrefix']}BlogSettings VALUES (1, 'timezone', '$baseTimezone');
INSERT INTO {$_POST['dbPrefix']}BlogSettings VALUES (1, 'defaultEditor', 'tinyMCE');
INSERT INTO {$_POST['dbPrefix']}BlogSettings VALUES (1, 'defaultFormatter', 'ttml');
INSERT INTO {$_POST['dbPrefix']}BlogSettings VALUES (1, 'acceptTrackbacks', 1);
INSERT INTO {$_POST['dbPrefix']}BlogSettings VALUES (1, 'acceptComments', 1);
INSERT INTO {$_POST['dbPrefix']}Plugins VALUES (1, 'CL_OpenID', null);
INSERT INTO {$_POST['dbPrefix']}SkinSettings VALUES (1,'skin','periwinkle');
INSERT INTO {$_POST['dbPrefix']}FeedSettings (blogid) values(1);
INSERT INTO {$_POST['dbPrefix']}FeedGroups (blogid) values(1);
INSERT INTO {$_POST['dbPrefix']}Entries (blogid, userid, id, category, visibility, location, title, slogan, contentformatter, contenteditor, starred, acceptcomment, accepttrackback, created, published, modified, content) VALUES (1, 1, 1, 0, 2, '/', '".POD::escapeString(_t('환영합니다'))."', 'welcome', 'ttml', 'tinyMCE', 0, 1, 1, ".Timestamp::getUNIXtime().", ".Timestamp::getUNIXtime().",".Timestamp::getUNIXtime().",'".POD::escapeString(getDefaultPostContent())."')";
            $query = explode(';', trim($schema));
            foreach ($query as $sub) {
				if (!empty($sub) && !POD::query($sub, false)) {
					$tables = getTables('2.0',$_POST['dbPrefix']);
					foreach ($tables as $table) {
						if (POD::dbms()=='Cubrid') {
							@POD::query("DROP ".$table);
						} else {
							@POD::query("DROP TABLE ".$table);
						}
					}
			/*		@POD::query(
						"DROP TABLE
							{$_POST['dbPrefix']}Attachments,
							{$_POST['dbPrefix']}BlogSettings,
							{$_POST['dbPrefix']}BlogStatistics,
							{$_POST['dbPrefix']}Categories,
							{$_POST['dbPrefix']}Comments,
							{$_POST['dbPrefix']}CommentsNotified,
							{$_POST['dbPrefix']}CommentsNotifiedQueue,
							{$_POST['dbPrefix']}CommentsNotifiedSiteInfo,
							{$_POST['dbPrefix']}ContentFilters,
							{$_POST['dbPrefix']}DailyStatistics,
							{$_POST['dbPrefix']}Entries,
							{$_POST['dbPrefix']}EntriesArchive,
							{$_POST['dbPrefix']}FeedGroupRelations,
							{$_POST['dbPrefix']}FeedGroups,
							{$_POST['dbPrefix']}FeedItems,
							{$_POST['dbPrefix']}FeedReads,
							{$_POST['dbPrefix']}FeedSettings,
							{$_POST['dbPrefix']}FeedStarred,
							{$_POST['dbPrefix']}Feeds,
							{$_POST['dbPrefix']}Filters,
							{$_POST['dbPrefix']}Links,
							{$_POST['dbPrefix']}LinkCategories,
							{$_POST['dbPrefix']}OpenIDUsers,
							{$_POST['dbPrefix']}PageCacheLog,
							{$_POST['dbPrefix']}Plugins,
							{$_POST['dbPrefix']}Privileges,
							{$_POST['dbPrefix']}RefererLogs,
							{$_POST['dbPrefix']}RefererStatistics,
							{$_POST['dbPrefix']}RemoteResponseLogs,
							{$_POST['dbPrefix']}RemoteResponses,
							{$_POST['dbPrefix']}ReservedWords,
							{$_POST['dbPrefix']}ServiceSettings,
							{$_POST['dbPrefix']}SessionVisits,
							{$_POST['dbPrefix']}Sessions,
							{$_POST['dbPrefix']}SkinSettings,
							{$_POST['dbPrefix']}TagRelations,
							{$_POST['dbPrefix']}Tags,
							{$_POST['dbPrefix']}UserSettings,
							{$_POST['dbPrefix']}Users,
							{$_POST['dbPrefix']}XMLRPCPingSettings"
					);*/
					echo '<script type="text/javascript">//<![CDATA['.CRLF.'alert("', _t('테이블을 생성하지 못했습니다.'), '")//]]></script>';
					$error = 1;
					break;
				}
			}
        }
		else {
			$ctx = Model_Context::getInstance();
			$ctx->setProperty('database.prefix',$_POST['dbPrefix']);
			$pool = DBModel::getInstance();
			$pool->reset('Users');
			$pool->setAttribute('loginid',$loginid,true);
			$pool->setAttribute('name',$name,true);
			$pool->setQualifier('userid','equals',1);
			$pool->update();

			$pool->reset('Users');
			$pool->setAttribute('password',$password,true);
			$pool->setQualifier('userid','equals',1);
			$pool->setQualifier('password','not',$password2,true);
			$pool->update();

			$pool->reset('BlogSettings');
			$pool->setAttribute('value',$_POST['blog'],true);
			$pool->setQualifier('blogid','equals',1);
			$pool->setQualifier('name','equals','name',true);
			$pool->update();

			$pool->reset('BlogSettings');
			$pool->setAttribute('value',$baseLanguage,true);
			$pool->setQualifier('blogid','equals',1);
			$pool->setQualifier('name','equals','language',true);
			$pool->update();

			$pool->reset('BlogSettings');
			$pool->setAttribute('value',$baseTimezone,true);
			$pool->setQualifier('blogid','equals',1);
			$pool->setQualifier('name','equals','timezone',true);
			$pool->update();

			$pool->reset('BlogSettings');
			$pool->setAttribute('value',Timestamp::getUNIXtime());
			$pool->setQualifier('blogid','equals',1);
			$pool->setQualifier('name','equals','created',true);
			$pool->update();
		}
		if (!$error) {
			POD::unbind();
			echo '<script type="text/javascript">//<![CDATA['.CRLF.'next() //]]></script>';
		}
?>
</body>
</html>
<?php
    }
    else if ($step == 8) {
        if ($check)
            return true;
        $useSSL = Utils_Misc::isSecureProtocol() ? 'true' : 'false';
        $filename = $root . '/config.php';
        $fp = fopen($filename, 'w+');
		// For first entry addition
		$database = array('server' => $_POST['dbServer'],
				'database' => $_POST['dbName'],
				'username' => $_POST['dbUser'],
				'port' => $_POST['dbPort'],
				'password' => $_POST['dbPassword'],
				'prefix'   => $_POST['dbPrefix']);
        if ($fp) {
            fwrite($fp,
"<?php
ini_set('display_errors', 'off');
\$database['server'] = '{$_POST['dbServer']}';
\$database['dbms'] = '{$_POST['dbms']}';
\$database['database'] = '{$_POST['dbName']}';
\$database['port'] = '{$_POST['dbPort']}';
\$database['username'] = '{$_POST['dbUser']}';
\$database['password'] = '{$_POST['dbPassword']}';
\$database['prefix'] = '{$_POST['dbPrefix']}';
\$service['type'] = '{$_POST['type']}';
\$service['domain'] = '{$_POST['domain']}';
\$service['path'] = '$path';
\$service['skin'] = 'periwinkle';
\$service['favicon_daily_traffic'] = 10; // 10MB
\$service['useSSL'] = {$useSSL};  // Force SSL protocol (via https)
//\$serviceURL = 'http://{$_POST['domain']}{$path}' ; // for path of Skin, plugin and etc.
//\$service['reader'] = true; // Use Textcube reader. You can set it to false if you do not use Textcube reader, and want to decrease DB load.
//\$service['debugmode'] = true; // uncomment for debugging, e.g. displaying DB Query or Session info
//\$service['pagecache'] = false; // uncomment if you want to disable page cache feature.
//\$service['codecache'] = true; // uncomment if you want to enable code cache feature.
//\$service['debug_session_dump'] = true; // session info debuging.
//\$service['debug_rewrite_module'] = true; // rewrite handling module debuging.
//\$service['session_cookie_path'] = \$service['path']; // for avoiding spoiling other textcube's session id sharing root.
//\$service['allowBlogVisibilitySetting'] = true; // Allow service users to change blog visibility.
//\$service['externalresources'] = false;  // Loads resources from external storage.
//\$service['resourcepath'] = 'http://example.com/resource';	// Specify the full URI of external resource.
//\$service['autologinTimeout'] = 1209600;	// Automatic login timeout (sec.)
//\$service['favicon_daily_traffic'] = 10; // Set favicon traffic limitation. default is 10MB.
//\$service['skincache'] = true;        // Use skin pre-fetching. Textcube will parse static elements (blog name, title…) only when you change skin. Reduces CPU loads.
//\$service['cookie_prefix'] = '';        // Service cookie prefix. Default cookie prefix is Textcube_[VERSION_NUMBER].
//\$database['port'] = 3639;            // Database port number
//\$database['dbms'] = 'MySQLi';         // DBMS. (MySQL, MySQLi, PostgreSQL, Cubrid.)
//\$service['memcached'] = true;       // Using memcache to handle session and cache
//\$memcached['server'] = 'localhost';  // Where memcache server is.
//\$service['requirelogin'] = false;    // Force log-in process to every blogs. (for private blog service)
//\$service['jqueryURL'] = '';		// Add URL if you want to use external jquery via CDN. e.g.) Microsoft's CDN: http://ajax.aspnetcdn.com/ajax/jQuery/
//\$service['lodashURL'] = '';		// Add URL if you want to use external lo-dash via CDN. e.g.) CDNJS' CDN: https://cdnjs.cloudflare.com/ajax/libs/lodash.js/2.4.1/
?>"
            );
            fclose($fp);
            @chmod($filename, 0666);
        }
      	if(!isset($_POST['disableRewrite']) || !$_POST['disableRewrite']) {
	        $filename = $root . '/.htaccess';
    	    $fp = fopen($filename, 'w+');

			switch ($_POST['rewriteMode']) {
			case 'ISAPI':
				// Users must copy these rules to IsapiRewrite4.ini
				$htaccessContent = <<<EOF
RewriteRule ^{$path}/(thumbnail)/([0-9]+/.+)\$ {$path}/cache/\$1/\$2 [L,U]
RewriteRule ^{$path}/attach/([0-9]+/.+)\$ {$path}/user/attach/\$1 [L,U]
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^{$path}/user+/+(cache)+/+(.+[^/]).(cache|xml|txt|log)\$ - [NC,F,L,U]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^{$path}/([^?]+[^/])\$ {$path}/\$1/ [L,U]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{QUERY_STRING} ^\$
RewriteRule ^{$path}/(.*)\$ {$path}/rewrite.php [L,U]
RewriteRule ^{$path}/(.*)\$ {$path}/rewrite.php?%{QUERY_STRING} [L,U]
EOF;
				break;
			case 'IISRewrite':
				// Users must import these rules into URL Rewrite module.
				$htaccessContent = <<<EOF
RewriteRule ^{$path}/(thumbnail)/([0-9]+/.+)\$ {$path}/cache/\$1/\$2 [L]
RewriteRule ^{$path}/attach/([0-9]+/.+)\$ {$path}/user/attach/\$1 [L]
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^{$path}/user+/+(cache)+/+(.+[^/]).(cache|xml|txt|log)\$ - [NC,F,L]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^{$path}/([^?]+[^/])\$ {$path}/\$1/ [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^{$path}/(.*)\$ {$path}/rewrite.php [L,QSA]
EOF;
				break;
			case 'mod_rewrite':
			default:
				$htaccessContent = <<<EOF
#<IfModule mod_url.c>
#CheckURL Off
#</IfModule>
#SetEnv PRELOAD_CONFIG 1
RewriteEngine On
RewriteBase {$path}/
RewriteRule ^(thumbnail)/([0-9]+/.+)\$ cache/\$1/\$2 [L]
RewriteRule ^attach/([0-9]+/.+)\$ user/attach/\$1 [L]
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^user+/+(cache)+/+(.+[^/]).(cache|xml|txt|log)\$ - [NC,F,L]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^(.+[^/])\$ \$1/ [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)\$ rewrite.php [L,QSA]
EOF;
			}

    	    if ($fp) {
        	    fwrite($fp, $htaccessContent);
            	fclose($fp);
	            @chmod($filename, 0666);
    	    }
		}

        $blogProtocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
        $blogDefaultPort = isset($_SERVER['HTTPS']) ? 443 : 80;

        switch ($_POST['type']) {
            case 'domain':
                $blogURL = "$blogProtocol://{$_POST['blog']}.{$_POST['domain']}" . ($_SERVER['SERVER_PORT'] != $blogDefaultPort ? ":{$_SERVER['SERVER_PORT']}" : '') . "$path".(empty($_POST['disableRewrite']) ? '' : '/index.php?');
                break;
            case 'path':
                $blogURL = "$blogProtocol://{$_POST['domain']}" . ($_SERVER['SERVER_PORT'] != $blogDefaultPort ? ":{$_SERVER['SERVER_PORT']}" : '') . "$path".(empty($_POST['disableRewrite']) ? '' : '/index.php?')."/{$_POST['blog']}";
                break;
            case 'single':
                $blogURL = "$blogProtocol://{$_POST['domain']}" . ($_SERVER['SERVER_PORT'] != $blogDefaultPort ? ":{$_SERVER['SERVER_PORT']}" : '') . "$path".(empty($_POST['disableRewrite']) ? '' : '/index.php?');
                break;
        }
?>
  <div id="inner">
    <h2><span class="step"><?php echo _t('설치완료');?></span> : <?php echo _t('텍스트큐브가 성공적으로 설치되었습니다.');?></h2>
    <div id="content-box">
      <p>
      </p>
      <ul>
        <li><?php echo _t('텍스트큐브 주소');?><br />
          <a href="<?php echo $blogURL.'/';?>"><?php echo $blogURL.'/';?></a><br />
          <br />
        </li>
        <li><?php echo _t('텍스트큐브 관리 툴 주소');?><br />
          <a href="<?php echo $blogURL.'/';?>owner"><?php echo $blogURL.'/';?>owner</a></li>
      </ul>
      <p>
		<?php if (checkIIS()) echo _t('새로 IIS용 Rewrite 모듈을 설치하셨다면 <b>.htaccess 내용을 모듈 설정에 적용</b>해주십시오.<br />'); ?>
		<?php echo '<li style="color:red">', _t('보안 관련 안내'), ': ', '<br /><span class="instruction">', _t('보안을 위하여 설치때 필요했던 권한 중 일부를 제거해주세요. FTP 프로그램으로 권한을 수정하시거나 다음의 명령을 터미널에 붙여 넣으시면 됩니다'), '<br />', 'chmod 0755 '.$root, '</span></li>';?>
        <?php echo _t('텍스트큐브 관리 툴로 로그인 하신 후 필요사항을 수정해 주십시오.');?><br />
        <?php echo _t('텍스트큐브를 이용해 주셔서 감사합니다.');?>
      </p>
    </div>
  </div>
<?php
    }
	else if ($step == 204) {
?>
  <input type="hidden" name="step" value="4" />
  <input type="hidden" name="mode" value="<?php echo $_POST['mode'];?>" />
  <input type="hidden" name="dbms" value="<?php echo (isset($_POST['dbms']) ? $_POST['dbms'] : '');?>" />
  <input type="hidden" name="dbServer" value="<?php echo (isset($_POST['dbServer']) ? $_POST['dbServer'] : '');?>" />
  <input type="hidden" name="dbPort" value="<?php echo (isset($_POST['dbPort']) ? $_POST['dbPort'] : '');?>" />
  <input type="hidden" name="dbName" value="<?php echo (isset($_POST['dbName']) ? $_POST['dbName'] : '');?>" />
  <input type="hidden" name="dbUser" value="<?php echo (isset($_POST['dbUser']) ? $_POST['dbUser'] : '');?>" />
  <input type="hidden" name="dbPassword" value="<?php echo (isset($_POST['dbPassword']) ? htmlspecialchars($_POST['dbPassword']) : '');?>" />
  <div id="inner">
    <h2><span class="step"><?php echo _f('%1단계', 4);?></span> : <?php echo _t('삭제하고자 하는 테이블을 선택하여 주십시오.');?></h2>
    <div id="userinput">
    <table id="info">
      <tr>
        <th><?php echo _t('식별자');?></th>
        <th><?php echo _t('버전');?></th>
        <th><?php echo _t('테이블');?></th>
 	    <th></th>
     </tr>
<?php
        $tables = array();
		$ckeckedString = 'checked ';
        if ($result = POD::tableList()) {
            foreach($result as $table) {
				//$table = $table[0];
				$entriesMatched = preg_match('/Entries$/', $table);


				if ($entriesMatched && checkTables('2.0', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>2.0</th>
        <td><?php echo implode(', ', getTables('2.0', $prefix));?></td>
	    <th><input type="radio" name="target" value="2.0_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';

				} else if ($entriesMatched && checkTables('1.9', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>1.9</th>
        <td><?php echo implode(', ', getTables('1.9', $prefix));?></td>
	    <th><input type="radio" name="target" value="1.9_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if ($entriesMatched && checkTables('1.8', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>1.8</th>
        <td><?php echo implode(', ', getTables('1.8', $prefix));?></td>
	    <th><input type="radio" name="target" value="1.8_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if ($entriesMatched && checkTables('1.7', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>1.7</th>
        <td><?php echo implode(', ', getTables('1.7', $prefix));?></td>
	    <th><input type="radio" name="target" value="1.7_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if ($entriesMatched && checkTables('1.6', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>1.6</th>
        <td><?php echo implode(', ', getTables('1.6', $prefix));?></td>
	    <th><input type="radio" name="target" value="1.6_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if ($entriesMatched && checkTables('1.5', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>1.5</th>
        <td><?php echo implode(', ', getTables('1.5', $prefix));?></td>
	    <th><input type="radio" name="target" value="1.5_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if ($entriesMatched && checkTables('1.1', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>1.1</th>
        <td><?php echo implode(', ', getTables('1.1', $prefix));?></td>
	    <th><input type="radio" name="target" value="1.1_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if ($entriesMatched && checkTables('1.0.2', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>1.0.2</th>
        <td><?php echo implode(', ', getTables('1.0.2', $prefix));?></td>
	    <th><input type="radio" name="target" value="1.0.2_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if ($entriesMatched && checkTables('1.0.0', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>1.0.0</th>
        <td><?php echo implode(', ', getTables('1.0.0', $prefix));?></td>
	    <th><input type="radio" name="target" value="1.0.0_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if ($entriesMatched && checkTables('1.0.b2', $prefix = substr($table, 0, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>1.0 Beta 2</th>
        <td><?php echo implode(', ', getTables('1.0.b2', $prefix));?></td>
	    <th><input type="radio" name="target" value="1.0.b2_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if (preg_match('/^t3_(.*)_10ofmg$/', $table) && checkTables('0.97', $prefix = substr($table, 3, strlen($table) - 10))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>0.97 (Classic)</th>
        <td><?php echo implode(', ', getTables('0.97', $prefix));?></td>
	    <th><input type="radio" name="target" value="0.97_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				} else if (preg_match('/^t3_(.*)_ct1$/', $table) && checkTables('0.96', $prefix = substr($table, 3, strlen($table) - 7))) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th>0.96x</th>
        <td><?php echo implode(', ', getTables('0.96', $prefix));?></td>
	    <th><input type="radio" name="target" value="0.96_<?php echo $prefix;?>" <?php echo $ckeckedString;?>/></th>
      </tr>
<?php
					$ckeckedString = '';
				}
			}
		}
?>
    </table>
    </div>
  <div id="navigation">
    <a href="#" onclick="window.history.back()" title="<?php echo _t('이전');?>"><img src="./resources/style/setup/image/icon_prev.gif" width="74" height="24" alt="<?php echo _t('이전');?>" /></a>
    <a href="#" onclick="if (confirm('<?php echo _t('삭제하시겠습니까?');?>') && confirm('<?php echo _t('정말 삭제하시겠습니까?');?>')) next(); return false;" title="<?php echo _t('다음');?>"><img src="./resources/style/setup/image/icon_next.gif" width="74" height="24" alt="<?php echo _t('다음');?>" /></a>
  </div>
  </div>
<?php
	}
	else if ($step == 205) {
?>
  <input type="hidden" name="step" value="1" />
  <div id="inner">
    <h2><span class="step"><?php echo _f('%1단계', 5);?></span> : <?php echo _t('선택된 테이블을 삭제하고 있습니다.');?></h2>
    <div id="userinput">
    <table id="info">
      <tr>
        <th><?php echo _t('식별자');?></th>
        <th><?php echo _t('버전');?></th>
        <th><?php echo _t('테이블');?></th>
     </tr>
<?php
		list($version, $prefix) = explode('_', $_POST['target'], 2);
		$result = false;
		if (checkTables($version, $prefix)) {
?>
      <tr>
        <th><?php echo $prefix;?></th>
        <th><?php echo $version;?></th>
        <td><?php echo implode(', ', getTables($version, $prefix));?></td>
      </tr>
<?php
			$result = @POD::query('DROP TABLE ' . implode(', ', getTables($version, $prefix)));
		}
?>
    </table>
	<p><?php echo ($result ? _t('삭제하였습니다.') : '<span style="color:red">' . _t('삭제하지 못했습니다.') . '</span>');?></p>
    </div>
  <div id="navigation">
    <a href="#" onclick="window.history.back()" title="<?php echo _t('이전');?>"><img src="./resources/style/setup/image/icon_prev.gif" width="74" height="24" alt="<?php echo _t('이전');?>" /></a>
    <a href="#" onclick="next(); return false;" title="<?php echo _t('다음');?>"><img src="./resources/style/setup/image/icon_next.gif" width="74" height="24" alt="<?php echo _t('다음');?>" /></a>
  </div>
  </div>
<?php
	}
}

function drawSetLang( $currentLang = "ko"  ,$curPosition = 'Norm' /*or 'Err'*/ ){
	$locale = Locales::getInstance();
	if( $locale->setDirectory(ROOT.'/resources/locale/setup'))   $availableLanguages =   $locale->getSupportedLocales();
	else return false;
?>
		Select Default Language :
		<select name="Lang" id = "Lang" onchange= "current();" >
<?php      foreach( $availableLanguages as $key => $value)
			print('			<option value="'.$key.'" '.( $key == $currentLang ? ' selected="selected" ' : '').'>'.$value.'</option>'.CRLF);
?>
		</select>
<?php
	return true;
}

function stripPath($path) {
	$path = rtrim($path, '/');
	while (strpos($path, '//') !== false)
		$path = str_replace('//', '/', $path);
	return $path;
}

function testMyself_fsocket($host, $path, $port) {
    $socket = @fsockopen($host, $port, $errno, $errstr, 10);
    if ($socket === false)
        return false;
    fputs($socket, "GET $path HTTP/1.1\r\n");
    fputs($socket, "Host: $host\r\n");
    fputs($socket, "User-Agent: Mozilla/4.0 (compatible; Textcube Setup)\r\n");
    fputs($socket, "Accept-Encoding: identity\r\n");
    fputs($socket, "Connection: close\r\n");
    fputs($socket, "\r\n");
    $response = '';
    while (!feof($socket))
        $response .= fgets($socket, 128);
    fclose($socket);
    return strstr($response, getFingerPrint()) ? true : false;
}

function testMyself_socket($host, $path, $port)
{
	$ip = gethostbyname($host);
	$s = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	if( !socket_connect( $s, $ip, $port ) ) {
		return false;
	}

	socket_write( $s, "GET $path HTTP/1.1\r\n".
		"Host: $host\r\n".
		"User-Agent: Mozilla/4.0 (compatible; Textcube Setup)\r\n".
		"Accept-Encoding: identity\r\n".
		"Connection: close\r\n".
		"\r\n" );

	$response = socket_read( $s, 8096 );
	socket_close($s);
    return strstr($response, getFingerPrint()) ? true : false;
}

function testMyself($host, $path, $port)
{
	if( function_exists('fsockopen') ) {
		return testMyself_fsocket($host,$path,$port);
	}
	if( function_exists('socket_create') ) {
		return testMyself_socket($host,$path,$port);
	}
	return false;
}

function checkIIS() {
	if (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== FALSE)
		return true;
	return false;
}

function getFingerPrint() {
    return md5($_SERVER['SERVER_SOFTWARE'] . $_SERVER['SCRIPT_FILENAME'] . phpversion());
}

function checkTables($version, $prefix) {
	if (!$tables = getTables($version, $prefix))
		return false;
	foreach ($tables as $table) {
		if ($result = POD::query("DESCRIBE $table"))
			POD::free($result);
		else
			return false;
	}
	return true;
}

function getTables($version, $prefix) {
	switch ($version) {
		case '2.0':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}TrashComments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}EntriesArchive", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Lines", "{$prefix}Links", "{$prefix}LinkCategories", "{$prefix}OpenIDUsers", "{$prefix}Plugins", "{$prefix}Properties", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}RemoteResponseLogs", "{$prefix}RemoteResponses", "{$prefix}TrashRemoteResponses","{$prefix}Users", "{$prefix}UserSettings", "{$prefix}Widgets", "{$prefix}XMLRPCPingSettings", "{$prefix}Privileges", "{$prefix}PageCacheLog");
        case '1.10':
            return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}TrashComments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}EntriesArchive", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Lines", "{$prefix}Links", "{$prefix}LinkCategories", "{$prefix}OpenIDUsers", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}RemoteResponseLogs", "{$prefix}RemoteResponses", "{$prefix}TrashRemoteResponses","{$prefix}Users", "{$prefix}UserSettings", "{$prefix}Widgets", "{$prefix}XMLRPCPingSettings", "{$prefix}Privileges", "{$prefix}PageCacheLog");
		case '1.9':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}TrashComments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}EntriesArchive", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Lines", "{$prefix}Links", "{$prefix}LinkCategories", "{$prefix}OpenIDUsers", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}RemoteResponseLogs", "{$prefix}RemoteResponses", "{$prefix}TrashRemoteResponses","{$prefix}Users", "{$prefix}UserSettings", "{$prefix}Widgets", "{$prefix}XMLRPCPingSettings", "{$prefix}Privileges", "{$prefix}PageCacheLog");
		case '1.8':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}EntriesArchive", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Lines", "{$prefix}Links", "{$prefix}LinkCategories", "{$prefix}OpenIDUsers", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}RemoteResponseLogs", "{$prefix}RemoteResponses", "{$prefix}Users", "{$prefix}UserSettings", "{$prefix}Widgets", "{$prefix}XMLRPCPingSettings", "{$prefix}Privileges", "{$prefix}PageCacheLog");
		case '1.7':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}EntriesArchive", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Links", "{$prefix}LinkCategories", "{$prefix}OpenIDUsers", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}Users", "{$prefix}UserSettings", "{$prefix}XMLRPCPingSettings", "{$prefix}Teamblog", "{$prefix}PageCacheLog");
		case '1.6':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}EntriesArchive", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Links", "{$prefix}OpenIDUsers", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}Users", "{$prefix}UserSettings", "{$prefix}XMLRPCPingSettings", "{$prefix}Teamblog", "{$prefix}PageCacheLog");
		case '1.5':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Links", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}Users", "{$prefix}UserSettings", "{$prefix}XMLRPCPingSettings", "{$prefix}Teamblog", "{$prefix}PageCacheLog");
		case '1.1':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Links", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}ServiceSettings", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}Users", "{$prefix}UserSettings");
		case '1.0.2':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}Filters", "{$prefix}Links", "{$prefix}Personalization", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}Users");
		case '1.0.0':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}Comments", "{$prefix}CommentsNotified", "{$prefix}CommentsNotifiedQueue", "{$prefix}CommentsNotifiedSiteInfo", "{$prefix}ContentFilters", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}FeedGroupRelations", "{$prefix}FeedGroups", "{$prefix}FeedItems", "{$prefix}FeedOwners", "{$prefix}FeedReads", "{$prefix}Feeds", "{$prefix}FeedSettings", "{$prefix}FeedStarred", "{$prefix}GuestFilters", "{$prefix}HostFilters", "{$prefix}Links", "{$prefix}MonthlyStatistics", "{$prefix}Personalization", "{$prefix}Plugins", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}ReservedWords", "{$prefix}Sessions", "{$prefix}SessionVisits", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}URLFilters", "{$prefix}Users");
		case '1.0.b2':
			return array("{$prefix}Attachments", "{$prefix}BlogSettings", "{$prefix}BlogStatistics", "{$prefix}Categories", "{$prefix}ContentFilters", "{$prefix}DailyStatistics", "{$prefix}Entries", "{$prefix}GuestFilters", "{$prefix}HostFilters", "{$prefix}Links", "{$prefix}MonthlyStatistics", "{$prefix}RefererLogs", "{$prefix}RefererStatistics", "{$prefix}Replies", "{$prefix}ReservedWords", "{$prefix}ServiceSetting", "{$prefix}SessionVisits", "{$prefix}Sessions", "{$prefix}SkinSettings", "{$prefix}TagRelations", "{$prefix}Tags", "{$prefix}TrackbackLogs", "{$prefix}Trackbacks", "{$prefix}URLFilters", "{$prefix}Users");
		case '0.97':
			return array("t3_{$prefix}_10ofmg", "t3_{$prefix}_10ofmg_cnt_log", "t3_{$prefix}_10ofmg_count", "t3_{$prefix}_10ofmg_ct1", "t3_{$prefix}_10ofmg_ct2", "t3_{$prefix}_10ofmg_files", "t3_{$prefix}_10ofmg_guest", "t3_{$prefix}_10ofmg_guest_icon", "t3_{$prefix}_10ofmg_guest_reply", "t3_{$prefix}_10ofmg_keyword", "t3_{$prefix}_10ofmg_keyword_files", "t3_{$prefix}_10ofmg_link", "t3_{$prefix}_10ofmg_notice_log", "t3_{$prefix}_10ofmg_notice_queue", "t3_{$prefix}_10ofmg_referlog", "t3_{$prefix}_10ofmg_referstat", "t3_{$prefix}_10ofmg_reply", "t3_{$prefix}_10ofmg_rss", "t3_{$prefix}_10ofmg_rss_group", "t3_{$prefix}_10ofmg_rss_item", "t3_{$prefix}_10ofmg_setting", "t3_{$prefix}_10ofmg_spam_filter", "t3_{$prefix}_10ofmg_tag", "t3_{$prefix}_10ofmg_tblog", "t3_{$prefix}_10ofmg_trackback");
		case '0.96':
			return array("t3_{$prefix}", "t3_{$prefix}_cnt_log", "t3_{$prefix}_count", "t3_{$prefix}_ct1", "t3_{$prefix}_ct2", "t3_{$prefix}_files", "t3_{$prefix}_guest", "t3_{$prefix}_guest_icon", "t3_{$prefix}_guest_reply", "t3_{$prefix}_keyword", "t3_{$prefix}_keyword_files", "t3_{$prefix}_link", "t3_{$prefix}_referlog", "t3_{$prefix}_referstat", "t3_{$prefix}_reply", "t3_{$prefix}_rss", "t3_{$prefix}_rss_group", "t3_{$prefix}_rss_item", "t3_{$prefix}_setting", "t3_{$prefix}_tblog", "t3_{$prefix}_trackback");
	}
	return null;
}
?>
  </form>
</div><!-- container -->
</body>
</html>
