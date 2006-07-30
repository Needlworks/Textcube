<?php
define('ROOT', '../../../..');
if(count($_POST) > 0) {
	$IV = array(
		'FILES' => array(
			'attachment' => array('file')
		)
	);
}
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
	<head>
		<title><?php echo  _t('File Uploader')?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo  $service['path'].$adminSkinSetting['skin']?>/editor.css" />
		<!--[if lte IE 6]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo  $service['path'].$adminSkinSetting['skin']?>/editor.ie.css" /><![endif]-->
		<script type="text/javascript" src="<?php echo  $service['path']?>/script/EAF.js"></script>
		<script type="text/javascript" src="<?php echo  $service['path']?>/script/common.js"></script>
		<script type="text/javascript">
			//<![CDATA[
				oSelect = window.parent.document.getElementById('fileList');
				
				function addAttachOption(value) {
					window.parent.makeCrossDamainSubmit("<?php echo  $blogURL?>/owner/entry/attach/<?php echo  $suri['id']?>","ie");
					if (isWin) {
						var fileName = value.substring(value.lastIndexOf('\\')+1);
					} else {
						var fileName = value.substring(value.lastIndexOf('/')+1);
					}
					
					var oSelect = window.parent.document.getElementById('fileList');	
					var oOption = window.parent.document.createElement("option");
					
					oOption.innerHTML= fileName+ " <?php echo  _t('업로드 중…')?>"; 
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
<?php
if (count($_FILES) == 1) {
	$file = array_pop($_FILES);
	if (getAttachmentByLabel($owner, $suri['id'], Path::getBaseName($file['name']))) {
		print ('alert("' . _t('동일한 이름을 가진 파일이 이미 첨부되었습니다.') . '");');
?>

				for( i=0; i<oSelect.options.length; i++) {
					if(oSelect.options[i].value == "<?php echo  escapeJSInCData($_POST['fileName'])?>") {
						oSelect.remove(i);
					}
				}
<?php
	} else if (($attachment = addAttachment($owner, $suri['id'], $file)) === false) {
		print ('alert("' . _t('첨부하지 못했습니다.') . '");');
?>
				
				for( i=0; i<oSelect.options.length; i++) {
					if(oSelect.options[i].value == "<?php echo  escapeJSInCData($_POST['fileName'])?>") {
						oSelect.remove(i);
					}
				}
<?php
	} else {
?>
				
				var oOption = window.parent.document.createElement("option");	
				oOption.innerHTML= "<?php echo  escapeJSInCData(getPrettyAttachmentLabel($attachment))?>";
				oOption.value = "<?php echo  escapeJSInCData(getAttachmentValue($attachment))?>";
				try {
<?php
		if (!empty($attachment)) {
?>
				for( i=0; i<oSelect.options.length; i++) {
					//alert(oSelect.options[i].value+"   "+ "<?php echo  escapeJSInCData($attachment['label'])?>");
					if(oSelect.options[i].value == "<?php echo  escapeJSInCData($attachment['label'])?>") {
						oSelect.remove(i);
					}
				}
<?php
		}
?>
				oSelect.appendChild(oOption);
				//oSelect.selectedIndex = oSelect.options.length - 1;
				//window.parent.document.getElementById("selectedImage").src = "<?php echo  (strncmp($attachment['mime'], 'image/', 6) == 0 ? "{$blogURL}/attach/$owner/{$attachment['name']}" : "{$blogURL}/image/spacer.gif")?>";
					window.parent.refreshFileSize();
				} catch(e) {
				alert('['+e.message+']');
			}
<?php
	}
}
?>
		//]]>
	</script>
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
					document.write('<input type="file" class="file-input" name="attachment" onclick="uploader.SetVariable(\'/:openBroswer\',\'true\');return false;" onchange="addAttachOption(this.value);document.forms[0].submit()" />');
				} else {
					document.write('<input type="file" class="file-input" name="attachment" onchange="addAttachOption(this.value);document.forms[0].submit()" />');
				}
			//]]>	
		</script>
		<input type="hidden" id="fileNameInput" name="fileName" value="" />
	</form>
</body>
</html>
