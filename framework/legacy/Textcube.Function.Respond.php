<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class Respond {
	function ResultPage($errorResult) {
		if (is_array($errorResult) && count($errorResult) < 2) {
			$errorResult = array_shift($errorResult);
		}
		if (is_array($errorResult)) {
			$error = $errorResult[0];
			$errorMsg = $errorResult[1];
		} else {
			$error = $errorResult;
			$errorMsg = '';
		}
		if ($error === true)
			$error = 0;
		else if ($error === false)
			$error = 1;
		header('Content-Type: text/xml; charset=utf-8');
		print ("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response>\n<error>$error</error>\n<message><![CDATA[$errorMsg]]></message></response>");
		exit;
	}
	
	function PrintResult($result, $useCDATA=true) {
		header('Content-Type: text/xml; charset=utf-8');
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$xml .= "<response>\n";
		$xml .= Respond::PrintValue($result, $useCDATA);
		$xml .= "</response>\n";
		die($xml);
	}
	
	function NotFoundPage($isAjaxCall = false) {
		if($isAjaxCall) {Respond::ResultPage(-1);exit;}
		header('HTTP/1.1 404 Not Found');
		header("Connection: close");
		exit;
	}
	
	function ForbiddenPage() {
		header('HTTP/1.1 403 Forbidden');
		header("Connection: close");
		exit;
	}
	
	function MessagePage($message) {
		global $service;
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<title><?php echo TEXTCUBE_NAME;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="<?php echo $service['path'];?>/resources/style/owner.css" />
	</head>
	<body id="body-message-page">
		<div class="message-box">
			<h1><?php echo TEXTCUBE_NAME;?></h1>
			
			<div class="message"><?php echo $message;?></div>
			<div class="button-box">
				<input type="button" class="input-button" value="<?php echo _text('이전');?>" onclick="window.history.go(-1)" />
			</div>
		</div>
	</body>
	</html>
<?php 
		exit;
	}
	
	function AlertPage($message) {
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<title><?php echo TEXTCUBE_NAME;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script type="text/javascript">
			//<![CDATA[
				alert("<?php echo $message;?>");
			//]]>	
		</script>
	</head>
	</html>
<?php 
		exit;
	}
	
	function ErrorPage($message=NULL, $buttonValue=NULL, $buttonLink=NULL, $isAjaxCall = false) {
		global $service;
		if($isAjaxCall) {Respond::ResultPage(-1);exit;}
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<title><?php echo TEXTCUBE_NAME;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="<?php echo $service['path'];?>/resources/style/owner.css" />
	</head>
	<body id="body-message-page">
		<div class="message-box">
			<h1><?php echo TEXTCUBE_NAME;?></h1>
			
			<div class="message"><?php echo $message;?></div>
			<div class="button-box">
				<input type="button" class="input-button" value="<?php echo !empty($buttonValue) ? $buttonValue : _text('이전');?>" onclick="<?php echo !empty($buttonLink) ? 'window.location.href=\''.$buttonLink.'\'' : 'window.history.go(-1)';?>" />
			</div>
		</div>
	</body>
	</html>
<?php
		exit;
	}
	
	function NoticePage($message, $redirection) {
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<title><?php echo TEXTCUBE_NAME;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script type="text/javascript">
			//<![CDATA[
				alert("<?php echo $message;?>");
				window.location.href = "<?php echo $redirection;?>";
			//]]>
		</script>
	</head>
	</html>
<?php 
		exit;
	}

	function PrintValue($array, $useCDATA=true) {
		$xml = '';
		if(is_array($array)) {
			foreach($array as $key => $value) {
				if(is_null($value))
					continue;
				else if(is_array($value)) {
					if(is_numeric($key))
						$xml .= Respond::PrintValue($value, $useCDATA)."\n";
					else
						$xml .= "<$key>".Respond::PrintValue($value, $useCDATA)."</$key>\n";
				}
				else {
					if($useCDATA)
						$xml .= "<$key><![CDATA[".Respond::escapeCData($value)."]]></$key>\n";
					else
						$xml .= "<$key>".htmlspecialchars($value)."</$key>\n";
				}
			}
		}
		return $xml;
	}
	
	function escapeJSInAttribute($str) {
		return htmlspecialchars(str_replace(array('\\', '\r', '\n', '\''), array('\\\\', '\\r', '\\n', '\\\''), $str));
	}

	function escapeCData($str) {
		return str_replace(']]>', ']]&gt;', $str);
	}
}
?>
