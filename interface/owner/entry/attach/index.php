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
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/basic.css" />
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

		progress {
			display: block;
			width: 100%;
		}

		#fileUploadInput:disabled + label {
			opacity: 0.5;
			pointer-events: none;
		}

		.file-upload-btn.busy {
			cursor: progress;
		}

		.input-button span {
			display: block;
		}
	</style>
</head>
<body id="body-editor-attachment">
	<form method="post" action="" enctype="multipart/form-data" id="uploadForm">
		<input type="file" id="fileUploadInput" class="input-file" name="attachment" multiple style="position: absolute; width: 1px; height: 1px; opacity: 0; top: 0; left: 0; overflow: hidden;"/>
		<label for="fileUploadInput" style="display:inline-block;"><span class="input-button file-upload-btn"><span id="fileUploadInputButtonLabel"><?php echo _t('파일 업로드');?></span></span></label>
		<a href id="deleteBtn" class="input-button" onclick="parent.deleteAttachment();return false"><span><?php echo _t('선택한 파일 삭제');?></span></a>
	</form>
	<div class="upload-progress">
		<progress id="upload-progress-bar" min="0" max="100" value="0">0% complete</progress>
	</div>
	<script>
	
		var servicePath = "<?php echo $service['path'];?>";
		var blogURL = "<?php echo $blogURL;?>";
		var postId = <?php echo $suri['id'];?>;
		var adminSkin = "<?php echo $adminSkinSetting['skin'];?>";
		var oSelect = window.parent.document.getElementById('TCfilelist');
		var fileUploadInput = document.getElementById('fileUploadInput');
		var progressBar = document.getElementById('upload-progress-bar');

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

		function uploadFile(file, currentFileIndex, fileCount){
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
				fileUploadInput.setAttribute('disabled','disabled');
				fileUploadInput.nextElementSibling.classList.add('busy');
				// document.getElementById('fileUploadInputButtonLabel').textContent = "Uploading " + (currentFileIndex + 1) + "/" + fileCount;
				document.getElementById('fileUploadInputButtonLabel').textContent = "Uploading";
			};

			xhr.upload.onerror = function(e) {
				removeAttachOption(file);
			}

			xhr.onreadystatechange = function() {
				
				if (xhr.readyState == xhr.DONE && xhr.status == 200) {

					if (xhr.responseText == 'error') {
						alert('<?php echo _t('첨부하지 못했습니다.'); ?>');
						removeAttachOption(file);
					} else {
						// alert('<?php echo _t('업로드 성공'); ?>');
						processFinishedUpload(xhr.response);
					}
				 }
			};
			formData.append('attachment', file);
			xhr.send(formData);
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

			// remove tempoption from filelist
			try {
				for( i=0; i<oSelect.options.length; i++) {
					
					if(oSelect.options[i].value == attachmentFileName) {
						oSelect.remove(i);
					}
				}
				oSelect.appendChild(oOption);
				parent.refreshFileSize();
				console.log('Finished uploading '+ attachmentFileName);
				document.getElementById('fileUploadInputButtonLabel').textContent = "<?php echo _t('파일 업로드');?>"
				fileUploadInput.removeAttribute('disabled');
				fileUploadInput.nextElementSibling.classList.remove('busy');
				resetProgress();
			} catch(e) {
				alert('['+e.message+']');
			}
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

		fileUploadInput.addEventListener('change', function() {
			var files = this.files;
			var fileCount = files.length;

			for(var i=0; i<fileCount; i++){
				var currentFileIndex = i;
				var file = this.files[i];

				// check filename before uploading
				if(checkFilenameIsUnique(file) == false) {
					console.log('Skipping upload of '+file.name);
					continue;
				} else {
					addAttachOption(file.name);
					uploadFile(file, currentFileIndex, fileCount);
				}
			}		
			document.getElementById('uploadForm').reset();
				
		}, false);
	</script>
</body>
</html>
