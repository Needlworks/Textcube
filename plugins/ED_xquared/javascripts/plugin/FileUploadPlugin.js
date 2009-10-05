/**
 * @requires Xquared.js
 * @requires Browser.js
 * @requires Editor.js
 * @requires plugin/Base.js
 * @requires ui/Control.js
 */
xq.plugin.FileUploadPlugin = xq.Class(xq.plugin.Base,
	/**
	 * @name xq.plugin.FileUploadPlugin
	 * @lends xq.plugin.FileUploadPlugin.prototype
	 * @extends xq.plugin.Base
	 * @constructor
	 */
	{
	onAfterLoad: function(xed) {
		
		if(!xed.isSingleFileUpload)
		{
			xed.isSingleFileUpload = false;
		}
		
		xed.config.defaultToolbarButtonGroups.insert.push(
			{className:"image", title:"Upload Image", handler:"xed.handleFileUpload(" + xed.isSingleFileUpload + ")"}
		)

		xed.insertImageFileToEditor = function(filePath, alt, xed){
			xed = xed || this;

			var img = xed.getDoc().createElement('IMG');
			img.src = filePath;
			img.alt = alt;
			
			xed.focus();
			xed.rdom.insertNode(img) ;
		}
		
		xed.fileUploadFieldName = "Filedata";
		xed.singleUploadTarget = "/examples/single_upload_submit.php";
		xed.multiUploadTarget = "/examples/upload.php";

		xed.setFileUploadTarget = function(singleUploadTarget, multiUploadTarget)
		{
			xed.singleUploadTarget = singleUploadTarget;
			xed.multiUploadTarget = multiUploadTarget;
		}
		
		xed.setUploadFieldName = function(fieldName)
		{
			xed.fileUploadFieldName = fieldName;
		}
		
		xed.fileUploadTarget = function(){
		// uploadTarget must be absolute path
			if (xed.isSingleFileUpload) {
				return xed.singleUploadTarget;
			} else {
				return xed.multiUploadTarget;
			}
		}
		
		xed.handleFileUpload = function(isSingleFileUpload) {
			var requiredMajorVersion = 9;
			var requiredMinorVersion = 0;
			var requiredRevision = 0;

			this.isSingleFileUpload = isSingleFileUpload || !DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);
			if (this.fileUploadController.dialog) this.fileUploadController.dialog.close();

			var dialog = new xq.ui.FormDialog(
				this,
				(this.isSingleFileUpload)? xq.ui_templates.basicFileUploadDialog : xq.ui_templates.basicMultiFileUploadDialog,
				function(dialog) {
					xed.fileUploadController.load(this);
				},
				function(dialog) {
					xed.focus();

					if(xq.Browser.isTrident) {
						var rng = xed.rdom.rng();
						rng.moveToBookmark(bm);
						rng.select();
					}
					
					// cancel?
					if(!dialog) {
						xed.fileUploadController.dialog = null;
						if (!xed.isSingleFileUpload && xed.fileUploadController.swfUploader){
							xed.fileUploadController.swfUploader.destroy();
							xed.fileUploadController.swfUploader = null;
						}
						return;
					}

					if (xed.isSingleFileUpload){
						this.form.submit();
					} else {
						xed.fileUploadController.startUpload();
					}
					
					return true;
				}
			);

			this.fileUploadController.dialog = dialog;
			if(xq.Browser.isTrident) var bm = this.rdom.rng().getBookmark();
			dialog.show({position: 'centerOfEditor', notSelfClose: true});

			return true;
		}

		xed.fileUploadListener = {
			fileListContainer: null,
			isError: false,
			onReady: function(){
				
			},
			onProgress: function(file, bytesLoaded, bytesTotal){
				var nPerc = bytesLoaded / bytesTotal * 100;
				xq.$('file-' + file.id + '-progress').style.width = nPerc + '%';
			},
			onSelectStart: function(){
				var ul = document.createElement('UL');
				ul.id = 'uploadFileList';
				this.fileListContainer = ul;
			},
			onSelectComplete: function(selectedFiles, successFiles, totalFiles){
				if(!successFiles) {
					this.filListContainer = null;
				} else {
					var fileListContainer = xq.$('fileListContainer');
					var fileUploadContainer = xq.$('fileUploadContainer');
	
				  	fileListContainer.style.display = 'block';
					
				  	if (fileUploadContainer.style.width != '1px'){
					  	fileUploadContainer.style.width = '1px';
					  	fileUploadContainer.style.height = '1px';
					  	fileUploadContainer.style.border = 'none';
					  	fileUploadContainer.style.padding = 0;
					  	fileUploadContainer.style.backgroundColor = '#ffffff';
	
					  	xed.fileUploadController.swfUploader.getMovieElement().style.width = '1px';
					  	xed.fileUploadController.swfUploader.getMovieElement().style.height = '1px';
	
					  	fileUploadContainer.removeChild(fileUploadContainer.getElementsByTagName('P')[0]);
				  	}
					
				  	fileListContainer.innerHTML = "";
				  	fileListContainer.appendChild(this.fileListContainer);
				}
			},
			
			onSelect: function(file){
				var li = document.createElement("LI");
				li.id = "file_" + file.id;

				var fileName = file["name"];
				var charlength = fileName.length;
				var maxLength = 30;
				
				for (var j = 0; j < fileName.length; j++){
					if (fileName.substr(j,1).charCodeAt() >= 256) {
						charlength++;
						maxLength--;
					}
				}
				if (charlength > maxLength) fileName = fileName.substr(0, maxLength) + '..';

				var divLeft = document.createElement("DIV");
				divLeft.innerHTML = fileName;
				divLeft.className = 'div_left';
				li.appendChild(divLeft);

				var span = document.createElement("SPAN");
				divLeft.appendChild(span);
				span.className = 'capacity';
				
				var sizes = ['B', 'KB', 'MB', 'GB'];
				var fileSize = file["size"];
				var i = 0;
				while (fileSize >= 1024 && i + 1 < sizes.length) {
				    i++;
				    fileSize = fileSize / 1024;
				}
				
				span.innerHTML = '(' + Math.ceil(fileSize) + sizes[i] + ')';

				var div = document.createElement("DIV");

				li.appendChild(div);

				div.id = 'file-' + file.id + '-option';
				div.className = 'cancel_button';

				div.innerHTML = '<a href="#" onclick="xed.fileUploadController.cancelUpload(\'' + file.id + '\'); return false;">Cancel</a>'

				var graphdiv = document.createElement("DIV");
				li.appendChild(graphdiv);
				graphdiv.className = 'graph';

				var progressDiv = document.createElement('DIV');
				graphdiv.appendChild(progressDiv);
				progressDiv.id = 'file-' + file.id + '-progress';

				var img = document.createElement('IMG');
				img.src = xed.config.imagePathForDialog + 'multifile_fill.gif';
				img.alt = 'Upload Complete';
				
				progressDiv.appendChild(img);
				graphdiv.style.display = 'none';
				
				this.fileListContainer.appendChild(li);
			},
			onUploadStart: function(file){
				xq.$('file-' + file.id + '-progress').parentNode.style.display = 'block'
				xq.$('file-' + file.id + '-option').style.display = 'none'
			},
			onSuccess: function(file, serverData){
				if (xed.isSingleFileUpload){
					var doc = xq.$("uploadTarget");
					var serverData = doc.contentWindow.document.body.innerHTML;
				}
				if (!serverData) return;
				eval("var data = " + serverData);
					
				if(data && data.success) {
					if (xed.isSingleFileUpload){
						xed.insertImageFileToEditor(data.file_url, file.name, window.parent.xed);
						this.onQueueComplete();
					} else {
						xed.insertImageFileToEditor(data.file_url, file.name);
						xq.$('file-' + file.id + '-progress').style.width = '100%';
					}
				} else {
					alert(data.message);
				}
				
			},
			onSelectError : function (file, errorCode, message) {
				try {
					var errorName = "";
					switch (errorCode) {
					case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
						errorName = "QUEUE LIMIT EXCEEDED";
						break;
					case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
						errorName = "FILE EXCEEDS SIZE LIMIT";
						break;
					case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
						errorName = "ZERO BYTE FILE";
						break;
					case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
						errorName = "INVALID FILE TYPE";
						break;
					default:
						errorName = "UNKNOWN";
						break;
					}
		
				} catch (ex) {
				}
			},
			onQueueComplete: function(numFilesUploaded){
				if (!this.isError) {
					if (!xed.isSingleFileUpload && xed.fileUploadController.swfUploader){
						xed.fileUploadController.swfUploader.destroy();
						xed.fileUploadController.swfUploader = null;
					}
					xed.fileUploadController.dialog.close();
					xed.fileUploadController.dialog = null;
				}
			},
			onUploadError: function(file, errorCode, message){
				this.isError = true;
				try {
					var errorName = "";
					switch (errorCode) {
					case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
						errorName = "HTTP ERROR";
						break;
					case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
						errorName = "MISSING UPLOAD URL";
						break;
					case SWFUpload.UPLOAD_ERROR.IO_ERROR:
						errorName = "IO ERROR";
						break;
					case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
						errorName = "SECURITY ERROR";
						break;
					case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
						errorName = "UPLOAD LIMIT EXCEEDED";
						break;
					case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
						errorName = "UPLOAD FAILED";
						break;
					case SWFUpload.UPLOAD_ERROR.SPECIFIED_FILE_ID_NOT_FOUND:
						errorName = "SPECIFIED FILE ID NOT FOUND";
						break;
					case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
						errorName = "FILE VALIDATION FAILED";
						break;
					case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
						errorName = "FILE CANCELLED";
						break;
					case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
						errorName = "FILE STOPPED";
						break;
					default:
						errorName = "UNKNOWN";
						break;
					}
					
					var index = file.id;
					var div = xq.$('file-' + index + '-option');
					if (!div) return ;
					div.style.display = "block"
					div.innerHTML = "";
					if (xq.$('file-' + index + '-progress')) xq.$('file-' + index + '-progress').parentNode.style.display ="none";
					var sError = document.createElement("SPAN");
					sError.className = 'error font-variation';
					sError.innerHTML = (errorName)? errorName:'Error';
					div.appendChild(sError);
					
				} catch (ex) {
				}
				
			}
		}

		xed.fileUploadController = {
			dialog: null,
			swfUploader: null,
			load: function(dialog){
				if (xed.isSingleFileUpload){
					dialog.form.action = xed.fileUploadTarget();
					xq.$('searchAttachFile').name = xed.fileUploadFieldName; 
					// iframe onload IE bug
					/*
					xq.$("uploadTarget").onload = function() {
						parent.xed.fileUploadListener.onSuccess();
					}
					*/ 
				} else {
					
					var settings = {
						flash_url : "../javascripts/plugin/swfupload/swfupload.swf",
						upload_url: xed.fileUploadTarget(),	// Relative to the SWF file
						//post_params: {},
						file_post_name : xed.fileUploadFieldName, 
						file_types : "*.jpg;*.gif;*.png;*.bmp;",
						file_types_description : "Images",
						//file_size_limit : "1024",
						//file_upload_limit : 10,
						debug: false,
		
						// Button settings
						button_image_url: xed.config.imagePathForDialog + "btn_gray_bg_78X23.gif",	// Relative to the Flash file
						button_width: "78",
						button_height: "23",
						button_placeholder_id: "MultiFileUploaderDiv",
						button_text: '<span class="theFont">Add Files</span>',
						button_cursor: -2,
						button_text_style: ".theFont { font-size: 12px; font-family: dotum; color:#ffffff; }",
						button_text_left_padding: "9",
						button_text_top_padding: "4",
						
						// The event handler functions are defined in handlers.js
						flash_ready__handler : xed.fileUploadListener.onReady, 
						file_dialog_start_handler : xed.fileUploadListener.onSelectStart, 
						file_queued_handler : xed.fileUploadListener.onSelect,
						file_queue_error_handler : xed.fileUploadListener.onSelectError,
						file_dialog_complete_handler : xed.fileUploadListener.onSelectComplete,
						upload_start_handler : xed.fileUploadListener.onUploadStart,
						upload_progress_handler : xed.fileUploadListener.onProgress,
						upload_error_handler : xed.fileUploadListener.onUploadError,
						upload_success_handler : xed.fileUploadListener.onSuccess,
						upload_complete_handler : xed.fileUploadListener.onComplete,
						queue_complete_handler : xed.fileUploadListener.onQueueComplete
					};
					this.swfUploader = new SWFUpload(settings);
				}
			},
			openDialog: function(isSingleFileUpload){
				if (isSingleFileUpload && xed.fileUploadController.swfUploader){
					xed.fileUploadController.swfUploader.destroy();
					xed.fileUploadController.swfUploader = null;
				}
				xed.handleFileUpload(isSingleFileUpload);
			},
			startUpload: function (){
				this.swfUploader.startUpload();
			},
			cancelUpload: function(file_id){
				this.swfUploader.cancelUpload(file_id);
			}
		}
	}
});