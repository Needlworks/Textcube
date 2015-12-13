/// Copyright (c) 2005-2016. Needlworks / Tatter & Company
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// Depends on jQuery 1.2.6 or later (from 2008/12/05, r7131)

function getObject(target) {
	try {
		switch(typeof(target)) {
			case"undefined":
				return null;
			case"object":
				return target;
			default:
				return document.getElementById(target);
		};
	} catch(e) {
		return null;
	};
};

Standardizer.prototype.namespace = "Eolin.Application.Framework";
Standardizer.prototype.name      = "Eolin Standardizer";
Standardizer.prototype.verion    = "1.1";
Standardizer.prototype.copyright = "Copyright (c) 2005,2015 Needlworks / Tatter & Company. All rights reserved.";

function Standardizer(){};

var ua = navigator.userAgent;
// Microsoft Explorer
Standardizer.prototype.isIE = (ua.indexOf("MSIE")>=0 && document.all);
if(Standardizer.prototype.isIE) {
	Standardizer.prototype.browserVersion  = parseFloat(ua.substr(Math.max(ua.indexOf("MSIE"),0)+4,4));
	Standardizer.prototype.engineVersion  = Standardizer.prototype.browserVersion;
}
// Mozilla Firefox
Standardizer.prototype.isFirefox = (ua.indexOf("Firefox")>=0 || ua.toLowerCase().indexOf("iceweasel")>=0 || ua.indexOf("Minefield")>0);
if(Standardizer.prototype.isFirefox) {
	Standardizer.prototype.browserVersion  = parseFloat(ua.substr(ua.indexOf("Firefox/")+8,10));
	Standardizer.prototype.engineVersion  = parseFloat(ua.substr(Math.max(ua.indexOf("rv:"),0)+3,7));
}
// Webkit / Safari
webkitIndex = Math.max(ua.indexOf("WebKit"), ua.indexOf("Safari"),0);
Standardizer.prototype.isSafari = (ua.indexOf("Safari")>=0);
Standardizer.prototype.isChrome = (ua.indexOf("Chrome")>=0);
Standardizer.prototype.isWebkit = (webkitIndex > 0);
if(Standardizer.prototype.isChrome) {
	chromeIndex = Math.max(ua.indexOf("Webkit"), ua.indexOf("Chrome"),0);
	Standardizer.prototype.browserVersion  = parseFloat(ua.substr(chromeIndex+7));
	Standardizer.prototype.engineVersion  = parseFloat(ua.substr(webkitIndex+7));
} else if(Standardizer.prototype.isWebkit) {
	Standardizer.prototype.browserVersion  = parseFloat(ua.split("Version/")[1]) || ( ( parseFloat(ua.substr(webkitIndex+7)) >= 419.3 ) ? 3 : 2 ) || 2;
	Standardizer.prototype.engineVersion  = parseFloat(ua.substr(webkitIndex+7));
}
// Opera
Standardizer.prototype.isOpera = (!Standardizer.prototype.isIE&&(ua.indexOf("Opera")>=0));
// Mozilla-compatible
Standardizer.prototype.isMozilla = (!Standardizer.prototype.isIE && !Standardizer.prototype.isFirefox && !Standardizer.prototype.isSafari && !Standardizer.prototype.isOpera && (ua.indexOf("Mozilla")>=0));
Standardizer.prototype.addEventListener = function(object) {
	if(!object.addEventListener)
		object.addEventListener = function addEventListener(type,listener,useCapture) {
			this.attachEvent("on"+type,listener);
		};
	if(!object.removeEventListener)
		object.removeEventListener = function removeEventListener(type,listener,useCapture) {
			this.detachEvent("on"+type,listener);
		};
};

Standardizer.prototype.removeEventListener = function(object) {
	if(object.removeEventListener) return;
	object.removeEventListener = function removeEventListener(type,listener,useCapture) {
		this.detachEvent("on"+type,listener);
	};
};

Standardizer.prototype.event = function(event) {
	if(window.event) {
		event = window.event;
		if(event.target) return event;
		if(event.srcElement) event.target = event.srcElement;
		if(event.preventDefault == undefined)
			event.preventDefault = function() { this.returnValue=false;};
	};
	return event;
};

Standardizer.prototype.getScrollTop = function() {
	return(this.isSafari?document.body.scrollTop:document.documentElement.scrollTop);
};

Standardizer.prototype.getScrollLeft = function() {
	return(this.isSafari?document.body.scrollLeft:document.documentElement.scrollLeft);
};

Standardizer.prototype.addLoadEventListener = function(fn) {
	if (jQuery.isFunction(fn))
		jQuery(fn);
};

Standardizer.prototype.addUnloadEventListener = function(fn) {
	if (jQuery.isFunction(fn))
		jQuery(document).bind('unload', fn);
};

Standardizer.prototype.querySelector = function(selector) {
	//if (document.querySelector) // Firefox 3.1+, IE8+, Webkit x.x+
	//	return document.querySelector(selector);
	if (typeof(selector) != 'string')
		return null;
	return jQuery(selector)[0];
	// NOTE: Possible side-effect:
	//       If you pass a html string as selector, jQuery function will return a new extended DOM node.
};

Standardizer.prototype.querySelectorAll = function(selector) {
	//if (document.querySelectorAll) // Firefox 3.1+, IE8+, Webkit x.x+
	//	return document.querySelectorAll(selector);
	if (typeof(selector) != 'string')
		return null;
	return jQuery(selector);
	// NOTE: Possible side-effect:
	//       If you pass a html string as selector, jQuery function will return a new extended DOM node.
};

var STD=new Standardizer();
STD.addEventListener(window);
var KeyCode = new function() {
	this.framework = "Eolin AJAX Framework";
	this.name = "Eolin LogViewer";
	this.verion = "1.0";
	this.copyright = "Copyright (c) 2005, Tatter & Company / Needlworks / Tatter Network Foundation. All rights reserved.";
	this.A=65;this.B=66;this.C=67;this.D=68;this.E=69;this.F=70;this.G=71;
	this.H=72;this.I=73;this.J=74;this.K=75;this.L=76;this.M=77;this.N=78;
	this.O=79;this.P=80;this.Q=81;this.R=82;this.S=83;this.T=84;this.U=85;
	this.V=86;this.W=87;this.X=88;this.Y=89;this.Z=90;
	this.Down=40;this.Up=38;this.Left=37;this.Right=39;
};

PageMaster.prototype.namespace = "Eolin.Application.Framework";
PageMaster.prototype.name      = "Eolin Page Master";
PageMaster.prototype.verion    = "1.0";
PageMaster.prototype.copyright = "Copyright (c) 2005, Tatter & Company / Needlworks / Tatter Network Foundation. All rights reserved.";
PageMaster.prototype.message   = "아직 처리중인 작업이 있습니다.";

function PageMaster() {
	this._status = null;
	this._messages = new Array();
	this._requests=new Array();
	this._holders=new Array();
	this._timer=null;
	window.addEventListener("load",PageMaster.prototype._onLoad,false);
	window.addEventListener("beforeunload",PageMaster.prototype._onBeforeUnload,false);
};

PageMaster.prototype._onLoad = function(event) {
	PM._status = document.createElement("div");
	PM._status.style.position = "absolute";
	PM._status.className = "ajaxMessage ajaxProcessingMessage";
	PM._status.style.color = "white";
	PM._status.style.backgroundColor = "navy";
	PM._status.style.margin = "0px";
	PM._status.style.paddingLeft = "10px";
	PM._status.style.paddingRight = "10px";
	STD.addEventListener(window);
	window.addEventListener("scroll",PageMaster.prototype._updateStatus,false);
	window.addEventListener("resize",PageMaster.prototype._updateStatus,false);
};

PageMaster.prototype._showStatus = function() {
	if(PM._status.parentNode == document.body) return;
	document.body.appendChild(this._status);
	this._updateStatus();
};

PageMaster.prototype._hideStatus = function(){
	if(PM._status.parentNode==document.body) document.body.removeChild(PM._status);
};

PageMaster.prototype._updateStatus=function() {
	if(PM._status.parentNode == document.body) {
		PM._status.style.top = (!STD.isSafari?document.documentElement.scrollTop:document.body.scrollTop)+"px";
		PM._status.style.left = ((!STD.isSafari?document.documentElement.scrollLeft:document.body.scrollLeft)+document.documentElement.clientWidth-PM._status.offsetWidth)+"px";
	};
	PM.updateMessages();
};

PageMaster.prototype.showMessage = function(message,align,valign,timeout) {
	if((typeof(message) != "string")||(message.length == 0)) return-1;
	if(align==undefined) align = "center";
	if(valign==undefined) valign="middle";
	if(timeout==undefined) timeout=3000;
	var oMessage = document.createElement("div");
	oMessage.innerHTML = message;oMessage.style.position="absolute";
	oMessage.className = "ajaxMessage ajaxSuccessMessage";
	oMessage.style.color = "white";
	oMessage.style.backgroundColor = "green";
	oMessage.style.margin = "0px";
	oMessage.style.paddingLeft = "10px";
	oMessage.style.paddingRight = "10px";
	oMessage._align = align;
	oMessage._valign = valign;
	document.body.appendChild(oMessage);

	var index=this._messages.push(oMessage)-1;

	this.updateMessages();
	window.setTimeout("PM._hideMessage("+index+")",timeout);

	return index;
};

PageMaster.prototype.showErrorMessage = function(message,align,valign,timeout) {
	if((typeof(message) != "string")||(message.length == 0)) return-1;
	if(align==undefined) align = "center";
	if(valign==undefined) valign="middle";
	if(timeout==undefined) timeout=3000;
	var oMessage = document.createElement("div");
	oMessage.innerHTML = message;oMessage.style.position="absolute";
	oMessage.className = "ajaxMessage ajaxErrorMessage";
	oMessage.style.color = "white";
	oMessage.style.backgroundColor = "red";
	oMessage.style.margin = "0px";
	oMessage.style.paddingLeft = "10px";
	oMessage.style.paddingRight = "10px";
	oMessage._align = align;
	oMessage._valign = valign;
	document.body.appendChild(oMessage);

	var index=this._messages.push(oMessage)-1;

	this.updateMessages();
	window.setTimeout("PM._hideMessage("+index+")",timeout);

	return index;
};

PageMaster.prototype._hideMessage = function(index) {
	document.body.removeChild(this._messages[index]);
	this._messages.splice(index,1,null);
	while((this._messages.length>0) && (this._messages[this._messages.length-1] == null))
		this._messages.pop();
};

PageMaster.prototype.updateMessages = function() {
	for(var i=0;i<this._messages.length;i++) {
		if(this._messages[i]==null) continue;
		switch(this._messages[i]._align) {
			case"left":
				this._messages[i].style.left = STD.getScrollLeft()+"px";
				break;
			case"center":
				this._messages[i].style.left = (STD.getScrollLeft()+(document.documentElement.clientWidth-this._messages[i].offsetWidth)/2)+"px";
				break;
			case"right":
				this._messages[i].style.left = (STD.getScrollLeft()+document.documentElement.clientWidth-this._messages[i].offsetWidth)+"px";
				break;
		};

		switch(this._messages[i]._valign) {
			case"top":
				this._messages[i].style.top = STD.getScrollTop()+"px";
				break;
			case"middle":
				this._messages[i].style.top = (STD.getScrollTop()+(document.documentElement.clientHeight-this._messages[i].offsetHeight)/2)+"px";
				break;
			case"bottom":
				this._messages[i].style.top = (STD.getScrollTop()+document.documentElement.clientHeight-this._messages[i].offsetHeight)+"px";
				break;
		};
	};
};

PageMaster.prototype.addRequest = function(request,message) {
	this._requests.push(new Array(request,message));
	if(this._status) {
		if(message!=undefined) {
			this._status.innerHTML+=message;this._showStatus();
		}
	}
};

PageMaster.prototype.removeRequest = function(request) {
	for(var i=0;i<this._requests.length;i++) {
		if(this._requests[i][0]==request) {
			this._requests.splice(i,1);
			break;
		};
	};

	var message="";

	for(var i=0;i<this._requests.length;i++) {
		if(this._requests[i][1]!=undefined) message+=this._requests[i][1];
	};
	if(this._status) {
		this._status.innerHTML=message;
		if(message.length==0) this._hideStatus();
		else this._updateStatus();
	};
};

PageMaster.prototype.addHolder = function(holder) {
	this._holders.push(holder);
};

PageMaster.prototype.removeHolder = function(holder) {
	for(var i=0;i<this._holders.length;i++) {
		if(this._holders[i]==holder) {
			this._holders.splice(i,1);
			return;
		};
	};
};

PageMaster.prototype.showPanel = function(panel,halign,valign) {
	try {
		if(typeof(panel)=="string") panel=document.getElementById(panel);
		if(typeof(panel)!="object") return;
		panel.style.position="absolute";
		panel.style.display="block";
		switch(halign) {
			case"left":
				panel.style.left = STD.getScrollLeft()+"px";
				break;
			default:
			case"center":
				panel.style.left = (STD.getScrollLeft()+(document.documentElement.clientWidth-panel.offsetWidth)/2)+"px";
				break;
			case"right":
				panel.style.left = (STD.getScrollLeft()+document.documentElement.clientWidth-panel.offsetWidth)+"px";
				break;
		};

		switch(valign) {
			case"top":
				panel.style.top = STD.getScrollTop()+"px";
				break;
			default:
			case"middle":
				panel.style.top = (STD.getScrollTop()+(document.documentElement.clientHeight-panel.offsetHeight)/2)+"px";
				break;
			case"bottom":
				panel.style.top=(STD.getScrollTop()+document.documentElement.clientHeight-panel.offsetHeight)+"px";
				break;
		};
	} catch(e){};
};

PageMaster.prototype._onBeforeUnload = function(event) {
	event = STD.event(event);
	if(PM._requests.length>0) {
		event.returnValue=PM.message;
		return;
	};
	for(var i=0;i<PM._holders.length;i++) {
		if(PM._holders[i].isHolding()) {
			event.returnValue=PM._holders[i].message;return;
		};
	};
};

var PM=new PageMaster();

HTTPRequest.prototype.namespace   = "Eolin.Application.Framework";
HTTPRequest.prototype.name        = "Eolin HTTPXMLRequest Processor";
HTTPRequest.prototype.verion      = "1.7";
HTTPRequest.prototype.copyright   = "Copyright (c) 2005, Tatter & Company / Needlworks / Tatter Network Foundation. All rights reserved.";
HTTPRequest.prototype.method      = "GET";
HTTPRequest.prototype.url         = null;
HTTPRequest.prototype.id          = null;
HTTPRequest.prototype.getfragment = "";
HTTPRequest.prototype.contentType = "application/x-www-form-urlencoded";
HTTPRequest.prototype.content     = "";
HTTPRequest.prototype.async       = true;
HTTPRequest.prototype.cache       = false;
HTTPRequest.prototype.persistent  = true;
HTTPRequest.prototype.timeout     = 0;
HTTPRequest.prototype.message     = "Requesting...";

HTTPRequest.prototype.onVerify = function() {
	return(this.getText("/response/error")==0);
};

HTTPRequest.prototype.onExecute = function(){};

HTTPRequest.prototype.onSuccess = function(){};

HTTPRequest.prototype.onError = function(){};

function HTTPRequest() {
	switch(arguments.length) {
		case 0:
			break;
		case 1:
			this.url=this.parseURL(arguments[0]);
			break;
		default:
		case 3:
			this.async=arguments[2];
		case 2:
			this.method=arguments[0];
			this.url=this.parseURL(arguments[1]);
			break;
	};
	try {
		this._request = new XMLHttpRequest();
	} catch(e) {
		var objectNames = ["MSXML2.XMLHTTP.5.0","MSXML2.XMLHTTP.4.0","MSXML2.XMLHTTP.3.0","MSXML2.XMLHTTP","Microsoft.XMLHTTP"];
		for(var i=0;i<objectNames.length;i++) {
			try{
				this._request=new ActiveXObject(objectNames[i]);
				break;
			} catch(e){};
		};

		if(this._request==null) {return null;};
	};

	this._properties = new Array();
	this._attributes = new Array();
	this._userData = new Array();
};

HTTPRequest.prototype.presetProperty = function(object,property,success,error) {
	if(error == undefined) {
		error=object[property];
		if(success==error) return;
	};
	object[property] = success;
	if(success == error) return;
	this._properties.push(new Array(object,property,error));
};

HTTPRequest.prototype.presetAttribute = function(object,attribute,success,error) {
	if(error==undefined) {
		error=object.getAttribute(attribute);
		if(success == error) return;
	};

	object.setAttribute(attribute,success);
	if(success == error) return;
	this._attributes.push(new Array(object,attribute,error));
};

HTTPRequest.prototype.send = function() {
	if(this.persistent) PM.addRequest(this);
	if(this.async) {
		var instance = this;
		this._request.onreadystatechange = function() {
			if(instance._request.readyState==4) {
				if(instance.persistent) PM.removeRequest(instance);
				if(instance.onVerify()) instance.onSuccess();
				else {
					for(var i in instance._properties)
						if (instance._properties[i] instanceof Array)
							instance._properties[i][0][instance._properties[i][1]] = instance._properties[i][2];
					for(var i in instance._attributes)
						if (instance._attributes[i] instanceof Array)
							instance._attributes[i][0].setAttribute(instance._attributes[i][1],instance._attributes[i][2]);
					instance.onError();
				};
			};
		};
	};
	if(this.method == 'GET'){
		if(this.getfragment.length > 0) {
			this.url=this.url+this.getfragment;
			if(this.id != null) {this.url=this.url+'&id='+this.id}
		} else {
			if(this.id != null) {this.url=this.url+'?id='+this.id}
		}
	}
	if(this.cache) this._request.open(this.method,this.url,this.async);
	else if(this.url.lastIndexOf("?") >= 0)
		this._request.open(this.method,this.url+"&__T__="+(new Date()).getTime(),this.async);
	else this._request.open(this.method,this.url+"?__T__="+(new Date()).getTime(),this.async);

	if(STD.isFirefox)
		this._request.setRequestHeader("Referer",location.href);
	if(arguments.length>0) {
		this.content=arguments[0];
	}
	if(this.content.length>0) this._request.setRequestHeader("Content-Type",this.contentType);
	if(this.timeout>0) {
		this._request.setRequestHeader("Connection","Keep-Alive");
		this._request.setRequestHeader("Keep-Alive","timeout="+this.timeout);
	}
	this._request.send(this.content);

	if(!this.async) {
		if(this.persistent) PM.removeRequest(this);
		if(this.onVerify()) this.onSuccess();
		else {
			for(var i in this._properties)
				if (this._properties[i] instanceof Array)
					this._properties[i][0][this._properties[i][1]] = this._properties[i][2];
			for(var i in this._attributes)
				if (this._attributes[i] instanceof Array)
					this._attributes[i][0].setAttribute(this._attributes[i][1],this._attributes[i][2]);
			this.onError();
		};
	};
};

HTTPRequest.prototype.getText = function(path) {
	try {
		if(path == undefined) return this._request.responseText;
		var directives=path.split("/");
		if(directives[0] != "") return null;
		var cursor = this._request.responseXML.documentElement;
		if(cursor.nodeName!=directives[1]) return null;
		for(var i=2;i<directives.length;i++) {
			for(var j=0;j<cursor.childNodes.length;j++) {
				if(cursor.childNodes[j].nodeName == directives[i]) {
					cursor=cursor.childNodes[j];
					j=-1;
					break;
				};
			};
			if(j!=-1) return null;
		};

		if(cursor.text) return cursor.text;
		return this._getText(cursor);
	} catch(e) {
		return null;
	};
};

HTTPRequest.prototype._getText = function(node) {
	var text="";
	if(node.nodeValue) text += node.nodeValue;
	for(var i=0;i<node.childNodes.length;i++) text += this._getText(node.childNodes[i]);
	return text;
};

HTTPRequest.prototype.parseURL = function(url) {
	return url;
};


HTTPRequest.prototype.setTimeout = function(time) {
	this.timeout = time;
};

FileUploadRequest.prototype.namespace  = "Eolin.Application.Framework";
FileUploadRequest.prototype.name       = "Eolin File Upload Request";
FileUploadRequest.prototype.verion     = "1.0";
FileUploadRequest.prototype.copyright  = "Copyright (c) 2005, Tatter & Company. All rights reserved.";
FileUploadRequest.prototype.message    = "Uploading...";
FileUploadRequest.prototype.autoDelete = false;

function FileUploadRequest(){};

FileUploadRequest.prototype.reset = function() {
	if(typeof(this._form)=="object") {
		STD.removeEventListener(this._form);
		this._form.removeEventListener("submit",FileUploadRequest.prototype._onsubmit,false);
	};
	if(typeof(this._target)=="object") {
		STD.removeEventListener(this._target);
		this._target.removeEventListener("load",FileUploadRequest.prototype._onload,false);
	};
};

FileUploadRequest.prototype.bind = function(form,target) {
	this.reset();
	switch(typeof(form)) {
		case"object":
			this._form = form;
			break;
		case"string":
			this._form = document.getElementById(form);
			if(this._form) break;
		default:
			return false;
	};

	switch(typeof(target)) {
		case"object":
			this._target = target;
			break;
		case"string":
			this._target = document.getElementById(target);
			if(this._target) break;
		default:
			return false;
	};

	if(this._form.target != this._target.name)
		this._form.target=this._target.name;
	STD.addEventListener(this._form);
	this._form.addEventListener("submit",FileUploadRequest.prototype._onsubmit,false);
	STD.addEventListener(this._target);

	this._form.upload = function() {
		PM.addRequest(this._instance,"Uploading...");
		this.submit();
	};

	this._target.addEventListener("load",FileUploadRequest.prototype._onload,false);
	this._form._instance = this;
	this._target._instance = this;
	return true;
};

FileUploadRequest.prototype._onsubmit = function(event) {
	event = STD.event(event);
	event.target._instance.setRunning(true);
};

FileUploadRequest.prototype._onload = function(event) {
	event = STD.event(event);
	var instance = event.target?event.target._instance:this._instance;PM.removeRequest(instance);
};

PageHolder.prototype.namespace  = "Eolin.Application.Framework";
PageHolder.prototype.name       = "Eolin Page Holder";
PageHolder.prototype.verion     = "1.0";
PageHolder.prototype.copyright  = "Copyright (c) 2005, Tatter & Company. All rights reserved.";
PageHolder.prototype.message    = "Wait..";
PageHolder.prototype.autoDelete = false;

function PageHolder(hold,message){
	PM.addHolder(this);
	switch(arguments.length) {
		default:
		case 2:
			this.message=message;
		case 1:
			this._holding=hold;
			break;
		case 0:
			this._holding=true;
			break;
	};
};

PageHolder.prototype.isHolding = function() {
	return this._holding;
};

PageHolder.prototype.hold = function() {
	this._holding=true;
};

PageHolder.prototype.release=function() {
	this._holding=false;
};
