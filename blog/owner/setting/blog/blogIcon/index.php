<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title>Blog Icon Uploader</title>
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
						var temp ='<table border="1">';
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
	if (strpos($_FILES['blogIcon']['name'], '.gif') === false && strpos($_FILES['blogIcon']['name'], '.jpg') === false) {
?>	
		alert('<?=_t('실패 했습니다.')?>');
<?
	}
	if (move_uploaded_file($_FILES['blogIcon']['tmp_name'], ROOT . "/attach/$owner/index.gif")) {
		@chmod(ROOT . "/attach/$owner/index.gif", 0666);
?>
			window.parent.document.getElementById('blogIcon').src = "<?=$blogURL?>/index.gif?"+(Math.random()*10000);
	
<?
	} else {
	}
}
?>
			window.onload=function() {
			//window.resizeTo(document.body.clientWidth,document.body.clientHeight);
}
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
	<link rel="stylesheet" type="text/css" href="<?=$blogURL?>/style/default/default-setting.css" />
	<link rel="stylesheet" type="text/css" href="<?=$blogURL?>/style/default/default-setting-opera.css" />
	<link rel="stylesheet" type="text/css" href="<?=$blogURL?>/style/default/default-setting-ie.css" />
</head>
<body id="blogcon-iframe">
	<form method="post" action="<?=$blogURL?>/owner/setting/blog/blogIcon" enctype="multipart/form-data">
		<input type="file" class="file-input" name="blogIcon" onchange="document.forms[0].submit()" />
	</form>
</body>
</html>
