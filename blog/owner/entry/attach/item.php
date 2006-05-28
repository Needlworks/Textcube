<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script type="text/javascript" src="<?=$service['path']?>/script/EAF.js"></script>
		<script type="text/javascript" src="<?=$service['path']?>/script/common.js"></script>
		<script type="text/javascript">
			//<![CDATA[
				oSelect = window.parent.document.getElementById('fileList');
				
				function addAttachOption(value) {
					window.parent.makeCrossDamainSubmit("<?=$blogURL?>/owner/entry/attach/<?=$suri['id']?>","ie");
					if (isWin) {
						var fileName = value.substring(value.lastIndexOf('\\')+1);
					} else {
						var fileName = value.substring(value.lastIndexOf('/')+1);
					}
					
					var oSelect = window.parent.document.getElementById('fileList');	
					var oOption = window.parent.document.createElement("option");
					
					oOption.innerHTML= fileName+ " <?=_t('업로드 중...')?>"; 
					oOption.value=fileName;
					oSelect.appendChild(oOption);
					oSelect.setAttribute('size',Math.max(8,Math.min(oSelect.length,30)));
					document.getElementById('fileNameInput').setAttribute('value',fileName);
				}
				
				function checkUploadMode(oEvent) {
					try {
						if(isIE) 
							uploader = window.parent.document.getElementById("uploader");
						else 
							uploader = window.parent.document.getElementById("uploader2");
					} catch(e) {		
						uploader = null;	
					}
					
					if (uploader!=null) {
						uploader.SetVariable('/:openBroswer','true');
					}
				}
<?
if (count($_FILES) == 1) {
	$file = array_pop($_FILES);
	if (getAttachmentByLabel($owner, $suri['id'], Path::getBaseName($file['name']))) {
		print ('alert("' . _t('동일한 이름을 가진 파일이 이미 첨부되었습니다.') . '");');
?>

				for( i=0; i<oSelect.options.length; i++) {
					if(oSelect.options[i].value == "<?=escapeJSInCData($_POST['fileName'])?>") {
						oSelect.remove(i);
					}
				}
<?
	} else if (($attachment = addAttachment($owner, $suri['id'], $file)) === false) {
		print ('alert("' . _t('첨부하지 못했습니다.') . '");');
?>
				
				for( i=0; i<oSelect.options.length; i++) {
					if(oSelect.options[i].value == "<?=escapeJSInCData($_POST['fileName'])?>") {
						oSelect.remove(i);
					}
				}
<?
	} else {
?>
				
				var oOption = window.parent.document.createElement("option");	
				oOption.innerHTML= "<?=escapeJSInCData(getPrettyAttachmentLabel($attachment))?>";
				oOption.value = "<?=escapeJSInCData(getAttachmentValue($attachment))?>";
				try {
<?
		if (!empty($attachment)) {
?>
				for( i=0; i<oSelect.options.length; i++) {
					//alert(oSelect.options[i].value+"   "+ "<?=escapeJSInCData($attachment['label'])?>");
					if(oSelect.options[i].value == "<?=escapeJSInCData($attachment['label'])?>") {
						oSelect.remove(i);
					}
				}
<?
		}
?>
				oSelect.appendChild(oOption);
				//oSelect.selectedIndex = oSelect.options.length - 1;
				//window.parent.document.getElementById("selectedImage").src = "<?=(strncmp($attachment['mime'], 'image/', 6) == 0 ? "{$service['path']}/attach/$owner/{$attachment['name']}" : "{$service['path']}/image/spacer.gif")?>";
					window.parent.refreshFileSize();
				} catch(e) {
				alert('['+e.message+']');
			}
<?
	}
}
?>
		//]]>
	</script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $blogURL .$service['adminSkin']?>/editor.css" />
	<style type="text/css">
		<!--
			body,
			form
			{
				margin                           : 0 !important;
				padding                          : 0 !important;
			}
		-->
	</style>
</head>
<body id="body-editor-attachment">
	<form method="post" action="" enctype="multipart/form-data">
		<script type="text/javascript">
			//<![CDATA[
				try {
					if(isIE) {
						uploader = window.parent.document.getElementById("uploader");
					} else {
						uploader = window.parent.document.getElementById("uploader2");
					}
				} catch(e) {		
					
				}
				
				if (uploader!=null) {
					document.write('<input type="file" class="file-input" name="attachment" onClick="uploader.SetVariable(\'/:openBroswer\',\'true\');return false;" onchange="addAttachOption(this.value);document.forms[0].submit()" />');
				} else {
					document.write('<input type="file" class="file-input" name="attachment" onchange="addAttachOption(this.value);document.forms[0].submit()" />');
				}
			//]]>	
		</script>
		<input type="hidden" id="fileNameInput" name="fileName" value="" />
	</form>
</body>
</html>
