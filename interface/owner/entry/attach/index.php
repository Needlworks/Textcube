<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
if(count($_POST) > 0) {
	$IV = array(
		'FILES' => array(
			'attachment' => array('file')
		), 
		'POST' => array(
			'fileName' => array('string', 'mandatory' => false)
		)
	);
}
require ROOT . '/library/preprocessor.php';
importlib("model.blog.attachment");

requireStrictRoute();
$context = Model_Context::getInstance();
?>
<!DOCTYPE html>
<html lang="<?php echo $context->getProperty('blog.language','ko');?>">
<head>
		<title><?php echo _t('File Uploader');?></title>
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/editor.css" />
		<!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/editor.ie7.css" /><![endif]-->
		<script type="text/javascript" src="<?php echo $context->getProperty('service.jqueryURL');?>jquery-<?php echo JQUERY_VERSION;?>.js"></script>
		<script type="text/javascript">jQuery.noConflict();</script>
		<script type="text/javascript" src="<?php echo $context->getProperty('service.path');?>/resources/script/EAF4.js"></script>
		<script type="text/javascript" src="<?php echo $context->getProperty('service.path');?>/resources/script/common3.min.js"></script>
		<script type="text/javascript">
			//<![CDATA[
				var servicePath = "<?php echo $context->getProperty('service.path');?>";
				var blogURL = "<?php echo $context->getProperty('uri.blog');?>";
				var adminSkin = "<?php echo $context->getProperty('panel.skin');?>";
				var oSelect = window.parent.document.getElementById('TCfilelist');
				
				function addAttachOption(fileField) {
<?php
if (defined('__TEXTCUBE_GAE__')) {
?>
				var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/owner/api/uploadurl", false);
				request.send("target=<?php echo $_SERVER["REQUEST_URI"];?>");
				fileField.parentElement.action = request.getText('/response/url');
<?php
}
?>
				try {
						//window.parent.makeCrossDamainSubmit("<?php echo $context->getProperty('uri.blog');?>/owner/entry/attach/<?php echo $suri['id'];?>","ie");	
						if (!isSafari) {
							if (isWin) {
								var fileName = fileField.value.substring(fileField.value.lastIndexOf('\\')+1);
							} else {
								var fileName = fileField.value.substring(fileField.value.lastIndexOf('/')+1);
							}	
							var oOption = window.parent.document.createElement("option");
							oOption.text = fileName + " <?php echo _t('업로드 중..');?>";
							oOption.value = fileName;					
							oSelect.options.add(oOption);
							oSelect.setAttribute('size', Math.max(8,Math.min(oSelect.length,30)));
							
							document.getElementById('fileNameInput').setAttribute('value', fileName);
						}
					} catch(e) {
						alert(e.message);
					}
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
	if (getAttachmentByLabel($blogid, $suri['id'], Path::getBaseName($file['name']))) {
		print ('alert("' . _t('동일한 이름을 가진 파일이 이미 첨부되었습니다.') . '");');
?>

				for( i=0; i<oSelect.options.length; i++) {
					if(oSelect.options[i].value == "<?php echo escapeJSInCData($_POST['fileName']);?>") {
						oSelect.remove(i);
					}
				}
<?php
	} else if (($attachment = addAttachment($blogid, $suri['id'], $file)) === false) {
		print ('alert("' . _t('첨부하지 못했습니다.') . '");');
?>
				
				for( i=0; i<oSelect.options.length; i++) {
					if(oSelect.options[i].value == "<?php echo escapeJSInCData($_POST['fileName']);?>") {
						oSelect.remove(i);
					}
				}
<?php
	} else {
?>
				
				var oOption = window.parent.document.createElement("option");	
				oOption.innerHTML= "<?php echo escapeJSInCData(getPrettyAttachmentLabel($attachment));?>";
				oOption.value = "<?php echo escapeJSInCData(getAttachmentValue($attachment));?>";
				try {
<?php
		if (!empty($attachment)) {
?>
					for( i=0; i<oSelect.options.length; i++) {
						//alert(oSelect.options[i].value+"   "+ "<?php echo escapeJSInCData($attachment['label']);?>");
						if(oSelect.options[i].value == "<?php echo escapeJSInCData($attachment['label']);?>") {
							oSelect.remove(i);
						}
					}
<?php
		}
?>
					oSelect.appendChild(oOption);
					//oSelect.selectedIndex = oSelect.options.length - 1;
					//window.parent.document.getElementById("selectedImage").src = "<?php echo (strncmp($attachment['mime'], 'image/', 6) == 0 ? "{$context->getProperty('uri.blog')}/attach/$blogid/{$attachment['name']}" : "{$context->getProperty('uri.blog')}/resources/image/spacer.gif");?>";
					parent.refreshFileSize();
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
		/*<![CDATA[*/
			body,
			form
			{
				margin                           : 0 !important;
				padding                          : 0 !important;
			}
			
			.input-file
			{
				font-size                        : 75%;
				width:180px;
			}
		/*]]>*/
	</style>
</head>
<body id="body-editor-attachment">
	<form method="post" action="" enctype="multipart/form-data" id="uploadForm">
		<script type="text/javascript">
			//<![CDATA[				
				document.write('<input type="file" class="input-file" name="attachment" size="16" onchange="addAttachOption(this); document.getElementById(\'uploadForm\').submit();" />');
			//]]>	
		</script>
		<input type="hidden" id="fileNameInput" name="fileName" value="" />
	</form>
</body>
</html>
