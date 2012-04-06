/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

var TTEditorResizer = function(target, resizer, documents) {
	this.target = target;
	this.documents = documents || [document];
	this.resizer = resizer;
	this.rowResize = this.rowResizeDown = false;
	this.onResizeBegin = function() {};
	this.onResizeEnd = function() {};
};

TTEditorResizer.prototype.initialize = function() {
	var docEventHandler = jQuery.proxy(this.docEventHandler, this);
	for (var i = 0, doc; doc = this.documents[i]; ++i) {
		STD.addEventListener(doc);
		jQuery(doc).bind({
			'mousemove.doc': docEventHandler,
			'mousedown.doc': docEventHandler,
			'mouseup.doc': docEventHandler,
			'selectstart.doc': docEventHandler
		});
	}
};

TTEditorResizer.prototype.finalize = function() {
	for (var i = 0, doc; doc = this.documents[i]; ++i) {
		jQuery(doc).unbind('.doc');
	}
};

TTEditorResizer.prototype.docEventHandler = function(event) {
	switch (event.type) {
	case "mousemove":
		if (this.rowResizeDown) {
			var pageY = parseInt(event.clientY);
			if (event.target.tagName != "BODY" && event.target.tagName != "HTML") {
				pageY += document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop;
				pageY -= getOffsetTop(this.target);
			}
			try {
				// Resolution of QSXGA screen is 2560 by 2048. When it is rotated 90 degress, its height is 2560px.
				this.target.style.height = Math.min(2600, Math.max(300, pageY)) + 'px';
			} catch(e) {}
		} else if(event.target == this.resizer) {
			this.rowResize = true;
		} else {
			this.rowResize = false;
		}
		break;
	case "mousedown":
		this.rowResizeDown = false;
		if(this.rowResize) {
			this.onResizeBegin.apply(this);
			this.rowResizeDown = true;
		}
		break;
	case "mouseup":
		this.rowResizeDown = false;
		this.onResizeEnd.apply(this);
		break;
	case "selectstart":
		return (this.rowResizeDown ? false : true);
	}
};

////////////////////////////////////////////////////////////////////////////////

var TTDefaultEditor = function() {};

// initialize the editor, with adding necessary things to given textarea
TTDefaultEditor.prototype.initialize = function(textarea) {
	this.textarea = textarea;
	var _this = this;

	// textarea event
	STD.addEventListener(textarea);
	var textareaEventHandler = function() { editorChanged(); savePosition(_this.textarea); return true; };
	this.textareaEventHandler_bounded = textareaEventHandler; // keep it to remove the handler later
	textarea.addEventListener("select", textareaEventHandler, false);
	textarea.addEventListener("click", textareaEventHandler, false);
	textarea.addEventListener("keyup", textareaEventHandler, false);

	this.resizer = new TTEditorResizer(textarea, getObject('status-container'));
	this.resizer.onResizeBegin = function() {
		getObject('attachManagerSelectNest').style.visibility = 'hidden';
	};
	this.resizer.onResizeEnd = function() {
		getObject('attachManagerSelectNest').style.visibility = 'visible';
	};
	this.resizer.initialize();
}

// finalize the editor, with removing things added by initialize method (required!)
TTDefaultEditor.prototype.finalize = function() {
	this.resizer.finalize();

	var textarea = this.textarea;
	textarea.removeEventListener("select", this.textareaEventHandler_bounded, false);
	textarea.removeEventListener("click", this.textareaEventHandler_bounded, false);
	textarea.removeEventListener("keyup", this.textareaEventHandler_bounded, false);
}

// if necessary, synchronize textarea to other editor elements
TTDefaultEditor.prototype.syncTextarea = function() {
	// do nothing
}

// add given objects (e.g. image, gallery, jukebox) to the editor
TTDefaultEditor.prototype.addObject = function(data) {
	switch (data.mode) {
	case 'Image1L': case 'Image1C': case 'Image1R': case 'ImageFree':
		var title = data.objects[0][2].replace(new RegExp('"', "g"), "&quot;").replace(new RegExp("'", "g"), "&#39;");
		if (data.mode == 'Image1L') data.objects[0][1] += ' style="float: left;"';
		if (data.mode == 'Image1R') data.objects[0][1] += ' style="float: right;"';
		var html = '<img src="[##_ATTACH_PATH_##]/' + data.objects[0][0] + '" ' + data.objects[0][1] + ' title="' + title + '" />';
		if (data.mode == 'Image1C') html = '<p style="text-align: center;">' + html + '</p>\n';
		insertTag(this.textarea, html, "");
		return true;

	case 'Image2C': case 'Image3C': case 'Gallery':
		for (var i = 0; data.objects[i]; ++i) {
			if (data.mode == 'Gallery') data.objects[i].push('');
			if (!this.addObject({mode: 'Image1C', objects: [data.objects[i]]})) return false;
		}
		return true;

	case 'Imazing':
		alert(_t('이 편집기에서는 iMazing 갤러리를 지원하지 않습니다.'));
		return false;

	case 'Jukebox':
		alert(_t('이 편집기에서는 주크박스를 지원하지 않습니다.'));
		return false;
	}
	return false;
}

////////////////////////////////////////////////////////////////////////////////

var iMazingProperties = new Array();
iMazingProperties['width'] = 450;
iMazingProperties['height'] = 350;
iMazingProperties['frame'] = 'net_imazing_frame_none';
iMazingProperties['transition'] = 'net_imazing_show_window_transition_alpha';
iMazingProperties['navigation'] = 'net_imazing_show_window_navigation_simple';
iMazingProperties['slideshowInterval'] = 10;
iMazingProperties['page'] = 1;
iMazingProperties['align'] = 'h';
iMazingProperties['skinPath'] = servicePath + '/resources/script/gallery/iMazing/';

function editorAddObject(editor, mode) {
	var oSelect = document.forms[0].TCfilelist;
	var objects = [];
	var result = {mode: mode};

	switch (mode) {
	case 'Image1L': case 'Image1C': case 'Image1R': case 'Image2C': case 'Image3C':
		var needed = parseInt(mode.substr(5));
		for (var i = 0; i < oSelect.options.length; i++) {
			if (oSelect.options[i].selected == true) {
				var value = oSelect.options[i].value.split("|");
				var result_w = new RegExp("width=['\"]?(\\d+)").exec(value[1]);
				var result_h = new RegExp("height=['\"]?(\\d+)").exec(value[1]);
				if (result_w && result_h) {
					orgWidth = result_w[1];
					orgHeight = result_h[1];
					if (orgWidth > skinContentWidth / needed) {
						width = parseInt(skinContentWidth / needed);
						height = parseInt(orgHeight * (skinContentWidth / needed/ orgWidth));
						value[1] = value[1].replace(new RegExp("width=['\"]?\\d+['\"]?", "gi"), 'width="' + width + '"');
						value[1] = value[1].replace(new RegExp("height=['\"]?\\d+['\"]?", "gi"), 'height="' + height + '"');
					}
				}
				if (value.length < 3) value.push('');
				objects.push(value);
			}
		}
		if (objects.length != needed) {
			switch (needed) {
			case 1: alert(_t('파일을 선택하십시오.')); break;
			case 2: alert(_t('파일 리스트에서 이미지를 2개 선택해 주십시오. (ctrl + 마우스 왼쪽 클릭)')); break;
			case 3: alert(_t('파일 리스트에서 이미지를 3개 선택해 주십시오. (ctrl + 마우스 왼쪽 클릭)')); break;
			}
			return false;
		}
		break;

	case 'ImageFree':
		for (var i = 0; i < oSelect.options.length; i++) {
			if (oSelect.options[i].selected) {
				var value = oSelect.options[i].value.split("|");
				if(new RegExp("\\.(gif|jpe?g|png|bmp)$", "i").test(value[0])) objects.push(value);
			}
		}
		break;

	case 'Imazing': case 'Gallery':
		try {
			if (oSelect.selectedIndex < 0) {
				alert(_t('파일을 선택하십시오'));
				return false;
			}
			var value = oSelect.options[oSelect.selectedIndex].value.split("|");
			
			var ignored = false;
			for (var i = 0; i<oSelect.length; i++) {
				if (!oSelect.options[i].selected) continue;
				file = (oSelect[i].value.substr(oSelect[i].value,oSelect[i].value.indexOf('|')));				
				if (new RegExp("\\.(jpe?g|gif|png)$", "gi").exec(file)) {
					objects.push([file, '']);
				} else {
					ignored = true;
				}
			}
			if (objects.length == 0) {
				//alert(_t('이미지 파일만 적용할 수 있습니다'));
				alert(_t('이미지 파일만 삽입 가능합니다.'));
				return false;
			}
			if (mode == 'Imazing') {
				if (ignored) {
					alert(_t('iMazing 갤러리는 JPG,PNG,GIF 파일만 지원됩니다\n그 외의 형식은 목록에서 제외했습니다'));
				}
				var props = '';
				for (var name in iMazingProperties) {
					props += name + '=' + iMazingProperties[name] + ' ';
				}
				result.properties = props;
			}
		} catch(e) { return false; }
		break;

	case 'Jukebox':
		try {
			if (oSelect.selectedIndex < 0) {
				alert(_t('파일을 선택하십시오.'));
				return false;
			}
			var value = oSelect.options[oSelect.selectedIndex].value.split("|");
			
			for (var i = 0; i<oSelect.length; i++) {
				if (!oSelect.options[i].selected) continue;
				file = (oSelect[i].value.substr(oSelect[i].value,oSelect[i].value.indexOf('|')));
				if (new RegExp("\\.mp3$", "gi").exec(file))
				{
					var desc = '', match;
					if (match = new RegExp("(.*)\\.mp3", "gi").exec(oSelect.options[i].text))
						desc = match[1].replaceAll("|","");
					objects.push([file, desc]);
				}
			}
			if (objects.length == 0) {
				alert(_t('MP3 파일만 삽입 가능합니다.'));
				return false;
			}
		} catch(e) { return false; }
		break;

	default:
		return false;
	}

	result.objects = objects;
	return editor.addObject(result);
}

