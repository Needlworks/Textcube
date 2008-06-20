<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
	define('ROOT', '.'); 
	/* Workaround for IIS environment */
	if(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
		if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) $_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
	}
	if (!empty($_SERVER['PRELOAD_CONFIG']) && file_exists('config.php')) require_once ROOT."/config.php";
	/* Retrieve Access Parameter Information. */
	$accessInfo = array(
		'host'     => $_SERVER['HTTP_HOST'],
		'fullpath' => str_replace('index.php', '', $_SERVER["REQUEST_URI"]),
		'position' => $_SERVER["SCRIPT_NAME"],
		'root'     => rtrim(str_replace('rewrite.php', '', $_SERVER["SCRIPT_NAME"]), 'index.php')
		);
	if (strpos($accessInfo['fullpath'],$accessInfo['root']) !== 0)
		$accessInfo['fullpath'] = $accessInfo['root'].substr($accessInfo['fullpath'], strlen($accessInfo['root']) - 1);
	// Workaround for compartibility with fastCGI / Other environment
	$accessInfo['input'] = ltrim(substr($accessInfo['fullpath'],
		strlen($accessInfo['root']) + (defined('__TEXTCUBE_NO_FANCY_URL__') ? 1 : 0)),'/');
	// Support Tattertools 0.9x legacy address (for upgrade users)
	if (array_key_exists('pl', $_GET) && strval(intval($_GET['pl'])) == $_GET['pl']) { header("Location: ".$accessInfo['root'].$_GET['pl']); exit;}
	$part = strtok($accessInfo['input'], '/');
	if (in_array($part, array('resource','plugins','cache','skin','attach','thumbnail'))) {
		$part = ltrim(rtrim($part == 'thumbnail' ?
			  preg_replace('/thumbnail/', 'cache/thumbnail', $accessInfo['input'], 1) :
			  $accessInfo['input']), '/');
		if(file_exists($part)) {
			require_once ROOT.'/lib/function/file.php';
			dumpWithEtag($part);
			exit;
		} else {
			header("HTTP/1.0 404 Not Found");exit;
		}
	}
	if (strtok($part, '?') == 'setup.php') {require 'setup.php'; exit;}
	$accessInfo['URLfragment'] = explode('/',strtok($accessInfo['input'],'?'));
	unset($part);

	/* Check the existence of config.php (whether installed or not) */
	if (!file_exists('config.php')) {
		if (file_exists('.htaccess')) {print "<html><body>Remove '.htaccess' file first!</body></html>";exit;}
		print "<html><body><a id='setup' href='".rtrim($_SERVER["REQUEST_URI"],"/")."/setup.php'>Click to setup.</a></body></html>";exit;
	}
	/* Determine that which interface should be loaded. */
	require_once 'config.php';
	if(defined('__TEXTCUBE_NO_FANCY_URL__')) $service['type'] = 'single';
	switch ($service['type']) {
		case 'path': // For path-based multi blog.
			array_splice($accessInfo['URLfragment'],0,1); 
			$pathPart = ltrim(rtrim(strtok(strstr($accessInfo['input'],'/'), '?'), '/'), '/');
			break;
		case 'single':
			$pathPart = (strpos($accessInfo['input'],'?') !== 0 ? ltrim(rtrim(strtok($accessInfo['input'], '?'), '/'), '/') : '');
			break;
		case 'domain': default: 
			$pathPart = ltrim(rtrim(strtok($accessInfo['fullpath'], '?'), '/'), '/');
			if(!empty($service['path'])) $pathPart = ltrim($pathPart,$service['path']);
			break;
	}
	$pathPart = strtok($pathPart,'&');
	/* Load interface. */
	$interfacePath = null;
	if (in_array($pathPart, array('favicon.ico','index.gif'))) {require_once 'interface/'.$pathPart.'.php';	exit;}
	if (!empty($accessInfo['URLfragment']) &&
		in_array($accessInfo['URLfragment'][0],
				 array('api','archive','attachment','author','category','checkup','cover','cron','entry','feeder','foaf','guestbook','keylog','location','logout','notice','page','plugin','pluginForOwner','search','suggest','sync','tag','ttxml')))
	{
		$pathPart = $accessInfo['URLfragment'][0];
		$interfacePath = 'interface/blog/'.$pathPart.'.php';
	} else if (is_numeric(strtok(end($accessInfo['URLfragment']), '&'))) {
		$pathPart = count($accessInfo['URLfragment'])==1 ? null : implode('/', array_slice($accessInfo['URLfragment'], 0, count($accessInfo['URLfragment']) - 1));
	}
	if (empty($interfacePath)) $interfacePath = 'interface/'.(empty($pathPart) ? '' : $pathPart.'/').'index.php';
	define('PATH', 'interface/'.(empty($pathPart) ? '' : $pathPart.'/'));
	unset($pathPart);
	if (!file_exists($interfacePath)) { require "lib/error.php";errorExit(404);}
	if (empty($service['debugmode'])) {	@include_once $interfacePath;}
	else {include_once $interfacePath;}
?>
