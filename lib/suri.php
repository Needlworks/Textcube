<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

if(isset($accessInfo)) {
	$url = str_replace('index.php?pl=','',$accessInfo['fullpath']);
} else if(isset($service['useFastCGI']) && $service['useFastCGI'] == true) {
	$url = str_replace('index.php?pl=','',$_SERVER['REQUEST_URI']);
	if (($url_fix_pos=strpos($url, '?', 1))!==false) $url = substr($url, 0, $url_fix_pos);
} else {
	$url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['SCRIPT_NAME'];
}

$defaultblogid = getServiceSetting("defaultBlogId",1);

$suri            = array('url' => $url, 'value' => '');
$blogid          = null;
$isStrictBlogURL = true;
$depth           = substr_count($service['path'], '/');

if ($depth > 0) {
	if (preg_match('@^((/+[^/]+){' . $depth . '})(.*)$@', $url, $matches))
		$url = $matches[3];
	else
		respondNotFoundPage();
}

if ($service['type'] == 'single') {
	$blogid = $defaultblogid;
} else {
	if ($service['type'] == 'domain') {
		if ($_SERVER['HTTP_HOST'] == $service['domain']) {
			$blogid = $defaultblogid;
		} else {
			$domain = explode('.', $_SERVER['HTTP_HOST'], 2);
			if ($domain[1] == $service['domain']) {
				$blogid = getBlogidByName($domain[0]);
				if ($blogid === null) 
					$blogid = getBlogidBySecondaryDomain($_SERVER['HTTP_HOST']);
			} else {
				$blogid = getBlogidBySecondaryDomain($_SERVER['HTTP_HOST']);
			}
		}
	} else {
		if ($url == '/') {
			$blogid = $defaultblogid;
		} else if (preg_match('@^/+([^/]+)(.*)$@', $url, $matches)) {
			$blogid = getBlogidByName($matches[1]);
			if ($blogid === null) {
				$blogid = $defaultblogid;
				$isStrictBlogURL = false;
			}
			$url = $matches[2];
		} else {
			respondNotFoundPage();
		}
	}
	if ($blogid === null)
		respondNotFoundPage();
}

$owner = $blogid; // For legacy.(<1.5)
$blog = getBlogSettings($blogid);
$skinSetting = getSkinSetting($blogid);

if(isset($accessInfo)) {
	$depth = substr_count($accessInfo['input'],'/');
	switch($service['type']) {
		case 'path' : break;
		case 'domain' :
		default :
		if($accessInfo['URLfragment'][0] == 'owner') $depth++;
	}
} else {
	$depth = substr_count(ROOT, '/');
}
if ($depth > 0) {
	if($service['useRewriteEngine'] === false) $url = substr($url, 5); // Exclude /blog path.
	if (preg_match('@^((/+[^/]+){' . $depth . '})/*(.*)$@', $url, $matches)) {
		$suri['directive'] = $matches[1];
		if ($matches[3] !== false) {
			if($service['useRewriteEngine'] === false) {
				$suri['value'] = str_replace('index.php?','',$matches[3]);
			} else {
				$suri['value'] = $matches[3];
			}
		}
	} else
		respondNotFoundPage();
} else {
	$suri['directive'] = '/';
	$suri['value'] = ltrim($url, '/');
}
if (is_numeric($suri['value'])) {
	$suri['id'] = $suri['value'];
} else {
	$suri['value'] = decodeURL(str_replace('index.php','',$suri['value']));
}

// Workaround for the environments redirect engine disabled.
if($service['useRewriteEngine'] == false) {
	if(isset($_POST['id'])) $suri['id'] = $_POST['id'];
	else if(isset($_GET['id'])) $suri['id'] = $_GET['id'];
}
// Parse page.
$suri['page'] = empty($_POST['page']) ? (empty($_GET['page']) ? true : $_GET['page']) : $_POST['page'];

if (!isset($serviceURL))
	$serviceURL = 'http://' . $service['domain'] . (isset($service['port']) ? ':' . $service['port'] : '') . $service['path'];

switch ($service['type']) {
	case 'domain':
		$pathURL = $service['path'];
		$blog['primaryBlogURL'] = 'http://' . $blog['name'] . '.' . $service['domain'] . (isset($service['port']) ? ':' . $service['port'] : '') . $pathURL;
		if( !empty($blog['secondaryDomain']) )
			$blog['secondaryBlogURL'] = 'http://' . $blog['secondaryDomain'] . (isset($service['port']) ? ':' . $service['port'] : '') . $pathURL;
		else
			$blog['secondaryBlogURL'] = null;
		if ($blog['defaultDomain']) {
			$defaultURL = $blog['secondaryBlogURL'];
			if ($_SERVER['HTTP_HOST'] == $blog['secondaryDomain'])
				$baseURL = $service['path'];
			else
				$baseURL = $defaultURL;
		} else {
			$defaultURL = $blog['primaryBlogURL'];
			if ($_SERVER['HTTP_HOST'] == ($blog['name'] . '.' . $service['domain']))
				$baseURL = $service['path'];
			else
				$baseURL = $defaultURL;
		}
		break;
	case 'path':
		$pathURL = $service['path'] . '/' . $blog['name'];
		$blog['primaryBlogURL'] = 'http://' . $service['domain'] . (isset($service['port']) ? ':' . $service['port'] : '') . $pathURL;
		$blog['secondaryBlogURL'] = null;
		$defaultURL = $blog['primaryBlogURL'];
		if ($_SERVER['HTTP_HOST'] == $service['domain'])
			$baseURL = $service['path'] . '/' . $blog['name'];
		else
			$baseURL = $defaultURL;
		break;
	case 'single':
	default:
		$pathURL = $service['path'];
		$blog['primaryBlogURL'] = 'http://' . $service['domain'] . (isset($service['port']) ? ':' . $service['port'] : '') . $pathURL;
		if(isset($service['useRewriteEngine']) && $service['useRewriteEngine'] === false) {
			$blog['primaryBlogURL'] = $blog['primaryBlogURL'].'/blog';
			$pathURL = $pathURL.'/blog';
		}
		$blog['secondaryBlogURL'] = null;
		$defaultURL = $blog['primaryBlogURL'];
		if ($_SERVER['HTTP_HOST'] == $service['domain'])
			$baseURL = $service['path'];
		else
			$baseURL = $defaultURL;
		break;
}

$hostURL = 'http://' . $_SERVER['HTTP_HOST'] . (isset($service['port']) ? ':' . $service['port'] : '');
$blogURL = $pathURL;
$folderURL = rtrim($blogURL . $suri['directive'], '/');

if (defined('__TEXTCUBE_MOBILE__')) {
	$blogURL .= '/m';
}
unset($url, $domain);

function respondNotFoundPage() {
	header('HTTP/1.1 404 Not Found');
	header("Connection: close");
	exit;
}

function respondForbiddenPage() {
	header('HTTP/1.1 403 Forbidden');
	header("Connection: close");
	exit;
}
?>
