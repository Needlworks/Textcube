<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function respondMessagePage($message) {
	global $service;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo TATTERTOOLS_NAME;?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'];?>/style/owner.css" />
</head>
<body id="body-message-page">
	<div class="message-box">
		<h1><?php echo TATTERTOOLS_NAME;?></h1>
		
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

function respondAlertPage($message) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo TATTERTOOLS_NAME;?></title>
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

function respondErrorPage($message=NULL, $buttonValue=NULL, $buttonLink=NULL) {
	global $service;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo TATTERTOOLS_NAME;?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'];?>/style/owner.css" />
</head>
<body id="body-message-page">
	<div class="message-box">
		<h1><?php echo TATTERTOOLS_NAME;?></h1>
		
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

function respondNoticePage($message, $redirection) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo TATTERTOOLS_NAME;?></title>
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

function respondResultPage($error) {
	if ($error === true)
		$error = 0;
	else if ($error === false)
		$error = 1;
	header('Content-Type: text/xml; charset=utf-8');
	print ("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response>\n<error>$error</error>\n</response>");
	exit;
}

function printRespond($result, $useCDATA=true) {
	header('Content-Type: text/xml; charset=utf-8');
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml .= "<response>\n";
	$xml .= printRespondValue($result, $useCDATA);
	$xml .= "</response>\n";
	die($xml);
}

function printRespondValue($array, $useCDATA=true) {
	$xml = '';
	if(is_array($array)) {
		foreach($array as $key => $value) {
			if(is_null($value))
				continue;
			else if(is_array($value)) {
				if(is_numeric($key))
					$xml .= printRespondValue($value, $useCDATA)."\n";
				else
					$xml .= "<$key>".printRespondValue($value, $useCDATA)."</$key>\n";
			}
			else {
				if($useCDATA)
					$xml .= "<$key><![CDATA[".escapeCData($value)."]]></$key>\n";
				else
					$xml .= "<$key>".htmlspecialchars($value)."</$key>\n";
			}
		}
	}
	return $xml;
}
?>
