<?

function respondMessagePage($message) {
	global $service;
?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title><?=TATTERTOOLS_NAME?></title>
  <link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
</head>
<body background="<?=$service['path']?>/image/e_back.gif" style="margin:0">
	<table cellpadding="0" cellspacing="0" width="100%" style="height:100%"><tr>
	<td align="center">
		<table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="border-style:solid;border-width:1;border-color:#444444"><tr>
		<td align="center" style="background-image:url('<?=$service['path']?>/image/back.gif')">
			<table style="margin:8 5 0 5"><tr>
			<td style="font-size:8pt;font-family:verdana;padding:7 0 8 0"><b><?=TATTERTOOLS_NAME?></b></td>
			</tr></table>
			<table cellpadding="0" cellspacing="0" style="margin:16 0 13 0"><tr>
			<td style="font-size:9pt;padding:3 10 0 0"><?=$message?></td>
			<td><img src="<?=$service['path']?>/image/b_back.gif" width="53" height="17" style="cursor:pointer;" onClick="history.go(-1);"></td>
			</tr></table>
		</td>
		</tr></table>
	</td>
	</tr></table>
</body>
</html>
<?
	exit;
}

function respondAlertPage($message) {
	global $service;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
  <script type="text/javascript">
    alert("<?=$message?>");
  </script>
</head>
</body>
</html>
<?
	exit;
}

function respondErrorPage($message = '') {
	global $service;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
  <title><?=TATTERTOOLS_NAME?></title>
</head>
<body background="<?=$service['path']?>/image/e_back.gif" style="margin:0">
	<table cellpadding="0" cellspacing="0" width="100%" style="height:100%"><tr>
	<td align="center">
		<table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="border-style:solid;border-width:1;border-color:#444444"><tr>
		<td align="center" style="background-image:url('<?=$service['path']?>/image/back.gif')">
			<table style="margin:8 5 0 5"><tr>
			<td style="font-size:8pt;font-family:verdana;padding:7 0 8 0"><b><?=TATTERTOOLS_NAME?></b></td>
			</tr></table>
			<table cellpadding="0" cellspacing="0" style="margin:16 0 13 0"><tr>
			<td style="font-size:9pt;padding:3 10 0 0"><?=$message?></td>
			<td>
				<table class="buttonTop" cellspacing="0" onClick="history.go(-1);">
					<tr>
						<td><img width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif" alt="" /></td>
						<td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif')"><?=_t('이전')?></td>
						<td><img width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif" alt="" /></td>
					</tr>
				</table></td>
			</tr></table>
		</td>
		</tr></table>
	</td>
	</tr></table>
</body>
</html>
<?
	exit;
}

function respondNoticePage($message, $redirection) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title><?=$message?></title>
  <script type="text/javascript">
    alert("<?=$message?>");
    window.location.href = "<?=$redirection?>";
  </script>
</head>
</html>
<?
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