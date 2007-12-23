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
	switch ($service['type']) {
		case 'path' : $path = 'blog'.strtok(strstr($accessInfo['input'],'/'), '&').'/index.php'; break;// For path-based multi blog.
		case 'domain' : case 'single' : default : $path = 'blog/'.strtok($accessInfo['fullpath'], '?').'/index.php'; break;
	}
	require $path;
?>
