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
	$accessInfo['URLfragment'] = explode('/',$accessInfo['input']);
	define('ROOT', '.'); // Legacy ( < 1.6)
	require ROOT.'/config.php';
	switch ($service['type']) {
		case 'path': $part = $accessInfo['URLfragment'][1]; break;
		default : $part = $accessInfo['URLfragment'][0]; break;
	}
	if(in_array($part, array('image','plugins','script','skin','style'))) {
		$content = @file_get_contents($accessInfo['root'].$accessInfo['input']);
		if(!empty($content)) { echo $content; exit;}
		else exit;
	}
	switch ($service['type']) {
		case 'path' : $path = 'blog'.strtok(strstr($accessInfo['input'],'/'), '&').'/index.php'; break;// For path-based multi blog.
		case 'domain' : case 'single' : default : $path = 'blog'.strtok($accessInfo['input'], '&').'/index.php'; break;
	}
	require $path;
?>
