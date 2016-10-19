/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

var defaultfonts = [
		['Andale Mono', 'times'],
		['Arial', 'helvetica', 'sans-serif'],
		['Arial Black', 'avant garde'],
		['Book Antiqua', 'palatino'],
		['Comic Sans MS', 'sand'],
		['Courier New', 'courier', 'monospace'],
		['Georgia', 'times new roman', 'times', 'serif'],
		['Helvetica'],
		['Impact', 'chicago'],
		['Symbol'],
		['Tahoma', 'arial', 'helvetica', 'sans-serif'],
		['Times New Roman', 'times', 'serif'],
		['Trebuchet MS', 'geneva'],
		['Verdana', 'arial', 'helvetica', 'sans-serif']
	];
var colors = ['666666', '008000', '009966', '99CC66', '999966', 'CC9900', 'D41A01',
				  'FF0000', 'FF7635', 'FF9900', 'FF3399', '9B18C1', '993366',
				  '666699', '0000FF', '177FCD', '006699', '003366', '333333',
				  '000000', '8E8E8E', 'C1C1C1', 'FFFFFF', 'FFDAED', 'C9EDFF',
				  'D0FF9D', 'FAFFA9', 'E4E4E4'];

function styleExecCommand(objname, flag, style){
	var obj1 = document.getElementById('nicknameStyle');
	var obj2 = (objname)?document.getElementById(objname):"";
	if(flag == 'fontcolor'){
		obj1.style.color = style;
		obj2.value = style;
		obj2.style.color = style;
	}else if(flag == 'fontname'){
		obj1.style.fontFamily = style;
	}else if(flag == 'fontsize'){
		obj1.style.fontSize = style + 'pt';
	}else if(flag == 'fontbold'){
		if(obj1.style.fontWeight){
			obj1.style.fontWeight = '';
			obj2.style.checked = false;
		}else{
			obj1.style.fontWeight = style;
			obj2.style.checked = true;
		}
	}else if(flag == 'fontitalic'){
		if(obj1.style.fontStyle){
			obj1.style.fontStyle = '';
			obj2.style.checked = false;
		}else{
			obj1.style.fontStyle = style;
			obj2.style.checked = true;
		}
	}else if(flag == 'fontunderline'){
		if(obj1.style.textDecoration){
			obj1.style.textDecoration = '';
			obj2.style.checked = false;
		}else{
			obj1.style.textDecoration = style;
			obj2.style.checked = true;
		}
	}else if(flag == 'teamimage'){
		obj2.src = style;
	}
}
function removeFormatting() {
	var str = document.getElementById('nicknameStyle').innerHTML;
	var styleTags = new Array("b", "strong", "i", "em", "u", "ins", "strike", "del", "font");
	for(var i in styleTags) {
		var regTag = new RegExp("</?" + styleTags[i] + "(?:>| [^>]*>)", "i");
		while(result = regTag.exec(str))
			str = str.replaceAll(result[0], "");
	}
	str = str.replace(new RegExp('\\s*style="[^"]*"', "gi"), "");
	var styleContainers = new Array("span", "div");
	for(var i in styleContainers) {
		var regTag = new RegExp("<span\\s*?>((?:.|\\s)*?)</span>", "i");
		while(result = regTag.exec(str))
			str = str.replace(result[0], result[1]);
	}
	document.getElementById('nicknameStyle').innerHTML = str;
}

function uploadImage(frm, type) {
	frm.type.value = type;
	if(type == 'upload'){
		frm.submit();
		frm.imageRemove.disabled = false;
	}else{
		if(confirm(_t('정말 삭제하시겠습니까?'))){
			frm.submit();
			frm.imageRemove.disabled = true;
			frm.imageRemove.checked = false;
		}else{
			frm.imageRemove.checked = false;
			return false;
		}
	}
	frm.reset();
}

function setStyleSave() {
	try {
		var fontBold = document.getElementById('fontBold').checked;
		var fontItalic = document.getElementById('fontItalic').checked;
		var fontUnderline = document.getElementById('fontUnderline').checked;
		var fontColor = document.getElementById('fontColor').value;
		var fontFamilyList = document.getElementById('fontFamilyList').value;
		var fontSizeList = document.getElementById('fontSizeList').value;
		var queryString = "flag=style&fontstyle=" + encodeURIComponent(fontBold + "|" + fontItalic + "|" + fontUnderline + "|" + fontColor + "|" + fontFamilyList + "|" + fontSizeList);

		var request = new HTTPRequest("POST", blogURL + "/plugin/teamContentsSave/");
		request.onSuccess = function () {
			PM.showMessage("필명 스타일이 저장 되었습니다.", "center", "bottom");
		}
		request.onError = function() {
			PM.showErrorMessage("저장하지 못했습니다. 다시 시도 해주세요.", "center", "bottom");
		}
		request.send(queryString);
	} catch(e) {}
}

function setProfileSave() {
	try {
		var profile = document.getElementById('profile').value;
		var queryString = "flag=profile&profile=" + encodeURIComponent(profile);

		var request = new HTTPRequest("POST", blogURL + "/plugin/teamContentsSave/");
		request.onSuccess = function () {
			PM.showMessage("프로필 설명이 저장 되었습니다.", "center", "bottom");
		}
		request.onError = function() {
			PM.showErrorMessage("저장하지 못했습니다. 다시 시도 해주세요.", "center", "bottom");
		}
		request.send(queryString);
	} catch(e) {}
}
