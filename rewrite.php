<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

	$accessInfo = array(
		'host'     => $_SERVER['HTTP_HOST'],
		'fullpath' => $_SERVER["REQUEST_URI"],
		//'input'    => (isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING'] : $_SERVER['QUERY_STRING']),
		//'input'    => ltrim($_SERVER['REQUEST_URI'],'/'),
		'position' => $_SERVER["SCRIPT_NAME"],
		'root'     => str_replace('rewrite.php','',$_SERVER["SCRIPT_NAME"])
		);
	$accessInfo['input'] = substr($accessInfo['fullpath'],strlen($accessInfo['root'])); //Workaround for compartibility with fastCGI / Other environment
	define('ROOT', '.'); // Legacy ( < 1.6)
	require ROOT.'/config.php';
	$part = strtok($accessInfo['input'],'/');
	if(in_array($part, array('image','plugins','script','skin','style','attach','cache','thumbnail'))) {
		$content = file_get_contents(ltrim(($part == 'thumbnail' ? str_replace('thumbnail','cache/thumbnail',$accessInfo['root'].$accessInfo['input']) : $accessInfo['root'].$accessInfo['input']),'/'));
		if(!empty($content)) { echo $content; exit;}
		else exit;
	}
	$accessInfo['URLfragment'] = explode('/',$accessInfo['input']);
	$lastElm = array_slice($accessInfo['URLfragment'], -1, 1);
	switch ($service['type']) {
		case 'path' : // For path-based multi blog.
			array_splice($accessInfo['URLfragment'],0,1); 
			$pathPart = ltrim(rtrim(strtok(strstr($accessInfo['input'],'/'), '?'),'/'),'/');
			break;
		case 'domain' : 	case 'single' : 	default : 
			$pathPart = ltrim(rtrim(strtok($accessInfo['fullpath'], '?'),'/'),'/');
			break;
	}
	if(!empty($accessInfo['URLfragment']) && in_array($accessInfo['URLfragment'][0],array('entry','notice','location','cover','attachment','category','keylog','tag','search','plugin','author'))) {
		$interfacePath = 'blog/'.$accessInfo['URLfragment'][0].'/index.php';
	} else if(is_numeric(strtok($lastElm[0], '?'))) {
		$pathPart = strtok(implode('/',array_slice($accessInfo['URLfragment'],0,count($accessInfo['URLfragment'])-1)), '&');
		$interfacePath = 'blog/'.(empty($pathPart) ? '' : $pathPart.'/').'item.php';
	} else {
		$interfacePath = 'blog/'.(empty($pathPart) ? '' : $pathPart.'/').'index.php';
	}
	require $interfacePath;
?>
