<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
	$accessInfo = array(
		'host'     => $_SERVER['HTTP_HOST'],
		'fullpath' => $_SERVER["REQUEST_URI"],
		'position' => $_SERVER["SCRIPT_NAME"],
		'root'     => str_replace('rewrite.php','',$_SERVER["SCRIPT_NAME"])
		);
	$accessInfo['input'] = ltrim(substr(str_replace('index.php','',$accessInfo['fullpath']),strlen(rtrim($accessInfo['root'],'index.php'))+(defined('__TEXTCUBE_NO_FANCY_URL__') ? 1 : 0)),'/'); //Workaround for compartibility with fastCGI / Other environment
	$part = strtok($accessInfo['input'],'/');
	if(in_array($part, array('image','plugins','script','skin','style','attach','thumbnail'))) {
		require_once 'lib/function/misc.php';
		dumpWithEtag(rtrim(ltrim(($part == 'thumbnail' ? preg_replace('/thumbnail/','cache/thumbnail',$accessInfo['input'],1) : $accessInfo['input']),'/'),'/'));
		exit;
	} else if( strcasecmp( $part, 'cache' ) == 0 ) {
		header("HTTP/1.0 404 Not found");
		exit;
	}
	if(strtok($part,'?') == 'setup.php') {require 'setup.php';exit;}
	define('ROOT', '.'); 
	$accessInfo['URLfragment'] = explode('/',strtok($accessInfo['input'],'?'));
	require ROOT.'/config.php';
	switch ($service['type']) {
		case 'path' : // For path-based multi blog.
			array_splice($accessInfo['URLfragment'],0,1); 
			$pathPart = ltrim(rtrim(strtok(strstr($accessInfo['input'],'/'), '?'),'/'),'/');
			break;
		case 'single' :
			$pathPart = (strpos($accessInfo['input'],'?') !== 0 ? ltrim(rtrim(strtok($accessInfo['input'], '?'),'/'),'/') : '');
			
			break;
		case 'domain' :	default : 
			$pathPart = ltrim(rtrim(strtok($accessInfo['fullpath'], '?'),'/'),'/');
			break;
	}
	$pathPart = strtok($pathPart,'&');
	$interfacePath = null;
	if(in_array($pathPart, array('favicon.ico','index.gif'))) {require_once 'interface/'.$pathPart.'.php';exit;}
	if(!empty($accessInfo['URLfragment']) && in_array($accessInfo['URLfragment'][0],array('api','archive','attachment','author','category','checkup','cover','entry','feeder','guestbook','keylog','location','logout','notice','page','plugin','pluginForOwner','rss','search','suggest','sync','tag'))) {
		$pathPart = $accessInfo['URLfragment'][0];
		$interfacePath = 'interface/blog/'.$pathPart.'.php';
	} else if(is_numeric(strtok(end($accessInfo['URLfragment']),'&'))) {
		$pathPart = implode('/',array_slice($accessInfo['URLfragment'],0,count($accessInfo['URLfragment'])-1));
	}
	if(empty($interfacePath)) $interfacePath = 'interface/'.(empty($pathPart) ? '' : $pathPart.'/').'index.php';
	define('PATH','interface/'.(empty($pathPart) ? '' : $pathPart.'/'));
	unset($pathPart,$part);
	if( empty($service['debugmode']) ) {
		@include_once $interfacePath;
	} else {
		include_once $interfacePath;
	}
?>
