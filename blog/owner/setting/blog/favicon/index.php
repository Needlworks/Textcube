<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title>Favicon Uploader</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript">
		//<![CDATA[
			function trace(msg,mode) {
				
				try {
					if(mode == undefined) {
						var temp ='';
						for(var name in msg) {
							temp +=name+'\t\t:'+msg[name]+'\n';
						}
						alert(temp);
					} else if(mode ='w') {
						var temp ='<table>';
						for(var name in msg) {
							temp +='<tr>';
							temp +='<td>'+name+'</td><td>'+msg[name]+'</td>';
							temp +='</tr>';
						}
						temp +='</table>';
						var traceWin = window.open('', "traceWin");
						try{
							traceWin.document.select();
						} catch(e) {
							
						}
						traceWin.document.write(temp);
					}
				} catch (e) {
				}
			}
<?
if (count($_FILES) == 1) {
	if (Path::getExtension($_FILES['favicon']['name']) != '.ico') {
?>	
			alert('<?=_t('변경하지 못했습니다.')?>');
<?
	} else { 
	
		requireComponent('Tattertools.Data.Attachment');
		Attachment::confirmFolder();
	
		if (move_uploaded_file($_FILES['favicon']['tmp_name'], ROOT . "/attach/$owner/favicon.ico")) {
			@chmod(ROOT . "/attach/$owner/favicon.ico", 0666);
?>
			var favicon = window.parent.document.getElementById('favicon');
			if (favicon)
				favicon.src = "<?="$blogURL/favicon.ico"?>?"+(Math.random()*10000);
			
<?
		} else {
		}
	}
}
?>
			//window.onload=function() {
			//window.resizeTo(document.body.clientWidth,document.body.clientHeight);
			//}
		//]]>
	</script>
	<style type="text/css">
		<!--
			body,
			form
			{
				margin                           : 0;
				padding                          : 0;
			}
		-->
	</style>
	<link rel="stylesheet" type="text/css" href="<?=$service['path'].$service['adminSkin']?>/setting.css" />
	<link rel="stylesheet" type="text/css" href="<?=$service['path'].$service['adminSkin']?>/setting.opera.css" />
	<!--[if lte IE 6]><link rel="stylesheet" type="text/css" href="<?=$service['path'].$service['adminSkin']?>/setting.ie.css" /><![endif]-->
</head>
<body id="favicon-iframe">
	<form method="post" action="<?=$blogURL?>/owner/setting/blog/favicon" enctype="multipart/form-data">
		<input type="file" class="file-input" name="favicon" onchange="document.forms[0].submit()" />
	</form>
</body>
</html>
