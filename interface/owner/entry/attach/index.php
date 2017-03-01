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
requireModel("blog.attachment");

requireStrictRoute();
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo _t('File Uploader');?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/editor.css" />
		<!--[if lte IE 6]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/editor.ie.css" /><![endif]-->
		<!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/editor.ie7.css" /><![endif]-->
		<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/jquery/jquery-<?php echo JQUERY_VERSION;?>.js"></script>
		<script type="text/javascript">jQuery.noConflict();</script>
		<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/EAF4.js"></script>
		<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/common3.js"></script>
	<style type="text/css">
		body, form {
			margin: 0 !important;
			padding: 0 !important;
		}
		
		.input-file {
			font-size: 75%;
			width: 180px;
		}
	</style>
</head>
<body id="body-editor-attachment">
	<form method="post" action="" enctype="multipart/form-data" id="uploadForm">
		<input type="file" id="fileUploadInput" class="input-file" name="attachment" multiple />
		<progress id="uploadProgress" min="0" max="100" value="0">0% complete</progress>
	</form>
	<script>
	
		var servicePath = "<?php echo $service['path'];?>";
		var blogURL = "<?php echo $blogURL;?>";
		var adminSkin = "<?php echo $adminSkinSetting['skin'];?>";
		var oSelect = window.parent.document.getElementById('TCfilelist');
		var progressBar = document.getElementById('uploadProgress');

		function addAttachOption(value) {
			try {
				//window.parent.makeCrossDamainSubmit("<?php echo $blogURL;?>/owner/entry/attach/<?php echo $suri['id'];?>","ie");	
				if (isWin) {
					var fileName = value.substring(value.lastIndexOf('\\')+1);
				} else {
					var fileName = value.substring(value.lastIndexOf('/')+1);
				}	
				var oOption = window.parent.document.createElement("option");
				oOption.text = fileName + " <?php echo _t('업로드 중..');?>";
				oOption.value = fileName;					
				oSelect.options.add(oOption);
				oSelect.setAttribute('size', Math.max(8,Math.min(oSelect.length,30)));
				
				// document.getElementById('fileNameInput').setAttribute('value', fileName);
				
			} catch(e) {
				alert(e.message);
			}
		}

		function removeAttachOption(value) {
			for( i=0; i<oSelect.options.length; i++) {
				if(oSelect.options[i].value == "<?php echo escapeJSInCData($_POST['fileName']);?>") {
					oSelect.remove(i);
				}
			}
		}

		function processFinishedUpload(value) {
			var oOption = window.parent.document.createElement("option");
			console.log('<?php echo $attachment ?>')
			oOption.innerHTML = "<?php echo escapeJSInCData(getPrettyAttachmentLabel($attachment));?>";
			oOption.value = "<?php echo escapeJSInCData(getAttachmentValue($attachment));?>";

			try {
				// remove attach uploading label
				for( i=0; i<oSelect.options.length; i++) {
					
					if(oSelect.options[i].value == "<?php echo escapeJSInCData($attachment['label']);?>") {
						oSelect.remove(i);
					}
				}
				// oSelect.appendChild(oOption);
				// parent.refreshFileSize();
			} catch(e) {
				alert('['+e.message+']');
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
				uploader.SetVariable('/:openBrowser','true');
			}
		}

		function uploadFile(file){
			var url = "../upload";
			var xhr = new XMLHttpRequest();
			var formData = new FormData();
			xhr.open("POST", url, true);

			// Listen to the upload progress.
			xhr.upload.onprogress = function(e) {
				if (e.lengthComputable) {
				  progressBar.value = (e.loaded / e.total) * 100;
				  progressBar.textContent = progressBar.value; // Fallback for unsupported browsers.
				}
			};

			xhr.upload.onerror = function(e) {
				removeAttachOption();
			}

			xhr.onreadystatechange = function() {
				
				if (xhr.readyState == xhr.DONE && xhr.status == 200) {

					if (xhr.responseText == 'samename') {
						alert('<?php echo _t('동일한 이름을 가진 파일이 이미 첨부되었습니다.') ?>');
						removeAttachOption();
					} else if (xhr.responseText == 'error') {
						alert('<?php echo _t('첨부하지 못했습니다.') ?>');
						removeAttachOption();
					} else if (xhr.responseText == 'success') {
						alert('<?php echo _t('업로드 성공') ?>');
						console.log('file uploaded.');
						processFinishedUpload(file);
					}
				 }
			};
			formData.append('attachment', file);
			xhr.send(formData);
		}


		document.getElementById('fileUploadInput').addEventListener('change', function () {
			var files = this.files;
			console.log(this.files);

			for(var i=0; i<files.length; i++){
				var file = this.files[i];
				console.log('uploading file'+i);
				console.log(file.name);
				addAttachOption(file.name);
				uploadFile(file);
			}
			console.log('for loop done');
			
			progressBar.value = 0;
			document.getElementById('uploadForm').reset();

		}, false);
	</script>
</body>
</html>
