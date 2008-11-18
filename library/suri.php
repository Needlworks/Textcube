<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

if(isset($context)) {$url = $context->accessInfo['fullpath'];} 
else { $url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['SCRIPT_NAME'];}
$defaultblogid = getServiceSetting("defaultBlogId",1);

$suri            = array('url' => $url, 'value' => '');
$blogid          = null;
$isStrictBlogURL = true;
$depth           = substr_count($service['path'], '/');

if ($depth > 0) {
	if (preg_match('@^((/+[^/]+){' . $depth . '})(.*)$@', $url, $matches))
		$url = $matches[3];
	else
		Respond::NotFoundPage();
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
			$blogid = getBlogidByName(strtok($matches[1],'?'));
			if ($blogid === null) {
				$blogid = $defaultblogid;
				$isStrictBlogURL = false;
			}
			$url = $matches[2];
		} else {
			Respond::NotFoundPage();
		}
	}
	if ($blogid === null)
		Respond::NotFoundPage();
}
$owner = $blogid; // For legacy.(<1.5)

$gCacheStorage = new globalCacheStorage; // Initialize global cache

$blog = getBlogSettings($blogid);
$skinSetting = getSkinSetting($blogid);
if(isset($interfacePath)) {
	$depth = substr_count($interfacePath, '/') - 1;
} else {
	$depth = substr_count(ROOT, '/');
}
if ($depth > 0) {
	if($service['fancyURL'] === 0 || $service['fancyURL'] === 1) $url = '/'.$accessInfo['input']; // Exclude /blog path.
	if (preg_match('@^((/+[^/]+){' . $depth . '})/*(.*)$@', $url, $matches)) {
		$suri['directive'] = $matches[1];
		if ($matches[3] !== false) {
			$suri['value'] = $matches[3];
		}
	} else {
		Respond::NotFoundPage();
	}
} else {
	$suri['directive'] = '/';
	$suri['value'] = ltrim($url, '/');
}
if(strpos($suri['value'],'?') === 0) $suri['value'] = '';
else $suri['value'] = strtok($suri['value'], '?');
$suri['directive'] = strtok($suri['directive'], '?');
if (is_numeric($suri['value'])) {
	$suri['id'] = $suri['value'];
} else {
	$suri['value'] = URL::decode(str_replace('index.php','',$suri['value']));
	if(is_numeric($isValue = strtok($suri['value'],'&'))) $suri['id'] = $isValue;
	unset($isValue);
}
/*
if( function_exists( 'mb_detect_encoding' ) && function_exists('iconv') ) {
	$encoding = mb_detect_encoding($suri['value'], 'UTF-8,EUC-KR,SHIFT_JIS,EUC-JP,BIG5,EUC-CN,EUC-TW,GBK');
	$suri['value'] = @iconv( $encoding, 'UTF-8', $suri['value'] );
}*/

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
		$blog['secondaryBlogURL'] = null;
		$defaultURL = $blog['primaryBlogURL'].getFancyURLpostfix();
		if ($_SERVER['HTTP_HOST'] == $service['domain'])
			$baseURL = $service['path'];
		else
			$baseURL = $defaultURL;
		break;
}
$hostURL = 'http://' . $_SERVER['HTTP_HOST'] . (isset($service['port']) ? ':' . $service['port'] : '');
$blogURL = $pathURL.getFancyURLpostfix();
$folderURL = rtrim($blogURL . $suri['directive'], '/');
if (defined('__TEXTCUBE_MOBILE__')) {
	$blogURL .= '/m';
}else if (defined('__TEXTCUBE_IPHONE__')) {
	$blogURL .= '/i';
}
unset($url, $domain);
?>
