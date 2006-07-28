<?
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'mode' => array(array('0','1'), 'mandatory' => false )
	),
	'FILES' => array(
		'logo' => array('file', 'mandatory' => false)
	)
	
);
require ROOT . '/lib/includeForOwner.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
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
	if($_POST['mode'] == 1) {
		removeBlogLogo($owner);
		?>
		window.parent.document.getElementById('logo').src = "<?="{$service['path']}/image/spacer.gif"?>";
		<?
	}
	else {
		$file = array_pop($_FILES);
		if (changeBlogLogo($owner, $file) === false) {
			print ('alert("' . _t('변경하지 못했습니다.') . '");');
		} else {
	?>
		window.parent.document.getElementById('logo').src = "<?=(empty($blog['logo']) ? "{$service['path']}/image/spacer.gif" : "{$service['path']}/attach/$owner/{$blog['logo']}")?>";
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
body {
	background-color: #EBF2F8;
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
-->
</style>
</head>
<body>
<form method="post" action="<?=$blogURL?>/owner/setting/blog/logo" enctype="multipart/form-data">
  &nbsp;&nbsp;&nbsp;<input type="file" name="logo" onchange="document.forms[0].submit()" />
  <input type="hidden" name="mode" value="0" />
  <input type="button" name="delete" value="<?=_t('삭제')?>" onclick="deleteLogo()" />
</form>
</body>
</html>
