<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

	$accessInfo = array(
		'host'     => $_SERVER['HTTP_HOST'],
		'fullpath' => $_SERVER["REQUEST_URI"],
		'input'    => (isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING'] : $_SERVER['QUERY_STRING']),
		'position' => $_SERVER["SCRIPT_NAME"],
		'root'     => str_replace('rewrite.php','',$_SERVER["SCRIPT_NAME"])
		);
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
			$pathPart = trim(strtok(strstr($accessInfo['input'],'/'), '&'),'/');
			break;
		case 'domain' : 	case 'single' : 	default : 
			$pathPart = trim(strtok($accessInfo['fullpath'], '?'),'/');
			break;
	}
	if(in_array($accessInfo['URLfragment'][0],array('entry','cover','attachment','category','keylog','tag','search','plugin','author'))) {
		$interfacePath = 'blog/'.$accessInfo['URLfragment'][0].'/index.php';
	} else if(is_numeric($lastElm[0])) {
		$interfacePath = 'blog/'.strtok(implode('/',array_slice($accessInfo['URLfragment'],0,count($firstElm)-1)), '&').'/item.php';
	} else {
		$interfacePath = 'blog/'.$pathPart.'index.php';
	}
	require $interfacePath;
?>
