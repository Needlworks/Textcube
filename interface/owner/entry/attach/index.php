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
		var postId = <?php echo $suri['id'];?>;
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

		function removeAttachOption(file) {
			for( i=0; i<oSelect.options.length; i++) {
				if(oSelect.options[i].value == file.name) {
					oSelect.remove(i);
				}
			}
		}

		function processFinishedUpload(file) {		
			var attachment = JSON.parse(file);
			var attachmentFileName = attachment[0].label;
			var attachmentLabel = attachment[1]; // processed prettyAttachmentLabel
			var attachmentValue = attachment[2]; // processed getAttachmentValue
			var oOption = window.parent.document.createElement("option");

			oOption.innerHTML = attachmentLabel;
			oOption.value = attachmentValue;
			oOption.dataset.filename = attachmentFileName;

			try {
				for( i=0; i<oSelect.options.length; i++) {
					
					if(oSelect.options[i].value == attachmentFileName) {
						oSelect.remove(i);
					}
				}
				oSelect.appendChild(oOption);
				parent.refreshFileSize();
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
			
			if (uploader != null) {
				uploader.SetVariable('/:openBrowser','true');
			}
		}

		function uploadFile(file){
			var url = "../upload?postId=<?php echo $suri['id']; ?>";
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

					if (xhr.responseText == 'error') {
						alert('<?php echo _t('첨부하지 못했습니다.'); ?>');
						removeAttachOption();
					} else {
						// alert('<?php echo _t('업로드 성공'); ?>');
						processFinishedUpload(xhr.response);
					}
					resetProgress();
				 }
			};
			formData.append('attachment', file);
			xhr.send(formData);
		}

		function resetProgress() {
			progressBar.value = "0";
			progressBar.textContent = progressBar.value;
		}

		function checkFilenameIsUnique(file) {
			for( i=0; i<oSelect.options.length; i++) {
				if(oSelect.options[i].dataset.filename == file.name) {
					alert('<?php echo _t('동일한 이름을 가진 파일이 이미 첨부되었습니다.'); ?> - '+file.name);
					return false;
				}
			}
		}

		document.getElementById('fileUploadInput').addEventListener('change', function () {
			var files = this.files;
			var fileCount = files.length;
			var skippedFileCount = 0;

			for(var i=0; i<fileCount; i++){
				var file = this.files[i];

				// check filename before uploading
				if(checkFilenameIsUnique(file) == false) {
					console.log('Skipping upload of '+file.name);
					skippedFileCount++;
					continue;
				}

				addAttachOption(file.name);
				uploadFile(file, postId);
				console.log('Finished uploading '+ file.name);
			}
			console.log('Uploaded '+(fileCount - skippedFileCount)+' file(s).');
			console.log('Skipped '+skippedFileCount+' file(s).');
			
			document.getElementById('uploadForm').reset();
		}, false);
	</script>
</body>
</html>
