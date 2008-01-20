//v1.0
//Copyright 2006 Adobe Systems, Inc. All rights reserved.
var sUserAgent = navigator.userAgent;
var fAppVersion = parseFloat(navigator.appVersion);

var isOpera = sUserAgent.indexOf("Opera") > -1;
var isIE = sUserAgent.indexOf("compatible") > -1 
           && sUserAgent.indexOf("MSIE") > -1
           && !isOpera;

function isExplore() {
	return sUserAgent.indexOf("compatible") > -1 
           && sUserAgent.indexOf("MSIE") > -1
           && !isOpera;	
}

function AC_AddExtension(src, ext)
{
  if (src.indexOf('?') != -1)
    return src.replace(/\?/, ext+'?'); 
  else
    return src + ext;
}

function AC_Generateobj(objAttrs, params, embedAttrs) 
{ 
	var str = '';
	if(isExplore()) {
		str += '<object ';
		for (var i in objAttrs)
			str += i + '="' + objAttrs[i] + '" ';
		str += '>';
		for (var i in params)
			str += '<param name="' + i + '" value="' + params[i] + '" /> ';	
	}
	str += '<embed ';
	for (var i in embedAttrs)
		str += i + '="' + embedAttrs[i] + '" ';
	str += ' ></embed>';
	if(isIE) {
		str += '</object>';
	}
  document.write(str);
}

function AC_GenerateobjNotWriteGetString(objAttrs, params, embedAttrs) 
{
  	var str = '';
	if(isExplore()) {
		str += '<object ';
		for (var i in objAttrs)
			str += i + '="' + objAttrs[i] + '" ';
		str += '>';
		for (var i in params)
			str += '<param name="' + i + '" value="' + params[i] + '" /> ';	
	}
	str += '<embed ';
	for (var i in embedAttrs)
		str += i + '="' + embedAttrs[i] + '" ';
	str += ' ></embed>';
	if(isIE) {
		str += '</object>';
	}
  return str;
}


function AC_FL_RunContent(){
  var ret = 
    AC_GetArgs
    (  arguments, ".swf", "movie", "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
     , "application/x-shockwave-flash"
    );
  AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs);
}

function AC_FL_RunContentNotWriteGetString(){
  var ret = 
    AC_GetArgs
    (  arguments, ".swf", "movie", "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
     , "application/x-shockwave-flash"
    );
  return AC_GenerateobjNotWriteGetString(ret.objAttrs, ret.params, ret.embedAttrs);
}

function insertObject(element, str) {
	document.getElementById(element).innerHTML = str;
}

function AC_GetArgs(args, ext, srcParamName, classid, mimeType){
  var ret = new Object();
  ret.embedAttrs = new Object();
  ret.params = new Object();
  ret.objAttrs = new Object();
  for (var i=0; i < args.length; i=i+2){
    var currArg = args[i].toLowerCase();    

    switch (currArg){	
      case "classid":
        break;
      case "pluginspage":
        ret.embedAttrs[args[i]] = args[i+1];
        break;
      case "src":
      case "movie":	
        args[i+1] = AC_AddExtension(args[i+1], ext);
        ret.embedAttrs["src"] = args[i+1];
        ret.params[srcParamName] = args[i+1];
        break;
      case "onafterupdate":
      case "onbeforeupdate":
      case "onblur":
      case "oncellchange":
      case "onclick":
      case "ondblClick":
      case "ondrag":
      case "ondragend":
      case "ondragenter":
      case "ondragleave":
      case "ondragover":
      case "ondrop":
      case "onfinish":
      case "onfocus":
      case "onhelp":
      case "onmousedown":
      case "onmouseup":
      case "onmouseover":
      case "onmousemove":
      case "onmouseout":
      case "onkeypress":
      case "onkeydown":
      case "onkeyup":
      case "onload":
      case "onlosecapture":
      case "onpropertychange":
      case "onreadystatechange":
      case "onrowsdelete":
      case "onrowenter":
      case "onrowexit":
      case "onrowsinserted":
      case "onstart":
      case "onscroll":
      case "onbeforeeditfocus":
      case "onactivate":
      case "onbeforedeactivate":
      case "ondeactivate":
      case "type":
      case "codebase":
        ret.objAttrs[args[i]] = args[i+1];
        break;
      case "width":
      case "height":
      case "align":
      case "vspace": 
      case "hspace":
      case "class":
      case "title":
      case "accesskey":
      case "name":
      case "id":
      case "tabindex":
        ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];
        break;
      default:
        ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
    }
  }
  ret.objAttrs["classid"] = classid;
  if (mimeType) ret.embedAttrs["type"] = mimeType;
  return ret;
}
//see below URL's comments to solve externalInterface in form problem 
//  http://livedocs.macromedia.com/flash/8/main/wwhelp/wwhimpl/common/html/wwhelp.htm?context=LiveDocs_Parts&file=00002200.html
//  http://egoing.net/268 (Korean)
function ExternalInterfaceManager() { 
	this.registerMovie = function(movieName) { 
		if(!window.fakeMovies) window.fakeMovies = new Array(); 
		window.fakeMovies[window.fakeMovies.length] = movieName; 
	} 
	this.initialize = function() { 
		if(document.all) {
			if(window.fakeMovies) { 
				for(i=0;i<window.fakeMovies.length;i++) { 
					window[window.fakeMovies[i]] = new Object(); 
				} 
				STD.addEventListener(window);
				window.addEventListener("load", initializeExternalInterface, false);
			} 
		} 
	} 
} 
function initializeExternalInterface() { 
	for(i=0;i<window.fakeMovies.length;i++) { 
		var movieName = window.fakeMovies[i]; 
		var fakeMovie = window[movieName]; 
		var realMovie = document.getElementById(movieName); 
		for(var method in fakeMovie) { 
			realMovie[method] = function() {
				flashFunction = "<invoke name=\"" + method.toString() + "\" returntype=\"javascript\">" + __flash__argumentsToXML(arguments, 0) + "</invoke>";this.CallFunction(flashFunction);
			} 
		} 
		window[movieName] = realMovie; 
	} 
}

function getVariableFromFlash(myFlashElementID, myVariableName){
	var myContent = "";
	if(document.all){
		//isIE
		myContent = document.all[myFlashElementID].getVariable(myVariableName);
	}else{
		//isNotIE
		myContent = document[myFlashElementID].GetVariable(myVariableName);
	}
	return myContent;
}	
