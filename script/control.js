//Suggestion 용 함수들 from locationTag
function ctlUserSuggestFunction_WatchInputBox(id)
{
	try
	{
		var instance = document.getElementById(id).instance;
		if(instance.input.value != instance.typingText)
		{
			instance.typingText = instance.input.value;
   			instance.requestSuggestion();
		}
	}
	catch(e) { }
}

// 서버에서 보내오는 내용을 실행하는 함수
function ctlUserSuggestFunction_showSuggestion()
{
	// Container의 ID를 통해 instance를 가져온다
	try { var instance = document.getElementById(arguments[0]).instance; }
	catch(e) { return; }

	debug("<span style=\"color: red\">Received " + instance.cursor + "</span>");

	// 보내온 cursor와 현재 cursor가 같지 않으면 필요없는 데이터이므로 버린다
	// 텍스트 박스를 벗어난 후에 도착한 데이터도 버린다
	if(instance.cursor != arguments[1] || !instance.isTyping)
		return;

	// input box의 위치를 구해서 suggestion window의 위치를 결정한다
	// TODO : suggestion window가 표시되면서 스크롤바가 생기면 위치를 다시 잡아줘야 한다
	instance.suggestion.style.left = getOffsetLeft(instance.input) + "px";
	instance.suggestion.style.top = getOffsetTop(instance.input) + instance.input.offsetHeight + "px";

	// suggestion window의 깜빡임을 방지하기 위해 dom을 이용한 삭제/삽입 대신 innerHTML을 이용한다
	var htmlText = new StringBuffer();

	// 전송된 결과가 있을때 
	// TODO : leftover
	if(arguments[2] == 0)
	{
		for(var i=3; i<arguments.length; i++)
		{
			arguments[i] = arguments[i].replaceAll("&quot;", '"');
			if(STD.isSafari)
				arguments[i] = decodeURIComponent(arguments[i]);
			htmlText.append("<li onmouseover=\"this.className='hover'\" onmouseout=\"this.className=''\" onmousedown=\"this.parentNode.instance.suggestionMouseClick(this)\">");
			htmlText.append(arguments[i].replace(new RegExp("(" + instance.input.value + ")", "gi"), "<em>$1</em>"));
			htmlText.append("</li>");
		}
	}
	else
	{
		instance.hideSuggestion();
		return false;
	}

	instance.suggestion.innerHTML = htmlText.toString();
	instance.suggestion.style.display = "block";
	instance.isSuggestionShown = true;

	// 이전에 추가했던 suggestion 노드가 있으면 삭제한다
	try { document.body.removeChild(instance.suggestion) } catch(e) { };
	document.body.appendChild(instance.suggestion);
}

function ctlUserSuggest(container, disable)
{
	this.name = "Textcube Control Panel User Suggestion Object";
	this.copyright = "Tatter Network Foundation";

	this.allowSuggestion = (typeof(disable) == "undefined") ? false : !disable;

	this.isFocused = false;
	this.isSettingValue = false;	// setValue가 짧은 시간에 여러번 실행될때 Safari가 죽어버리는 문제 해결

	this.instance = this;	// requestSuggestion() 함수에서 참조한다
	this.cursor = 0;		// 비동기로 전송되는 스크립트의 짝을 맞추기 위한 커서

	this.inputClassName = "";

	this.isTyping = false;			// input box에 포커스가 있는지 여부
	this.isSuggestionShown = false;	// suggest window가 보여지고 있는지의 여부

	this.typingText = "";			// eolinTagFunction_WatchInputBox에서 input box의 값을 감시하기 위한 변수

	this.container = container;		// tag list가 들어갈 container
	this.container.instance = this;

	// suggestion window
	this.suggestion = document.createElement("ul");
	this.suggestion.instance = this;
	this.suggestion.selectedIndex = 0;
	this.suggestion.className = "ctlUserSuggest";
	this.suggestion.style.margin = "0px";
	this.suggestion.style.padding = "0px";
	this.suggestion.style.listStyleType = "none";
	this.suggestion.style.position = "absolute";
	this.suggestion.style.display = "none";
	this.suggestion.style.zIndex = "999";

	this.input = document.createElement("input");
	this.input.instance = this;
	this.input.className = this.inputClassName;
	this.input.setAttribute("autocomplete", "off");
	this.input.onblur = function() {
		this.instance.isTyping = false;
		this.instance.typingText = "";
		this.instance.setValue(this.value);
		this.instance.isFocused = false;
	}
	this.input.onfocus = function() {
		if(this.instance.isFocused)
			return;
		else
			this.instance.isFocused = true;

		this.instance.isTyping = true;
		this.instance.typingText = this.value;
		this.instance.requestSuggestion();
	}
	this.input.onkeydown = function(event) {
		var instance = this.instance;

		instance.isTyping = true;

		event = instance.adjustEventCompatibility(event);
		
	    switch(event.keyCode)
	    {
		    case 13:	// Enter
			    instance.setValue(this.value, false);
			    break;
		    case 9 :    // tab
			    instance.setValue(this.value, false);
			    return event.keyCode;
			    break;
		    case 27:	// ESC
			    instance.hideSuggestion();
			    break;
			case 38:	// Key Up
			    instance.moveUp();
			    break;
		    case 40:	// Key Down
			    instance.moveDown();
			    break;
		    default:
			    return event.keyCode;
	    }
        		
		event.returnValue = false;
		event.cancelBubble = true;

		try { event.preventDefault(); } catch(e) { }

		return false;
	}

	this.input.onkeypress = function(event) { return preventEnter(event); };
	
	this.input.onkeyup = function(event) {
		var instance = this.instance;

		instance.isTyping = true;

		event = instance.adjustEventCompatibility(event);
		
	    switch(event.keyCode)
	    {
		    default:
			    return event.keyCode;
	    }
        		
		event.returnValue = false;
		event.cancelBubble = true;

		try { event.preventDefault(); } catch(e) { }

		return false;
	}
	
	// 10ms마다 input box의 값이 변했는지 체크
	setInterval("ctlUserSuggestFunction_WatchInputBox('" + this.container.id + "')", 10);

	this.container.appendChild(this.input);
}

// 입력받은 값으로 리스트를 세팅한다
ctlUserSuggest.prototype.setValue = function(str, focusOnInput)
{
	if(this.isSettingValue)
		return;
	else
		this.isSettingValue = true;
	this.hideSuggestion();
	var input = this.input;
	this.input.value = str;
	this.typingText = str;	
	this.isSettingValue = false;
}

// input box로 포커스를 이동시킨다
ctlUserSuggest.prototype.focusOnInput = function()
{
	this.input.focus();
	this.input.select();

	// 가끔씩 IE에서 포커스가 안가는 문제
	try { setTimeout("document.getElementById('" + this.container.id + "').instance.input.focus()", 1); } catch(e) { }
}

ctlUserSuggest.prototype.setInputClassName = function(str)
{
	this.inputClassName = str;
	this.input.className = str;
}

ctlUserSuggest.prototype.getValue = function()
{
	return this.input.value;
}

// suggestion window의 항목을 클릭하면 값을 세팅한다
ctlUserSuggest.prototype.suggestionMouseClick = function(obj)
{
	this.setValue(obj.innerHTML.replace(new RegExp("<\/?em>", "gi"), "").replaceAll("&amp;", "&").match("^[^ ]*"), true);
	this.hideSuggestion();
}

// suggestion window를 숨긴다
ctlUserSuggest.prototype.hideSuggestion = function()
{
	this.isSuggestionShown = false;
	this.suggestion.style.display = "none";
	this.suggestion.selectedIndex = 0;
}

// suggestion window 커서를 위로 이동
ctlUserSuggest.prototype.moveUp = function()
{
	if(this.isSuggestionShown)
	{
		this.cursor++;
		this.suggestion.selectedIndex--;

		if(this.suggestion.selectedIndex < 1)
			this.suggestion.selectedIndex = this.suggestion.childNodes.length;

		this.highlightRow();
	}
}


// suggestion window 커서를 아래로 이동
ctlUserSuggest.prototype.moveDown = function()
{
	if(this.isSuggestionShown)
	{
		this.cursor++;
		this.suggestion.selectedIndex++;

		if(this.suggestion.selectedIndex > this.suggestion.childNodes.length)
			this.suggestion.selectedIndex = 1;

		this.highlightRow();
	}
}

// 이동 후에 현재 열의 style class를 변경한다
ctlUserSuggest.prototype.highlightRow = function()
{
	// suggest window가 보이지 않는 상태거나 전송받은 내용이 없으면 제낌
	if(this.isSuggestionShown && this.suggestion.childNodes[0].className != "disabled")
	{
		for(var i=0; i<this.suggestion.childNodes.length; i++)
			this.suggestion.childNodes[i].className = (i == this.suggestion.selectedIndex - 1) ? "hover" : "";

		// 선택된 열의 값을 input box에 채운다
		this.input.value = this.typingText = this.suggestion.childNodes[this.suggestion.selectedIndex-1].innerHTML.replace(new RegExp("<\/?em>", "gi"), "").unhtmlspecialchars().match("^[^ ]*");
	}
}

ctlUserSuggest.prototype.requestSuggestion = function()
{
	var instance = this.instance;

	if(!instance.allowSuggestion)
		return;

	instance.isTyping = true;
	instance.cursor++;

	debug("Request " + instance.cursor);

	var script = document.createElement("script");
	script.setAttribute("src", blogURL + "/owner/control/action/user/suggest/?id=" + instance.container.getAttribute("id") + "&cursor=" + instance.cursor + "&input=" + encodeURIComponent(instance.input.value) + (STD.isSafari ? "&encode=1" : ""));
	document.body.appendChild(script);
}


// cross browser event
ctlUserSuggest.prototype.adjustEventCompatibility = function(event)
{
	if(navigator.appName == "Microsoft Internet Explorer")
	{
		event = window.event;
		event.target = event.srcElement;
	}

	return event;
}

// 이하 잡 유틸들

function getOffsetTop(obj)
{ return obj ? obj.offsetTop + getOffsetTop(obj.offsetParent) : 0; }

function getOffsetLeft(obj)
{ return obj ? obj.offsetLeft + getOffsetLeft(obj.offsetParent) : 0; }

var StringBuffer = function()
{ this.buffer = new Array(); }

StringBuffer.prototype.append=function(str)
{ this.buffer[this.buffer.length] = str; }

StringBuffer.prototype.toString = function()
{ return this.buffer.join(""); }

if(!String.prototype.trim) {
	String.prototype.trim = function()
	{ return this.replace(new RegExp("(^\\s*)|(\\s*$)", "g"), ""); }
}

if(!String.prototype.htmlspecialchars) {
	String.prototype.htmlspecialchars = function()
	{ return this.replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll("<", "&gt;"); }
}

if(!String.prototype.unhtmlspecialchars) {
	String.prototype.unhtmlspecialchars = function()
	{ return this.replaceAll("&amp;", "&").replaceAll("&lt;", "<").replaceAll("&gt;", ">"); }
}

var x=0;

function debug(s){try{document.getElementById("debug").innerHTML=++x+")"+s+"<br />"+document.getElementById("debug").innerHTML}catch(e){}}

// 여기까지 Suggestion용 Function

function sendUserAddInfo(name,email) {
	var request = new HTTPRequest(blogURL + "/owner/control/action/user/add/?name=" + name + "&email=" + email);
	request.onSuccess = function() {
		PM.showMessage(_t('새로운 사용자가 추가되었습니다.'), "center", "top");
		ctlRefresh();
	}
	request.onError = function() {
		msg = this.getText("/response/result");
		alert(_t('사용자를 추가하지 못했습니다.') + "Error : \r\n" + msg);
	}
	request.send();
}

function sendBlogAddInfo(owner,identify) {
	var request = new HTTPRequest(blogURL + "/owner/control/action/blog/add/?owner="+owner+"&identify="+identify);
	request.onSuccess = function() {
		PM.showMessage(_t('새로운 블로그가 추가되었습니다.'), "center", "top");
		ctlRefresh();
	}
	request.onError = function() {
		msg = this.getText("/response/result");
		alert(_t('블로그를 추가하지 못했습니다.') + "Error : \r\n" + msg);
	}
	request.send();
}

//From prototype.js
function ctlExtend(destination,source) {

	for(var property in source.prototype) {
		destination.prototype[property] = source.prototype[property];
	}
	return destination;
}

function ctlTable(containername)
{
	this.init();
	
	this.name = "Textcube Control Panel Blog Table Object";
	this.copyright = "Tatter Network Foundation";
	
	this.container = document.getElementById(containername);	
}

ctlTable.prototype.init = function() {
	this.instance = this;
	this.enablecheckbox = true;
	this.enableaction = true;
	this.enableinfo = true;

	this.table = document.createElement("TABLE");		
	this.rows = 20;
	
	this.url="";
	this.colitem = new Array();
	this.collink = new Array();	
	
}

ctlTable.prototype.setsource = function(url,page,rows) {
	this.url = url;
}

ctlTable.prototype.setColumns = function(columns) {
	this.columns = columns.length;
	this.colitem = columns;
}

ctlTable.prototype.refreshTable = function()
{
	this.showTable();
}
ctlTable.prototype.showTable = function()
{
	this.requesturl = blogURL + this.url + "?page="+this.page+"&rows="+this.rows;
	var request = new HTTPRequest(this.requesturl);
	request.instance=this.instance;
	request.onSuccess = function () {
		var Line_id,resultResponse,resultRow;
		var instance=this.instance;
		
		resultResponse = this.getText("/response/result");
		resultRow = instance.resultRow = resultResponse.split('*');
		instance.container.innerHTML='';
		instance.table.innerHTML = '';
		
		if (resultRow.length == 1)
			instance.table = '';
		else {
			if(instance.enableinfo) {
				tempInfo = instance.printInfo();
			}
			var containerThead, containerTr, tempTh, tempCheckBox,containerTbody;

			containerThead = document.createElement("THEAD");
			containerTr = document.createElement("TR");

			if(instance.enablecheckbox) {
				tempTh = document.createElement("TH");
				tempCheckBox = document.createElement("input");
				tempCheckBox.type = "checkbox";
				tempCheckBox.onclick = function() { instance.selectCheckBoxAll(this.checked); };
				tempTh.appendChild(tempCheckBox);
				containerTr.appendChild(tempTh);
			}
			for(var i=0;i<instance.columns;i++) {
				tempTh = document.createElement("TH");
				tempTh.innerHTML = instance.colitem[i];
				containerTr.appendChild(tempTh);
			}
			tempTh = document.createElement("TH");
			tempTh.innerHTML = _t('Actions');
			containerTr.appendChild(tempTh);
			
			containerThead.appendChild(containerTr);
			instance.table.appendChild(containerThead);

			containerTbody = document.createElement("TBODY");

			for (var i=0; i<instance.rows && resultRow.length > 0; i++) {
				tempRow = resultRow.shift();
				field = tempRow.split(',');
				containerTr = document.createElement("TR");

				var Line_id = field[0];
				containerTr.id = instance.table.id + "_" + Line_id;
				Td_id = document.createElement("td");

				if(instance.enablecheckbox) {
					tempCheckBox = document.createElement("input");
					Td_checkbox = document.createElement("td");
					tempCheckBox.id = containerTr.id+"_check";
					tempCheckBox.type = "checkbox";
					Td_checkbox.appendChild(tempCheckBox);
					containerTr.appendChild(Td_checkbox);
				}
				for (var j=0;j<4;j++) {
					Td_id = document.createElement("TD");
					if (instance.collink[j]!='') {
						var tempLink = document.createElement("A");
						tempLink.innerHTML = field[j];
						tempLink.setAttribute("href",instance.collink[j] + Line_id);
						Td_id.appendChild(tempLink);
					}
					else {
						Td_id.innerHTML = field[j];
					}
					containerTr.appendChild(Td_id);
				}

				if(instance.enableaction) {
					Td_action = document.createElement("TD");
					instance.printAction(Td_action);
					containerTr.appendChild(Td_action);
				}

				containerTbody.appendChild(containerTr);
				}
			this.instance.table.appendChild(containerTbody);
		}
		if (this.instance.table != '' ) {
			this.instance.container.appendChild(this.instance.table);
			if(instance.enablecheckbox) {
				tempTable = instance.printActionChecked();
				this.instance.container.appendChild(tempTable);
			}
			if(instance.enableinfo) {
				this.instance.container.appendChild(tempInfo);
			}	
		}
		return true;
	}
	request.onError = function() {
		error = this.getText("/response/error");
		if (error == -2 ) {
			window.location = "?page="+ this.getText("/response/result");
		}
	}
	request.send();
}

ctlTable.prototype.getChecked = function() {
	var tbody = this.table.childNodes[1];
	var returnText = new Array();
	for(var i=0; i<tbody.childNodes.length; i++) {
		var checkbox =  document.getElementById(tbody.childNodes[i].id+"_check");
		if (checkbox.checked)
		{
			returnText.push(tbody.childNodes[i].id.substr(this.table.id.length+1));
		}
	}
	return returnText.join(",");
}

ctlTable.prototype.selectCheckBoxAll = function(checked) {
	var tbody = this.table.childNodes[1];
	for(var i=0; i<tbody.childNodes.length; i++) {
		var checkbox =  document.getElementById(tbody.childNodes[i].id+"_check");
		checkbox.checked = checked;
	}
} 

ctlTable.prototype.reverseCheckBoxAll = function(checked) {
	var tbody = this.table.childNodes[1];
	for(var i=0; i<tbody.childNodes.length; i++) {
		var checkbox =  document.getElementById(tbody.childNodes[i].id+"_check");
		//checkbox.checked = checked;
		checkbox.checked = !checkbox.checked;
	}
}


//ctlBlog..

ctlBlog = ctlExtend(ctlBlog, ctlTable);

function ctlBlog(containername) {
	this.name = "Textcube Control Panel Blog Table Object";
	this.copyright = "Tatter Network Foundation";

	this.init();

	this.container = document.getElementById(containername);

	this.table.id = "table-blog-list";
	this.table.className = "data-inbox";
	this.table.setAttribute("cellpadding", 0);
	this.table.setAttribute("cellspacing", 0);				
	
	this.colitem.push(_t('블로그 ID'));
	this.collink.push('');
	this.colitem.push(_t('블로그 구분자'));
	this.collink.push(blogURL + "/owner/control/blog/");
	this.colitem.push(_t('블로그 제목'));
	this.collink.push('');
	this.colitem.push(_t('블로그 소유자'));
	this.collink.push('');
		
	this.url="/owner/control/action/blog/index/";
	this.rows = (typeof(rows) == "undefined") ? 20 : rows;
	this.columns = this.colitem.length;
}

ctlBlog.prototype.setPage = function(page) {
	this.page = page;
	this.requesturl = blogURL + this.url + "?page="+this.page+"&rows="+this.rows;
}

ctlBlog.prototype.printInfo = function() {
	var pagelist = this.resultRow.shift();
	var tempInfo = document.createElement("div");
	tempInfo.id = "page-navigation";
	var tempSpan = document.createElement("span");
	tempSpan.id = "page-list";
	tempSpan.innerHTML = pagelist
	tempInfo.appendChild(tempSpan);
	var blogs = this.resultRow.shift();
	tempSpan = document.createElement("span");
	tempSpan.id = "total-count";
	tempSpan.innerHTML = "총 " + blogs + "개의 블로그";
	tempInfo.appendChild(tempSpan);
	return tempInfo;
}

ctlBlog.prototype.printAction = function(Td) {
	var tempInfo = document.createElement("div");
	var tempLink = document.createElement("A");
	tempLink.className = "remove-button button";
	tempLink.id = "rb_" + field[0];
	tempLink.setAttribute("href", "#void");
	tempLink.onclick = function() { deleteBlog(this.id.substr(3)); showTable(page);return false; };
	tempLink.setAttribute("title", _t('이 블로그를 삭제합니다.'));

	var tempSpan = document.createElement("SPAN");
	tempSpan.className = "text";
	tempSpan.innerHTML = _t('삭제');

	tempLink.appendChild(tempSpan);
	Td.appendChild(tempLink);
}

ctlBlog.prototype.printActionChecked = function() {
	var instance = this.instance;
	var tempInfo = document.createElement("div");
	var tempLink = document.createElement("A");
	tempLink.className = "remove-button button";
	tempLink.id = "rb_" + field[0];
	tempLink.setAttribute("href", "#void");
	tempLink.onclick = function() { instance.deleteBlogChecked();return false; };
	tempLink.setAttribute("title", _t('선택된 블로그를 삭제합니다.'));

	var tempSpan = document.createElement("SPAN");
	tempSpan.className = "text";
	tempSpan.innerHTML = _t('선택된 블로그 삭제');
	
	tempLink.appendChild(tempSpan);
	tempInfo.appendChild(tempLink);
	return tempInfo;
}

ctlBlog.prototype.deleteBlogChecked = function() {
	var tbody = this.table.childNodes[1];
	var itemString = this.getChecked();

	if (!confirm(_t('되돌릴 수 없습니다.\t\n\n계속 진행하시겠습니까?'))) return false;
	var request = new HTTPRequest(blogURL + "/owner/control/action/blog/delete/?item=" +itemString);
	request.instance=this.instance;
	request.onSuccess = function() {
		PM.showMessage(_t('선택된 블로그가 삭제되었습니다.'), "center", "top");
		this.instance.showTable();
	}
	request.onError = function() {
		PM.showMessage(_t('삭제 도중 오류가 발생하였습니다.'), "center", "top");
	}
	request.send();
}


//ctlUser
ctlUser = ctlExtend(ctlUser, ctlTable);

function ctlUser(containername) {
	this.name = "Textcube Control Panel User Table Object";
	this.copyright = "Tatter Network Foundation";

	this.init();

	this.container = document.getElementById(containername);

	this.table.id = "table-user-list";
	this.table.className = "data-inbox";
	this.table.setAttribute("cellpadding", 0);
	this.table.setAttribute("cellspacing", 0);				
	
	this.colitem.push(_t('ID'));
	this.collink.push('');
	this.colitem.push(_t('로그인 ID'));
	this.collink.push(blogURL + "/owner/control/user/");
	this.colitem.push(_t('필명'));
	this.collink.push('');
	this.colitem.push(_t('최종 접속일'));
	this.collink.push('');
		
	this.url="/owner/control/action/user/index/";
	this.rows = (typeof(rows) == "undefined") ? 20 : rows;
	this.columns = this.colitem.length;
}

ctlUser.prototype.setPage = function(page) {
	this.page = page;
	this.requesturl = blogURL + this.url + "?page="+this.page+"&rows="+this.rows;
}

ctlUser.prototype.printInfo = function() {
	var pagelist = this.resultRow.shift();
	var tempInfo = document.createElement("div");
	tempInfo.id = "page-navigation";
	var tempSpan = document.createElement("span");
	tempSpan.id = "page-list";
	tempSpan.innerHTML = pagelist
	tempInfo.appendChild(tempSpan);
	var blogs = this.resultRow.shift();
	tempSpan = document.createElement("span");
	tempSpan.id = "total-count";
	tempSpan.innerHTML = "총 " + blogs + "명의 사용자";
	tempInfo.appendChild(tempSpan);
	return tempInfo;
}

ctlUser.prototype.printAction = function(Td) {
	var tempInfo = document.createElement("div");
	var tempLink = document.createElement("A");
	tempLink.className = "remove-button button";
	tempLink.id = "rb_" + field[0];
	tempLink.setAttribute("href", "#void");
	tempLink.onclick = function() { return false; }; //TODO:미구현
	tempLink.setAttribute("title", _t('이 사용자를 삭제합니다.'));

	var tempSpan = document.createElement("SPAN");
	tempSpan.className = "text";
	tempSpan.innerHTML = _t('삭제');

	tempLink.appendChild(tempSpan);
	Td.appendChild(tempLink);
}

ctlUser.prototype.printActionChecked = function() {
	var instance = this.instance;
	var tempInfo = document.createElement("div");
	var tempLink = document.createElement("A");
	tempLink.className = "remove-button button";
	tempLink.id = "rb_" + field[0];
	tempLink.setAttribute("href", "#void");
	tempLink.onclick = function() { return false; }; //TODO:미구현
	tempLink.setAttribute("title", _t('선택된 사용자를 삭제합니다.'));

	var tempSpan = document.createElement("SPAN");
	tempSpan.className = "text";
	tempSpan.innerHTML = _t('선택된 사용자 삭제');
	
	tempLink.appendChild(tempSpan);
	tempInfo.appendChild(tempLink);
	return tempInfo;
}

