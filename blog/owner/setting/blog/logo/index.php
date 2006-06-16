<?
define('ROOT', '../../../../..');
require ROOT . '/lib/includeForOwner.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<title>Logo Uploader</title>
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
	if($_POST['mode'] == 1) {
		removeBlogLogo($owner);
		?>
		window.parent.document.getElementById('logo').src = "<?="{$blogURL}/image/spacer.gif"?>";
		<?
	}
	else {
		$file = array_pop($_FILES);
		if (changeBlogLogo($owner, $file) === false) {
			print ('alert("' . _t('변경하지 못했습니다.') . '");');
		} else {
	?>
		window.parent.document.getElementById('logo').src = "<?=(empty($blog['logo']) ? "{$blogURL}/image/spacer.gif" : "{$blogURL}/attach/$owner/{$blog['logo']}")?>";
	<?
		}
	}
}
?>
			function deleteLogo() {
				if(confirm("<?=_t('로고 이미지를 삭제하시겠습니까?')?>")) {
					document.forms[0].mode.value = "1";
					document.forms[0].submit();
				}
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
	<link rel="stylesheet" type="text/css" href="<?=$service['path'].$service['adminSkin']?>/setting.css" />
	<link rel="stylesheet" type="text/css" href="<?=$service['path'].$service['adminSkin']?>/setting.opera.css" />
	<!--[if lte IE 6]><link rel="stylesheet" type="text/css" href="<?=$service['path'].$service['adminSkin']?>/setting.ie.css" /><![endif]-->
</head>
<body id="logo-iframe">
	<form method="post" action="<?=$blogURL?>/owner/setting/blog/logo" enctype="multipart/form-data">
		<div class="form-variables">
			<input type="file" class="file-input" name="logo" onchange="document.forms[0].submit()" />
			<input type="hidden" name="mode" value="0" />
		</div>
		<div class="button-box">
			<a class="delete-button button" href="#void" onclick="deleteLogo()"><span><?=_t('삭제')?></span></a>
		</div>
	</form>
</body>
</html>
