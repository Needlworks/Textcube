<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

class URIError extends Exception {};

final class Context extends Singleton
{
	public $accessInfo;

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}

	protected function __construct() {
		$config = Config::getInstance();

		// TEMPORARY: copyed from rewrite.php
		// Workaround for IIS environment
		if(!isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SCRIPT_NAME'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
			if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
				$_SERVER['REQUEST_URI'] .= '?'.$_SERVER['QUERY_STRING'];
		}
		// IIS 7.0 and URL Rewrite Module CTP, but non-ASCII URLs are NOT supported.
		if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
		} // IIS 5.x/6.0/7.0 and Ionics ISAPI Rewrite Filter
		else if (isset($_SERVER['HTTP_X_REWRITE_URL']) && strpos($_SERVER['REQUEST_URI'], 'rewrite.php') !== FALSE) {
			$_SERVER['REQUEST_URI'] = urldecode($_SERVER['HTTP_X_REWRITE_URL']);
		}
		/* Retrieve Access Parameter Information. */
		$accessInfo = array(
			'host'      => $_SERVER['HTTP_HOST'],
			'fullpath' => str_replace('index.php', '', $_SERVER["REQUEST_URI"]), // SUGGEST: change the name 'fullpath' to 'fullQuery'
			'position'  => $_SERVER["SCRIPT_NAME"],
			'root'      => rtrim(str_replace('dispatcher.php', '', $_SERVER["SCRIPT_NAME"]), 'index.php')
			);
		if (strpos($accessInfo['fullpath'],$accessInfo['root']) !== 0)
			$accessInfo['fullpath'] = $accessInfo['root'].substr($accessInfo['fullpath'], strlen($accessInfo['root']) - 1);
		// Workaround for compartibility with fastCGI / Other environment
		$accessInfo['input'] = ltrim(substr($accessInfo['fullpath'],
			strlen($accessInfo['root']) + (defined('__TEXTCUBE_NO_FANCY_URL__') ? 1 : 0)),'/');
		// DEPRECATE?: Support for Tattertools 0.9x legacy address
	$part = strtok($accessInfo['input'], '/');
	if (in_array($part, array('resources','plugins','cache','skin','attach','thumbnail'))) {
		$part = ltrim(rtrim($part == 'thumbnail' ?
			  preg_replace('/thumbnail/', 'cache/thumbnail', $accessInfo['input'], 1) :
			  $accessInfo['input']), '/');
		$part = (($qpos = strpos($part, '?')) !== false) ? substr($part, 0, $qpos) : $part;
		if(file_exists($part)) {
			require_once ROOT.'/library/function/file.php';
			dumpWithEtag($part);
			exit;
		} else {
			header("HTTP/1.0 404 Not Found");exit;
		}
	}
	if (strtok($part, '?') == 'setup.php') {require 'setup.php'; exit;}
	$accessInfo['URLfragment'] = explode('/',strtok($accessInfo['input'],'?'));
	unset($part);

		/* Determine that which interface should be loaded. */
		if (defined('__TEXTCUBE_NO_FANCY_URL__')) 
			$config->service['type'] = 'single';
		switch ($config->service['type']) {
			case 'path': // For path-based multi blog.
				array_splice($accessInfo['URLfragment'], 0, 1); 
				$pathPart = ltrim(rtrim(strtok(strstr($accessInfo['input'],'/'), '?'), '/'), '/');
				break;
			case 'single':
				$pathPart = (strpos($accessInfo['input'],'?') !== 0 ?
					ltrim(rtrim(strtok($accessInfo['input'], '?'), '/'), '/') :
					'');
				break;
			case 'domain': default: 
				$pathPart = ltrim(rtrim(strtok($accessInfo['fullpath'], '?'), '/'), '/');
				if (!empty($config->service['path']))
					$pathPart = ltrim($pathPart, $config->service['path']);
				break;
		}
		$pathPart = strtok($pathPart,'&');
		/* Load interface. */
		$interfacePath = null;
		if (in_array($pathPart, array('favicon.ico','index.gif'))) {
			$accessInfo['interfacePath'] = 'interface/'.$pathPart.'.php';
			$accessInfo['prehandler'] = TRUE;
		} else {
			$accessInfo['prehandler'] = FALSE;
			if (!empty($accessInfo['URLfragment']) &&
				in_array($accessInfo['URLfragment'][0],
						 array('api','archive','attachment','author','category','checkup','cover','cron','entry','feeder','foaf','guestbook','iMazing','keylog','location','logout','notice','page','plugin','pluginForOwner','search','suggest','sync','tag','ttxml')))
			{
				$pathPart = $accessInfo['URLfragment'][0];
				$interfacePath = 'interface/blog/'.$pathPart.'.php';
			} else if (is_numeric(strtok(end($accessInfo['URLfragment']), '&'))) {
				$pathPart = count($accessInfo['URLfragment'])==1 ?
					null :
					implode('/', array_slice($accessInfo['URLfragment'], 0, count($accessInfo['URLfragment']) - 1));
			}
			if (empty($interfacePath))
				$interfacePath = 'interface/'.(empty($pathPart) ? '' : $pathPart.'/').'index.php';
			define('PATH', 'interface/'.(empty($pathPart) ? '' : $pathPart.'/'));
			unset($pathPart);
			if (!file_exists($interfacePath)) {
				throw new URIError("No such interface");
			}
			$accessInfo['interfacePath'] = $interfacePath;
		}
		// TODO: Add these to debug-mode output
		//echo "<b>\$accessInfo : </b>";
		//var_dump($accessInfo);
		//echo "<br /><b>\$pathPart : </b>";
		//var_dump($pathPart);
		//echo "<br />\n";

		// TODO: Parse $_GET, $_POST, and etc./
		$this->accessInfo = $accessInfo;
	}

	function __destruct() {
		// Nothing to do: destruction of this class means the end of execution
	}
}
?>
