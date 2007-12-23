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
	if(in_array(strtok($accessInfo['input'],'/'), array('image','plugins','script','skin','style','attach','cache','thumbnail'))) {
		$content = @file_get_contents(($part == 'thumbnail' ? str_replace('thumbnail','cache/thumbnail',$accessInfo['root'].$accessInfo['input']) : $accessInfo['root'].$accessInfo['input']));
		if(!empty($content)) { echo $content; exit;}
		else exit;
	}
	$accessInfo['URLfragment'] = explode('/',$accessInfo['input']);
	$lastElm = array_slice($accessInfo['URLfragment'], -1, 1);

	switch ($service['type']) {
		case 'path' : // For path-based multi blog.
			$firstElm = array_slice($accessInfo['URLfragment'], 1, count($accessInfo['URLfragment'])-1);
			if(in_array($accessInfo['URLfragment'][1],array('entry','attachment','category','keylog','tag','search','plugin','author'))) {
				$path = 'blog/'.$accessInfo['URLfragment'][1].'/index.php';
			} else if(is_numeric($lastElm[0])) {
				$path = 'blog/'.strtok(implode('/',$firstElm), '&').'/item.php';
			} else {
				$path = 'blog'.strtok(strstr($accessInfo['input'],'/'), '&').'/index.php';
			}
			break;
		case 'domain' : 
		case 'single' : 
		default : 
			$firstElm = array_slice($accessInfo['URLfragment'], 0, count($accessInfo['URLfragment'])-1);
			if(in_array($accessInfo['URLfragment'][0],array('entry','attachment','category','keylog','tag','search','plugin','author'))) {
				$path = 'blog/'.$accessInfo['URLfragment'][0].'/index.php';
			} else if(is_numeric($lastElm[0])) {
				$path = 'blog/'.strtok(implode('/',$firstElm), '&').'/item.php';
			} else {
				$path = 'blog'.rtrim(strtok($accessInfo['fullpath'], '?'),'/').'/index.php'; break;
			}
			break;
	}
	var_dump($path);
	require $path;
?>
