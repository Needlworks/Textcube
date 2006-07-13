<?php 

function respondMessagePage($message) {
	global $service, $blogURL;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo TATTERTOOLS_NAME?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo $blogURL ?>/style/owner.css" />
</head>
<body id="body-message-page">
	<div class="message-box">
		<h1><?php echo TATTERTOOLS_NAME?></h1>
		
		<div class="message"><?php echo $message?></div>
		<div class="button-box">
			<input type="button" class="button-input" value="<?php echo _text('이전')?>" onclick="window.history.go(-1)" />
		</div>
	</div>
</body>
</html>
<?php 
	exit;
}

function respondAlertPage($message) {
	global $service, $blogURL;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo TATTERTOOLS_NAME?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo $blogURL ?>/style/owner.css" />
	<script type="text/javascript">
		//<![CDATA[
			alert("<?php echo $message?>");
		//]]>	
	</script>
</head>
</html>
<?php 
	exit;
}

function respondErrorPage($message = '') {
	global $service, $blogURL;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title><?php echo TATTERTOOLS_NAME?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="<?php echo $blogURL ?>/style/owner.css" />
</head>
<body id="body-message-page">
	<div class="message-box">
		<h1><?php echo TATTERTOOLS_NAME?></h1>
		
		<div class="message"><?php echo $message?></div>
		<div class="button-box">
			<input type="button" class="button-input" value="<?php echo _text('이전')?>" onclick="window.history.go(-1)" />
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
	<title><?php echo TATTERTOOLS_NAME?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
		//<![CDATA[
			alert("<?php echo $message?>");
			window.location.href = "<?php echo $redirection?>";
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

function printRespond($result) {
	header('Content-Type: text/xml; charset=utf-8');
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
	$xml .= "<response>\n";
	foreach ($result as $key => $value) {
		$xml .= "	<$key><![CDATA[$value]]></$key>\n";
	}
	$xml .= "</response>\n";
	die($xml);
}
?>
