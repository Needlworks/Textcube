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
	$accessInfo['input'] = substr($accessInfo['fullpath'],strlen($accessInfo['root'])); //Workaround for compartibility with fastCGI / Other environment
	$part = strtok($accessInfo['input'],'/');
	if(in_array($part, array('image','plugins','script','skin','style','attach','cache','thumbnail'))) {
		$file = @file_get_contents(ltrim(($part == 'thumbnail' ? preg_replace('/thumbnail/','cache/thumbnail',$accessInfo['input'],1) : $accessInfo['input']),'/'));
		if(!empty($file)) { echo $file; exit;}
		else exit;
	}
	if(strtok($part,'?') == 'setup.php') {require 'setup.php';exit;}
	$accessInfo['URLfragment'] = explode('/',strtok($accessInfo['input'],'?'));
	define('ROOT', '.'); 
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
	if(!empty($accessInfo['URLfragment']) && in_array($accessInfo['URLfragment'][0],array('entry','notice','location','cover','attachment','category','keylog','tag','search','plugin','author'))) {
		$interfacePath = 'interface/'.$accessInfo['URLfragment'][0].'/index.php';
	} else if(is_numeric(end($accessInfo['URLfragment']))) {
		$pathPart = implode('/',array_slice($accessInfo['URLfragment'],0,count($accessInfo['URLfragment'])-1));
		$interfacePath = 'interface/'.(empty($pathPart) ? '' : $pathPart.'/').'item.php';
	} else {
		$interfacePath = 'interface/'.(empty($pathPart) ? '' : $pathPart.'/').'index.php';
	}
	unset($pathPart,$part);
	if( empty($service['enableDebugMode']) ) {
		@include_once $interfacePath;
	} else {
		include_once $interfacePath;
	}
?>
