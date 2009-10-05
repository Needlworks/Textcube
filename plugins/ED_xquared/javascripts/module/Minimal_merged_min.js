/* Xquared is copyrighted free software by Alan Kang <jania902@gmail.com>.
 *  For more information, see http://xquared.springnote.com/
 */
if(!window.xq){var xq={}
}xq.majorVersion="0.82";
xq.minorVersion="20090423";
xq.compilePattern=function(C,B){if(!RegExp.prototype.compile){return new RegExp(C,B)
}var A=new RegExp();
A.compile(C,B);
return A
};
xq.Class=function(){var D=null,C=xq.$A(arguments),B;
if(typeof C[0]==="function"){D=C.shift()
}function A(){this.initialize.apply(this,arguments)
}if(D){for(B in D.prototype){A.prototype[B]=D.prototype[B]
}}for(B in C[0]){if(C[0].hasOwnProperty(B)){A.prototype[B]=C[0][B]
}}if(!A.prototype.initialize){A.prototype.initialize=function(){}
}A.prototype.constructor=A;
return A
};
xq.observe=function(B,A,C){if(B.addEventListener){B.addEventListener(A,C,false)
}else{B.attachEvent("on"+A,C)
}B=null
};
xq.stopObserving=function(B,A,C){if(B.removeEventListener){B.removeEventListener(A,C,false)
}else{B.detachEvent("on"+A,C)
}B=null
};
xq.cancelHandler=function(A){xq.stopEvent(A);
return false
};
xq.stopEvent=function(B,A){if(B.preventDefault){B.preventDefault()
}if(B.stopPropagation){B.stopPropagation()
}if(A){B.returnValue=A
}else{B.returnValue=false
}B.cancelBubble=true;
B.stopped=true
};
xq.isButton=function(B,A){return B.which?(B.which===A+1):(B.button===A)
};
xq.isLeftClick=function(A){return xq.isButton(A,0)
};
xq.isMiddleClick=function(A){return xq.isButton(A,1)
};
xq.isRightClick=function(A){return xq.isButton(A,2)
};
xq.getEventPoint=function(A){return{x:A.pageX||(A.clientX+(document.documentElement.scrollLeft||document.body.scrollLeft)),y:A.pageY||(A.clientY+(document.documentElement.scrollTop||document.body.scrollTop))}
};
xq.getCumulativeOffset=function(A,D){var C=0,B=0;
do{C+=A.offsetTop||0;
B+=A.offsetLeft||0;
A=A.offsetParent
}while(A&&A!=D);
return{top:C,left:B}
};
xq.$=function(A){return document.getElementById(A)
};
xq.isEmptyHash=function(B){for(var A in B){if(B.hasOwnProperty(A)){return false
}}return true
};
xq.emptyFunction=function(){};
xq.$A=function(C){var A=C.length,B=[];
while(A--){B[A]=C[A]
}return B
};
xq.addClassName=function(A,B){if(!xq.hasClassName(A,B)){A.className+=(A.className?" ":"")+B
}return A
};
xq.removeClassName=function(A,B){if(xq.hasClassName(A,B)){A.className=A.className.replace(new RegExp("(^|\\s+)"+B+"(\\s+|$)")," ").strip()
}return A
};
xq.hasClassName=function(A,B){var C=A.className;
return(C.length>0&&(C===B||new RegExp("(^|\\s)"+B+"(\\s|$)").test(C)))
};
xq.serializeForm=function(F){var I={hash:true};
var C={};
var A=F.getElementsByTagName("*");
for(var D=0;
D<A.length;
D++){var E=A[D];
var B=E.tagName.toLowerCase();
if(E.disabled||!E.name||["input","textarea","option","select"].indexOf(B)===-1){continue
}var H=E.name;
var G=xq.getValueOfElement(E);
if(G===undefined){continue
}if(H in C){if(C[H].constructor===Array){C[H]=[C[H]]
}C[H].push(G)
}else{C[H]=G
}}return C
};
xq.getValueOfElement=function(B){var A=B.type.toLowerCase();
if(A==="checkbox"||A==="radio"){return B.checked?B.value:undefined
}else{return B.value
}};
xq.getElementsByClassName=function(D,G,C){if(!C&&D.getElementsByClassName){return D.getElementsByClassName(G)
}var A=D.getElementsByTagName(C||"*");
var F=A.length;
var I=[];
var B=xq.compilePattern("(^|\\s)"+G+"($|\\s)","i");
for(var E=0;
E<F;
E++){var H=A[E];
if(B.test(H.className)){I.push(H)
}}return I
};
if(!window.Prototype){if(!Function.prototype.bind){Function.prototype.bind=function(){var B=this,A=xq.$A(arguments),C=A.shift();
return function(){return B.apply(C,A.concat(xq.$A(arguments)))
}
}
}if(!Function.prototype.bindAsEventListener){Function.prototype.bindAsEventListener=function(){var B=this,A=xq.$A(arguments),C=A.shift();
return function(D){return B.apply(C,[D||window.event].concat(A))
}
}
}Array.prototype.find=function(B){for(var A=0;
A<this.length;
A++){if(B(this[A])){return this[A]
}}};
Array.prototype.findAll=function(C){var A=[];
for(var B=0;
B<this.length;
B++){if(C(this[B])){A.push(this[B])
}}return A
};
Array.prototype.first=function(){return this[0]
};
Array.prototype.last=function(){return this[this.length-1]
};
Array.prototype.flatten=function(){var A=[];
var B=function(D){for(var C=0;
C<D.length;
C++){if(D[C].constructor===Array){B(D[C])
}else{A.push(D[C])
}}};
B(this);
return A
};
xq.pStripTags=xq.compilePattern("</?[^>]+>","gi");
String.prototype.stripTags=function(){return this.replace(xq.pStripTags,"")
};
String.prototype.escapeHTML=function(){xq.textNode.data=this;
return xq.divNode.innerHTML
};
String.prototype.unescapeHTML=function(){xq.divNode.innerHTML=this;
return xq.divNode.childNodes[0]?xq.divNode.childNodes[0].nodeValue:""
};
xq.textNode=document.createTextNode("");
xq.divNode=document.createElement("div");
xq.divNode.appendChild(xq.textNode);
xq.pStrip1=xq.compilePattern("^\\s+");
xq.pStrip2=xq.compilePattern("\\s+$");
String.prototype.strip=function(){return this.replace(xq.pStrip1,"").replace(xq.pStrip2,"")
};
Array.prototype.indexOf=function(B){for(var A=0;
A<this.length;
A++){if(this[A]===B){return A
}}return -1
}
}Array.prototype.includeElement=function(C){if(this.indexOf(C)!==-1){return true
}var B=false;
for(var A=0;
A<this.length;
A++){if(this[A]===C){return true
}}return false
};
xq.asEventSource=function(A,D,C){A.autoRegisteredEventListeners=[];
A.registerEventFirer=function(F,E){this["_fireOn"+E]=function(){for(var G=0;
G<this.autoRegisteredEventListeners.length;
G++){var I=this.autoRegisteredEventListeners[G];
var H=I["on"+F+E];
if(H){H.apply(I,xq.$A(arguments))
}}}
};
A.addListener=function(E){this.autoRegisteredEventListeners.push(E)
};
for(var B=0;
B<C.length;
B++){A.registerEventFirer(D,C[B])
}};
xq.json2element=function(A,B){var C=B.createElement("DIV");
C.innerHTML=xq.json2html(A);
return C.firstChild||{}
};
xq.element2json=function(D){var G,C,F;
if(D.nodeName==="DL"){G={};
F=xq.findChildElements(D);
for(C=0;
C<F.length;
C++){var E=F[C];
var B=F[++C];
G[E.innerHTML]=xq.element2json(xq.findChildElements(B)[0])
}return G
}else{if(D.nodeName==="OL"){G=[];
F=xq.findChildElements(D);
for(C=0;
C<F.length;
C++){var A=F[C];
G[C]=xq.element2json(xq.findChildElements(A)[0])
}}else{if(D.nodeName==="SPAN"&&D.className==="number"){return parseFloat(D.innerHTML)
}else{if(D.nodeName==="SPAN"&&D.className==="string"){return D.innerHTML
}else{return null
}}}}};
xq.json2html=function(A){var B=[];
xq._json2html(A,B);
return B.join("")
};
xq._json2html=function(C,D){if(typeof C==="number"){D.push('<span class="number">'+C+"</span>")
}else{if(typeof C==="string"){D.push('<span class="string">'+C.escapeHTML()+"</span>")
}else{if(C.constructor===Array){D.push("<ol>");
for(var B=0;
B<C.length;
B++){D.push("<li>");
xq._json2html(C[B],D);
D.push("</li>")
}D.push("</ol>")
}else{D.push("<dl>");
for(var A in C){if(C.hasOwnProperty(A)){D.push("<dt>"+A+"</dt>");
D.push("<dd>");
xq._json2html(C[A],D);
D.push("</dd>")
}}D.push("</dl>")
}}}};
xq.findChildElements=function(B){var D=B.childNodes;
var C=[];
for(var A=0;
A<D.length;
A++){if(D[A].nodeType===1){C.push(D[A])
}}return C
};
Date.preset=null;
Date.pass=function(A){if(Date.preset!==null){Date.preset=new Date(Date.preset.getTime()+A)
}};
Date.get=function(){return Date.preset===null?new Date():Date.preset
};
Date.prototype.elapsed=function(B,A){return(A||Date.get()).getTime()-this.getTime()>=B
};
String.prototype.merge=function(C){var B=this;
for(var A in C){if(C.hasOwnProperty(A)){B=B.replace("{"+A+"}",C[A])
}}return B
};
xq.pBlank=xq.compilePattern("^\\s*$");
String.prototype.isBlank=function(){return xq.pBlank.test(this)
};
xq.pURL=xq.compilePattern("((((\\w+)://(((([^@:]+)(:([^@]+))?)@)?([^:/\\?#]+)?(:(\\d+))?))?([^\\?#]+)?)(\\?([^#]+))?)(#(.+))?");
String.prototype.parseURL=function(){var E=this.match(xq.pURL);
var D=E[0];
var B=E[1]||undefined;
var M=E[2]||undefined;
var L=E[3]||undefined;
var A=null;
var N=E[4]||undefined;
var G=E[8]||undefined;
var K=E[10]||undefined;
var F=E[11]||undefined;
var C=E[13]||undefined;
var O=E[14]||undefined;
var J=E[16]||undefined;
var H=E[18]||undefined;
if(!O||O==="/"){A=L+"/"
}else{var I=O.lastIndexOf("/");
A=L+O.substring(0,I+1)
}return{includeAnchor:D,includeQuery:B,includePath:M,includeBase:A,includeHost:L,protocol:N,user:G,password:K,domain:F,port:C,path:O,query:J,anchor:H}
};
xq.commonAttrs=["title","class","id","style"];
xq.predefinedWhitelist={a:xq.commonAttrs.concat("href","charset","rev","rel","type","hreflang","tabindex"),abbr:xq.commonAttrs.concat(),acronym:xq.commonAttrs.concat(),address:xq.commonAttrs.concat(),blockquote:xq.commonAttrs.concat("cite"),br:xq.commonAttrs.concat(),button:xq.commonAttrs.concat("disabled","type","name","value"),caption:xq.commonAttrs.concat(),cite:xq.commonAttrs.concat(),code:xq.commonAttrs.concat(),dd:xq.commonAttrs.concat(),dfn:xq.commonAttrs.concat(),div:xq.commonAttrs.concat(),dl:xq.commonAttrs.concat(),dt:xq.commonAttrs.concat(),em:xq.commonAttrs.concat(),embed:xq.commonAttrs.concat("src","width","height","allowscriptaccess","type","allowfullscreen","bgcolor"),h1:xq.commonAttrs.concat(),h2:xq.commonAttrs.concat(),h3:xq.commonAttrs.concat(),h4:xq.commonAttrs.concat(),h5:xq.commonAttrs.concat(),h6:xq.commonAttrs.concat(),hr:xq.commonAttrs.concat(),iframe:xq.commonAttrs.concat("name","src","frameborder","scrolling","width","height","longdesc"),input:xq.commonAttrs.concat("type","name","value","size","checked","readonly","src","maxlength"),img:xq.commonAttrs.concat("alt","width","height","src","longdesc"),label:xq.commonAttrs.concat("for"),kbd:xq.commonAttrs.concat(),li:xq.commonAttrs.concat(),object:xq.commonAttrs.concat("align","classid","codetype","archive","width","type","codebase","height","data","name","standby","declare"),ol:xq.commonAttrs.concat(),option:xq.commonAttrs.concat("disabled","selected","label","value"),p:xq.commonAttrs.concat(),param:xq.commonAttrs.concat("name","value","valuetype","type"),pre:xq.commonAttrs.concat(),q:xq.commonAttrs.concat("cite"),samp:xq.commonAttrs.concat(),script:xq.commonAttrs.concat("src","type"),select:xq.commonAttrs.concat("disabled","size","multiple","name"),span:xq.commonAttrs.concat(),sup:xq.commonAttrs.concat(),sub:xq.commonAttrs.concat(),strong:xq.commonAttrs.concat(),table:xq.commonAttrs.concat("summary","width"),thead:xq.commonAttrs.concat(),textarea:xq.commonAttrs.concat("cols","disabled","rows","readonly","name"),tbody:xq.commonAttrs.concat(),th:xq.commonAttrs.concat("colspan","rowspan"),td:xq.commonAttrs.concat("colspan","rowspan"),tr:xq.commonAttrs.concat(),tt:xq.commonAttrs.concat(),ul:xq.commonAttrs.concat(),"var":xq.commonAttrs.concat()};
xq.autoFinalizeQueue=[];
xq.addToFinalizeQueue=function(A){xq.autoFinalizeQueue.push(A)
};
xq.finalize=function(C){if(typeof C.finalize==="function"){try{C.finalize()
}catch(A){}}for(var B in C){if(C.hasOwnProperty(B)){C[B]=null
}}};
xq.observe(window,"unload",function(){if(xq&&xq.autoFinalizeQueue){for(var A=0;
A<xq.autoFinalizeQueue.length;
A++){xq.finalize(xq.autoFinalizeQueue[A])
}xq=null
}});
xq.findXquaredScript=function(){return xq.$A(document.getElementsByTagName("script")).find(function(A){return A.src&&A.src.match(/xquared\.js/i)
})
};
xq.shouldLoadOthers=function(){var A=xq.findXquaredScript();
return A&&!!A.src.match(/xquared\.js\?load_others=1/i)
};
xq.loadScript=function(A){document.write('<script type="text/javascript" src="'+A+'"><\/script>')
};
xq.getXquaredScriptFileNames=function(){return["Xquared.js","Browser.js","DomTree.js","rdom/Base.js","rdom/W3.js","rdom/Gecko.js","rdom/Webkit.js","rdom/Trident.js","rdom/Factory.js","validator/Base.js","validator/W3.js","validator/Gecko.js","validator/Webkit.js","validator/Trident.js","validator/Factory.js","macro/Base.js","macro/Factory.js","macro/FlashMovieMacro.js","macro/IFrameMacro.js","macro/JavascriptMacro.js","EditHistory.js","plugin/Base.js","RichTable.js","Timer.js","Layer.js","ui/Base.js","ui/Control.js","ui/Toolbar.js","ui/_templates.js","Json2.js","Shortcut.js","Editor.js","Messages.js"]
};
xq.getXquaredScriptBasePath=function(){var A=xq.findXquaredScript();
return A.src.match(/(.*\/)xquared\.js.*/i)[1]
};
xq.loadOthers=function(){var C=xq.getXquaredScriptBasePath();
var B=xq.getXquaredScriptFileNames();
for(var A=1;
A<B.length;
A++){xq.loadScript(C+B[A])
}};
if(xq.shouldLoadOthers()){xq.loadOthers()
}xq.Browser=new function(){this.language=(navigator.appName=="Netscape")?navigator.language:navigator.browserLanguage;
this.isTrident=navigator.appName==="Microsoft Internet Explorer",this.isWebkit=navigator.userAgent.indexOf("AppleWebKit/")>-1,this.isGecko=navigator.userAgent.indexOf("Gecko")>-1&&navigator.userAgent.indexOf("KHTML")===-1,this.isKHTML=navigator.userAgent.indexOf("KHTML")!==-1,this.isPresto=navigator.appName==="Opera",this.isMac=navigator.userAgent.indexOf("Macintosh")!==-1,this.isUbuntu=navigator.userAgent.indexOf("Ubuntu")!==-1,this.isWin=navigator.userAgent.indexOf("Windows")!==-1,this.isIE=navigator.appName==="Microsoft Internet Explorer",this.isIE6=navigator.userAgent.indexOf("MSIE 6")!==-1,this.isIE7=navigator.userAgent.indexOf("MSIE 7")!==-1,this.isIE8=navigator.userAgent.indexOf("MSIE 8")!==-1,this.isFF=navigator.userAgent.indexOf("Firefox")!==-1,this.isFF2=navigator.userAgent.indexOf("Firefox/2")!==-1,this.isFF3=navigator.userAgent.indexOf("Firefox/3")!==-1,this.isSafari=navigator.userAgent.indexOf("Safari")!==-1
};
xq.Timer=xq.Class({initialize:function(A){xq.addToFinalizeQueue(this);
this.precision=A;
this.jobs={};
this.nextJobId=0;
this.checker=null
},finalize:function(){this.stop()
},start:function(){this.stop();
this.checker=window.setInterval(function(){this.executeJobs()
}.bind(this),this.precision)
},stop:function(){if(this.checker){window.clearInterval(this.checker)
}},register:function(C,B){var A=this.nextJobId++;
this.jobs[A]={func:C,interval:B,lastExecution:Date.get()};
return A
},unregister:function(A){delete this.jobs[A]
},executeJobs:function(){var A=new Date();
for(var D in this.jobs){var B=this.jobs[D];
if(B.lastExecution.elapsed(B.interval,A)){try{B.lastReturn=B.func()
}catch(C){B.lastException=C
}finally{B.lastExecution=A
}}}}});
xq.DomTree=xq.Class({initialize:function(){xq.addToFinalizeQueue(this);
this._blockTags=["DIV","DD","LI","ADDRESS","CAPTION","DT","H1","H2","H3","H4","H5","H6","HR","P","BODY","BLOCKQUOTE","PRE","PARAM","DL","OL","UL","TABLE","THEAD","TBODY","TR","TH","TD"];
this._blockContainerTags=["DIV","DD","LI","BODY","BLOCKQUOTE","UL","OL","DL","TABLE","THEAD","TBODY","TR","TH","TD"];
this._listContainerTags=["OL","UL","DL"];
this._tableCellTags=["TH","TD"];
this._blockOnlyContainerTags=["BODY","BLOCKQUOTE","UL","OL","DL","TABLE","THEAD","TBODY","TR"];
this._atomicTags=["IMG","OBJECT","PARAM","BR","HR"]
},getBlockTags:function(){return this._blockTags
},findCommonAncestorAndImmediateChildrenOf:function(E,C){if(E.parentNode===C.parentNode){return{left:E,right:C,parent:E.parentNode}
}else{var D=this.collectParentsOf(E,true);
var G=this.collectParentsOf(C,true);
var B=this.getCommonAncestor(D,G);
var F=D.find(function(H){return H.parentNode===B
});
var A=G.find(function(H){return H.parentNode===B
});
return{left:F,right:A,parent:B}
}},getLeavesAtEdge:function(C){if(!C.hasChildNodes()){return[null,null]
}var D=function(G){for(var F=0;
F<G.childNodes.length;
F++){if(G.childNodes[F].nodeType===1&&this.isBlock(G.childNodes[F])){return D(G.childNodes[F])
}}return G
}.bind(this);
var B=function(G){for(var F=G.childNodes.length;
F--;
){if(G.childNodes[F].nodeType===1&&this.isBlock(G.childNodes[F])){return B(G.childNodes[F])
}}return G
}.bind(this);
var E=D(C);
var A=B(C);
return[E===C?null:E,A===C?null:A]
},getCommonAncestor:function(B,A){for(var D=0;
D<B.length;
D++){for(var C=0;
C<A.length;
C++){if(B[D]===A[C]){return B[D]
}}}},collectParentsOf:function(D,C,A){var B=[];
if(C){B.push(D)
}while((D=D.parentNode)&&(D.nodeName!=="HTML")&&!(typeof A==="function"&&A(D))){B.push(D)
}return B
},isDescendantOf:function(B,C){if(B.length>0){for(var A=0;
A<B.length;
A++){if(this.isDescendantOf(B[A],C)){return true
}}return false
}if(B===C){return false
}while(C=C.parentNode){if(C===B){return true
}}return false
},walkForward:function(A){var B=A.firstChild;
if(B){return B
}if(B=A.nextSibling){return B
}while(A=A.parentNode){if(B=A.nextSibling){return B
}}return null
},walkBackward:function(A){if(A.previousSibling){A=A.previousSibling;
while(A.hasChildNodes()){A=A.lastChild
}return A
}return A.parentNode
},walkNext:function(A){return A.nextSibling
},walkPrev:function(A){return A.previousSibling
},checkTargetForward:function(B,A){return this._check(B,this.walkForward,A)
},checkTargetBackward:function(B,A){return this._check(B,this.walkBackward,A)
},findForward:function(C,B,A){return this._find(C,this.walkForward,B,A)
},findBackward:function(C,B,A){return this._find(C,this.walkBackward,B,A)
},_check:function(C,B,A){if(C===A){return false
}while(C=B(C)){if(C===A){return true
}}return false
},_find:function(D,B,C,A){while(D=B(D)){if(A&&A(D)){return null
}if(C(D)){return D
}}return null
},collectNodesBetween:function(D,A,C){if(D===A){return[D,A].findAll(C||function(){return true
})
}var B=this.collectForward(D,function(E){return E===A
},C);
if(D!==A&&typeof C==="function"&&C(A)){B.push(A)
}return B
},collectForward:function(C,A,B){return this.collect(C,this.walkForward,A,B)
},collectBackward:function(C,A,B){return this.collect(C,this.walkBackward,A,B)
},collectNext:function(C,A,B){return this.collect(C,this.walkNext,A,B)
},collectPrev:function(C,A,B){return this.collect(C,this.walkPrev,A,B)
},collect:function(E,D,A,C){var B=[E];
while(true){E=D(E);
if((E===null)||(typeof A==="function"&&A(E))){break
}B.push(E)
}return(typeof C==="function")?B.findAll(C):B
},hasBlocks:function(C){var A=C.childNodes;
for(var B=0;
B<A.length;
B++){if(this.isBlock(A[B])){return true
}}return false
},hasMixedContents:function(C){if(!this.isBlock(C)){return false
}if(!this.isBlockContainer(C)){return false
}var A=false;
var E=false;
for(var B=0;
B<C.childNodes.length;
B++){var D=C.childNodes[B];
if(!A&&this.isTextOrInlineNode(D)){A=true
}if(!E&&this.isBlock(D)){E=true
}if(A&&E){break
}}if(!A||!E){return false
}return true
},isBlockOnlyContainer:function(A){if(!A){return false
}return this._blockOnlyContainerTags.indexOf(typeof A==="string"?A:A.nodeName)!==-1
},isTableCell:function(A){if(!A){return false
}return this._tableCellTags.indexOf(typeof A==="string"?A:A.nodeName)!==-1
},isBlockContainer:function(A){if(!A){return false
}return this._blockContainerTags.indexOf(typeof A==="string"?A:A.nodeName)!==-1
},isHeading:function(A){if(!A){return false
}return(typeof A==="string"?A:A.nodeName).match(/H\d/)
},isBlock:function(A){if(!A){return false
}return this._blockTags.indexOf(typeof A==="string"?A:A.nodeName)!==-1
},isAtomic:function(A){if(!A){return false
}return this._atomicTags.indexOf(typeof A==="string"?A:A.nodeName)!==-1
},isListContainer:function(A){if(!A){return false
}return this._listContainerTags.indexOf(typeof A==="string"?A:A.nodeName)!==-1
},isTextOrInlineNode:function(A){return A&&(A.nodeType===3||!this.isBlock(A))
}});
xq.rdom={};
xq.rdom.Base=xq.Class({initialize:function(){xq.addToFinalizeQueue(this);
this.tree=new xq.DomTree();
this.focused=false;
this._lastMarkerId=0
},setWin:function(A){if(!A){throw"[win] is null"
}this.win=A
},setRoot:function(A){if(!A){throw"[root] is null"
}this.root=A
},getWin:function(){return this.win||(this.root?(this.root.ownerDocument.defaultView||this.root.ownerDocument.parentWindow):window)
},getRoot:function(){return this.root||this.win.document.body
},getDoc:function(){return this.getWin().document||this.getRoot().ownerDocument
},clearRoot:function(){this.getRoot().innerHTML="";
this.getRoot().appendChild(this.makeEmptyParagraph())
},removePlaceHoldersAndEmptyNodes:function(C){if(!C.hasChildNodes()){return 
}var A=this.getBottommostLastChild(C);
if(!A){return 
}A=this.tree.walkForward(A);
while(C&&C!==A){if(this.isPlaceHolder(C)||(C.nodeType===3&&(C.nodeValue===""||(!C.nextSibling&&C.nodeValue.isBlank())))){var B=C;
C=this.tree.walkForward(C);
this.deleteNode(B)
}else{C=this.tree.walkForward(C)
}}},setAttributes:function(B,C){for(var A in C){B.setAttribute(A,C[A])
}},createTextNode:function(A){return this.getDoc().createTextNode(A)
},createElement:function(A){return this.getDoc().createElement(A)
},createElementFromHtml:function(A){var B=this.createElement("div");
B.innerHTML=A;
if(B.childNodes.length!==1){throw"Illegal HTML fragment"
}return this.getFirstChild(B)
},deleteNode:function(D,A,C){if(!D||!D.parentNode){return 
}if(D.nodeName==="BODY"){throw"Cannot delete BODY"
}var B=D.parentNode;
B.removeChild(D);
if(A){while(!B.hasChildNodes()){D=B;
B=D.parentNode;
if(!B||this.getRoot()===D){break
}B.removeChild(D)
}}if(C&&this.isEmptyBlock(B)){B.innerHTML="";
this.correctEmptyElement(B)
}},insertNode:function(A){throw"Not implemented"
},insertHtml:function(A){return this.insertNode(this.createElementFromHtml(A))
},insertText:function(A){this.insertNode(this.createTextNode(A))
},insertNodeAt:function(B,F,E,D){if(["HTML","HEAD"].indexOf(F.nodeName)!==-1||"BODY"===F.nodeName&&["before","after"].indexOf(E)!==-1){throw"Illegal argument. Cannot move node["+B.nodeName+"] to '"+E+"' of target["+F.nodeName+"]"
}var C;
var I;
var G;
switch(E.toLowerCase()){case"before":C=F.parentNode;
I="insertBefore";
G=F;
break;
case"start":if(F.firstChild){C=F;
I="insertBefore";
G=F.firstChild
}else{C=F;
I="appendChild"
}break;
case"end":C=F;
I="appendChild";
break;
case"after":if(F.nextSibling){C=F.parentNode;
I="insertBefore";
G=F.nextSibling
}else{C=F.parentNode;
I="appendChild"
}break
}if(D&&this.tree.isListContainer(C)&&B.nodeName!=="LI"){var H=this.createElement("LI");
H.appendChild(B);
B=H;
C[I](B,G)
}else{if(D&&!this.tree.isListContainer(C)&&B.nodeName==="LI"){this.wrapAllInlineOrTextNodesAs("P",B,true);
var A=this.createElement("DIV");
this.moveChildNodes(B,A);
this.deleteNode(B);
C[I](A,G);
B=this.unwrapElement(A,true)
}else{C[I](B,G)
}}return B
},insertTextAt:function(C,B,A){return this.insertNodeAt(this.createTextNode(C),B,A)
},insertHtmlAt:function(B,C,A){return this.insertNodeAt(this.createElementFromHtml(B),C,A)
},replaceTag:function(A,B){if(B.nodeName===A){return null
}if(this.tree.isTableCell(B)){return null
}var C=this.createElement(A);
this.moveChildNodes(B,C);
this.copyAttributes(B,C,true);
B.parentNode.replaceChild(C,B);
if(!C.hasChildNodes()){this.correctEmptyElement(C)
}return C
},unwrapUnnecessaryParagraph:function(A){if(!A){return false
}if(!this.tree.isBlockOnlyContainer(A)&&A.childNodes.length===1&&A.firstChild.nodeName==="P"&&!this.hasImportantAttributes(A.firstChild)){var B=A.firstChild;
this.moveChildNodes(B,A);
this.deleteNode(B);
return true
}return false
},unwrapElement:function(B,A){if(A){this.wrapAllInlineOrTextNodesAs("P",B)
}var C=B.firstChild;
while(B.firstChild){this.insertNodeAt(B.firstChild,B,"before")
}this.deleteNode(B);
return C
},wrapElement:function(A,B){var C=this.insertNodeAt(this.createElement(A),B,"before");
C.appendChild(B);
return C
},testSmartWrap:function(A,B){return this.smartWrap(A,null,B,true)
},smartWrap:function(G,S,F,R){var H=this.getParentBlockElementOf(G);
S=S||"SPAN";
F=F||function(T){return -1
};
if(!R&&(!G.previousSibling||this.isEmptyBlock(H))){var E=this.insertNodeAt(this.createElement(S),G,"before");
return E
}var B=this.tree.collectForward(H,function(T){return T===G
},function(T){return T.nodeType===3
});
var M=0;
var Q=[];
for(var L=0;
L<B.length;
L++){Q.push(B[L].nodeValue)
}var P=Q.join("");
var N=F(P);
var C=N;
if(C===-1){C=0
}else{P=P.substring(C)
}for(var L=0;
L<B.length;
L++){if(C>Q[L].length){C-=Q[L].length
}else{M=L;
break
}}if(R){return{text:P,textIndex:N,nodeIndex:M,breakPoint:C}
}if(C!==0){var I=B[M].splitText(C);
M++;
B.splice(M,0,I)
}var A=B[M]||H.firstChild;
var O=this.tree.findCommonAncestorAndImmediateChildrenOf(A,G);
var K=O.parent;
if(K){if(A.parentNode!==K){A=this.splitElementUpto(A,K,true)
}if(G.parentNode!==K){G=this.splitElementUpto(G,K,true)
}var D=A.previousSibling;
var J=G.nextSibling;
if(D&&D.nodeType===1&&this.isEmptyBlock(D)){this.deleteNode(D)
}if(J&&J.nodeType===1&&this.isEmptyBlock(J)){this.deleteNode(J)
}var E=this.insertNodeAt(this.createElement(S),A,"before");
while(E.nextSibling!==G){E.appendChild(E.nextSibling)
}return E
}else{var E=this.insertNodeAt(this.createElement(S),G,"before");
return E
}},wrapAllInlineOrTextNodesAs:function(A,B,E){var D=[];
if(!E&&!this.tree.hasMixedContents(B)){return D
}var C=B.firstChild;
while(C){if(this.tree.isTextOrInlineNode(C)){var F=this.wrapInlineOrTextNodesAs(A,C);
D.push(F);
C=F.nextSibling
}else{C=C.nextSibling
}}return D
},wrapInlineOrTextNodesAs:function(A,B){var D=this.createElement(A);
var C=B;
C.parentNode.replaceChild(D,C);
D.appendChild(C);
while(D.nextSibling&&this.tree.isTextOrInlineNode(D.nextSibling)){D.appendChild(D.nextSibling)
}return D
},turnElementIntoListItem:function(C,E,D){E=E.toUpperCase();
D=D||"";
var B=this.createElement(E);
if(D){B.className=D
}if(this.tree.isTableCell(C)){var F=this.wrapAllInlineOrTextNodesAs("P",C,true)[0];
B=this.insertNodeAt(B,C,"start");
var A=this.insertNodeAt(this.createElement("LI"),B,"start");
A.appendChild(F)
}else{B=this.insertNodeAt(B,C,"after");
var A=this.insertNodeAt(this.createElement("LI"),B,"start");
A.appendChild(C)
}this.unwrapUnnecessaryParagraph(A);
this.mergeAdjustLists(B);
return A
},extractOutElementFromParent:function(B){if(!B||B===this.getRoot()||B.parentNode===this.getRoot()||!B.offsetParent){return null
}if(B.nodeName==="LI"){this.wrapAllInlineOrTextNodesAs("P",B,true);
B=B.firstChild
}if(!B){return null
}var A=B.parentNode;
var D=null;
if(A.nodeName==="LI"&&A.parentNode.parentNode.nodeName==="LI"){if(B.previousSibling){this.splitContainerOf(B,true);
this.correctEmptyElement(B)
}this.outdentListItem(B);
D=B
}else{if(A.nodeName==="LI"){if(this.tree.isListContainer(B.nextSibling)){var E=A.parentNode;
this.splitContainerOf(A,true);
this.correctEmptyElement(B);
D=A.firstChild;
while(A.firstChild){this.insertNodeAt(A.firstChild,E,"before")
}var C=E.previousSibling;
this.deleteNode(E);
if(C&&this.tree.isListContainer(C)){this.mergeAdjustLists(C)
}}else{this.splitContainerOf(B,true);
this.correctEmptyElement(B);
var E=this.splitContainerOf(A);
this.insertNodeAt(B,E.parentNode,"before");
this.deleteNode(E.parentNode);
D=B
}}else{if(this.tree.isTableCell(A)||this.tree.isTableCell(B)){}else{this.splitContainerOf(B,true);
this.correctEmptyElement(B);
D=this.insertNodeAt(B,A,"before");
this.deleteNode(A)
}}}return D
},insertNewBlockAround:function(E,D,B){var C=E.nodeName==="LI"||E.parentNode.nodeName==="LI";
this.removeTrailingWhitespace(E);
if(this.isFirstLiWithNestedList(E)&&!B&&D){var A=this.getParentElementOf(E,["LI"]);
var F=this._insertNewBlockAround(A,D);
return F
}else{if(C&&!B){var A=this.getParentElementOf(E,["LI"]);
var F=this._insertNewBlockAround(E,D);
if(A!==E){F=this.splitContainerOf(F,false,"prev")
}return F
}else{if(this.tree.isBlockContainer(E)){this.wrapAllInlineOrTextNodesAs("P",E,true);
return this._insertNewBlockAround(E.firstChild,D,B)
}else{return this._insertNewBlockAround(E,D,this.tree.isHeading(E)?"P":B)
}}}},_insertNewBlockAround:function(B,C,A){var D=this.createElement(A||B.nodeName);
this.copyAttributes(B,D,false);
this.correctEmptyElement(D);
D=this.insertNodeAt(D,B,C?"before":"after");
return D
},applyTagIntoElement:function(B,C,D){if(!B&&!D){return null
}var A=C;
if(B){if(this.tree.isBlockOnlyContainer(B)){A=this.wrapBlock(B,C)
}else{if(this.tree.isBlockContainer(C)){var E=this.createElement(B);
this.moveChildNodes(C,E);
A=this.insertNodeAt(E,C,"start")
}else{if(this.tree.isBlockContainer(B)&&this.hasImportantAttributes(C)){A=this.wrapBlock(B,C)
}else{A=this.replaceTag(B,C)
}}}}if(D){A.className=D
}return A
},applyTagIntoElements:function(C,L,M,J){if(!C&&!J){return[L,M]
}var F=[];
if(C){if(this.tree.isBlockContainer(C)){var H=this.tree.findCommonAncestorAndImmediateChildrenOf(L,M);
var D=H.left;
var B=this.insertNodeAt(this.createElement(C),D,"before");
var N=H.parent.nodeName==="LI"&&H.parent.parentNode.childNodes.length===1&&!H.left.previousSilbing&&!H.right.nextSibling;
if(N){var I=D.parentNode.parentNode;
this.insertNodeAt(B,I,"before");
B.appendChild(I)
}else{while(D!==H.right){next=D.nextSibling;
B.appendChild(D);
D=next
}B.appendChild(H.right)
}F.push(B)
}else{var A=this.getBlockElementsBetween(L,M);
for(var G=0;
G<A.length;
G++){if(this.tree.isBlockContainer(A[G])){var K=this.wrapAllInlineOrTextNodesAs(C,A[G],true);
for(var E=0;
E<K.length;
E++){F.push(K[E])
}}else{F.push(this.replaceTag(C,A[G])||A[G])
}}}}if(J){var A=this.tree.collectNodesBetween(L,M,function(O){return O.nodeType==1
});
for(var G=0;
G<A.length;
G++){A[G].className=J
}}return F
},moveBlock:function(H,A){H=this.getParentElementOf(H,["TR"])||H;
while(H.nodeName!=="TR"&&H.parentNode!==this.getRoot()&&!H.previousSibling&&!H.nextSibling&&!this.tree.isListContainer(H.parentNode)){H=H.parentNode
}var G,B;
if(A){G=H.previousSibling;
if(G){var F=G.nodeName==="LI"&&((G.childNodes.length===1&&this.tree.isBlock(G.firstChild))||!this.tree.hasBlocks(G));
var E=["TABLE","TR"].indexOf(G.nodeName)!==-1;
B=this.tree.isBlockContainer(G)&&!F&&!E?"end":"before"
}else{if(H.parentNode!==this.getRoot()){G=H.parentNode;
B="before"
}}}else{G=H.nextSibling;
if(G){var F=G.nodeName==="LI"&&((G.childNodes.length===1&&this.tree.isBlock(G.firstChild))||!this.tree.hasBlocks(G));
var E=["TABLE","TR"].indexOf(G.nodeName)!==-1;
B=this.tree.isBlockContainer(G)&&!F&&!E?"start":"after"
}else{if(H.parentNode!==this.getRoot()){G=H.parentNode;
B="after"
}}}if(!G){return null
}if(["TBODY","THEAD"].indexOf(G.nodeName)!==-1){return null
}this.wrapAllInlineOrTextNodesAs("P",G,true);
if(this.isFirstLiWithNestedList(H)){this.insertNewBlockAround(H,false,"P")
}var D=H.parentNode;
var C=this.insertNodeAt(H,G,B,true);
if(!D.hasChildNodes()){this.deleteNode(D,true)
}this.unwrapUnnecessaryParagraph(C);
this.unwrapUnnecessaryParagraph(G);
if(A){if(C.previousSibling&&this.isEmptyBlock(C.previousSibling)&&!C.previousSibling.previousSibling&&C.parentNode.nodeName==="LI"&&this.tree.isListContainer(C.nextSibling)){this.deleteNode(C.previousSibling)
}}else{if(C.nextSibling&&this.isEmptyBlock(C.nextSibling)&&!C.previousSibling&&C.parentNode.nodeName==="LI"&&this.tree.isListContainer(C.nextSibling.nextSibling)){this.deleteNode(C.nextSibling)
}}this.correctEmptyElement(C);
return C
},removeBlock:function(E){var D;
while(E.parentNode!==this.getRoot()&&!E.previousSibling&&!E.nextSibling&&!this.tree.isListContainer(E.parentNode)){E=E.parentNode
}var C=function(F){return this.tree.isBlock(F)&&!this.tree.isAtomic(F)&&!this.tree.isDescendantOf(E,F)&&!this.tree.hasBlocks(F)
}.bind(this);
var A=function(F){return this.tree.isBlock(F)&&!this.tree.isDescendantOf(this.getRoot(),F)
}.bind(this);
if(this.isFirstLiWithNestedList(E)){D=this.outdentListItem(E.nextSibling.firstChild);
this.deleteNode(D.previousSibling,true)
}else{if(this.tree.isTableCell(E)){var B=new xq.RichTable(this,this.getParentElementOf(E,["TABLE"]));
D=B.getBelowCellOf(E);
if(E.parentNode.parentNode.nodeName==="TBODY"&&B.hasHeadingAtTop()&&B.getDom().tBodies[0].rows.length===1){return D
}D=D||this.tree.findForward(E,C,A)||this.tree.findBackward(E,C,A);
this.deleteNode(E.parentNode,true)
}else{D=D||this.tree.findForward(E,C,A)||this.tree.findBackward(E,C,A);
if(!D){D=this.insertNodeAt(this.makeEmptyParagraph(),E,"after")
}this.deleteNode(E,true)
}}if(!this.getRoot().hasChildNodes()){D=this.createElement("P");
this.getRoot().appendChild(D);
this.correctEmptyElement(D)
}return D
},removeTrailingWhitespace:function(A){throw"Not implemented"
},changeListTypeTo:function(C,E,D){E=E.toUpperCase();
D=D||"";
var A=this.getParentElementOf(C,["LI"]);
if(!A){throw"IllegalArgumentException"
}var B=A.parentNode;
this.splitContainerOf(A);
var F=this.insertNodeAt(this.createElement(E),B,"before");
if(D){F.className=D
}this.insertNodeAt(A,F,"start");
this.deleteNode(B);
this.mergeAdjustLists(F);
return C
},splitContainerOf:function(C,F,B){if([C,C.parentNode].indexOf(this.getRoot())!==-1){return C
}var A=C.parentNode;
if(C.previousSibling&&(!B||B.toLowerCase()==="prev")){var E=this.createElement(A.nodeName);
this.copyAttributes(A,E);
while(A.firstChild!==C){E.appendChild(A.firstChild)
}this.insertNodeAt(E,A,"before");
this.unwrapUnnecessaryParagraph(E)
}if(C.nextSibling&&(!B||B.toLowerCase()==="next")){var D=this.createElement(A.nodeName);
this.copyAttributes(A,D);
while(A.lastChild!==C){this.insertNodeAt(A.lastChild,D,"start")
}this.insertNodeAt(D,A,"after");
this.unwrapUnnecessaryParagraph(D)
}if(!F){C=this.unwrapUnnecessaryParagraph(A)?A:C
}return C
},splitParentElement:function(A){var C=A.parentNode;
if(["HTML","HEAD","BODY"].indexOf(C.nodeName)!==-1){throw"Illegal argument. Cannot seperate element["+C.nodeName+"]"
}var D=A.previousSibling;
var E=A.nextSibling;
var F=this.insertNodeAt(this.createElement(C.nodeName),C,"after");
var B;
while(B=A.nextSibling){F.appendChild(B)
}this.insertNodeAt(A,F,"start");
this.copyAttributes(C,F);
return F
},splitElementUpto:function(B,A,C){while(B.previousSibling!==A){if(C&&B.parentNode===A){break
}B=this.splitParentElement(B)
}return B
},mergeElement:function(E,J,I){this.wrapAllInlineOrTextNodesAs("P",E.parentNode,true);
if(J){var D=E;
var F=this.tree.findForward(E,function(K){return this.tree.isBlock(K)&&!this.tree.isListContainer(K)&&K!==E.parentNode
}.bind(this))
}else{var F=E;
var D=this.tree.findBackward(E,function(K){return this.tree.isBlock(K)&&!this.tree.isListContainer(K)&&K!==E.parentNode
}.bind(this))
}if(F&&this.tree.isDescendantOf(this.getRoot(),F)){var G=F.parentNode;
if(this.tree.isBlockContainer(F)){G=F;
this.wrapAllInlineOrTextNodesAs("P",G,true);
F=G.firstChild
}}else{F=null
}if(D&&this.tree.isDescendantOf(this.getRoot(),D)){var H=D.parentNode;
if(this.tree.isBlockContainer(D)){H=D;
this.wrapAllInlineOrTextNodesAs("P",H,true);
D=H.lastChild
}}else{D=null
}try{var C=H&&(this.tree.isTableCell(H)||["TR","THEAD","TBODY"].indexOf(H.nodeName)!==-1)&&G&&(this.tree.isTableCell(G)||["TR","THEAD","TBODY"].indexOf(G.nodeName)!==-1);
if(C&&H!==G){return null
}if((!I||!D)&&F&&G.nodeName!=="LI"&&this.outdentElement(F)){return E
}if(G&&G.nodeName==="LI"&&this.tree.isListContainer(F.nextSibling)){if(D&&H&&H.nodeName!=="BODY"){this.moveChildNodes(G,H)
}else{if(!D){D=this.insertNodeAt(this.createElement("P"),this.getParentElementOf(G,"UL"),"before")
}}this.removePlaceHoldersAndEmptyNodes(D);
this.moveChildNodes(F,D);
this.deleteNode(F);
if(!H||H.nodeName==="BODY"){this.moveChildNodes(G,D)
}return D
}if(G&&G.nodeName==="LI"&&this.tree.isListContainer(G.parentNode.previousSibling)){this.mergeAdjustLists(G.parentNode.previousSibling,true,"next");
return D
}if(F&&!C&&H&&H.nodeName==="LI"&&G&&G.nodeName==="LI"&&H.parentNode.nextSibling===G.parentNode){var A=G.parentNode;
this.moveChildNodes(G.parentNode,H.parentNode);
this.deleteNode(A);
return D
}if(F&&!C&&H&&H.nextSibling===G&&((I&&H.nodeName!=="LI")||(!I&&H.nodeName==="LI"))){this.moveChildNodes(G,H);
return D
}if(G&&G.nodeName!=="LI"&&!this.getParentElementOf(G,["TABLE"])&&!this.tree.isListContainer(G)&&G!==this.getRoot()&&!F.previousSibling){return this.unwrapElement(G,true)
}if(J&&G&&G.nodeName==="TABLE"){this.deleteNode(G,true);
return D
}else{if(!J&&H&&this.tree.isTableCell(H)&&!this.tree.isTableCell(G)){if(E.nodeName=="LI"){return null
}this.deleteNode(this.getParentElementOf(H,["TABLE"]),true);
return F
}}if(D===F){return null
}if(!D||!F||!H||!G){return null
}if(this.getParentElementOf(D,["TD","TH"])!==this.getParentElementOf(F,["TD","TH"])){return null
}var B=false;
if(xq.Browser.isTrident&&D.childNodes.length>=2&&this.isMarker(D.lastChild.previousSibling)&&D.lastChild.nodeType===3&&D.lastChild.nodeValue.length===1&&D.lastChild.nodeValue.charCodeAt(0)===160){this.deleteNode(D.lastChild)
}this.removePlaceHoldersAndEmptyNodes(D);
if(this.isEmptyBlock(D)){if(this.tree.isAtomic(D)){D=this.replaceTag("P",D)
}D=this.replaceTag(F.nodeName,D)||D;
D.innerHTML=""
}else{if(D.firstChild===D.lastChild&&this.isMarker(D.firstChild)){D=this.replaceTag(F.nodeName,D)||D
}}if(this.isEmptyBlock(F)){if(this.tree.isAtomic(F)){F=this.replaceTag("P",F)
}F.innerHTML=""
}this.moveChildNodes(F,D);
this.deleteNode(F);
return D
}finally{if(H&&this.isEmptyBlock(H)){this.deleteNode(H,true)
}if(G&&this.isEmptyBlock(G)){this.deleteNode(G,true)
}if(H){this.unwrapUnnecessaryParagraph(H)
}if(G){this.unwrapUnnecessaryParagraph(G)
}}},mergeAdjustLists:function(A,G,D){var F=A.previousSibling;
var C=F&&(F.nodeName===A.nodeName&&F.className===A.className);
if((!D||D.toLowerCase()==="prev")&&(C||(G&&this.tree.isListContainer(F)))){while(F.lastChild){this.insertNodeAt(F.lastChild,A,"start")
}this.deleteNode(F)
}var E=A.nextSibling;
var B=E&&(E.nodeName===A.nodeName&&E.className===A.className);
if((!D||D.toLowerCase()==="next")&&(B||(G&&this.tree.isListContainer(E)))){while(E.firstChild){this.insertNodeAt(E.firstChild,A,"end")
}this.deleteNode(E)
}},moveChildNodes:function(B,A){if(this.tree.isDescendantOf(B,A)||["HTML","HEAD"].indexOf(A.nodeName)!==-1){throw"Illegal argument. Cannot move children of element["+B.nodeName+"] to element["+A.nodeName+"]"
}if(B===A){return 
}while(B.firstChild){A.appendChild(B.firstChild)
}},copyAttributes:function(E,D,B){var A=E.attributes;
if(!A){return 
}for(var C=0;
C<A.length;
C++){if(A[C].nodeName==="class"&&A[C].nodeValue){D.className=A[C].nodeValue
}else{if((B||"id"!==A[C].nodeName)&&A[C].nodeValue){D.setAttribute(A[C].nodeName,A[C].nodeValue)
}}}},_indentElements:function(C,E,D){for(var B=0;
B<D.length;
B++){if(D[B]===C||this.tree.isDescendantOf(D[B],C)){return 
}}leaves=this.tree.getLeavesAtEdge(C);
if(E.includeElement(leaves[0])){var F=this.indentElement(C,true);
if(F){D.push(F);
return 
}}if(E.includeElement(C)){var F=this.indentElement(C,true);
if(F){D.push(F);
return 
}}var A=xq.$A(C.childNodes);
for(var B=0;
B<A.length;
B++){this._indentElements(A[B],E,D)
}return 
},indentElements:function(H,G){var E=this.getBlockElementsBetween(H,G);
var C=this.tree.findCommonAncestorAndImmediateChildrenOf(H,G);
var D=[];
leaves=this.tree.getLeavesAtEdge(C.parent);
if(E.includeElement(leaves[0])){var F=this.indentElement(C.parent);
if(F){return[F]
}}var B=xq.$A(C.parent.childNodes);
for(var A=0;
A<B.length;
A++){this._indentElements(B[A],E,D)
}D=D.flatten();
return D.length>0?D:E
},outdentElementsCode:function(A){if(A.tagName==="LI"){A=A.parentNode
}if(A.tagName==="OL"&&A.className==="code"){return true
}return false
},_outdentElements:function(C,F,E){for(var B=0;
B<E.length;
B++){if(E[B]===C||this.tree.isDescendantOf(E[B],C)){return 
}}leaves=this.tree.getLeavesAtEdge(C);
if(F.includeElement(leaves[0])&&!this.outdentElementsCode(leaves[0])){var G=this.outdentElement(C,true);
if(G){E.push(G);
return 
}}if(F.includeElement(C)){var A=xq.$A(C.parentNode.childNodes);
var D=this.outdentElementsCode(C);
var G=this.outdentElement(C,true,D);
if(G){if(A.includeElement(G)&&this.tree.isListContainer(C.parentNode)&&!D){for(var B=0;
B<A.length;
B++){if(F.includeElement(A[B])&&!E.includeElement(A[B])){E.push(A[B])
}}}else{E.push(G)
}return 
}}var A=xq.$A(C.childNodes);
for(var B=0;
B<A.length;
B++){this._outdentElements(A[B],F,E)
}return 
},outdentElements:function(I,J){var B,D;
if(I.parentNode.tagName==="LI"){B=I.parentNode
}if(J.parentNode.tagName==="LI"){D=J.parentNode
}var A=this.getBlockElementsBetween(I,J);
var G=this.tree.findCommonAncestorAndImmediateChildrenOf(I,J);
var H=[];
leaves=this.tree.getLeavesAtEdge(G.parent);
if(A.includeElement(leaves[0])&&!this.outdentElementsCode(G.parent)){var E=this.outdentElement(G.parent);
if(E){return[E]
}}var C=xq.$A(G.parent.childNodes);
for(var F=0;
F<C.length;
F++){this._outdentElements(C[F],A,H)
}if(I.offsetParent&&J.offsetParent){B=I;
D=J
}else{if(A.first().offsetParent&&A.last().offsetParent){B=A.first();
D=A.last()
}}H=H.flatten();
if(!B||!B.offsetParent){B=H.first()
}if(!D||!D.offsetParent){D=H.last()
}return this.getBlockElementsBetween(B,D)
},indentElement:function(E,D,A){if(!A&&(E.nodeName==="LI"||(!this.tree.isListContainer(E)&&!E.previousSibling&&E.parentNode.nodeName==="LI"))){return this.indentListItem(E,D)
}var C=this.getRoot();
if(!E||E===C){return null
}if(E.parentNode!==C&&!E.previousSibling&&!D){E=E.parentNode
}var F=E.style.marginLeft;
var B=F?this._getCssValue(F,"px"):{value:0,unit:"em"};
B.value+=2;
E.style.marginLeft=B.value+B.unit;
return E
},outdentElement:function(E,D,A){if(!A&&E.nodeName==="LI"){return this.outdentListItem(E,D)
}var C=this.getRoot();
if(!E||E===C){return null
}var F=E.style.marginLeft;
var B=F?this._getCssValue(F,"px"):{value:0,unit:"em"};
if(B.value===0){return E.previousSibling||A?null:this.outdentElement(E.parentNode,D)
}B.value-=2;
E.style.marginLeft=B.value<=0?"":B.value+B.unit;
if(E.style.cssText===""){E.removeAttribute("style")
}return E
},indentListItem:function(E,B){var A=this.getParentElementOf(E,["LI"]);
var C=A.parentNode;
var G=A.previousSibling;
if(!A.previousSibling){return this.indentElement(C)
}if(A.parentNode.nodeName==="OL"&&A.parentNode.className==="code"){return this.indentElement(A,B,true)
}if(!G.lastChild){G.appendChild(this.makePlaceHolder())
}var F=this.tree.isListContainer(G.lastChild)?G.lastChild:this.insertNodeAt(this.createElement(C.nodeName),G,"end");
this.wrapAllInlineOrTextNodesAs("P",G,true);
F.appendChild(A);
if(!B&&A.lastChild&&this.tree.isListContainer(A.lastChild)){var D=A.lastChild;
var H;
while(H=D.lastChild){this.insertNodeAt(H,A,"after")
}this.deleteNode(D)
}this.unwrapUnnecessaryParagraph(A);
return A
},outdentListItem:function(E,C){var B=this.getParentElementOf(E,["LI"]);
var D=B.parentNode;
if(!B.previousSibling){var H=this.outdentElement(D);
if(H){return H
}}if(D.parentNode.nodeName=="OL"||D.parentNode.nodeName=="UL"){if(D.previousSibling&&D.previousSibling.nodeName=="LI"){D.previousSibling.appendChild(D)
}}if(B.parentNode.nodeName==="OL"&&B.parentNode.className==="code"){return this.outdentElement(B,C,true)
}var A=D.parentNode;
if(A.nodeName!=="LI"){return null
}if(C){while(D.lastChild!==B){this.insertNodeAt(D.lastChild,A,"after")
}}else{if(B.nextSibling){var G=B.lastChild&&this.tree.isListContainer(B.lastChild)?B.lastChild:this.insertNodeAt(this.createElement(D.nodeName),B,"end");
this.copyAttributes(D,G);
var F;
while(F=B.nextSibling){G.appendChild(F)
}}}B=this.insertNodeAt(B,A,"after");
if(D.childNodes.length===0){this.deleteNode(D)
}if(B.firstChild&&this.tree.isListContainer(B.firstChild)){this.insertNodeAt(this.makePlaceHolder(),B,"start")
}this.wrapAllInlineOrTextNodesAs("P",B);
this.unwrapUnnecessaryParagraph(A);
return B
},justifyBlock:function(C,B){while(C.parentNode!==this.getRoot()&&!C.previousSibling&&!C.nextSibling&&!this.tree.isListContainer(C.parentNode)){C=C.parentNode
}var A=B.toLowerCase()==="both"?"justify":B;
if(A==="left"){C.style.textAlign="";
if(C.style.cssText===""){C.removeAttribute("style")
}}else{C.style.textAlign=A
}return C
},justifyBlocks:function(C,A){for(var B=0;
B<C.length;
B++){this.justifyBlock(C[B],A)
}return C
},applyList:function(C,E,D){E=E.toUpperCase();
D=D||"";
var A=E;
if(C.nodeName==="LI"||(C.parentNode.nodeName==="LI"&&!C.previousSibling)){var C=this.getParentElementOf(C,["LI"]);
var B=C.parentNode;
if(B.nodeName===A&&B.className===D){return this.extractOutElementFromParent(C)
}else{return this.changeListTypeTo(C,E,D)
}}else{return this.turnElementIntoListItem(C,E,D)
}},applyLists:function(N,O,L,I){L=L.toUpperCase();
I=I||"";
var J=L;
var A=this.getBlockElementsBetween(N,O);
var K=A.findAll(function(Q){return Q.nodeName==="LI"||!this.tree.isBlockContainer(Q)
}.bind(this));
var B=K.findAll(function(Q){return Q.nodeName==="LI"
}.bind(this));
var H=K.findAll(function(Q){return Q.nodeName!=="LI"&&!(Q.parentNode.nodeName==="LI"&&!Q.previousSibling&&!Q.nextSibling)&&!this.tree.isDescendantOf(B,Q)
}.bind(this));
var P=B.findAll(function(Q){return Q.parentNode.nodeName!==J
}.bind(this));
var E=H.length>0;
var D=P.length>0;
var M=null;
if(E){M=H
}else{if(D){M=P
}else{M=B
}}for(var F=0;
F<M.length;
F++){var C=M[F];
var G=A.indexOf(C);
A[G]=this.applyList(C,L,I)
}return A
},correctEmptyElement:function(A){throw"Not implemented"
},correctParagraph:function(){throw"Not implemented"
},makePlaceHolder:function(){throw"Not implemented"
},makePlaceHolderString:function(){throw"Not implemented"
},makeEmptyParagraph:function(){throw"Not implemented"
},applyBackgroundColor:function(A){throw"Not implemented"
},applyForegroundColor:function(A){this.execCommand("forecolor",A)
},applyFontFace:function(A){this.execCommand("fontname",A)
},applyFontSize:function(A){this.execCommand("fontsize",A)
},execCommand:function(A,B){throw"Not implemented"
},applyRemoveFormat:function(){throw"Not implemented"
},applyEmphasis:function(){throw"Not implemented"
},applyStrongEmphasis:function(){throw"Not implemented"
},applyStrike:function(){throw"Not implemented"
},applyUnderline:function(){throw"Not implemented"
},applySuperscription:function(){this.execCommand("superscript")
},applySubscription:function(){this.execCommand("subscript")
},indentBlock:function(B,A){return(!B.previousSibling&&B.parentNode.nodeName==="LI")?this.indentListItem(B,A):this.indentElement(B)
},outdentBlock:function(B,A){while(true){if(!B.previousSibling&&B.parentNode.nodeName==="LI"){B=this.outdentListItem(B,A);
return B
}else{var C=this.outdentElement(B);
if(C){return C
}if(!B.previousSibling){B=B.parentNode
}else{break
}}}return null
},wrapBlock:function(B,F,C){if(this.tree._blockTags.indexOf(B)===-1){throw"Unsuppored block container: ["+B+"]"
}if(!F){F=this.getCurrentBlockElement()
}if(!C){C=F
}var A=false;
if(F===C){A=true
}else{if(F.parentNode===C.parentNode&&!F.previousSibling&&!C.nextSibling){A=true;
F=C=F.parentNode
}else{A=(F.parentNode===C.parentNode)&&(F.nodeName!=="LI")
}}if(!A){return null
}var E=this.createElement(B);
if(F===C){if(this.tree.isBlockContainer(F)&&!this.tree.isListContainer(F)){if(this.tree.isBlockOnlyContainer(E)){this.correctEmptyElement(F);
this.wrapAllInlineOrTextNodesAs("P",F,true)
}this.moveChildNodes(F,E);
F.appendChild(E)
}else{E=this.insertNodeAt(E,F,"after");
E.appendChild(F)
}this.correctEmptyElement(E)
}else{E=this.insertNodeAt(E,F,"before");
var D=F;
while(D!==C){next=D.nextSibling;
E.appendChild(D);
D=next
}E.appendChild(D)
}return E
},focus:function(){throw"Not implemented"
},sel:function(){throw"Not implemented"
},rng:function(){throw"Not implemented"
},hasSelection:function(){throw"Not implemented"
},hasFocus:function(){return this.focused
},scrollIntoView:function(C,B,A){C.scrollIntoView(B);
if(A){this.placeCaretAtStartOf(C)
}},selectAll:function(){return this.execCommand("selectall")
},selectElement:function(B,A){throw"Not implemented"
},selectBlocksBetween:function(B,A){throw"Not implemented"
},deleteSelection:function(){throw"Not implemented"
},collapseSelection:function(A){throw"Not implemented"
},getSelectionAsHtml:function(){throw"Not implemented"
},getSelectionAsText:function(){throw"Not implemented"
},placeCaretAtStartOf:function(A){throw"Not implemented"
},isCaretAtBlockStart:function(){if(this.isCaretAtEmptyBlock()){return true
}if(this.hasSelection()){return false
}var C=this.getCurrentBlockElement();
var B=this.pushMarker();
var A=false;
while(C=this.getFirstChild(C)){if(C===B){A=true;
break
}}this.popMarker();
return A
},isCaretAtBlockEnd:function(){throw"Not implemented"
},isEmptyTextNode:function(A){return A.nodeType===3&&(A.nodeValue.length===0||(A.nodeValue.length===1&&(A.nodeValue.charAt(0)===32||A.nodeValue.charAt(0)===160)))
},isCaretAtEmptyBlock:function(){return this.isEmptyBlock(this.getCurrentBlockElement())
},saveSelection:function(){throw"Not implemented"
},restoreSelection:function(A){throw"Not implemented"
},createMarker:function(){var A=this.createElement("SPAN");
A.id="xquared_marker_"+(this._lastMarkerId++);
A.className="xquared_marker";
return A
},pushMarker:function(){var A=this.createMarker();
return this.insertNode(A)
},popMarker:function(B){var C="xquared_marker_"+(--this._lastMarkerId);
var A=this.$(C);
if(!A){return 
}if(B){this.selectElement(A,true);
this.collapseSelection(false)
}this.deleteNode(A)
},isMarker:function(A){return(A.nodeType===1&&A.nodeName==="SPAN"&&A.className==="xquared_marker")
},isFirstBlockOfBody:function(C){var A=this.getRoot();
if(this.isFirstLiWithNestedList(C)){C=C.parentNode
}var B=this.tree.findBackward(C,function(D){return D===A||(this.tree.isBlock(D)&&!this.tree.isBlockOnlyContainer(D))
}.bind(this));
return B===A
},getOuterHTML:function(A){throw"Not implemented"
},getInnerText:function(A){return A.innerHTML.stripTags()
},isPlaceHolder:function(A){throw"Not implemented"
},isFirstLiWithNestedList:function(A){return !A.previousSibling&&A.parentNode.nodeName==="LI"&&this.tree.isListContainer(A.nextSibling)
},searchAnchors:function(B,D){if(!B){B=this.getRoot()
}if(!D){D=[]
}var C=B.getElementsByTagName("A");
for(var A=0;
A<C.length;
A++){D.push(C[A])
}return D
},searchHeadings:function(D,G){if(!D){D=this.getRoot()
}if(!G){G=[]
}var F=/^h[1-6]/ig;
var B=D.childNodes;
if(!B){return[]
}for(var C=0;
C<B.length;
C++){var E=B[C]&&this.tree._blockContainerTags.indexOf(B[C].nodeName)!==-1;
var A=B[C]&&B[C].nodeName.match(F);
if(E){this.searchHeadings(B[C],G)
}else{if(A){G.push(B[C])
}}}return G
},collectStructureAndStyle:function(E){if(!E||E.nodeName==="#document"){return{}
}var I=this.getParentBlockElementOf(E);
if(I===null||(xq.Browser.isTrident&&["ready","complete"].indexOf(I.readyState)===-1)){return{}
}var U=this.tree.collectParentsOf(E,true,function(W){return I.parentNode===W
});
var O=I.nodeName;
var T={};
var V=this.getDoc();
var C=V.queryCommandState("Italic");
var H=V.queryCommandState("Bold");
var A=V.queryCommandState("Strikethrough");
var J=V.queryCommandState("Underline")&&!this.getParentElementOf(E,["A"]);
var L=V.queryCommandState("superscript");
var Q=V.queryCommandState("subscript");
var N=V.queryCommandValue("forecolor");
var M=V.queryCommandValue("fontname");
var F=V.queryCommandValue("fontsize");
if(xq.Browser.isTrident&&F===5&&this.getParentElementOf(E,["H1","H2","H3","H4","H5","H6"])){F="";
N="";
M=""
}var G;
if(xq.Browser.isGecko){this.execCommand("styleWithCSS","true");
try{G=V.queryCommandValue("hilitecolor")
}catch(S){G=""
}this.execCommand("styleWithCSS","false")
}else{G=V.queryCommandValue("backcolor")
}while(I.parentNode&&I.parentNode!==this.getRoot()&&!I.previousSibling&&!I.nextSibling&&!this.tree.isListContainer(I.parentNode)){I=I.parentNode
}var R=false;
if(I.nodeName==="LI"&&I.parentNode){var K=I.parentNode;
var D=K.nodeName==="OL"&&K.className==="code";
var B=K.className.length>0;
if(D){R="CODE"
}else{if(B){R=false
}else{R=K.nodeName
}}}var P=I.style.textAlign||"left";
return{block:O,em:C,strong:H,strike:A,underline:J,superscription:L,subscription:Q,list:R,justification:P,foregroundColor:N,backgroundColor:G,fontSize:F,fontName:M}
},hasImportantAttributes:function(A){throw"Not implemented"
},isEmptyBlock:function(A){throw"Not implemented"
},getCurrentElement:function(){throw"Not implemented"
},getCurrentBlockElement:function(){var B=this.getCurrentElement();
if(!B){return null
}var A=this.getParentBlockElementOf(B);
if(!A){return null
}return(A.nodeName==="BODY")?null:A
},getParentBlockElementOf:function(A){while(A){if(this.tree._blockTags.indexOf(A.nodeName)!==-1){return A
}A=A.parentNode
}return null
},getParentElementOf:function(B,A){while(B){if(A.indexOf(B.nodeName)!==-1){return B
}B=B.parentNode
}return null
},getBlockElementsBetween:function(B,A){return this.tree.collectNodesBetween(B,A,function(C){return C.nodeType===1&&this.tree.isBlock(C)
}.bind(this))
},getBlockElementAtSelectionStart:function(){throw"Not implemented"
},getBlockElementAtSelectionEnd:function(){throw"Not implemented"
},getBlockElementsAtSelectionEdge:function(B,A){throw"Not implemented"
},getSelectedBlockElements:function(){var B=this.getBlockElementsAtSelectionEdge(true,true);
var C=B[0];
var A=B[1];
return this.tree.collectNodesBetween(C,A,function(D){return D.nodeType===1&&this.tree.isBlock(D)
}.bind(this))
},getElementById:function(A){return this.getDoc().getElementById(A)
},$:function(A){return this.getElementById(A)
},getFirstChild:function(B){if(!B){return null
}var A=xq.$A(B.childNodes);
return A.find(function(C){return !this.isEmptyTextNode(C)
}.bind(this))
},getLastChild:function(B,A){throw"Not implemented"
},getNextSibling:function(A){while(A=A.nextSibling){if(A.nodeType!==3||!A.nodeValue.isBlank()){break
}}return A
},getBottommostFirstChild:function(A){while(A.firstChild&&A.nodeType===1){A=A.firstChild
}return A
},getBottommostLastChild:function(A){while(A.lastChild&&A.nodeType===1){A=A.lastChild
}return A
},_getCssValue:function(C,A){if(!C||C.length===0){return{value:0,unit:A}
}var B=C.match(/(\d+)(.*)/);
return{value:parseInt(B[1]),unit:B[2]||A}
}});
xq.rdom.Trident=xq.Class(xq.rdom.Base,{makePlaceHolder:function(){return this.createTextNode(" ")
},makePlaceHolderString:function(){return"&nbsp;"
},makeEmptyParagraph:function(){return this.createElementFromHtml("<p>&nbsp;</p>")
},isPlaceHolder:function(A){return false
},getOuterHTML:function(A){return A.outerHTML
},getCurrentBlockElement:function(){var D=this.getCurrentElement();
if(!D){return null
}var C=this.getParentBlockElementOf(D);
if(!C){return null
}if(C.nodeName==="BODY"){var B=this.insertNode(this.makeEmptyParagraph());
var A=B.nextSibling;
if(this.tree.isAtomic(A)){this.deleteNode(B);
return A
}}else{return C
}},insertNode:function(B){if(this.hasSelection()){this.collapseSelection(true)
}this.rng().pasteHTML('<span id="xquared_temp"></span>');
var A=this.$("xquared_temp");
if(B.id==="xquared_temp"){return A
}if(A){A.replaceNode(B)
}return B
},removeTrailingWhitespace:function(F){if(!F){return 
}if(this.tree.isBlockOnlyContainer(F)){return 
}if(this.isEmptyBlock(F)){return 
}var E=F.innerText;
var C=F.innerHTML;
var B=E.charCodeAt(E.length-1);
if(E.length<=1||[32,160].indexOf(B)===-1){return 
}if(E==C.replace(/&nbsp;/g," ")){F.innerHTML=C.replace(/&nbsp;$/,"");
return 
}var D=F;
while(D&&D.nodeType!==3){D=D.lastChild
}if(!D){return 
}var A=D.nodeValue;
if(A.length<=1){this.deleteNode(D,true)
}else{D.nodeValue=A.substring(0,A.length-1)
}},correctEmptyElement:function(A){if(!A||A.nodeType!==1||this.tree.isAtomic(A)){return 
}if(A.firstChild){this.correctEmptyElement(A.firstChild)
}else{A.innerHTML="&nbsp;"
}},copyAttributes:function(C,B,A){B.mergeAttributes(C,!A)
},correctParagraph:function(){if(!this.hasFocus()){return false
}if(this.hasSelection()){return false
}var C=this.getCurrentElement();
if(this.tree.isBlockOnlyContainer(C)){C=this.insertNode(this.makeEmptyParagraph());
if(this.tree.isAtomic(C.nextSibling)){this.recentHR=C.nextSibling;
this.deleteNode(C);
return false
}else{var B=this.tree.findForward(C,function(D){return this.tree.isBlock(D)&&!this.tree.isBlockOnlyContainer(D)
}.bind(this));
if(B){this.deleteNode(C);
this.placeCaretAtStartOf(B)
}else{this.placeCaretAtStartOf(C)
}return true
}}else{C=this.getCurrentBlockElement();
if(C.nodeType===3){C=C.parentNode
}if(this.tree.hasMixedContents(C)){var A=this.pushMarker();
this.wrapAllInlineOrTextNodesAs("P",C,true);
this.popMarker(true);
return true
}else{if((this.tree.isTextOrInlineNode(C.previousSibling)||this.tree.isTextOrInlineNode(C.nextSibling))&&this.tree.hasMixedContents(C.parentNode)){this.wrapAllInlineOrTextNodesAs("P",C.parentNode,true);
return true
}else{return false
}}}},execCommand:function(A,B){return this.getDoc().execCommand(A,false,B)
},applyBackgroundColor:function(A){this.execCommand("BackColor",A)
},applyEmphasis:function(){this.execCommand("Italic")
},applyStrongEmphasis:function(){this.execCommand("Bold")
},applyStrike:function(){this.execCommand("strikethrough")
},applyUnderline:function(){this.execCommand("underline")
},applyRemoveFormat:function(){this.execCommand("RemoveFormat")
},applyRemoveLink:function(){this.execCommand("Unlink")
},focus:function(){this.getWin().focus()
},sel:function(){return this.getDoc().selection
},crng:function(){return this.getDoc().body.createControlRange()
},rng:function(){try{var B=this.sel();
return(B===null)?null:B.createRange()
}catch(A){return null
}},hasSelection:function(B){var A=this.sel().type.toLowerCase();
if("none"===A){return false
}if("text"===A&&this.getSelectionAsHtml().length===0){return false
}if(B&&"control"===A){return false
}return true
},deleteSelection:function(){if(this.getSelectionAsText()!==""||this.sel().type.toLowerCase()==="control"){this.sel().clear()
}},placeCaretAtStartOf:function(A){var B=this.insertNodeAt(this.createElement("SPAN"),A,"start");
this.selectElement(B);
this.collapseSelection(false);
this.deleteNode(B)
},selectElement:function(B,C,D){if(!B){throw"[element] is null"
}if(B.nodeType!==1){throw"[element] is not an element"
}var A=null;
if(!D&&this.tree.isAtomic(B)){A=this.crng();
A.addElement(B)
}else{var A=this.rng();
A.moveToElementText(B)
}A.select()
},selectBlocksBetween:function(D,B){var A=this.rng();
var C=this.rng();
C.moveToElementText(D);
A.setEndPoint("StartToStart",C);
C.moveToElementText(B);
A.setEndPoint("EndToEnd",C);
A.select()
},collapseSelection:function(C){if(this.sel().type.toLowerCase()==="control"){var B=this.getCurrentElement();
this.sel().empty();
this.selectElement(B,false,true)
}var A=this.rng();
A.collapse(C);
A.select()
},getSelectionAsHtml:function(){var A=this.rng();
return A&&A.htmlText?A.htmlText:""
},getSelectionAsText:function(){var A=this.rng();
return A&&A.text?A.text:""
},hasImportantAttributes:function(A){return !!(A.id||A.className||A.style.cssText)
},isEmptyBlock:function(A){if(!A.hasChildNodes()){return true
}if(A.nodeType===3&&!A.nodeValue){return true
}if(["&nbsp;"," ",""].indexOf(A.innerHTML)!==-1){return true
}return false
},getLastChild:function(D,B){if(!D||!D.hasChildNodes()){return null
}var A=xq.$A(D.childNodes).reverse();
for(var C=0;
C<A.length;
C++){if(A[C].nodeType!==3||A[C].nodeValue.length!==0){return A[C]
}}return null
},getCurrentElement:function(){if(this.sel().type.toLowerCase()==="control"){return this.rng().item(0)
}var A=this.rng();
if(!A){return false
}var B=A.parentElement();
if(B.nodeName=="BODY"&&this.hasSelection()){return null
}return B
},getBlockElementAtSelectionStart:function(){var B=this.rng();
var C=B.duplicate();
C.collapse(true);
var A=this.getParentBlockElementOf(C.parentElement());
if(A.nodeName==="BODY"){A=A.firstChild
}return A
},getBlockElementAtSelectionEnd:function(){var B=this.rng();
var C=B.duplicate();
C.collapse(false);
var A=this.getParentBlockElementOf(C.parentElement());
if(A.nodeName==="BODY"){A=A.lastChild
}return A
},getBlockElementsAtSelectionEdge:function(B,A){return[this.getBlockElementAtSelectionStart(),this.getBlockElementAtSelectionEnd()]
},isCaretAtBlockEnd:function(){if(this.isCaretAtEmptyBlock()){return true
}if(this.hasSelection()){return false
}var D=this.getCurrentBlockElement();
var B=this.pushMarker();
var A=false;
while(D=this.getLastChild(D)){var C=D.nodeValue;
if(D===B){A=true;
break
}else{if(D.nodeType===3&&D.previousSibling===B&&(C===" "||(C.length===1&&C.charCodeAt(0)===160))){A=true;
break
}}}this.popMarker();
return A
},saveSelection:function(){return this.rng()
},restoreSelection:function(A){A.select()
}});
xq.rdom.W3=xq.Class(xq.rdom.Base,{insertNode:function(B){var A=this.rng();
if(!A){this.getRoot().appendChild(B)
}else{A.insertNode(B);
A.selectNode(B);
A.collapse(false)
}return B
},removeTrailingWhitespace:function(A){},getOuterHTML:function(A){var B=A.ownerDocument.createElement("div");
B.appendChild(A.cloneNode(true));
return B.innerHTML
},correctEmptyElement:function(A){if(!A||A.nodeType!==1||this.tree.isAtomic(A)){return 
}if(A.firstChild){this.correctEmptyElement(A.firstChild)
}else{A.appendChild(this.makePlaceHolder())
}},correctParagraph:function(){if(this.hasSelection()){return false
}var E=this.getCurrentBlockElement();
var I=false;
if(!E){try{this.execCommand("InsertParagraph");
I=true
}catch(D){}}else{if(this.tree.isBlockOnlyContainer(E)){this.execCommand("InsertParagraph");
var G=this.getCurrentElement();
if(this.tree.isAtomic(G.previousSibling)&&G.previousSibling.nodeName==="HR"){var C=this.tree.findForward(G,function(J){return this.tree.isBlock(J)&&!this.tree.isBlockOnlyContainer(J)
}.bind(this));
if(C){this.deleteNode(G);
this.placeCaretAtStartOf(C)
}}I=true
}else{if(this.tree.hasMixedContents(E)){this.wrapAllInlineOrTextNodesAs("P",E,true);
I=true
}}}E=this.getCurrentBlockElement();
if(this.tree.isBlock(E)&&!this._hasPlaceHolderAtEnd(E)){E.appendChild(this.makePlaceHolder());
I=true
}if(this.tree.isBlock(E)){var H=E.parentNode.lastChild;
if(this.isPlaceHolder(H)){this.deleteNode(H);
I=true
}}if(this.tree.isBlock(E)){var A=E.childNodes;
for(var F=0;
F<A.length;
F++){var B=A[F];
if(B.nodeType===1&&!this.tree.isAtomic(B)&&!B.hasChildNodes()&&!this.isPlaceHolder(B)){this.deleteNode(B)
}}}return I
},_hasPlaceHolderAtEnd:function(A){if(!A.hasChildNodes()){return false
}return this.isPlaceHolder(this.getLastChild(A,true))||this._hasPlaceHolderAtEnd(this.getLastChild(A))
},applyBackgroundColor:function(A){this.execCommand("styleWithCSS","true");
this.execCommand("hilitecolor",A);
this.execCommand("styleWithCSS","false");
var E=this.saveSelection();
var F=this.getSelectedBlockElements();
if(F.length===0){return 
}for(var D=0;
D<F.length;
D++){if((D===0||D===F.length-1)&&!F[D].style.backgroundColor){continue
}var C=this.wrapAllInlineOrTextNodesAs("SPAN",F[D],true);
for(var B=0;
B<C.length;
B++){C[B].style.backgroundColor=A
}F[D].style.backgroundColor=""
}this.restoreSelection(E)
},execCommand:function(A,B){return this.getDoc().execCommand(A,false,B||null)
},applyRemoveFormat:function(){this.execCommand("RemoveFormat")
},applyRemoveLink:function(){this.execCommand("Unlink")
},applyEmphasis:function(){this.execCommand("styleWithCSS","false");
this.execCommand("italic")
},applyStrongEmphasis:function(){this.execCommand("styleWithCSS","false");
this.execCommand("bold")
},applyStrike:function(){this.execCommand("styleWithCSS","false");
this.execCommand("strikethrough")
},applyUnderline:function(){this.execCommand("styleWithCSS","false");
this.execCommand("underline")
},focus:function(){this.getWin().focus()
},sel:function(){return this.getWin().getSelection()
},rng:function(){var A=this.sel();
return(A===null||A.rangeCount===0)?null:A.getRangeAt(0)
},saveSelection:function(){var A=this.rng();
return[A.startContainer,A.startOffset,A.endContainer,A.endOffset]
},restoreSelection:function(B){var A=this.rng();
A.setStart(B[0],B[1]);
A.setEnd(B[2],B[3])
},hasSelection:function(){var A=this.sel();
return A&&!A.isCollapsed
},deleteSelection:function(){this.rng().deleteContents();
this.sel().collapseToStart()
},selectElement:function(A,B){throw"Not implemented yet"
},selectBlocksBetween:function(D,B){try{if(!xq.Browser.isMac){this.getDoc().execCommand("SelectAll",false,null)
}}catch(C){}var A=this.rng();
A.setStart(D.firstChild,0);
A.setEnd(B,B.childNodes.length)
},collapseSelection:function(B){var A=this.rng();
if(A){A.collapse(B)
}},placeCaretAtStartOf:function(A){while(this.tree.isBlock(A.firstChild)){A=A.firstChild
}this.selectElement(A,false);
this.collapseSelection(true)
},placeCaretAtEndOf:function(A){while(this.tree.isBlock(A.lastChild)){A=A.lastChild
}this.selectElement(A,false);
this.collapseSelection(false)
},getSelectionAsHtml:function(){var A=document.createElement("div");
A.appendChild(this.rng().cloneContents());
return A.innerHTML
},getSelectionAsText:function(){return this.rng().toString()
},hasImportantAttributes:function(A){return !!(A.id||A.className||A.style.cssText)
},isEmptyBlock:function(C){if(!C.hasChildNodes()){return true
}var B=C.childNodes;
for(var A=0;
A<B.length;
A++){if(!this.isPlaceHolder(B[A])&&!this.isEmptyTextNode(B[A])){return false
}}return true
},getLastChild:function(D,B){if(!D||!D.hasChildNodes()){return null
}var A=xq.$A(D.childNodes).reverse();
for(var C=0;
C<A.length;
C++){if((B||!this.isPlaceHolder(A[C]))&&!this.isEmptyTextNode(A[C])){return A[C]
}}return null
},getCurrentElement:function(){var B=this.rng();
if(!B){return null
}var A=B.startContainer;
if(A.nodeType===3){return A.parentNode
}else{if(this.tree.isBlockOnlyContainer(A)){return A.childNodes[B.startOffset]
}else{return A
}}},getBlockElementsAtSelectionEdge:function(E,A){var F=this.getBlockElementAtSelectionStart();
var B=this.getBlockElementAtSelectionEnd();
var D=false;
if(E&&F!==B&&this.tree.checkTargetBackward(F,B)){var C=F;
F=B;
B=C;
D=true
}if(A&&F!==B){}return[F,B]
},isCaretAtBlockEnd:function(){if(this.isCaretAtEmptyBlock()){return true
}if(this.hasSelection()){return false
}var D=this.getCurrentBlockElement();
var B=this.pushMarker();
var A=false;
while(D=this.getLastChild(D)){var C=D.nodeValue;
if(D===B){A=true;
break
}}this.popMarker();
return A
},getBlockElementAtSelectionStart:function(){var A=this.getParentBlockElementOf(this.sel().anchorNode);
while(this.tree.isBlockContainer(A)&&A.firstChild&&this.tree.isBlock(A.firstChild)){A=A.firstChild
}return A
},getBlockElementAtSelectionEnd:function(){var A=this.getParentBlockElementOf(this.sel().focusNode);
while(this.tree.isBlockContainer(A)&&A.lastChild&&this.tree.isBlock(A.lastChild)){A=A.lastChild
}return A
}});
xq.rdom.Gecko=xq.Class(xq.rdom.W3,{makePlaceHolder:function(){var A=this.createElement("BR");
A.setAttribute("type","_moz");
return A
},makePlaceHolderString:function(){return'<br type="_moz" />'
},makeEmptyParagraph:function(){return this.createElementFromHtml('<p><br type="_moz" /></p>')
},isPlaceHolder:function(A){return A.nodeName==="BR"&&(A.getAttribute("type")==="_moz"||!this.getNextSibling(A))
},selectElement:function(C,D){if(!C){throw"[element] is null"
}if(C.nodeType!==1){throw"[element] is not an element"
}try{if(!xq.Browser.isMac){this.getDoc().execCommand("SelectAll",false,null)
}}catch(B){}var A=this.rng()||this.getDoc().createRange();
if(D){A.selectNode(C)
}else{A.selectNodeContents(C)
}}});
xq.rdom.Webkit=xq.Class(xq.rdom.W3,{makePlaceHolder:function(){var A=this.createElement("BR");
A.className="webkit-block-placeholder";
return A
},makePlaceHolderString:function(){return'<br class="webkit-block-placeholder" />'
},makeEmptyParagraph:function(){return this.createElementFromHtml('<p><br class="webkit-block-placeholder" /></p>')
},isPlaceHolder:function(A){return A.className==="webkit-block-placeholder"
},selectElement:function(B,C){if(!B){throw"[element] is null"
}if(B.nodeType!==1){throw"[element] is not an element"
}var A=this.rng()||this.getDoc().createRange();
if(C){A.selectNode(B)
}else{A.selectNodeContents(B)
}this._setSelectionByRange(A)
},getSelectionAsHtml:function(){var B=this.createElement("div");
var A=this.rng();
var C=this.rng().cloneContents();
if(C){B.appendChild(C)
}return B.innerHTML
},collapseSelection:function(B){var A=this.rng();
A.collapse(B);
this._setSelectionByRange(A)
},_setSelectionByRange:function(A){var B=this.sel();
B.setBaseAndExtent(A.startContainer,A.startOffset,A.endContainer,A.endOffset)
}});
xq.rdom.Base.createInstance=function(){if(xq.Browser.isTrident){return new xq.rdom.Trident()
}else{if(xq.Browser.isWebkit){return new xq.rdom.Webkit()
}else{return new xq.rdom.Gecko()
}}};
xq.validator={};
xq.validator.Base=xq.Class({initialize:function(C,B,A){xq.addToFinalizeQueue(this);
xq.asEventSource(this,"Validator",["Preprocessing","BeforeDomValidation","AfterDomValidation","BeforeStringValidation","AfterStringValidation","BeforeDomInvalidation","AfterDomInvalidation","BeforeStringInvalidation","AfterStringInvalidation"]);
this.whitelist=A||xq.predefinedWhitelist;
this.pRGB=xq.compilePattern("rgb\\((\\d+),\\s*(\\d+),\\s*(\\d+)\\)");
this.curUrl=C;
this.curUrlParts=C?C.parseURL():null;
this.urlValidationMode=B
},validate:function(C,A){C=A?C:C.cloneNode(true);
this._fireOnBeforeDomValidation(C);
this.validateDom(C);
this._fireOnAfterDomValidation(C);
var B={value:C.innerHTML};
this._fireOnBeforeStringValidation(B);
B.value=this.validateString(B.value);
this._fireOnAfterStringValidation(B);
return B.value
},validateDom:function(A){throw"Not implemented"
},validateString:function(A){throw"Not implemented"
},invalidate:function(B){var B={value:B};
this._fireOnPreprocessing(B);
var A=document.createElement("DIV");
A.innerHTML=B.value;
this._fireOnBeforeDomInvalidation(A);
this.invalidateDom(A);
this._fireOnAfterDomInvalidation(A);
B.value=A.innerHTML;
this._fireOnBeforeStringInvalidation(B);
B.value=this.invalidateString(B.value);
this._fireOnAfterStringInvalidation(B);
return B.value
},invalidateDom:function(A){throw"Not implemented"
},invalidateString:function(A){throw"Not implemented"
},invalidateStrikesAndUnderlines:function(B){var G=xq.rdom.Base.createInstance();
G.setRoot(B);
var A=xq.Browser.isTrident?"className":"class";
var E=xq.getElementsByClassName(G.getRoot(),"underline","em");
var D=xq.compilePattern("(^|\\s)underline($|\\s)");
var H=E.length;
for(var C=0;
C<H;
C++){G.replaceTag("u",E[C]).removeAttribute(A)
}var J=xq.getElementsByClassName(G.getRoot(),"strike","span");
var F=xq.compilePattern("(^|\\s)strike($|\\s)");
var I=J.length;
for(var C=0;
C<I;
C++){G.replaceTag("strike",J[C]).removeAttribute(A)
}},validateStrike:function(A){A=A.replace(/<strike(>|\s+[^>]*>)/ig,'<span class="strike"$1');
A=A.replace(/<\/strike>/ig,"</span>");
return A
},validateUnderline:function(A){A=A.replace(/<u(>|\s+[^>]*>)/ig,'<em class="underline"$1');
A=A.replace(/<\/u>/ig,"</em>");
return A
},replaceTag:function(A,C,B){return A.replace(new RegExp("(</?)"+C+"(>|\\s+[^>]*>)","ig"),"$1"+B+"$2")
},validateSelfClosingTags:function(A){return A.replace(/<(br|hr|img|value)([^>]*?)>/img,function(D,B,C){return"<"+B+C+" />"
})
},validateFont:function(D){var H=xq.rdom.Base.createInstance();
H.setRoot(D);
var I=D.getElementsByTagName("FONT");
var B=["x-small","small","medium","large","x-large","xx-large"];
var F=I.length-1;
for(var E=F;
E>=0;
E--){var A=I[E];
var C=A.getAttribute("color");
var K=A.style.backgroundColor;
var G=A.getAttribute("face");
var L=B[parseInt(A.getAttribute("size"))%8-1];
if(C||K||G||L){var J=H.replaceTag("span",A);
J.removeAttribute("color");
J.removeAttribute("face");
J.removeAttribute("size");
if(C){J.style.color=C
}if(K){J.style.backgroundColor=K
}if(G){J.style.fontFamily=G
}if(L){J.style.fontSize=L
}}}},invalidateFont:function(D){var I=xq.rdom.Base.createInstance();
I.setRoot(D);
var G=D.getElementsByTagName("SPAN");
var B={"x-small":1,small:2,medium:3,large:4,"x-large":5,"xx-large":6};
var F=G.length-1;
for(var E=F;
E>=0;
E--){var K=G[E];
if(K.className==="strike"){continue
}var C=K.style.color;
var J=K.style.backgroundColor;
var H=K.style.fontFamily;
var L=B[K.style.fontSize];
if(C||J||H||L){var A=I.replaceTag("font",K);
A.style.cssText="";
if(C){A.setAttribute("color",this.asRGB(C))
}if(J){A.style.backgroundColor=J
}if(H){A.setAttribute("face",H)
}if(L){A.setAttribute("size",L)
}}}},asRGB:function(C){if(C.indexOf("#")===0){return C
}var B=this.pRGB.exec(C);
if(!B){return C
}var E=Number(B[1]).toString(16);
var D=Number(B[2]).toString(16);
var A=Number(B[3]).toString(16);
if(E.length===1){E="0"+E
}if(D.length===1){D="0"+D
}if(A.length===1){A="0"+A
}return"#"+E+D+A
},removeComments:function(A){return A.replace(/<!--(\n|\r|.)*?-->/img,"")
},removeDangerousElements:function(D){var B=["SCRIPT"];
for(var C=0;
C<B.length;
C++){var E=D.getElementsByTagName(B[C]);
for(var A=E.length-1;
A>=0;
A--){E[A].parentNode.removeChild(E[A])
}}},addNbspToEmptyBlocks:function(B){var C=new xq.DomTree().getBlockTags().join("|");
var A=new RegExp("<("+C+")([^>]+?)?>\\s*?</("+C+")>","img");
return B.replace(A,"<$1$2>&nbsp;</$3>")
},applyWhitelist:function(B){var A=this.whitelist;
var E=null;
var D=xq.compilePattern('(^|\\s")([^"=]+)(\\s|$)',"g");
var C=xq.compilePattern('(\\S+?)="[^"]*"',"g");
return B.replace(new RegExp("(</?)([^>]+?)(>|\\s+([^>]*?)(\\s?/?)>)","g"),function(J,M,O,I,N,K){E=A[O];
if(!E){return""
}if(N){if(xq.Browser.isTrident){N=N.replace(D,'$1$2="$2"$3')
}var L=[];
var G=N.match(C);
for(var H=0;
H<G.length;
H++){var F=G[H].split("=")[0];
if(E.indexOf(F.toLowerCase())!==-1){L.push(G[H])
}}if(L.length){N=L.join(" ");
return M+O+" "+N+K+">"
}else{return M+O+K+">"
}}else{return J
}})
},makeUrlsRelative:function(A){var D=this.curUrl;
var E=this.curUrlParts;
var C=xq.compilePattern('(href|src)="([^"]+)"',"g");
var B=xq.compilePattern("^\\w+://");
return A.replace(/(<\w+\s+)(\/|([^>]+?)(\/?))>/g,function(J,I,F,H,G){if(H){H=H.replace(C,function(O,N,M){var L=null;
if(M.charAt(0)==="#"){L=E.includeQuery+M
}else{if(M.charAt(0)==="?"){L=E.includePath+M
}else{if(M.charAt(0)==="/"){L=E.includeHost+M
}else{if(M.match(B)){L=M
}else{L=E.includeBase+M
}}}}var K=L;
if(L===E.includeHost){K="/"
}else{if(L.indexOf(E.includeQuery)===0){K=L.substring(E.includeQuery.length)
}else{if(L.indexOf(E.includePath)===0){K=L.substring(E.includePath.length)
}else{if(L.indexOf(E.includeBase)===0){K=L.substring(E.includeBase.length)
}else{if(L.indexOf(E.includeHost)===0){K=L.substring(E.includeHost.length)
}}}}}if(K===""){K="#"
}return N+'="'+K+'"'
});
return I+H+G+">"
}else{return J
}});
return A
},makeUrlsHostRelative:function(A){var D=this.curUrl;
var E=this.curUrlParts;
var C=xq.compilePattern('(href|src)="([^"]+)"',"g");
var B=xq.compilePattern("^\\w+://");
return A.replace(/(<\w+\s+)(\/|([^>]+?)(\/?))>/g,function(J,I,F,H,G){if(H){H=H.replace(C,function(O,N,M){var L=null;
if(M.charAt(0)==="#"){L=E.includeQuery+M
}else{if(M.charAt(0)==="?"){L=E.includePath+M
}else{if(M.charAt(0)==="/"){L=E.includeHost+M
}else{if(M.match(B)){L=M
}else{L=E.includeBase+M
}}}}var K=L;
if(L===E.includeHost){K="/"
}else{if(L.indexOf(E.includeQuery)===0&&L.indexOf("#")!==-1){K=L.substring(L.indexOf("#"))
}else{if(L.indexOf(E.includeHost)===0){K=L.substring(E.includeHost.length)
}}}if(K===""){K="#"
}return N+'="'+K+'"'
});
return I+H+G+">"
}else{return J
}});
return A
},makeUrlsAbsolute:function(A){var D=this.curUrl;
var E=this.curUrlParts;
var C=xq.compilePattern('(href|src)="([^"]+)"',"g");
var B=xq.compilePattern("^\\w+://");
return A.replace(/(<\w+\s+)(\/|([^>]+?)(\/?))>/g,function(J,I,F,H,G){if(H){H=H.replace(C,function(N,M,L){var K=null;
if(L.charAt(0)==="#"){K=E.includeQuery+L
}else{if(L.charAt(0)==="?"){K=E.includePath+L
}else{if(L.charAt(0)==="/"){K=E.includeHost+L
}else{if(L.match(B)){K=L
}else{K=E.includeBase+L
}}}}return M+'="'+K+'"'
});
return I+H+G+">"
}else{return J
}})
}});
xq.validator.Trident=xq.Class(xq.validator.Base,{validateDom:function(A){this.removeDangerousElements(A);
this.validateFont(A)
},validateString:function(B){try{B=this.addNbspToEmptyBlocks(B);
B=this.validateStrike(B);
B=this.validateUnderline(B);
B=this.performFullValidation(B)
}catch(A){}return B
},invalidateDom:function(A){this.invalidateFont(A);
this.invalidateStrikesAndUnderlines(A)
},invalidateString:function(A){A=this.removeComments(A);
return A
},performFullValidation:function(A){A=this.lowerTagNamesAndUniformizeQuotation(A);
A=this.validateSelfClosingTags(A);
A=this.applyWhitelist(A);
if(this.urlValidationMode==="relative"){A=this.makeUrlsRelative(A)
}else{if(this.urlValidationMode==="host_relative"){A=this.makeUrlsHostRelative(A)
}else{if(this.urlValidationMode==="absolute"){}}}return A
},lowerTagNamesAndUniformizeQuotation:function(A){this.pAttrQuotation1=xq.compilePattern('\\s(\\w+?)=\\s+"([^"]+)"',"mg");
this.pAttrQuotation2=xq.compilePattern('\\s(\\w+?)=([^ "]+)',"mg");
this.pAttrQuotation3=xq.compilePattern('\\sNAME="(\\w+?)" VALUE="(\\w+?)"',"mg");
A=A.replace(/<(\/?)(\w+)([^>]*?)>/img,function(E,B,D,C){return"<"+B+D.toLowerCase()+this.correctHtmlAttrQuotation(C)+">"
}.bind(this));
return A
},correctHtmlAttrQuotation:function(A){A=A.replace(this.pAttrQuotation1,function(D,B,C){return" "+B.toLowerCase()+'="'+C+'"'
});
A=A.replace(this.pAttrQuotation2,function(D,B,C){return" "+B.toLowerCase()+'="'+C+'"'
});
A=A.replace(this.pAttrQuotation3,function(D,B,C){return' name="'+B+'" value="'+C+'"'
});
return A
}});
xq.validator.W3=xq.Class(xq.validator.Base,{validateDom:function(A){var B=xq.rdom.Base.createInstance();
B.setRoot(A);
this.removeDangerousElements(A);
B.removePlaceHoldersAndEmptyNodes(A);
this.validateFont(A)
},validateString:function(B){try{B=this.replaceTag(B,"b","strong");
B=this.replaceTag(B,"i","em");
B=this.removeComments(B);
B=this.validateStrike(B);
B=this.validateUnderline(B);
B=this.addNbspToEmptyBlocks(B);
B=this.performFullValidation(B);
B=this.insertNewlineBetweenBlockElements(B)
}catch(A){}return B
},invalidateDom:function(A){this.invalidateFont(A);
this.invalidateStrikesAndUnderlines(A)
},invalidateString:function(A){A=this.replaceTag(A,"strong","b");
A=this.replaceTag(A,"em","i");
A=this.removeComments(A);
A=this.replaceNbspToBr(A);
return A
},performFullValidation:function(A){A=this.validateSelfClosingTags(A);
A=this.applyWhitelist(A);
if(this.urlValidationMode==="relative"){A=this.makeUrlsRelative(A)
}else{if(this.urlValidationMode==="host_relative"){A=this.makeUrlsHostRelative(A)
}else{if(this.urlValidationMode==="absolute"){A=this.makeUrlsAbsolute(A)
}}}return A
},insertNewlineBetweenBlockElements:function(A){var C=new xq.DomTree().getBlockTags().join("|");
var B=new RegExp("</("+C+")>([^\n])","img");
return A.replace(B,"</$1>\n$2")
},replaceNbspToBr:function(B){var D=new xq.DomTree().getBlockTags().join("|");
var A=new RegExp("<("+D+")([^>]+?)?>(&nbsp;|\xA0)?</("+D+")>","img");
var C=xq.rdom.Base.createInstance();
return B.replace(A,"<$1$2>"+C.makePlaceHolderString()+"</$4>")
}});
xq.validator.Gecko=xq.Class(xq.validator.W3,{});
xq.validator.Webkit=xq.Class(xq.validator.W3,{validateDom:function(A){var B=xq.rdom.Base.createInstance();
B.setRoot(A);
this.removeDangerousElements(A);
B.removePlaceHoldersAndEmptyNodes(A);
this.validateAppleStyleTags(A)
},validateString:function(B){try{B=this.addNbspToEmptyBlocks(B);
B=this.performFullValidation(B);
B=this.insertNewlineBetweenBlockElements(B)
}catch(A){}return B
},invalidateDom:function(A){this.invalidateAppleStyleTags(A)
},invalidateString:function(A){A=this.replaceTag(A,"strong","b");
A=this.replaceTag(A,"em","i");
A=this.removeComments(A);
A=this.replaceNbspToBr(A);
return A
},validateAppleStyleTags:function(E){try{var H=xq.rdom.Base.createInstance();
H.setRoot(E);
var A=xq.getElementsByClassName(H.getRoot(),"Apple-style-span");
var D=[];
for(var C=0;
C<A.length;
C++){var F=A[C];
if(F.style.fontStyle==="italic"){F=H.replaceTag("em",F);
F.style.fontStyle="";
D.push({node:F})
}else{if(F.style.fontWeight==="bold"){F=H.replaceTag("strong",F);
F.style.fontWeight="";
D.push({node:F})
}else{if(F.style.textDecoration==="underline"){F=H.replaceTag("em",F);
F.style.textDecoration="";
D.push({node:F,className:"underline"})
}else{if(F.style.textDecoration==="line-through"){F.style.textDecoration="";
D.push({node:F,className:"strike"})
}else{if(F.style.verticalAlign==="super"){F=H.replaceTag("sup",F);
F.style.verticalAlign="";
D.push({node:F})
}else{if(F.style.verticalAlign==="sub"){F=H.replaceTag("sub",F);
F.style.verticalAlign="";
D.push({node:F})
}else{if(F.style.fontFamily){D.push({node:F})
}}}}}}}}for(var B=0;
B<D.length;
B++){if(D[B].className){D[B].node.className=D[B].className
}else{D[B].node.removeAttribute("class")
}}}catch(G){}},invalidateAppleStyleTags:function(E){var J=xq.rdom.Base.createInstance();
J.setRoot(E);
var H;
var I=J.getRoot().getElementsByTagName("span");
for(var F=0;
F<I.length;
F++){var B=I[F];
if(B.className=="strike"){B.className="Apple-style-span";
B.style.textDecoration="line-through"
}else{if(B.style.fontFamily){B.className="Apple-style-span"
}}}var A=J.getRoot().getElementsByTagName("em");
H=A.length;
for(var F=0;
F<H;
F++){var B=A[0];
B=J.replaceTag("span",B);
if(B.className==="underline"){B.className="Apple-style-span";
B.style.textDecoration="underline"
}else{B.className="Apple-style-span";
B.style.fontStyle="italic"
}}var C=J.getRoot().getElementsByTagName("strong");
H=C.length;
for(var F=0;
F<H;
F++){var B=C[0];
B=J.replaceTag("span",B);
B.className="Apple-style-span";
B.style.fontWeight="bold"
}var G=J.getRoot().getElementsByTagName("sup");
H=G.length;
for(var F=0;
F<H;
F++){var B=G[0];
B=J.replaceTag("span",B);
B.className="Apple-style-span";
B.style.verticalAlign="super"
}var D=J.getRoot().getElementsByTagName("sub");
H=D.length;
for(var F=0;
F<H;
F++){var B=D[0];
B=J.replaceTag("span",B);
B.className="Apple-style-span";
B.style.verticalAlign="sub"
}}});
xq.validator.Base.createInstance=function(C,B,A){if(xq.Browser.isTrident){return new xq.validator.Trident(C,B,A)
}else{if(xq.Browser.isWebkit){return new xq.validator.Webkit(C,B,A)
}else{return new xq.validator.Gecko(C,B,A)
}}};
xq.EditHistory=xq.Class({initialize:function(B,A){xq.addToFinalizeQueue(this);
if(!B){throw"IllegalArgumentException"
}this.disabled=false;
this.max=A||100;
this.rdom=B;
this.index=-1;
this.queue=[];
this.lastModified=Date.get()
},getLastModifiedDate:function(){return this.lastModified
},isUndoable:function(){return this.queue.length>0&&this.index>0
},isRedoable:function(){return this.queue.length>0&&this.index<this.queue.length-1
},disable:function(){this.disabled=true
},enable:function(){this.disabled=false
},undo:function(){this.pushContent();
if(this.isUndoable()){this.index--;
this.popContent();
return true
}else{return false
}},redo:function(){if(this.isRedoable()){this.index++;
this.popContent();
return true
}else{return false
}},onCommand:function(){this.lastModified=Date.get();
if(this.disabled){return false
}return this.pushContent()
},onEvent:function(A){this.lastModified=Date.get();
if(this.disabled){return false
}var B=[33,34,35,36,37,39];
if(!xq.Browser.isMac){B.push(38,40)
}if(["blur","mouseup"].indexOf(A.type)!==-1){return false
}if("keydown"===A.type&&!(A.ctrlKey||A.metaKey)){return false
}if(["keydown","keyup","keypress"].indexOf(A.type)!==-1&&!A.ctrlKey&&!A.altKey&&!A.metaKey&&B.indexOf(A.keyCode)===-1){return false
}if(["keydown","keyup","keypress"].indexOf(A.type)!==-1&&(A.ctrlKey||A.metaKey)&&[89,90].indexOf(A.keyCode)!==-1){return false
}if([16,17,18,224].indexOf(A.keyCode)!==-1){return false
}return this.pushContent()
},popContent:function(){this.lastModified=Date.get();
var B=this.queue[this.index];
if(B.caret>0){var A=B.html.substring(0,B.caret)+'<span id="caret_marker_eh"></span>'+B.html.substring(B.caret);
this.rdom.getRoot().innerHTML=A
}else{this.rdom.getRoot().innerHTML=B.html
}this.restoreCaret()
},pushContent:function(B){if(xq.Browser.isTrident&&!B&&!this.rdom.hasFocus()){return false
}if(!this.rdom.getCurrentElement()){return false
}var A=this.rdom.getRoot().innerHTML;
if(A===(this.queue[this.index]?this.queue[this.index].html:null)){return false
}var C=B?-1:this.saveCaret();
if(this.queue.length>=this.max){this.queue.shift()
}else{this.index++
}this.queue.splice(this.index,this.queue.length-this.index,{html:A,caret:C});
return true
},clear:function(){this.index=-1;
this.queue=[];
this.pushContent(true)
},saveCaret:function(){if(this.rdom.hasSelection()){return null
}var A=this.rdom.pushMarker();
var C=xq.Browser.isTrident?"<SPAN class="+A.className:'<span class="'+A.className+'"';
var B=this.rdom.getRoot().innerHTML.indexOf(C);
this.rdom.popMarker(true);
return B
},restoreCaret:function(){var A=this.rdom.$("caret_marker_eh");
if(A){this.rdom.selectElement(A,true);
this.rdom.collapseSelection(false);
this.rdom.deleteNode(A)
}else{var B=this.rdom.tree.findForward(this.rdom.getRoot(),function(C){return this.isBlock(C)&&!this.hasBlocks(C)
}.bind(this.rdom.tree));
this.rdom.selectElement(B,false);
this.rdom.collapseSelection(false)
}}});
xq.plugin={};
xq.plugin.Base=xq.Class({initialize:function(){},load:function(A){this.editor=A;
if(this.isEventListener()){this.editor.addListener(this)
}this.onBeforeLoad(this.editor);
this.editor.addShortcuts(this.getShortcuts()||[]);
this.editor.addAutocorrections(this.getAutocorrections()||[]);
this.editor.addAutocompletions(this.getAutocompletions()||[]);
this.editor.addTemplateProcessors(this.getTemplateProcessors()||[]);
this.editor.addContextMenuHandlers(this.getContextMenuHandlers()||[]);
this.onAfterLoad(this.editor)
},unload:function(){this.onBeforeUnload(this.editor);
for(var A in this.getShortcuts()){this.editor.removeShortcut(A)
}for(var A in this.getAutocorrections()){this.editor.removeAutocorrection(A)
}for(var A in this.getAutocompletions()){this.editor.removeAutocompletion(A)
}for(var A in this.getTemplateProcessors()){this.editor.removeTemplateProcessor(A)
}for(var A in this.getContextMenuHandlers()){this.editor.removeContextMenuHandler(A)
}this.onAfterUnload(this.editor)
},isEventListener:function(){return false
},onBeforeLoad:function(A){},onAfterLoad:function(A){},onBeforeUnload:function(A){},onAfterUnload:function(A){},getShortcuts:function(){return[]
},getAutocorrections:function(){return[]
},getAutocompletions:function(){return[]
},getTemplateProcessors:function(){return[]
},getContextMenuHandlers:function(){return[]
}});
xq.RichTable=xq.Class({initialize:function(B,A){xq.addToFinalizeQueue(this);
this.rdom=B;
this.table=A
},collectCells:function(B){var C=[];
var A=this.getXIndexOf(B);
var E=0;
while(true){var D=this.getCellAt(A,E);
if(!D){break
}C.push(D);
E++
}return C
},insertNewRowAt:function(E,C){var F=this.rdom.createElement("TR");
var B=E.cells;
for(var D=0;
D<B.length;
D++){var A=this.rdom.createElement(B[D].nodeName);
this.rdom.correctEmptyElement(A);
F.appendChild(A)
}return this.rdom.insertNodeAt(F,E,C)
},insertNewCellAt:function(A,C){var B=this.collectCells(A);
for(var D=0;
D<B.length;
D++){var A=this.rdom.createElement(B[D].nodeName);
this.rdom.correctEmptyElement(A);
this.rdom.insertNodeAt(A,B[D],C)
}},deleteTable:function(A){return this.rdom.deleteNode(A)
},deleteRow:function(A){return this.rdom.removeBlock(A)
},deleteCell:function(A){if(!A.previousSibling&&!A.nextSibling){this.rdom.deleteNode(this.table);
return 
}var B=this.collectCells(A);
for(var C=0;
C<B.length;
C++){this.rdom.deleteNode(B[C])
}},getPreviousCellOf:function(A){if(A.previousSibling){return A.previousSibling
}var B=this.getPreviousRowOf(A.parentNode);
if(B){return B.lastChild
}return null
},getNextCellOf:function(A){if(A.nextSibling){return A.nextSibling
}var B=this.getNextRowOf(A.parentNode);
if(B){return B.firstChild
}return null
},getPreviousRowOf:function(B){if(B.previousSibling){return B.previousSibling
}var A=B.parentNode;
if(A.previousSibling&&A.previousSibling.lastChild){return A.previousSibling.lastChild
}return null
},getNextRowOf:function(B){if(B.nextSibling){return B.nextSibling
}var A=B.parentNode;
if(A.nextSibling&&A.nextSibling.firstChild){return A.nextSibling.firstChild
}return null
},getAboveCellOf:function(B){var C=this.getPreviousRowOf(B.parentNode);
if(!C){return null
}var A=this.getXIndexOf(B);
return C.cells[A]
},getBelowCellOf:function(B){var C=this.getNextRowOf(B.parentNode);
if(!C){return null
}var A=this.getXIndexOf(B);
return C.cells[A]
},getXIndexOf:function(A){var C=A.parentNode;
for(var B=0;
B<C.cells.length;
B++){if(C.cells[B]===A){return B
}}return -1
},getYIndexOf:function(A){var D=-1;
var C=row.parentNode;
for(var B=0;
B<C.rows.length;
B++){if(C.rows[B]===row){D=B;
break
}}if(this.hasHeadingAtTop()&&C.nodeName==="TBODY"){D=D+1
}return D
},getTableProperty:function(){var A={width:this.table.style.width||null,height:this.table.style.height||null,textAlign:this.table.style.textAlign||null,borderColor:this.table.style.borderLeftColor||null,borderWidth:this.table.style.borderLeftWidth.replace(/ .*/,"").replace(/[^0-9]/g,"")||null,backgroundColor:this.table.style.backgroundColor||null};
return A
},setTableProperty:function(A){this._setTableProperty(this.table,A)
},getRowProperty:function(A){var B={height:A.style.height||null,verticalAlign:A.style.verticalAlign||null,textAlign:A.style.textAlign||null,backgroundColor:A.style.backgroundColor||null};
return B
},setRowProperty:function(A,B){this._setTableProperty(A,B)
},getColumnProperty:function(A){var B={width:A.style.width||null,verticalAlign:A.style.verticalAlign||null,textAlign:A.style.textAlign||null,backgroundColor:A.style.backgroundColor||null};
return B
},setColumnProperty:function(A,C){for(var B=0;
B<A.offsetParent.rows.length;
B++){this._setTableProperty(A.offsetParent.rows[B].cells[A.cellIndex],C)
}},_setTableProperty:function(A,D){for(propName in D){if(D[propName]!=null){var C=typeof D[propName]=="string"?D[propName]:D[propName].size+D[propName].unit;
if(propName=="className"){A.className=D.className||""
}else{if(propName!="headerPositions"){var B=(propName=="width")?A.nodeName.toLowerCase()+"Width":propName;
A.style[propName]=(xed.config.enableTableInlineStyle||(C.length!=0&&C!=xq.RichTable.defaultPropertyValues[B]))?C:""
}}}}},getLocationOf:function(B){var A=this.getXIndexOf(B);
var C=this.getYIndexOf(B);
return{x:A,y:C}
},getCellAt:function(A,B){var B=this.getRowAt(B);
return(B&&B.cells.length>A)?B.cells[A]:null
},getRowAt:function(A){if(this.hasHeadingAtTop()){return A===0?this.table.tHead.rows[0]:this.table.tBodies[0].rows[A-1]
}else{var B=this.table.tBodies[0].rows;
return(B.length>A)?B[A]:null
}},getDom:function(){return this.table
},hasHeadingAtTop:function(){return !!(this.table.tHead&&this.table.tHead.rows[0])
},hasHeadingAtLeft:function(){return this.table.tBodies[0].rows[0].cells[0].nodeName==="TH"
},correctEmptyCells:function(){var A=xq.$A(this.table.getElementsByTagName("TH"));
var C=xq.$A(this.table.getElementsByTagName("TD"));
for(var B=0;
B<C.length;
B++){A.push(C[B])
}for(var B=0;
B<A.length;
B++){if(this.rdom.isEmptyBlock(A[B])){this.rdom.correctEmptyElement(A[B])
}}}});
xq.RichTable.defaultPropertyValues={borderColor:"#000000",borderWidth:1,backgroundColor:"#FFFFFF",tableWidth:"100%",columnWidth:"auto",height:"auto",textAlign:"",verticalAlign:"top"};
xq.RichTable.create=function(D,F){if(["t","tl","lt"].indexOf(F.headerPositions)!==-1){var H=true
}if(["l","tl","lt"].indexOf(F.headerPositions)!==-1){var I=true
}var E=[];
E.push('<table class="datatable2" style="width:100%;">');
if(H){E.push("<thead><tr>");
for(var C=0;
C<F.cols;
C++){E.push("<th></th>")
}E.push("</tr></thead>");
F.rows-=1
}E.push("<tbody>");
for(var C=0;
C<F.rows;
C++){E.push("<tr>");
for(var B=0;
B<F.cols;
B++){if(I&&B===0){E.push("<th></th>")
}else{E.push("<td></td>")
}}E.push("</tr>")
}E.push("</tbody>");
E.push("</table>");
var A=D.createElement("div");
A.innerHTML=E.join("");
var G=new xq.RichTable(D,A.firstChild);
G.correctEmptyCells();
return G
};
xq.RichTableController={dialogType:null,initDialog:function(A,C){xq.$("tableDialog").tableTypeField.value="";
this.changeType(xq.$("tableTypeDefaultValue"),"");
xq.$("tableRowsField").value="3";
xq.$("tableColsField").value="3";
this.previewTable();
var B=xq.RichTable.defaultPropertyValues;
xq.getElementsByClassName(xq.$("tableDialog"),"tableWidths")[0].selectedIndex=(A=="new"||A=="table")?0:1;
xq.$("tableWidthValue").value=(A=="new"||A=="table")?B.tableWidth:B.columnWidth;
xq.$("tableWidthValue").style.display="none";
xq.$("tableWidthValueUnit").value="";
xq.getElementsByClassName(xq.$("tableDialog"),"tableHeights")[0].selectedIndex=0;
xq.$("tableHeightValue").value=B.height;
xq.$("tableHeightValue").style.display="none";
xq.$("tableHeightValueUnit").value="";
xq.$("tableDialog").tableHorizontalAlign.selectedIndex=0;
xq.$("tableDialog").tableVerticalAlign.selectedIndex=1;
xq.$("tableDialog").tableBorderColor.parentNode.getElementsByTagName("A")[0].style.backgroundColor=B.borderColor;
xq.$("tableDialog").tableBorderColor.value=B.borderColor;
xq.$("tableDialog").tableBorderSize.value=B.borderWidth;
xq.$("tableDialog").tableBackgroundColor.parentNode.getElementsByTagName("A")[0].style.backgroundColor=B.backgroundColor;
xq.$("tableDialog").tableBackgroundColor.value=B.backgroundColor;
if(C){this.setDialog(C)
}},setDialog:function(C){if(typeof C.width!="undefined"||C.width!=null){xq.$("tableWidthValue").value=C.width;
if(C.width.match(/(\%|px)/)){xq.getElementsByClassName(xq.$("tableDialog"),"tableWidths")[0].selectedIndex=(C.width.indexOf("%")!=-1)?3:2;
xq.$("tableWidthValue").style.display="inline";
xq.$("tableWidthValueUnit").value=(C.width.indexOf("%")!=-1)?"%":"px"
}}if(typeof C.height!="undefined"||C.height!=null){if(C.height.indexOf("px")!=-1){xq.getElementsByClassName(xq.$("tableDialog"),"tableHeights")[0].selectedIndex=1;
xq.$("tableHeightValue").style.display="inline";
xq.$("tableHeightValue").value=C.height;
xq.$("tableHeightValueUnit").value="px"
}}if(typeof C.verticalAlign!="undefined"||C.verticalAlign!=null){var A={top:0,middle:1,bottom:2};
xq.$("tableDialog").tableVerticalAlign.selectedIndex=A[C.verticalAlign]
}if(typeof C.textAlign!="undefined"||C.textAlign!=null){var B={left:0,center:1,right:2};
xq.$("tableDialog").tableHorizontalAlign.selectedIndex=B[C.textAlign]
}if(typeof C.borderColor!="undefined"||C.borderColor!=null){xq.$("tableDialog").tableBorderColor.parentNode.getElementsByTagName("A")[0].style.backgroundColor=C.borderColor;
xq.$("tableDialog").tableBorderColor.value=C.borderColor
}if(typeof C.borderWidth!="undefined"||C.borderWidth!=null){xq.$("tableDialog").tableBorderSize.value=C.borderWidth
}if(typeof C.backgroundColor!="undefined"||C.backgroundColor!=null){xq.$("tableDialog").tableBackgroundColor.parentNode.getElementsByTagName("A")[0].style.backgroundColor=C.backgroundColor;
xq.$("tableDialog").tableBackgroundColor.value=C.backgroundColor
}},openDialog:function(C,B){var D=xq.$("tableDialog");
if(D&&D.style.display!="none"){this.lastTableDialog.close()
}var F=xed.rdom.getSelectionAsText()||"";
var A=new xq.ui.FormDialog(xed,xq.ui_templates.basicTableDialog,function(G){var H=C=="new";
var I;
if(C!="new"){switch(C){case"table":I=xed.handleTableProperty();
break;
case"row":I=xed.handleRowProperty();
break;
case"column":I=xed.handleColumnProperty();
break
}}xq.$("tableDialog").className+=" "+C;
xq.RichTableController.initDialog(C,I)
},function(H){xed.focus();
if(xq.Browser.isTrident){var G=xed.rdom.rng();
G.moveToBookmark(E);
G.select()
}if(!H){return 
}xq.RichTableController.submit()
});
if(xq.Browser.isTrident){var E=xed.rdom.rng().getBookmark()
}A.show({position:"centerOfEditor"});
this.dialogType=C;
this.lastTableDialog=A;
return true
},submit:function(){var B=this.dialogType;
var C={};
if(B=="new"){if(!xq.$("tableDialog").tableCols.value.replace(/[^0-9]/g,"")||parseInt(xq.$("tableDialog").tableCols.value,10)>30){alert(xed._("Please enter column value between 1 to 30."));
xq.$("tableDialog").tableCols.focus();
return false
}if(!xq.$("tableDialog").tableRows.value.replace(/[^0-9]/g,"")||parseInt(xq.$("tableDialog").tableRows.value,10)>120){alert(xed._("Please enter row value between 1 to 120."));
xq.$("tableDialog").tableRows.focus();
return false
}C.cols=xq.$("tableDialog").tableCols.value;
C.rows=xq.$("tableDialog").tableRows.value;
C.headerPositions=xq.$("tableDialog").tableType.value
}if(B!="row"){if(xq.$("tableDialog").tableWidth.value>0){C.width={};
C.width.size=parseInt(xq.$("tableDialog").tableWidth.value,10);
C.width.unit=xq.$("tableDialog").tableWidthUnit.value
}else{C.width=xq.$("tableDialog").tableWidth.value
}}if(B!="column"){if(xq.$("tableDialog").tableHeight.value>0){C.height={};
C.height.size=parseInt(xq.$("tableDialog").tableHeight.value,10);
C.height.unit=xq.$("tableDialog").tableHeightUnit.value
}else{C.height=xq.$("tableDialog").tableHeight.value
}}if(B=="row"||B=="column"){C.verticalAlign=xq.$("tableDialog").tableVerticalAlign.value;
C.textAlign=xq.$("tableDialog").tableHorizontalAlign.value
}if(B=="new"||B=="table"){C.borderColor=xq.$("tableDialog").tableBorderColor.value
}var A=xed.rdom.getParentElementOf(xed.rdom.getCurrentBlockElement(),["TABLE"]);
C.className=(A)?A.className:"";
if(C.className.indexOf("dataTable2")==-1){C.className+=(A&&A.className)?" datatable2":"datatable2"
}if(B=="new"||B=="table"){C.borderWidth=xq.$("tableDialog").tableBorderSize.value;
if(xq.$("tableDialog").tableBorderSize.value<1&&C.className.indexOf("zeroborder")==-1){C.className+=" zeroborder"
}else{if(xq.$("tableDialog").tableBorderSize.value>0){C.className=C.className.replace("zeroborder","")
}}}C.backgroundColor=xq.$("tableDialog").tableBackgroundColor.value;
xed.focus();
switch(B){case"new":xed.handleTable(C);
break;
case"table":xed.handleTableProperty(C);
break;
case"row":xed.handleRowProperty(C);
break;
case"column":xed.handleColumnProperty(C);
break
}return false
},insertParagraph:function(A){var D=xed.rdom.getCurrentBlockElement();
if(!D){return 
}var B=xed.rdom.getParentElementOf(D,["TABLE"]);
if(!B){return true
}var C=xed.rdom.insertNodeAt(xed.rdom.makeEmptyParagraph(),B,A);
xed.rdom.placeCaretAtStartOf(C);
xed.focus()
},changeType:function(B,C){var D=B.parentNode.parentNode.getElementsByTagName("A");
for(var A=0;
A<D.length;
A++){D[A].className=""
}B.className="selected";
xq.$("tableTypeField").value=C;
return false
},changeSize:function(B){var A=B.parentNode.parentNode.getElementsByTagName("INPUT")[0];
if(B.className.indexOf("plus")!=-1){A.value++
}else{if(A.value>1||(A.name=="tableBorderSize"&&A.value>0)){A.value--
}}this.previewTable()
},previewTable:function(){var H=xq.$("previewTable").getElementsByTagName("TABLE")[0];
if(H.tBodies.length>0){H=H.tBodies[0]
}var I=parseInt(xq.$("tableRowsField").value,10);
var A=parseInt(xq.$("tableColsField").value,10);
if(I<1){xq.$("tableRowsField").value=I=1
}if(I>120){xq.$("tableRowsField").value=I=120
}if(A<1){xq.$("tableColsField").value=A=1
}if(A>30){xq.$("tableColsField").value=A=30
}I=Math.min(parseInt(xq.$("tableRowsField").value,10),20);
var G=I-H.rows.length;
var B=A-H.rows[0].cells.length;
for(var E=0;
E<Math.abs(G);
E++){if(G>0){H.appendChild(H.rows[0].cloneNode(true))
}else{H.deleteRow(0)
}}for(var D=0;
D<Math.abs(B);
D++){for(var C=0;
C<H.rows.length;
C++){var F=H.rows[C];
if(B>0){F.insertCell(0)
}else{F.deleteCell(0)
}}}},changeStyle:function(A){var B=(A.className.indexOf("Width")!=-1)?"Width":"Height";
switch(A.value){case"fullsize":xq.$("table"+B+"Value").value="100";
xq.$("table"+B+"ValueUnit").value="%";
xq.$("table"+B+"Value").style.display="none";
break;
case"content":xq.$("table"+B+"Value").value="";
xq.$("table"+B+"ValueUnit").value="";
xq.$("table"+B+"Value").style.display="none";
break;
case"pixel":xq.$("table"+B+"Value").value="";
xq.$("table"+B+"ValueUnit").value="px";
xq.$("table"+B+"Value").style.display="inline";
break;
case"percentage":xq.$("table"+B+"Value").value="100";
xq.$("table"+B+"ValueUnit").value="%";
xq.$("table"+B+"Value").style.display="inline";
break
}},showColorPicker:function(B){xed.lastAnchor=B;
var A=xq.$("foregroundColorDialog");
A.style.display="block";
A.style.position="absolute";
A.style.top=B.offsetTop+xq.$("tableDialog").offsetTop+B.offsetHeight+2+"px";
A.style.left=B.offsetLeft+xq.$("tableDialog").offsetLeft+"px"
}};
xq.ui={};
xq.ui.FormDialog=xq.Class({initialize:function(D,C,B,A){xq.addToFinalizeQueue(this);
this.xed=D;
this.html=C;
this.onLoadHandler=B||function(){};
this.onCloseHandler=A||function(){};
this.form=null
},show:function(A){A=A||{};
A.position=A.position||"centerOfWindow";
A.mode=A.mode||"modal";
A.cancelOnEsc=A.cancelOnEsc||true;
if(typeof A.dialogId==="undefined"){this._createDialog(A)
}else{if(!xq.$(A.dialogId)){this._createDialog(A)
}else{xq.$(A.dialogId).style.display=""
}}},_createDialog:function(D){var B=this;
var A=document.createElement("DIV");
A.style.display="none";
document.body.appendChild(A);
A.innerHTML=this.html;
this.form=A.getElementsByTagName("FORM")[0];
var G=xq.getElementsByClassName(this.form,"submit");
for(var E=0;
E<G.length;
E++){G[E].onclick=function(){B.onCloseHandler(xq.serializeForm(this.form));
if(!D.notSelfClose){B.close()
}return false
}.bind(this)
}var H=xq.getElementsByClassName(this.form,"cancel");
for(var C=0;
C<H.length;
C++){H[C].onclick=function(){B.onCloseHandler();
B.close()
}
}if(D.mode==="modal"){this.dimmed=document.createElement("DIV");
this.dimmed.style.position="absolute";
this.dimmed.style.backgroundColor="black";
this.dimmed.style.opacity=0.5;
this.dimmed.style.filter="alpha(opacity=50)";
this.dimmed.style.zIndex=902;
this.dimmed.style.top="0px";
this.dimmed.style.left="0px";
this.xed.outmostWrapper.appendChild(this.dimmed);
this.resizeDimmedDiv=function(I){this.dimmed.style.display="none";
this.dimmed.style.width=this.xed.outmostWrapper.offsetWidth+"px";
this.dimmed.style.height=this.xed.outmostWrapper.offsetHeight+"px";
this.dimmed.style.display="block"
}.bind(this);
xq.observe(window,"resize",this.resizeDimmedDiv);
this.resizeDimmedDiv()
}this.xed.toolbar.dialogContainer.appendChild(this.form);
A.parentNode.removeChild(A);
this.setPosition(D.position);
var F=xq.getElementsByClassName(this.form,"initialFocus");
if(F.length>0){setTimeout(function(){F[0].focus()
},0)
}if(D.cancelOnEsc){xq.observe(this.form,"keydown",function(I){if(I.keyCode===27){this.onCloseHandler();
this.close()
}}.bind(this))
}this.onLoadHandler(this)
},close:function(){if(this.form.parentNode){this.form.parentNode.removeChild(this.form)
}if(this.dimmed){this.dimmed.parentNode.removeChild(this.dimmed);
this.dimmed=null;
xq.stopObserving(window,"resize",this.resizeDimmedDiv);
this.resizeDimmedDiv=null
}},setPosition:function(H){var C=null;
var G=0;
var F=0;
if(H==="centerOfWindow"){C=document.documentElement||document.body;
G+=C.scrollLeft;
F+=C.scrollTop
}else{if(H==="centerOfEditor"){C=this.xed.getCurrentEditMode()=="wysiwyg"?this.xed.wysiwygEditorDiv:this.xed.sourceEditorDiv
}else{if(H==="nearbyCaret"){throw"Not implemented yet"
}else{throw"Invalid argument: "+H
}}}var D=C.clientWidth;
var A=C.clientHeight;
var E=this.form.clientWidth;
var B=this.form.clientHeight;
G+=parseInt((D-E)/2);
F+=parseInt((A-B)/2);
this.form.style.left=Math.max(G,0)+"px";
this.form.style.top=Math.max(F,0)+"px"
}});
xq.ui.QuickSearchDialog=xq.Class({initialize:function(A,B){xq.addToFinalizeQueue(this);
this.xed=A;
this.rdom=xq.rdom.Base.createInstance();
this.param=B;
if(!this.param.renderItem){this.param.renderItem=function(C){return this.rdom.getInnerText(C)
}.bind(this)
}this.container=null
},getQuery:function(){if(!this.container){return""
}return this._getInputField().value
},onSubmit:function(A){if(this.matchCount()>0){this.param.onSelect(this.xed,this.list[this._getSelectedIndex()])
}this.close();
xq.stopEvent(A);
return false
},onCancel:function(A){if(this.param.onCancel){this.param.onCancel(this.xed)
}this.close()
},onBlur:function(A){setTimeout(function(){this.onCancel(A)
}.bind(this),400)
},onKey:function(C){var B=new xq.Shortcut("ESC");
var D=new xq.Shortcut("ENTER");
var A=new xq.Shortcut("UP");
var E=new xq.Shortcut("DOWN");
if(B.matches(C)){this.onCancel(C)
}else{if(D.matches(C)){this.onSubmit(C)
}else{if(A.matches(C)){this._moveSelectionUp()
}else{if(E.matches(C)){this._moveSelectionDown()
}else{this.updateList()
}}}}},onClick:function(C){var B=C.srcElement||C.target;
if(B.nodeName==="LI"){var A=this._getIndexOfLI(B);
this.param.onSelect(this.xed,this.list[A])
}},onList:function(A){this.list=A;
this.renderList(A)
},updateList:function(){window.setTimeout(function(){this.param.listProvider(this.getQuery(),this.xed,this.onList.bind(this))
}.bind(this),0)
},renderList:function(D){var B=this._getListContainer();
B.innerHTML="";
for(var C=0;
C<D.length;
C++){var A=this.rdom.createElement("LI");
A.innerHTML=this.param.renderItem(D[C]);
B.appendChild(A)
}if(B.hasChildNodes()){B.firstChild.className="selected"
}},show:function(){if(!this.container){this.container=this._create()
}var A=this.rdom.insertNodeAt(this.container,this.rdom.getRoot(),"end");
this.setPosition("centerOfEditor");
this.updateList();
this.focus()
},close:function(){this.rdom.deleteNode(this.container)
},focus:function(){this._getInputField().focus()
},setPosition:function(E){var F=null;
var B=0;
var H=0;
if(E==="centerOfWindow"){B+=F.scrollLeft;
H+=F.scrollTop;
F=document.documentElement
}else{if(E==="centerOfEditor"){F=this.xed.getCurrentEditMode()=="wysiwyg"?this.xed.wysiwygEditorDiv:this.xed.sourceEditorDiv;
var A=F;
do{B+=A.offsetLeft;
H+=A.offsetTop
}while(A=A.offsetParent)
}else{if(E==="nearbyCaret"){throw"Not implemented yet"
}else{throw"Invalid argument: "+E
}}}var I=F.clientWidth;
var D=F.clientHeight;
var C=this.container.clientWidth;
var G=this.container.clientHeight;
B+=parseInt((I-C)/2);
H+=parseInt((D-G)/2);
this.container.style.left=B+"px";
this.container.style.top=H+"px"
},matchCount:function(){return this.list?this.list.length:0
},_create:function(){var A=this.rdom.createElement("DIV");
A.className="xqQuickSearch";
if(this.param.title){var F=this.rdom.createElement("H1");
F.innerHTML=this.param.title;
A.appendChild(F)
}var C=this.rdom.createElement("DIV");
C.className="input";
var D=this.rdom.createElement("FORM");
var B=this.rdom.createElement("INPUT");
B.type="text";
B.value="";
D.appendChild(B);
C.appendChild(D);
A.appendChild(C);
var E=this.rdom.createElement("OL");
xq.observe(B,"blur",this.onBlur.bindAsEventListener(this));
xq.observe(B,"keypress",this.onKey.bindAsEventListener(this));
xq.observe(E,"click",this.onClick.bindAsEventListener(this),true);
xq.observe(D,"submit",this.onSubmit.bindAsEventListener(this));
xq.observe(D,"reset",this.onCancel.bindAsEventListener(this));
A.appendChild(E);
return A
},_getInputField:function(){return this.container.getElementsByTagName("INPUT")[0]
},_getListContainer:function(){return this.container.getElementsByTagName("OL")[0]
},_getSelectedIndex:function(){var A=this._getListContainer();
for(var B=0;
B<A.childNodes.length;
B++){if(A.childNodes[B].className==="selected"){return B
}}},_getIndexOfLI:function(A){var B=this._getListContainer();
for(var C=0;
C<B.childNodes.length;
C++){if(B.childNodes[C]===A){return C
}}},_moveSelectionUp:function(){var C=this.matchCount();
if(C===0){return 
}var B=this._getSelectedIndex();
var A=this._getListContainer();
A.childNodes[B].className="";
B--;
if(B<0){B=C-1
}A.childNodes[B].className="selected"
},_moveSelectionDown:function(){var C=this.matchCount();
if(C===0){return 
}var B=this._getSelectedIndex();
var A=this._getListContainer();
A.childNodes[B].className="";
B++;
if(B>=C){B=0
}A.childNodes[B].className="selected"
}});
xq.ui.Toolbar=xq.Class({initialize:function(G,C,H,E,D,B,A){xq.addToFinalizeQueue(this);
this.xed=G;
if(typeof C==="string"){C=xq.$(C)
}if(C&&C.nodeType!==1){throw"[container] is not an element"
}this.wrapper=H;
this.doc=this.wrapper.ownerDocument;
this.buttonMap=E;
this.buttonList=D;
this.imagePath=B;
this.structureAndStyleCollector=A;
this.buttons=null;
this.anchorsCache=[];
this._scheduledUpdate=null;
if(!C){this.create();
this._addStyleRules([{selector:".xquared div.toolbar",rule:"background-image: url("+B+"toolbarBg.gif)"},{selector:".xquared ul.buttons li",rule:"background-image: url("+B+"toolbarButtonBg.gif)"},{selector:".xquared ul.buttons li.xq_separator",rule:"background-image: url("+B+"toolbarSeparator.gif)"}])
}else{this.container=C;
if(!this.dialogContainer){var F=this.doc.createElement("div");
F.className="dialogs";
this.dialogContainer=F;
this.wrapper.appendChild(F)
}}G.addListener({onEditorInitialized:function(){xq.observe(G.getDoc(),"mousedown",this._closeAllLightweight.bindAsEventListener(this));
xq.observe(document,"mousedown",this._closeAllLightweight.bindAsEventListener(this))
}.bind(this)})
},finalize:function(){for(var A=0;
A<this.anchorsCache.length;
A++){this.anchorsCache[A].xed=null;
this.anchorsCache[A].handler=null;
this.anchorsCache[A]=null
}this.toolbarAnchorsCache=null
},triggerUpdate:function(){if(this._scheduledUpdate){return 
}this._scheduledUpdate=window.setTimeout(function(){this._scheduledUpdate=null;
var A=this.structureAndStyleCollector();
if(A){this.update(A)
}}.bind(this),200)
},update:function(E){if(!this.container){return 
}if(!this.buttons){var F=["emphasis","strongEmphasis","underline","strike","superscription","subscription","justifyLeft","justifyCenter","justifyRight","justifyBoth","unorderedList","orderedList","code","paragraph","heading1","heading2","heading3","heading4","heading5","heading6"];
this.buttons={};
for(var B=0;
B<F.length;
B++){var D=xq.getElementsByClassName(this.container,F[B]);
var A=D&&D.length>0?D[0]:null;
if(A){this.buttons[F[B]]=A
}}}var C=this.buttons;
this._updateButtonStatus("emphasis",E.em);
this._updateButtonStatus("strongEmphasis",E.strong);
this._updateButtonStatus("underline",E.underline);
this._updateButtonStatus("strike",E.strike);
this._updateButtonStatus("superscription",E.superscription);
this._updateButtonStatus("subscription",E.subscription);
this._updateButtonStatus("justifyLeft",E.justification==="left");
this._updateButtonStatus("justifyCenter",E.justification==="center");
this._updateButtonStatus("justifyRight",E.justification==="right");
this._updateButtonStatus("justifyBoth",E.justification==="justify");
this._updateButtonStatus("orderedList",E.list==="OL");
this._updateButtonStatus("unorderedList",E.list==="UL");
this._updateButtonStatus("code",E.list==="CODE");
this._updateButtonStatus("paragraph",E.block==="P");
this._updateButtonStatus("heading1",E.block==="H1");
this._updateButtonStatus("heading2",E.block==="H2");
this._updateButtonStatus("heading3",E.block==="H3");
this._updateButtonStatus("heading4",E.block==="H4");
this._updateButtonStatus("heading5",E.block==="H5");
this._updateButtonStatus("heading6",E.block==="H6")
},enableButtons:function(A){if(!this.container){return 
}this._execForAllButtons(A,function(B,C){B.firstChild.className=!C?"":"disabled"
});
if(xq.Browser.isIE6){this.container.style.display="none";
setTimeout(function(){this.container.style.display="block"
}.bind(this),0)
}},disableButtons:function(A){this._execForAllButtons(A,function(B,C){B.firstChild.className=C?"":"disabled"
})
},create:function(){this.container=this.doc.createElement("div");
this.container.className="toolbar";
var H=this.doc.createElement("ul");
H.className="buttons";
this.container.appendChild(H);
var G=this.doc.createElement("div");
G.className="dialogs";
this.dialogContainer=G;
this.wrapper.appendChild(G);
if(this.buttonList.length!==0){var C=this.buttonList.length;
for(var E=0;
E<C;
E++){if(this.buttonList[E]=="separator"){continue
}var B=this.buttonList[E];
var A=this.doc.createElement("li");
H.appendChild(A);
A.className=B.className;
if(typeof this.buttonList[E-1]!=="undefined"&&this.buttonList[E-1]=="separator"){A.className+=" xq_separator"
}var F=this.doc.createElement("span");
A.appendChild(F);
if(B.list){this._createDropdown(B,F)
}else{this._createButton(B,F)
}}}else{if(this.buttonMap){for(var E=0;
E<this.buttonMap.length;
E++){for(var D=0;
D<this.buttonMap[E].length;
D++){var B=this.buttonMap[E][D];
var A=this.doc.createElement("li");
H.appendChild(A);
A.className=B.className;
var F=this.doc.createElement("span");
A.appendChild(F);
if(B.list){this._createDropdown(B,F)
}else{this._createButton(B,F)
}if(D===0&&E!==0){A.className+=" xq_separator"
}}}}}this.wrapper.appendChild(this.container)
},_createButton:function(B,D){var A=this.doc.createElement("a");
D.appendChild(A);
A.href="#";
A.title=B.title;
if(B.handler){A.handler=B.handler;
xq.observe(A,"click",this._clickHandler.bindAsEventListener(this))
}this.anchorsCache.push(A);
xq.observe(A,"mousedown",xq.cancelHandler);
var C=this.doc.createElement("img");
A.appendChild(C);
C.className=B.className;
C.src=this.imagePath+B.className+".gif";
if(B.title){C.alt=B.title
}else{C.alt=B.className
}return A
},_createDropdown:function(A,J){var B=this._createButton(A,J);
B.items=A.list;
xq.observe(B,"click",this._openDropdownDialog.bindAsEventListener(this));
var F=this.doc.createElement("DIV");
F.id=A.className+"Dialog";
F.className="xqFormDialog lightweight";
F.style.display="none";
var H=this.doc.createElement("H3");
H.innerHTML=A.title;
F.appendChild(H);
var I=this.doc.createElement("DIV");
I.className="dialog-content";
var E=this.doc.createElement("UL");
E.className="item-list";
for(var D=0;
D<B.items.length;
D++){var L=B.items[D];
var K=this.doc.createElement("LI");
var C=this.doc.createElement("A");
K.appendChild(C);
if(L.html){if(A.className=="emoticon"){var G=this.doc.createElement("IMG");
G.src=this.xed.config.imagePathForEmoticon+L.html;
G.alt=L.html;
C.appendChild(G)
}else{C.innerHTML=decodeURIComponent(L.html)
}}C.href="#";
C.handler=L.handler;
for(attr in L.style){C.style[attr]=L.style[attr]
}xq.observe(C,"click",xq.cancelHandler);
xq.observe(C,"mouseup",this._closeAllLightweight.bindAsEventListener(this));
xq.observe(C,"mousedown",this._clickHandler.bindAsEventListener(this));
E.appendChild(K)
}I.appendChild(E);
F.appendChild(I);
this.dialogContainer.appendChild(F);
J.appendChild(B)
},_openDropdownDialog:function(B){this._closeAllLightweight(B);
var C=B.target||B.srcElement;
this.xed.lastAnchor=C;
var A=xq.$(C.className+"Dialog");
if(A){A.style.display="block";
A.style.top=this.container.offsetTop+this.container.offsetHeight+"px";
A.style.left=this.container.offsetLeft+C.parentNode.offsetLeft+"px"
}xq.stopEvent(B);
return false
},_closeAllLightweight:function(D){if(D){var E=D.target||D.srcElement;
var C=xq.$("linkDialog");
if((C!==null&&C.style.display!=="none")||E.id.indexOf("extForeColor")!==-1||E.className.indexOf("jscolor")!==-1){return false
}}var B=xq.getElementsByClassName(this.dialogContainer,"lightweight");
for(var A=0;
A<B.length;
A++){B[A].style.display="none"
}},_clickHandler:function(e){var src=e.target||e.srcElement;
while(src.nodeName!=="A"){src=src.parentNode
}if(xq.hasClassName(src.parentNode,"disabled")||xq.hasClassName(this.container,"disabled")){xq.stopEvent(e);
return false
}var handler=src.handler;
var xed=this.xed;
xed.focus();
if(typeof handler==="function"){handler(this)
}else{eval(handler)
}xq.stopEvent(e);
return false
},_updateButtonStatus:function(D,C){var B=this.buttons[D];
if(B){var A=C?"selected":"";
var E=B.firstChild.firstChild;
if(E.className!==A){E.className=A
}}},_execForAllButtons:function(E,A){if(!this.container){return 
}E=E||[];
var B=this.container.getElementsByTagName("LI");
for(var D=0;
D<B.length;
D++){var F=B[D].className.split(" ").find(function(G){return G!=="xq_separator"
});
var C=E.indexOf(F)!==-1;
A(B[D],C)
}},_addStyleRules:function(D){if(!this.dynamicStyle){if(xq.Browser.isTrident){this.dynamicStyle=this.doc.createStyleSheet()
}else{var B=this.doc.createElement("style");
this.doc.body.appendChild(B);
this.dynamicStyle=xq.$A(this.doc.styleSheets).last()
}}for(var A=0;
A<D.length;
A++){var C=D[A];
if(xq.Browser.isTrident){this.dynamicStyle.addRule(D[A].selector,D[A].rule)
}else{this.dynamicStyle.insertRule(D[A].selector+" {"+D[A].rule+"}",this.dynamicStyle.cssRules.length)
}}}});
if(!xq){xq={}
}if(!xq.ui_templates){xq.ui_templates={}
}xq.ui_templates.basicColorPickerDialog='<form action="#" class="xqFormDialog xqBasicColorPickerDialog">\n		<div>\n			<label>\n				<input type="radio" class="initialFocus" name="color" value="black" checked="checked" />\n				<span style="color: black;">Black</span>\n			</label>\n			<label>\n				<input type="radio" name="color" value="red" />\n				<span style="color: red;">Red</span>\n			</label>\n				<input type="radio" name="color" value="yellow" />\n				<span style="color: yellow;">Yellow</span>\n			</label>\n			</label>\n				<input type="radio" name="color" value="pink" />\n				<span style="color: pink;">Pink</span>\n			</label>\n			<label>\n				<input type="radio" name="color" value="blue" />\n				<span style="color: blue;">Blue</span>\n			</label>\n			<label>\n				<input type="radio" name="color" value="green" />\n				<span style="color: green;">Green</span>\n			</label>\n			\n			<input type="submit" value="Ok" />\n			<input type="button" class="cancel" value="Cancel" />\n		</div>\n	</form>';
if(!xq){xq={}
}if(!xq.ui_templates){xq.ui_templates={}
}xq.ui_templates.basicFileUploadDialog='<form id="fileUploadDialog" class="xqFormDialog modal" method="post" enctype="multipart/form-data" target="uploadTarget" action="">\n		<h3>Upload image</h3>\n		<div class="dialog-content">\n			<div class="attachment-section">\n				<label class="label" for="searchAttachFile">Find a file</label><input type="file" class="type-text" id="searchAttachFile" name="FileData" />\n			</div>\n			<div class="dialog-buttons">\n				<a href="#" class="button-white cancel">Close</a>\n				<a href="#" class="button-gray submit">Insert</a>\n			</div>\n		</div>\n		<iframe id="uploadTarget" name="uploadTarget" src="" style="width:0;height:0;border:0px solid #fff;"></iframe>\n		<a href="#" class="cancel close-dialog"><img src="../images/dialogs/icon_close.gif" alt="Close" /></a>\n	</form>';
if(!xq){xq={}
}if(!xq.ui_templates){xq.ui_templates={}
}xq.ui_templates.basicIFrameDialog='<form id="iframeDialog" class="xqFormDialog modal">\n		<h3>Insert IFrame</h3>\n		<div class="dialog-content">\n			<p>IFrame src: <input type="text" class="initialFocus type-text" name="p_src" size="36" value="http://" /></p>\n			<p>Width: <input type="text" class="type-text" name="p_width" size="6" value="320" /></p>\n			<p>Height: <input type="text" class="type-text" name="p_height" size="6" value="200" /></p>\n			<p>Frame border:\n				<select name="p_frameborder">\n					<option value="0" selected="selected">No</option>\n					<option value="1">Yes</option>\n				</select></p>\n			<p>Scrolling: \n				<select name="p_scrolling">\n					<option value="0">No</option>\n					<option value="1" selected="selected">Yes</option>\n				</select></p>\n			<p>ID(optional): <input type="text" class="type-text" name="p_id" size="24" value="" /></p>\n			<p>Class(optional): <input type="text" class="type-text" name="p_class" size="24" value="" /></p>\n			\n			<div class="dialog-buttons">\n				<a href="#" class="button-white cancel">Close</a>\n				<a href="#" class="button-gray submit">Insert</a>\n			</div>\n		</div>\n		<a href="#" class="cancel close-dialog"><img src="../images/dialogs/icon_close.gif" alt="close" /></a>\n	</form>';
if(!xq){xq={}
}if(!xq.ui_templates){xq.ui_templates={}
}xq.ui_templates.basicLinkDialog='<form id="linkDialog" class="xqFormDialog lightweight" action="#">\n		<h3>link</h3>\n		<div class="dialog-content">\n			<p>Please enter the URL and label.</p>\n			<input type="text" name="text" class="type-text" value="" />\n			<input type="text" name="url" class="type-text" value="http://" />\n			<div class="dialog-buttons">\n				<a href="#" class="button-white cancel">Cancel</a>\n				<a href="#" class="button-gray submit">Create</a>\n			</div>\n		</div>\n	</form>';
if(!xq){xq={}
}if(!xq.ui_templates){xq.ui_templates={}
}xq.ui_templates.basicMovieDialog='<form id="videoDialog" class="xqFormDialog modal">\n		<h3>Insert Movie</h3>\n		<div class="dialog-content">\n			<p>Insert an Embed code to add a movie clip from YouTube, Yahoo video, vimeo and slideshare.</p>\n			<textarea class="initialFocus" name="html"></textarea>\n			<div class="dialog-buttons">\n				<a href="#" class="button-white cancel">Close</a>\n				<a href="#" class="button-gray submit">Insert</a>\n			</div>\n		</div>\n		<a href="#" class="cancel close-dialog"><img src="../images/dialogs/icon_close.gif" alt="close" /></a>\n	</form>';
if(!xq){xq={}
}if(!xq.ui_templates){xq.ui_templates={}
}xq.ui_templates.basicMultiFileUploadDialog='<form id="multiFileUploadDialog" class="xqFormDialog modal" method="post" enctype="multipart/form-data">\n		<h3>Upload image</h3>\n		<div class="dialog-content">\n			<div id="fileUploadContainer" class="attachment-section">\n				<p>\n					You can insert more than one file at once.\n					A file up to <strong>20MB</strong> can be uploaded. Please use the Ctrl key on PC and the Command(Apple) key on Mac to select multiple files.\n				</p>\n				<div class="dialog-buttons upload-buttons"><div id="MultiFileUploaderDiv"></div></div>\n			</div>\n			<div id="fileListContainer" class="attachment-section">\n			</div>\n			<div class="dialog-buttons">\n				<a href="#" class="button-white cancel">Close</a>\n				<a href="#" class="button-gray submit">Insert</a>\n			</div>\n			<p class="noti">\n				Adobe Flash is required to insert multiple files. <span><a href="#" onclick="xed.fileUploadController.openDialog(true)">Click here to upload files without Adobe Flash</a></span>\n			</p>\n		</div>\n		<a href="#" class="cancel close-dialog"><img src="../images/dialogs/icon_close.gif" alt="Close" /></a>\n		<div id="MultiFileUploader"></div>\n	</form>';
if(!xq){xq={}
}if(!xq.ui_templates){xq.ui_templates={}
}xq.ui_templates.basicScriptDialog='<form id="scriptDialog" class="xqFormDialog modal">\n		<h3>Insert Script</h3>\n		<div class="dialog-content">\n			<p>Script URL:\n			<input type="text" class="initialFocus" class="type-text" name="url" size="36" value="http://" /></p>\n			<div class="dialog-buttons">\n				<a href="#" class="button-white cancel">Close</a>\n				<a href="#" class="button-gray submit">Insert</a>\n			</div>\n		</div>\n		<a href="#" class="cancel close-dialog"><img src="../images/dialogs/icon_close.gif" alt="close" /></a>\n	</form>';
if(!xq){xq={}
}if(!xq.ui_templates){xq.ui_templates={}
}xq.ui_templates.basicTableDialog='<form id="tableDialog" class="xqFormDialog modal" action="#">\n		<h3 id="tableDialogTitle">Insert Table</h3>\n		<input type="hidden" name="tableDialogType" value="" />\n		<div class="dialog-content">\n			<fieldset class="table-type">\n				<legend>table type</legend>\n				<ul>\n					<li><a href="#" id="tableTypeDefaultValue" class="selected" onclick="xq.RichTableController.changeType(this, \'\'); return false;"><img src="../images/dialogs/iconTable_01.gif?1214533817" alt="Untitled Table" /></a></li>\n					<li><a href="#" onclick="xq.RichTableController.changeType(this, \'t\'); return false;"><img src="../images/dialogs/iconTable_02.gif?1214533817" alt="Column Title on top" /></a></li>\n					<li><a href="#" onclick="xq.RichTableController.changeType(this, \'l\'); return false;"><img src="../images/dialogs/iconTable_03.gif?1214533817" alt="Column Title on left" /></a></li>\n					<li class="last-child"><a href="#" onclick="xq.RichTableController.changeType(this, \'tl\'); return false;"><img src="../images/dialogs/iconTable_04.gif?1214533817" alt="Column Title on top and left" /></a></li>\n				</ul>\n				<input id="tableTypeField" type="hidden" name="tableType" value="" />\n			</fieldset>\n			<fieldset class="table-size">\n				<legend id="tableSizeLabel" class="font-variation">Size</legend>\n				<div class="wrapper">\n					<ul>\n						<li class="new-only">\n							<label>Row(s)</label>\n							<span class="controller"><input type="text" onchange="xq.RichTableController.previewTable(); return false;" id="tableRowsField" name="tableRows" value="3" class="type-text" /><span class="buttons"><img src="../images/dialogs/arrow_top.gif" alt="plus" class="plus rows" onclick="xq.RichTableController.changeSize(this); return false;" /><img src="../images/dialogs/arrow_bottom.gif" alt="minus" class="minus rows" onclick="xq.RichTableController.changeSize(this); return false;" /></span></span>\n						</li>\n						<li class="new-only">\n							<label>Column(s)</label>\n							<span class="controller"><input type="text" onchange="xq.RichTableController.previewTable(); return false;" id="tableColsField" name="tableCols" value="3" class="type-text" /><span class="buttons"><img src="../images/dialogs/arrow_top.gif" alt="plus" class="plus cols" onclick="xq.RichTableController.changeSize(this); return false;" /><img src="../images/dialogs/arrow_bottom.gif" alt="minus" class="minus cols" onclick="xq.RichTableController.changeSize(this); return false;" /></span></span>\n						</li>\n						<li class="table-width">\n							<label>Width</label>\n							<select class="tableWidths" onchange="xq.RichTableController.changeStyle(this); return false;">\n								<option value="fullsize">Full size</option>\n								<option value="content">Size to content</option>\n								<option value="pixel">Fixed size (pixels)</option>\n								<option value="percentage">Fixed percentage (%)</option>\n							</select>			\n							<input type="text" name="tableWidth" id="tableWidthValue" class="type-text height-box" style="display:none;" />				\n							<input type="hidden" name="tableWidthUnit" value="" id="tableWidthValueUnit" />				\n							<!--input type="checkbox" name="tableFixed" class="type-checkbox" /><label class="none font-valiation">Same cell spacing</label-->\n						</li>\n						<li class="table-height">\n							<label>Height</label>\n							<select class="tableHeights" onchange="xq.RichTableController.changeStyle(this); return false;">\n								<option selected value="content">Size to content</option>\n								<option value="pixel">Fixed size (pixels)</option>\n							</select>							\n							<input type="text" name="tableHeight" id="tableHeightValue" class="type-text height-box" style="display:none;" />\n							<input type="hidden" name="tableHeightUnit" id="tableHeightValueUnit" />\n						</li>\n					</ul>\n					<div id="previewTable" class="new-only">\n						<table>\n							<tr>\n								<td></td>\n								<td></td>\n								<td></td>\n							</tr>\n							<tr>\n								<td></td>\n								<td></td>\n								<td></td>\n							</tr>\n							<tr>\n								<td></td>\n								<td></td>\n								<td></td>\n							</tr>\n						</table>\n					</div>\n				</div>\n			</fieldset>\n			<fieldset class="table-alignment">\n				<legend class="font-variation">Alignment</legend>\n				<div class="wrapper">\n					<ul>\n						<li>\n							<label>Horizontal</label>\n							<select name="tableHorizontalAlign">\n								<option value="left">Left</option>\n								<option value="center">Center</option>\n								<option value="right">Right</option>\n							</select>\n						</li>\n						<li>\n							<label>Vertical</label>\n							<select name="tableVerticalAlign">\n								<option value="top">Top</option>\n								<option value="middle">Middle</option>\n								<option value="bottom">Bottom</option>\n							</select>\n						</li>\n					</ul>\n				</div>\n			</fieldset>\n			<fieldset class="table-border">\n				<legend class="font-variation">Border</legend>\n				<div class="wrapper button-style03">\n					<ul>\n						<li class="weight-li">\n							<label>Size</label><span class="controller"><input type="text" name="tableBorderSize" value="0" class="type-text" /><span class="buttons"><img src="../images/dialogs/arrow_top.gif" alt="" class="plus border" onclick="xq.RichTableController.changeSize(this); return false;" /><img src="../images/dialogs/arrow_bottom.gif" alt="" class="minus border" onclick="xq.RichTableController.changeSize(this); return false;" /></span></span>\n						</li>\n						<li class="color-li">\n							<label>color</label>\n							<span class="color">\n								<a href="#" id="borderColorBoard" class="tableDialog border" onclick="xq.RichTableController.showColorPicker(this); return false;">&nbsp;</a>\n							</span>\n							<a href="#" class="tableDialog" onclick="xq.RichTableController.showColorPicker(this); return false;" title="search"><img src="../images/dialogs/arrow06.gif" alt="search" class="search-color" /></a>\n							<input id="borderColorCode" type="hidden" name="tableBorderColor" value="#000000" />\n						</li>\n					</ul>\n				</div>\n			</fieldset>\n			<fieldset class="table-background">\n				<legend class="font-variation">Background</legend>\n				<div class="wrapper button-style03">\n					<ul>\n						<li>\n							<label>color</label>\n							<span class="color">\n								<a href="#" id="backgroundColorBoard" class="tableDialog background" onclick="xq.RichTableController.showColorPicker(this); return false;">&nbsp;</a>\n							</span>\n							<a href="#" class="tableDialog" onclick="xq.RichTableController.showColorPicker(this); return false;" title="search"><img src="../images/dialogs/arrow06.gif" alt="search" class="search-color" /></a>\n							<input id="backgroundColorCode" type="hidden" name="tableBackgroundColor" value="#ffffff" />\n						</li>\n					</ul>\n				</div>\n			</fieldset>\n			<div class="dialog-buttons">\n				<a href="#" class="button-white cancel">Cancel</a>\n				<a href="#" class="button-gray submit" id="tableDialogSubmit">Insert</a>\n			</div>\n		</div>\n		<a href="#" class="cancel close-dialog"><img src="../images/dialogs/icon_close.gif" alt="close" /></a>\n	</form>';
xq.Shortcut=xq.Class({initialize:function(A){xq.addToFinalizeQueue(this);
this.keymap=A
},matches:function(A){if(typeof this.keymap==="string"){this.keymap=xq.Shortcut.interprete(this.keymap).keymap
}var B=xq.Browser.isGecko&&xq.Browser.isMac?(A.keyCode+"_"+A.charCode):A.keyCode;
var D=(this.keymap.which===B)||(this.keymap.which===32&&B===25);
if(!D){return false
}if(typeof A.metaKey==="undefined"){A.metaKey=false
}var C=(this.keymap.shiftKey===A.shiftKey||typeof this.keymap.shiftKey==="undefined")&&(this.keymap.altKey===A.altKey||typeof this.keymap.altKey==="undefined")&&(this.keymap.ctrlKey===A.ctrlKey||typeof this.keymap.ctrlKey==="undefined")&&(xq.Browser.isWin&&xq.Browser.isWebkit||this.keymap.metaKey===A.metaKey||typeof this.keymap.metaKey==="undefined");
return C
}});
xq.Shortcut.interprete=function(G){G=G.toUpperCase();
var F=xq.Shortcut._interpreteWhich(G.split("+").pop());
var E=xq.Shortcut._interpreteModifier(G,"CTRL");
var C=xq.Shortcut._interpreteModifier(G,"ALT");
var B=xq.Shortcut._interpreteModifier(G,"SHIFT");
var D=xq.Shortcut._interpreteModifier(G,"META");
var A={};
A.which=F;
if(typeof E!=="undefined"){A.ctrlKey=E
}if(typeof C!=="undefined"){A.altKey=C
}if(typeof B!=="undefined"){A.shiftKey=B
}if(typeof D!=="undefined"){A.metaKey=D
}return new xq.Shortcut(A)
};
xq.Shortcut._interpreteModifier=function(A,B){return A.match("\\("+B+"\\)")?undefined:A.match(B)?true:false
};
xq.Shortcut._interpreteWhich=function(A){var B=A.length===1?((xq.Browser.isMac&&xq.Browser.isGecko)?"0_"+A.toLowerCase().charCodeAt(0):A.charCodeAt(0)):xq.Shortcut._keyNames[A];
if(typeof B==="undefined"){throw"Unknown special key name: ["+A+"]"
}return B
};
xq.Shortcut._keyNames=xq.Browser.isMac&&xq.Browser.isGecko?{BACKSPACE:"8_0",TAB:"9_0",RETURN:"13_0",ENTER:"13_0",ESC:"27_0",SPACE:"0_32",SHIFT_SPACE:"0_64",LEFT:"37_0",UP:"38_0",RIGHT:"39_0",DOWN:"40_0",DELETE:"46_0",HOME:"36_0",END:"35_0",PAGEUP:"33_0",PAGEDOWN:"34_0",COMMA:"0_44",HYPHEN:"0_45",EQUAL:"0_61",PERIOD:"0_46",SLASH:"0_47",F1:"112_0",F2:"113_0",F3:"114_0",F4:"115_0",F5:"116_0",F6:"117_0",F7:"118_0",F8:"119_0"}:{BACKSPACE:8,TAB:9,RETURN:13,ENTER:13,ESC:27,SPACE:32,LEFT:37,UP:38,RIGHT:39,DOWN:40,DELETE:46,HOME:36,END:35,PAGEUP:33,PAGEDOWN:34,COMMA:188,HYPHEN:xq.Browser.isTrident?189:109,EQUAL:xq.Browser.isTrident?187:61,PERIOD:190,SLASH:191,F1:112,F2:113,F3:114,F4:115,F5:116,F6:117,F7:118,F8:119,F9:120,F10:121,F11:122,F12:123};
xq.Editor=xq.Class({initialize:function(B,A){xq.addToFinalizeQueue(this);
if(typeof B==="string"){B=xq.$(B)
}if(!B){throw"[contentElement] is null"
}if(B.nodeName!=="TEXTAREA"){throw"[contentElement] is not a TEXTAREA"
}xq.asEventSource(this,"Editor",["StartInitialization","Initialized","ElementChanged","BeforeEvent","AfterEvent","CurrentContentChanged","StaticContentChanged","CurrentEditModeChanged"]);
this.config={};
this.config.enablePreventExit=false;
this.config.PreventExitMessage="Document is not empty. If you want to leave, click 'Ok' button.";
this.config.autoFocusOnInit=false;
this.config.lang="en";
if(xq.Browser.language){this.config.lang=xq.Browser.language.substr(0,2)
}this.config.enableLinkClick=false;
this.config.changeCursorOnLink=false;
this.config.generateDefaultToolbar=true;
this._generateDefaultToolbar();
this.config.imagePathForDefaultToolbar="../images/toolbar/";
this.config.imagePathForContent="../images/content/";
this.config.imagePathForDialog="../images/dialogs/";
this.config.imagePathForEmoticon="../images/dialogs/emoticon/";
this.config.widgetContainerPath="widget_container.html";
this.config.contentCssList=["../stylesheets/xq_contents.css"];
this.config.contentJsList=[];
this.config.urlValidationMode="absolute";
this.config.noValidationInSourceEditMode=false;
this.config.automaticallyHookSubmitEvent=true;
this.config.whitelist=xq.predefinedWhitelist;
this.config.bodyId="";
this.config.bodyClass="xed";
this.config.plugins={};
this.config.shortcuts={};
this.config.autocorrections={};
this.config.autocompletions={};
this.config.templateProcessors={};
this.config.contextMenuHandlers={};
this.contentElement=B;
this.doc=this.contentElement.ownerDocument;
this.body=this.doc.body;
this.currentEditMode="";
this.timer=new xq.Timer(100);
this.rdom=xq.rdom.Base.createInstance();
this.validator=null;
this.outmostWrapper=null;
this.sourceEditorDiv=null;
this.sourceEditorTextarea=null;
this.wysiwygEditorDiv=null;
this.outerFrame=null;
this.editorFrame=null;
this.toolbarContainer=A;
this.toolbar=null;
this.editHistory=null;
this.contextMenuContainer=null;
this.contextMenuItems=null;
this.platformDepedentKeyEventType=(xq.Browser.isMac&&xq.Browser.isGecko?"keypress":"keydown");
this.addShortcuts(this.getDefaultShortcuts());
this.addListener({onEditorCurrentContentChanged:function(D){var C=D.rdom.getCurrentElement();
if(!C||C.ownerDocument!==D.rdom.getDoc()){return 
}if(D.lastFocusElement!==C){if(!D.rdom.tree.isBlockOnlyContainer(D.lastFocusElement)&&D.rdom.tree.isBlock(D.lastFocusElement)){D.rdom.removeTrailingWhitespace(D.lastFocusElement)
}D._fireOnElementChanged(D,D.lastFocusElement,C);
D.lastFocusElement=C
}D.toolbar.triggerUpdate()
}});
if(!this.PreventExit){this.PreventExit={}
}xq.observe(window,"beforeunload",function(D){if(xed.config.enablePreventExit===false){return 
}var C=xed.getCurrentContent().stripTags();
if(C!=="&nbsp;"&&C!==xed.PreventExit.defaultContent){xq.stopEvent(D,xed.config.PreventExitMessage)
}})
},finalize:function(){for(var A in this.config.plugins){this.config.plugins[A].unload()
}},_generateDefaultToolbar:function(){this.config.defaultToolbarButtonList=[];
this.config.defaultToolbarButtons={};
this.config.defaultToolbarButtons.foregroundColor={className:"foregroundColor",title:this._("Foreground color"),list:[{style:{backgroundColor:"#ffd8d8",border:"1px solid #e5d2c4"},handler:"xed.handleColorPicker('#ffd8d8')"},{style:{backgroundColor:"#ffead9",border:"1px solid #e4d1c3"},handler:"xed.handleColorPicker('#ffead9')"},{style:{backgroundColor:"#fef2dc",border:"1px solid #e5dac6"},handler:"xed.handleColorPicker('#fef2dc')"},{style:{backgroundColor:"#fff5da",border:"1px solid #e5ddc6"},handler:"xed.handleColorPicker('#fff5da')"},{style:{backgroundColor:"#eefed9",border:"1px solid #d5e4c5"},handler:"xed.handleColorPicker('#eefed9')"},{style:{backgroundColor:"#dafeda",border:"1px solid #c2e4c3"},handler:"xed.handleColorPicker('#dafeda')"},{style:{backgroundColor:"#d8ffff",border:"1px solid #c2e6e6"},handler:"xed.handleColorPicker('#d8ffff')"},{style:{backgroundColor:"#d9f7ff",border:"1px solid #c2dfe7"},handler:"xed.handleColorPicker('#d9f7ff')"},{style:{backgroundColor:"#d5ebff",border:"1px solid #bed3e6"},handler:"xed.handleColorPicker('#d5ebff')"},{style:{backgroundColor:"#eed8ff",border:"1px solid #d6c3e3"},handler:"xed.handleColorPicker('#eed8ff')"},{style:{backgroundColor:"#fed8ff",border:"1px solid #e5c1e5"},handler:"xed.handleColorPicker('#fed8ff')"},{style:{backgroundColor:"#ffffff",border:"1px solid #e5e5e5"},handler:"xed.handleColorPicker('#ffffff')"},{style:{backgroundColor:"#fe8c8c",border:"1px solid #e77f80"},handler:"xed.handleColorPicker('#fe8c8c')"},{style:{backgroundColor:"#feba8d",border:"1px solid #e7a67c"},handler:"xed.handleColorPicker('#feba8d')"},{style:{backgroundColor:"#ffe88b",border:"1px solid #e5d07d"},handler:"xed.handleColorPicker('#ffe88b')"},{style:{backgroundColor:"#ffff8d",border:"1px solid #e6e47d"},handler:"xed.handleColorPicker('#ffff8d')"},{style:{backgroundColor:"#d0fc8d",border:"1px solid #bbe17e"},handler:"xed.handleColorPicker('#d0fc8d')"},{style:{backgroundColor:"#8efb8e",border:"1px solid #7ee280"},handler:"xed.handleColorPicker('#8efb8e')"},{style:{backgroundColor:"#8bffff",border:"1px solid #7ee6e5"},handler:"xed.handleColorPicker('#8bffff')"},{style:{backgroundColor:"#8ce8ff",border:"1px solid #7fcfe6"},handler:"xed.handleColorPicker('#8ce8ff')"},{style:{backgroundColor:"#8b8cff",border:"1px solid #7d7fe6"},handler:"xed.handleColorPicker('#8b8cff')"},{style:{backgroundColor:"#d18cff",border:"1px solid #bc7de5"},handler:"xed.handleColorPicker('#d18cff')"},{style:{backgroundColor:"#ff8bfe",border:"1px solid #e47fe5"},handler:"xed.handleColorPicker('#ff8bfe')"},{style:{backgroundColor:"#cccccc",border:"1px solid #aeaeae"},handler:"xed.handleColorPicker('#cccccc')"},{style:{backgroundColor:"#ff0103",border:"1px solid #e40001"},handler:"xed.handleColorPicker('#ff0103')"},{style:{backgroundColor:"#ff6600",border:"1px solid #e85c00"},handler:"xed.handleColorPicker('#ff6600')"},{style:{backgroundColor:"#ffcc01",border:"1px solid #e4b600"},handler:"xed.handleColorPicker('#ffcc01')"},{style:{backgroundColor:"#ffff01",border:"1px solid #e5e400"},handler:"xed.handleColorPicker('#ffff01')"},{style:{backgroundColor:"#96f908",border:"1px solid #86e004"},handler:"xed.handleColorPicker('#96f908')"},{style:{backgroundColor:"#07f905",border:"1px solid #03e005"},handler:"xed.handleColorPicker('#07f905')"},{style:{backgroundColor:"#02feff",border:"1px solid #00e4e3"},handler:"xed.handleColorPicker('#02feff')"},{style:{backgroundColor:"#00ccff",border:"1px solid #00b8e4"},handler:"xed.handleColorPicker('#00ccff')"},{style:{backgroundColor:"#0100fe",border:"1px solid #0000e6"},handler:"xed.handleColorPicker('#0100fe')"},{style:{backgroundColor:"#9801ff",border:"1px solid #8900e6"},handler:"xed.handleColorPicker('#9801ff')"},{style:{backgroundColor:"#fc01fe",border:"1px solid #e700e6"},handler:"xed.handleColorPicker('#fc01fe')"},{style:{backgroundColor:"#999999",border:"1px solid #808080"},handler:"xed.handleColorPicker('#999999')"},{style:{backgroundColor:"#990002",border:"1px solid #890101"},handler:"xed.handleColorPicker('#990002')"},{style:{backgroundColor:"#b65006",border:"1px solid #ad6d00"},handler:"xed.handleColorPicker('#b65006')"},{style:{backgroundColor:"#bf7900",border:"1px solid #ac6e01"},handler:"xed.handleColorPicker('#bf7900')"},{style:{backgroundColor:"#cca500",border:"1px solid #b89200"},handler:"xed.handleColorPicker('#cca500')"},{style:{backgroundColor:"#5a9603",border:"1px solid #518604"},handler:"xed.handleColorPicker('#5a9603')"},{style:{backgroundColor:"#059502",border:"1px solid #048504"},handler:"xed.handleColorPicker('#059502')"},{style:{backgroundColor:"#009997",border:"1px solid #008886"},handler:"xed.handleColorPicker('#009997')"},{style:{backgroundColor:"#007998",border:"1px solid #006d89"},handler:"xed.handleColorPicker('#007998')"},{style:{backgroundColor:"#095392",border:"1px solid #084a84"},handler:"xed.handleColorPicker('#095392')"},{style:{backgroundColor:"#6a19a4",border:"1px solid #601693"},handler:"xed.handleColorPicker('#6a19a4')"},{style:{backgroundColor:"#98019a",border:"1px solid #8a008b"},handler:"xed.handleColorPicker('#98019a')"},{style:{backgroundColor:"#666666",border:"1px solid #555555"},handler:"xed.handleColorPicker('#666666')"},{style:{backgroundColor:"#590100",border:"1px solid #510000"},handler:"xed.handleColorPicker('#590100')"},{style:{backgroundColor:"#773505",border:"1px solid #714901"},handler:"xed.handleColorPicker('#773505')"},{style:{backgroundColor:"#7f5000",border:"1px solid #734901"},handler:"xed.handleColorPicker('#7f5000')"},{style:{backgroundColor:"#927300",border:"1px solid #836600"},handler:"xed.handleColorPicker('#927300')"},{style:{backgroundColor:"#365802",border:"1px solid #304f03"},handler:"xed.handleColorPicker('#365802')"},{style:{backgroundColor:"#035902",border:"1px solid #025102"},handler:"xed.handleColorPicker('#035902')"},{style:{backgroundColor:"#01595a",border:"1px solid #00504f"},handler:"xed.handleColorPicker('#01595a')"},{style:{backgroundColor:"#00485b",border:"1px solid #004252"},handler:"xed.handleColorPicker('#00485b')"},{style:{backgroundColor:"#083765",border:"1px solid #06315b"},handler:"xed.handleColorPicker('#083765')"},{style:{backgroundColor:"#370159",border:"1px solid #300151"},handler:"xed.handleColorPicker('#370159')"},{style:{backgroundColor:"#59005a",border:"1px solid #520052"},handler:"xed.handleColorPicker('#59005a')"},{style:{backgroundColor:"#000000",border:"1px solid #000000"},handler:"xed.handleColorPicker('#000000')"}]};
this.config.defaultToolbarButtons.backgroundColor={className:"backgroundColor",title:this._("Background color"),list:[{style:{backgroundColor:"#FFF700"},handler:"xed.handleBackgroundColor('#FFF700')"},{style:{backgroundColor:"#AEFF66"},handler:"xed.handleBackgroundColor('#AEFF66')"},{style:{backgroundColor:"#FFCC66"},handler:"xed.handleBackgroundColor('#FFCC66')"},{style:{backgroundColor:"#DCB0FB"},handler:"xed.handleBackgroundColor('#DCB0FB')"},{style:{backgroundColor:"#B0EEFB"},handler:"xed.handleBackgroundColor('#B0EEFB')"},{style:{backgroundColor:"#FBBDB0"},handler:"xed.handleBackgroundColor('#FBBDB0')"},{style:{backgroundColor:"#FFFFFF"},handler:"xed.handleBackgroundColor('#FFFFFF')"}]};
this.config.defaultToolbarButtons.fontFace={className:"fontFace",title:this._("Font face"),list:[{html:"Arial",style:{fontFamily:"Arial"},handler:"xed.handleFontFace('Arial')"},{html:"Comic Sans MS",style:{fontFamily:"Comic Sans MS"},handler:"xed.handleFontFace('Comic Sans MS')"},{html:"Courier New",style:{fontFamily:"Courier New"},handler:"xed.handleFontFace('Courier New')"},{html:"Georgia",style:{fontFamily:"Georgia"},handler:"xed.handleFontFace('Georgia')"},{html:"Tahoma",style:{fontFamily:"Tahoma"},handler:"xed.handleFontFace('Tahoma')"},{html:"Times",style:{fontFamily:"Times"},handler:"xed.handleFontFace('Times')"},{html:"Trebuchte MS",style:{fontFamily:"Trebuchte MS"},handler:"xed.handleFontFace('Trebuchte MS')"},{html:"Verdana",style:{fontFamily:"Verdana"},handler:"xed.handleFontFace('Verdana')"}]};
this.config.defaultToolbarButtons.fontSize={className:"fontSize",title:this._("Font size"),list:[{html:"Lorem ipsum dolor (8pt)",style:{fontSize:"8pt",marginBottom:"3px"},handler:"xed.handleFontSize('1')"},{html:"Lorem ipsum dolor (10pt)",style:{fontSize:"10pt",marginBottom:"3px"},handler:"xed.handleFontSize('2')"},{html:"Lorem ipsum dolor (12pt)",style:{fontSize:"12pt",marginBottom:"6px"},handler:"xed.handleFontSize('3')"},{html:"Lorem ipsum dolor (14pt)",style:{fontSize:"14pt",marginBottom:"10px"},handler:"xed.handleFontSize('4')"},{html:"Lorem ipsum dolor (18pt)",style:{fontSize:"18pt",marginBottom:"16px"},handler:"xed.handleFontSize('5')"},{html:"Lorem ipsum dolor (24pt)",style:{fontSize:"24pt",marginBottom:"6px"},handler:"xed.handleFontSize('6')"}]};
this.config.defaultToolbarButtons.link={className:"link",title:this._("Link"),handler:"xed.handleLink()"};
this.config.defaultToolbarButtons.removeLink={className:"removeLink",title:this._("Remove link"),handler:"xed.handleRemoveLink()"};
this.config.defaultToolbarButtons.strongEmphasis={className:"strongEmphasis",title:this._("Strong emphasis"),handler:"xed.handleStrongEmphasis()"};
this.config.defaultToolbarButtons.emphasis={className:"emphasis",title:this._("Emphasis"),handler:"xed.handleEmphasis()"};
this.config.defaultToolbarButtons.underline={className:"underline",title:this._("Underline"),handler:"xed.handleUnderline()"};
this.config.defaultToolbarButtons.strike={className:"strike",title:this._("Strike"),handler:"xed.handleStrike()"};
this.config.defaultToolbarButtons.superscription={className:"superscription",title:this._("Superscription"),handler:"xed.handleSuperscription()"};
this.config.defaultToolbarButtons.subscription={className:"subscription",title:this._("Subscription"),handler:"xed.handleSubscription()"};
this.config.defaultToolbarButtons.removeFormat={className:"removeFormat",title:this._("Remove format"),handler:"xed.handleRemoveFormat()"};
this.config.defaultToolbarButtons.justifyLeft={className:"justifyLeft",title:this._("Justify left"),handler:"xed.handleJustify('left')"};
this.config.defaultToolbarButtons.justifyCenter={className:"justifyCenter",title:this._("Justify center"),handler:"xed.handleJustify('center')"};
this.config.defaultToolbarButtons.justifyRight={className:"justifyRight",title:this._("Justify right"),handler:"xed.handleJustify('right')"};
this.config.defaultToolbarButtons.justifyBoth={className:"justifyBoth",title:this._("Justify both"),handler:"xed.handleJustify('both')"};
this.config.defaultToolbarButtons.indent={className:"indent",title:this._("Indent"),handler:"xed.handleIndent()"};
this.config.defaultToolbarButtons.outdent={className:"outdent",title:this._("Outdent"),handler:"xed.handleOutdent()"};
this.config.defaultToolbarButtons.paragraph={className:"paragraph",title:this._("Paragraph"),handler:"xed.handleApplyBlock('P')"};
this.config.defaultToolbarButtons.heading1={className:"heading1",title:this._("Heading"),list:[{html:"Heading1",style:{fontSize:"2.845em",marginBottom:"3px"},handler:"xed.handleApplyBlock('H1')"},{html:"Heading2",style:{fontSize:"2.46em",marginBottom:"3px"},handler:"xed.handleApplyBlock('H2')"},{html:"Heading3",style:{fontSize:"2.153em",marginBottom:"3px"},handler:"xed.handleApplyBlock('H3')"},{html:"Heading4",style:{fontSize:"1.922em",marginBottom:"3px"},handler:"xed.handleApplyBlock('H4')"},{html:"Heading5",style:{fontSize:"1.461em",marginBottom:"3px"},handler:"xed.handleApplyBlock('H5')"},{html:"Heading6",style:{fontSize:"1.23em",marginBottom:"3px"},handler:"xed.handleApplyBlock('H6')"}]};
this.config.defaultToolbarButtons.blockquote={className:"blockquote",title:this._("Blockquote"),handler:"xed.handleApplyBlock('BLOCKQUOTE')"};
this.config.defaultToolbarButtons.code={className:"code",title:this._("Code"),handler:"xed.handleList('OL', 'code')"};
this.config.defaultToolbarButtons.division={className:"division",title:this._("Div"),handler:"xed.handleApplyBlock('DIV')"};
this.config.defaultToolbarButtons.unorderedList={className:"unorderedList",title:this._("Unordered list"),handler:"xed.handleList('UL')"};
this.config.defaultToolbarButtons.orderedList={className:"orderedList",title:this._("Ordered list"),handler:"xed.handleList('OL')"};
this.config.defaultToolbarButtons.table={className:"table",title:this._("Table"),handler:"xed.handleTable()"};
this.config.defaultToolbarButtons.separator={className:"separator",title:this._("Separator"),handler:"xed.handleSeparator()"};
this.config.defaultToolbarButtons.character={className:"character",title:this._("Character"),list:[{html:"%E3%80%81",handler:"xed.handleCharacter('%E3%80%81')"},{html:"%E3%80%82",handler:"xed.handleCharacter('%E3%80%82')"},{html:"%C2%B7",handler:"xed.handleCharacter('%C2%B7')"},{html:"%E2%80%A5",handler:"xed.handleCharacter('%E2%80%A5')"},{html:"%E2%80%A6",handler:"xed.handleCharacter('%E2%80%A6')"},{html:"%C2%A8",handler:"xed.handleCharacter('%C2%A8')"},{html:"%E3%80%83",handler:"xed.handleCharacter('%E3%80%83')"},{html:"%E2%80%95",handler:"xed.handleCharacter('%E2%80%95')"},{html:"%E2%88%A5",handler:"xed.handleCharacter('%E2%88%A5')"},{html:"%EF%BC%BC",handler:"xed.handleCharacter('%EF%BC%BC')"},{html:"%E2%88%BC",handler:"xed.handleCharacter('%E2%88%BC')"},{html:"%E2%80%98",handler:"xed.handleCharacter('%E2%80%98')"},{html:"%E2%80%99",handler:"xed.handleCharacter('%E2%80%99')"},{html:"%E2%80%9C",handler:"xed.handleCharacter('%E2%80%9C')"},{html:"%E2%80%9D",handler:"xed.handleCharacter('%E2%80%9D')"},{html:"%E3%80%94",handler:"xed.handleCharacter('%E3%80%94')"},{html:"%E3%80%95",handler:"xed.handleCharacter('%E3%80%95')"},{html:"%E3%80%88",handler:"xed.handleCharacter('%E3%80%88')"},{html:"%E3%80%89",handler:"xed.handleCharacter('%E3%80%89')"},{html:"%E3%80%8A",handler:"xed.handleCharacter('%E3%80%8A')"},{html:"%E3%80%8B",handler:"xed.handleCharacter('%E3%80%8B')"},{html:"%E3%80%8C",handler:"xed.handleCharacter('%E3%80%8C')"},{html:"%E3%80%8D",handler:"xed.handleCharacter('%E3%80%8D')"},{html:"%E3%80%8E",handler:"xed.handleCharacter('%E3%80%8E')"},{html:"%E3%80%8F",handler:"xed.handleCharacter('%E3%80%8F')"},{html:"%E3%80%90",handler:"xed.handleCharacter('%E3%80%90')"},{html:"%E3%80%91",handler:"xed.handleCharacter('%E3%80%91')"},{html:"%C2%B1",handler:"xed.handleCharacter('%C2%B1')"},{html:"%C3%97",handler:"xed.handleCharacter('%C3%97')"},{html:"%C3%B7",handler:"xed.handleCharacter('%C3%B7')"},{html:"%E2%89%A0",handler:"xed.handleCharacter('%E2%89%A0')"},{html:"%E2%89%A4",handler:"xed.handleCharacter('%E2%89%A4')"},{html:"%E2%89%A5",handler:"xed.handleCharacter('%E2%89%A5')"},{html:"%E2%88%9E",handler:"xed.handleCharacter('%E2%88%9E')"},{html:"%E2%88%B4",handler:"xed.handleCharacter('%E2%88%B4')"},{html:"%C2%B0",handler:"xed.handleCharacter('%C2%B0')"},{html:"%E2%80%B2",handler:"xed.handleCharacter('%E2%80%B2')"},{html:"%E2%80%B3",handler:"xed.handleCharacter('%E2%80%B3')"},{html:"%E2%84%83",handler:"xed.handleCharacter('%E2%84%83')"},{html:"%E2%84%AB",handler:"xed.handleCharacter('%E2%84%AB')"},{html:"%EF%BF%A0",handler:"xed.handleCharacter('%EF%BF%A0')"},{html:"%EF%BF%A1",handler:"xed.handleCharacter('%EF%BF%A1')"},{html:"%EF%BF%A5",handler:"xed.handleCharacter('%EF%BF%A5')"},{html:"%E2%99%82",handler:"xed.handleCharacter('%E2%99%82')"},{html:"%E2%99%80",handler:"xed.handleCharacter('%E2%99%80')"},{html:"%E2%88%A0",handler:"xed.handleCharacter('%E2%88%A0')"},{html:"%E2%8A%A5",handler:"xed.handleCharacter('%E2%8A%A5')"},{html:"%E2%8C%92",handler:"xed.handleCharacter('%E2%8C%92')"},{html:"%E2%88%82",handler:"xed.handleCharacter('%E2%88%82')"},{html:"%E2%88%87",handler:"xed.handleCharacter('%E2%88%87')"},{html:"%E2%89%A1",handler:"xed.handleCharacter('%E2%89%A1')"},{html:"%E2%89%92",handler:"xed.handleCharacter('%E2%89%92')"},{html:"%C2%A7",handler:"xed.handleCharacter('%C2%A7')"},{html:"%E2%80%BB",handler:"xed.handleCharacter('%E2%80%BB')"},{html:"%E2%98%86",handler:"xed.handleCharacter('%E2%98%86')"},{html:"%E2%98%85",handler:"xed.handleCharacter('%E2%98%85')"},{html:"%E2%97%8B",handler:"xed.handleCharacter('%E2%97%8B')"},{html:"%E2%97%8F",handler:"xed.handleCharacter('%E2%97%8F')"},{html:"%E2%97%8E",handler:"xed.handleCharacter('%E2%97%8E')"},{html:"%E2%97%87",handler:"xed.handleCharacter('%E2%97%87')"},{html:"%E2%97%86",handler:"xed.handleCharacter('%E2%97%86')"},{html:"%E2%96%A1",handler:"xed.handleCharacter('%E2%96%A1')"},{html:"%E2%96%A0",handler:"xed.handleCharacter('%E2%96%A0')"},{html:"%E2%96%B3",handler:"xed.handleCharacter('%E2%96%B3')"},{html:"%E2%96%B2",handler:"xed.handleCharacter('%E2%96%B2')"},{html:"%E2%96%BD",handler:"xed.handleCharacter('%E2%96%BD')"},{html:"%E2%96%BC",handler:"xed.handleCharacter('%E2%96%BC')"},{html:"%E2%86%92",handler:"xed.handleCharacter('%E2%86%92')"},{html:"%E2%86%90",handler:"xed.handleCharacter('%E2%86%90')"},{html:"%E2%86%91",handler:"xed.handleCharacter('%E2%86%91')"},{html:"%E2%86%93",handler:"xed.handleCharacter('%E2%86%93')"},{html:"%E2%86%94",handler:"xed.handleCharacter('%E2%86%94')"},{html:"%E3%80%93",handler:"xed.handleCharacter('%E3%80%93')"},{html:"%E2%89%AA",handler:"xed.handleCharacter('%E2%89%AA')"},{html:"%E2%89%AB",handler:"xed.handleCharacter('%E2%89%AB')"},{html:"%E2%88%9A",handler:"xed.handleCharacter('%E2%88%9A')"},{html:"%E2%88%BD",handler:"xed.handleCharacter('%E2%88%BD')"},{html:"%E2%88%9D",handler:"xed.handleCharacter('%E2%88%9D')"},{html:"%E2%88%B5",handler:"xed.handleCharacter('%E2%88%B5')"},{html:"%E2%88%AB",handler:"xed.handleCharacter('%E2%88%AB')"},{html:"%E2%88%AC",handler:"xed.handleCharacter('%E2%88%AC')"},{html:"%E2%88%88",handler:"xed.handleCharacter('%E2%88%88')"},{html:"%E2%88%8B",handler:"xed.handleCharacter('%E2%88%8B')"},{html:"%E2%8A%86",handler:"xed.handleCharacter('%E2%8A%86')"},{html:"%E2%8A%87",handler:"xed.handleCharacter('%E2%8A%87')"},{html:"%E2%8A%82",handler:"xed.handleCharacter('%E2%8A%82')"},{html:"%E2%8A%83",handler:"xed.handleCharacter('%E2%8A%83')"},{html:"%E2%88%AA",handler:"xed.handleCharacter('%E2%88%AA')"},{html:"%E2%88%A9",handler:"xed.handleCharacter('%E2%88%A9')"},{html:"%E2%88%A7",handler:"xed.handleCharacter('%E2%88%A7')"},{html:"%E2%88%A8",handler:"xed.handleCharacter('%E2%88%A8')"},{html:"%EF%BF%A2",handler:"xed.handleCharacter('%EF%BF%A2')"},{html:"%E2%87%92",handler:"xed.handleCharacter('%E2%87%92')"},{html:"%E2%87%94",handler:"xed.handleCharacter('%E2%87%94')"},{html:"%E2%88%80",handler:"xed.handleCharacter('%E2%88%80')"},{html:"%E2%88%83",handler:"xed.handleCharacter('%E2%88%83')"},{html:"%EF%BD%9E",handler:"xed.handleCharacter('%EF%BD%9E')"},{html:"%CB%87",handler:"xed.handleCharacter('%CB%87')"},{html:"%CB%98",handler:"xed.handleCharacter('%CB%98')"},{html:"%C2%B8",handler:"xed.handleCharacter('%C2%B8')"},{html:"%CB%9B",handler:"xed.handleCharacter('%CB%9B')"},{html:"%C2%A1",handler:"xed.handleCharacter('%C2%A1')"},{html:"%C2%BF",handler:"xed.handleCharacter('%C2%BF')"},{html:"%CB%90",handler:"xed.handleCharacter('%CB%90')"},{html:"%E2%88%AE",handler:"xed.handleCharacter('%E2%88%AE')"},{html:"%E2%88%91",handler:"xed.handleCharacter('%E2%88%91')"},{html:"%E2%88%8F",handler:"xed.handleCharacter('%E2%88%8F')"},{html:"%C2%A4",handler:"xed.handleCharacter('%C2%A4')"},{html:"%E2%84%89",handler:"xed.handleCharacter('%E2%84%89')"},{html:"%E2%80%B0",handler:"xed.handleCharacter('%E2%80%B0')"},{html:"%E2%97%81",handler:"xed.handleCharacter('%E2%97%81')"},{html:"%E2%97%80",handler:"xed.handleCharacter('%E2%97%80')"},{html:"%E2%96%B7",handler:"xed.handleCharacter('%E2%96%B7')"},{html:"%E2%96%B6",handler:"xed.handleCharacter('%E2%96%B6')"},{html:"%E2%99%A4",handler:"xed.handleCharacter('%E2%99%A4')"},{html:"%E2%99%A0",handler:"xed.handleCharacter('%E2%99%A0')"},{html:"%E2%99%A1",handler:"xed.handleCharacter('%E2%99%A1')"},{html:"%E2%99%A5",handler:"xed.handleCharacter('%E2%99%A5')"},{html:"%E2%99%A7",handler:"xed.handleCharacter('%E2%99%A7')"},{html:"%E2%99%A3",handler:"xed.handleCharacter('%E2%99%A3')"},{html:"%E2%8A%99",handler:"xed.handleCharacter('%E2%8A%99')"},{html:"%E2%97%88",handler:"xed.handleCharacter('%E2%97%88')"},{html:"%E2%96%A3",handler:"xed.handleCharacter('%E2%96%A3')"},{html:"%E2%97%90",handler:"xed.handleCharacter('%E2%97%90')"},{html:"%E2%97%91",handler:"xed.handleCharacter('%E2%97%91')"},{html:"%E2%96%92",handler:"xed.handleCharacter('%E2%96%92')"},{html:"%E2%96%A4",handler:"xed.handleCharacter('%E2%96%A4')"},{html:"%E2%96%A5",handler:"xed.handleCharacter('%E2%96%A5')"},{html:"%E2%96%A8",handler:"xed.handleCharacter('%E2%96%A8')"},{html:"%E2%96%A7",handler:"xed.handleCharacter('%E2%96%A7')"},{html:"%E2%96%A6",handler:"xed.handleCharacter('%E2%96%A6')"},{html:"%E2%96%A9",handler:"xed.handleCharacter('%E2%96%A9')"},{html:"%E2%99%A8",handler:"xed.handleCharacter('%E2%99%A8')"},{html:"%E2%98%8F",handler:"xed.handleCharacter('%E2%98%8F')"},{html:"%E2%98%8E",handler:"xed.handleCharacter('%E2%98%8E')"},{html:"%E2%98%9C",handler:"xed.handleCharacter('%E2%98%9C')"},{html:"%E2%98%9E",handler:"xed.handleCharacter('%E2%98%9E')"},{html:"%C2%B6",handler:"xed.handleCharacter('%C2%B6')"},{html:"%E2%80%A0",handler:"xed.handleCharacter('%E2%80%A0')"},{html:"%E2%80%A1",handler:"xed.handleCharacter('%E2%80%A1')"},{html:"%E2%86%95",handler:"xed.handleCharacter('%E2%86%95')"},{html:"%E2%86%97",handler:"xed.handleCharacter('%E2%86%97')"},{html:"%E2%86%99",handler:"xed.handleCharacter('%E2%86%99')"},{html:"%E2%86%96",handler:"xed.handleCharacter('%E2%86%96')"},{html:"%E2%86%98",handler:"xed.handleCharacter('%E2%86%98')"},{html:"%E2%99%AD",handler:"xed.handleCharacter('%E2%99%AD')"},{html:"%E2%99%A9",handler:"xed.handleCharacter('%E2%99%A9')"},{html:"%E2%99%AA",handler:"xed.handleCharacter('%E2%99%AA')"},{html:"%E2%99%AC",handler:"xed.handleCharacter('%E2%99%AC')"},{html:"%E3%89%BF",handler:"xed.handleCharacter('%E3%89%BF')"},{html:"%E3%88%9C",handler:"xed.handleCharacter('%E3%88%9C')"},{html:"%E2%84%96",handler:"xed.handleCharacter('%E2%84%96')"},{html:"%E3%8F%87",handler:"xed.handleCharacter('%E3%8F%87')"},{html:"%E2%84%A2",handler:"xed.handleCharacter('%E2%84%A2')"},{html:"%E3%8F%82",handler:"xed.handleCharacter('%E3%8F%82')"},{html:"%E3%8F%98",handler:"xed.handleCharacter('%E3%8F%98')"},{html:"%E2%84%A1",handler:"xed.handleCharacter('%E2%84%A1')"}]};
this.config.defaultToolbarButtons.emoticon={className:"emoticon",title:this._("Emoticon"),list:[{html:"num1.gif",handler:"xed.handleEmoticon('num1.gif')"},{html:"num2.gif",handler:"xed.handleEmoticon('num2.gif')"},{html:"num3.gif",handler:"xed.handleEmoticon('num3.gif')"},{html:"num4.gif",handler:"xed.handleEmoticon('num4.gif')"},{html:"num5.gif",handler:"xed.handleEmoticon('num5.gif')"},{html:"question.gif",handler:"xed.handleEmoticon('question.gif')"},{html:"disk.gif",handler:"xed.handleEmoticon('disk.gif')"},{html:"play.gif",handler:"xed.handleEmoticon('play.gif')"},{html:"flag1.gif",handler:"xed.handleEmoticon('flag1.gif')"},{html:"flag2.gif",handler:"xed.handleEmoticon('flag2.gif')"},{html:"flag3.gif",handler:"xed.handleEmoticon('flag3.gif')"},{html:"flag4.gif",handler:"xed.handleEmoticon('flag4.gif')"},{html:"arrow_left.gif",handler:"xed.handleEmoticon('arrow_left.gif')"},{html:"arrow_right.gif",handler:"xed.handleEmoticon('arrow_right.gif')"},{html:"arrow_up.gif",handler:"xed.handleEmoticon('arrow_up.gif')"},{html:"arrow_down.gif",handler:"xed.handleEmoticon('arrow_down.gif')"},{html:"step1.gif",handler:"xed.handleEmoticon('step1.gif')"},{html:"step2.gif",handler:"xed.handleEmoticon('step2.gif')"},{html:"step3.gif",handler:"xed.handleEmoticon('step3.gif')"},{html:"note.gif",handler:"xed.handleEmoticon('note.gif')"},{html:"heart.gif",handler:"xed.handleEmoticon('heart.gif')"},{html:"good.gif",handler:"xed.handleEmoticon('good.gif')"},{html:"bad.gif",handler:"xed.handleEmoticon('bad.gif')"}]};
this.config.defaultToolbarButtons.html={className:"html",title:this._("Edit source"),handler:"xed.toggleSourceAndWysiwygMode()"};
this.config.defaultToolbarButtons.undo={className:"undo",title:this._("Undo"),handler:"xed.handleUndo()"};
this.config.defaultToolbarButtons.redo={className:"redo",title:this._("Redo"),handler:"xed.handleRedo()"};
this.config.defaultToolbarButtonGroups={color:[this.config.defaultToolbarButtons.foregroundColor,this.config.defaultToolbarButtons.backgroundColor],font:[this.config.defaultToolbarButtons.fontFace,this.config.defaultToolbarButtons.fontSize],link:[this.config.defaultToolbarButtons.link,this.config.defaultToolbarButtons.removeLink],style:[this.config.defaultToolbarButtons.strongEmphasis,this.config.defaultToolbarButtons.emphasis,this.config.defaultToolbarButtons.underline,this.config.defaultToolbarButtons.strike,this.config.defaultToolbarButtons.superscription,this.config.defaultToolbarButtons.subscription,this.config.defaultToolbarButtons.removeFormat],justification:[this.config.defaultToolbarButtons.justifyLeft,this.config.defaultToolbarButtons.justifyCenter,this.config.defaultToolbarButtons.justifyRight,this.config.defaultToolbarButtons.justifyBoth],indentation:[this.config.defaultToolbarButtons.indent,this.config.defaultToolbarButtons.outdent],block:[this.config.defaultToolbarButtons.blockquote,this.config.defaultToolbarButtons.code,this.config.defaultToolbarButtons.division,this.config.defaultToolbarButtons.unorderedList,this.config.defaultToolbarButtons.orderedList],insert:[this.config.defaultToolbarButtons.table,this.config.defaultToolbarButtons.separator,this.config.defaultToolbarButtons.character,this.config.defaultToolbarButtons.emoticon],html:[this.config.defaultToolbarButtons.html],undo:[this.config.defaultToolbarButtons.undo,this.config.defaultToolbarButtons.redo]};
this.config.defaultToolbarButtonMap=[this.config.defaultToolbarButtonGroups.font,this.config.defaultToolbarButtonGroups.color,this.config.defaultToolbarButtonGroups.style,this.config.defaultToolbarButtonGroups.justification,this.config.defaultToolbarButtonGroups.indentation,this.config.defaultToolbarButtonGroups.block,this.config.defaultToolbarButtonGroups.link,this.config.defaultToolbarButtonGroups.insert,this.config.defaultToolbarButtonGroups.html,this.config.defaultToolbarButtonGroups.undo]
},getDefaultShortcuts:function(){if(xq.Browser.isMac){return[{event:"Ctrl+Shift+SPACE",handler:"this.handleAutocompletion(); stop = true;"},{event:"SPACE",handler:"this.handleSpace()"},{event:"ENTER",handler:"this.handleEnter(false, false)"},{event:"Ctrl+ENTER",handler:"this.handleEnter(true, false)"},{event:"Ctrl+Shift+ENTER",handler:"this.handleEnter(true, true)"},{event:"TAB",handler:"this.handleTab()"},{event:"Shift+TAB",handler:"this.handleShiftTab()"},{event:"DELETE",handler:"this.handleDelete()"},{event:"BACKSPACE",handler:"this.handleBackspace()"},{event:"Ctrl+B",handler:"this.handleStrongEmphasis()"},{event:"Meta+B",handler:"this.handleStrongEmphasis()"},{event:"Ctrl+I",handler:"this.handleEmphasis()"},{event:"Meta+I",handler:"this.handleEmphasis()"},{event:"Ctrl+U",handler:"this.handleUnderline()"},{event:"Meta+U",handler:"this.handleUnderline()"},{event:"Ctrl+K",handler:"this.handleStrike()"},{event:"Meta+K",handler:"this.handleStrike()"},{event:"Meta+Z",handler:"this.handleUndo()"},{event:"Meta+Shift+Z",handler:"this.handleRedo()"},{event:"Meta+Y",handler:"this.handleRedo()"}]
}else{if(xq.Browser.isUbuntu){return[{event:"Ctrl+SPACE",handler:"this.handleAutocompletion(); stop = true;"},{event:"SPACE",handler:"this.handleSpace()"},{event:"ENTER",handler:"this.handleEnter(false, false)"},{event:"Ctrl+ENTER",handler:"this.handleEnter(true, false)"},{event:"Ctrl+Shift+ENTER",handler:"this.handleEnter(true, true)"},{event:"TAB",handler:"this.handleTab()"},{event:"Shift+TAB",handler:"this.handleShiftTab()"},{event:"DELETE",handler:"this.handleDelete()"},{event:"BACKSPACE",handler:"this.handleBackspace()"},{event:"Ctrl+B",handler:"this.handleStrongEmphasis()"},{event:"Ctrl+I",handler:"this.handleEmphasis()"},{event:"Ctrl+U",handler:"this.handleUnderline()"},{event:"Ctrl+K",handler:"this.handleStrike()"},{event:"Ctrl+Z",handler:"this.handleUndo()"},{event:"Ctrl+Shift+Z",handler:"this.handleRedo()"},{event:"Ctrl+Y",handler:"this.handleRedo()"}]
}else{return[{event:"Ctrl+SPACE",handler:"this.handleAutocompletion(); stop = true;"},{event:"SPACE",handler:"this.handleSpace()"},{event:"ENTER",handler:"this.handleEnter(false, false)"},{event:"Ctrl+ENTER",handler:"this.handleEnter(true, false)"},{event:"Ctrl+Shift+ENTER",handler:"this.handleEnter(true, true)"},{event:"TAB",handler:"this.handleTab()"},{event:"Shift+TAB",handler:"this.handleShiftTab()"},{event:"DELETE",handler:"this.handleDelete()"},{event:"BACKSPACE",handler:"this.handleBackspace()"},{event:"Ctrl+B",handler:"this.handleStrongEmphasis()"},{event:"Ctrl+I",handler:"this.handleEmphasis()"},{event:"Ctrl+U",handler:"this.handleUnderline()"},{event:"Ctrl+K",handler:"this.handleStrike()"},{event:"Ctrl+Z",handler:"this.handleUndo()"},{event:"Ctrl+Shift+Z",handler:"this.handleRedo()"},{event:"Ctrl+Y",handler:"this.handleRedo()"}]
}}},addPlugin:function(C){if(this.config.plugins[C]){return 
}var A=xq.plugin[C+"Plugin"];
if(!A){throw"Unknown plugin id: ["+C+"]"
}var B=new A();
this.config.plugins[C]=B;
B.load(this)
},addPlugins:function(B){for(var A=0;
A<B.length;
A++){this.addPlugin(B[A])
}},getPlugin:function(A){return this.config.plugins[A]
},getPlugins:function(){return this.config.plugins
},removePlugin:function(B){var A=this.config.shortcuts[B];
if(A){A.unload()
}delete this.config.shortcuts[B]
},addShortcut:function(A,B){this.config.shortcuts[A]={event:new xq.Shortcut(A),handler:B}
},addShortcuts:function(B){for(var A=0;
A<B.length;
A++){this.addShortcut(B[A].event,B[A].handler)
}},getShortcut:function(A){return this.config.shortcuts[A]
},getShortcuts:function(){return this.config.shortcuts
},removeShortcut:function(A){delete this.config.shortcuts[A]
},addAutocorrection:function(D,C,A){if(C.exec){var B=C;
C=function(E){return E.match(B)
}
}this.config.autocorrections[D]={criteria:C,handler:A}
},addAutocorrections:function(B){for(var A=0;
A<B.length;
A++){this.addAutocorrection(B[A].id,B[A].criteria,B[A].handler)
}},getAutocorrection:function(A){return this.config.autocorrection[A]
},getAutocorrections:function(){return this.config.autocorrections
},removeAutocorrection:function(A){delete this.config.autocorrections[A]
},addAutocompletion:function(D,C,A){if(C.exec){var B=C;
C=function(F){var E=B.exec(F);
return E?E.index:-1
}
}this.config.autocompletions[D]={criteria:C,handler:A}
},addAutocompletions:function(B){for(var A=0;
A<B.length;
A++){this.addAutocompletion(B[A].id,B[A].criteria,B[A].handler)
}},getAutocompletion:function(A){return this.config.autocompletions[A]
},getAutocompletions:function(){return this.config.autocompletions
},removeAutocompletion:function(A){delete this.config.autocompletions[A]
},addTemplateProcessor:function(B,A){this.config.templateProcessors[B]={handler:A}
},addTemplateProcessors:function(B){for(var A=0;
A<B.length;
A++){this.addTemplateProcessor(B[A].id,B[A].handler)
}},getTemplateProcessor:function(A){return this.config.templateProcessors[A]
},getTemplateProcessors:function(){return this.config.templateProcessors
},removeTemplateProcessor:function(A){delete this.config.templateProcessors[A]
},addContextMenuHandler:function(B,A){this.config.contextMenuHandlers[B]={handler:A}
},addContextMenuHandlers:function(B){for(var A=0;
A<B.length;
A++){this.addContextMenuHandler(B[A].id,B[A].handler)
}},getContextMenuHandler:function(A){return this.config.contextMenuHandlers[A]
},getContextMenuHandlers:function(){return this.config.contextMenuHandlers
},removeContextMenuHandler:function(A){delete this.config.contextMenuHandlers[A]
},setWidth:function(A){this.outmostWrapper.style.width=A
},setHeight:function(A){this.wysiwygEditorDiv.style.height=A;
this.sourceEditorDiv.style.height=A
},getCurrentEditMode:function(){return this.currentEditMode
},toggleSourceAndWysiwygMode:function(){var A=this.getCurrentEditMode();
this.setEditMode(A==="wysiwyg"?"source":"wysiwyg")
},setEditMode:function(D){if(typeof D!=="string"){throw"[mode] is not a string."
}if(["wysiwyg","source"].indexOf(D)===-1){throw"Illegal [mode] value: '"+D+"'. Use 'wysiwyg' or 'source'"
}if(this.currentEditMode===D){return 
}var C=!!this.outmostWrapper;
if(!C){this.validator=xq.validator.Base.createInstance(this.doc.location.href,this.config.urlValidationMode,this.config.whitelist);
this._fireOnStartInitialization(this);
this._createEditorFrame(D);
var B=window.setInterval(function(){if(this.getBody()){window.clearInterval(B);
if(xq.Browser.isIE6){this.rdom.getDoc().documentElement.style.overflowY="auto";
this.rdom.getDoc().documentElement.style.overflowX="hidden"
}this.setEditMode(D);
this.PreventExit.defaultContent=this.getCurrentContent().stripTags();
if(this.config.autoFocusOnInit){this.focus()
}this.timer.start();
this._fireOnInitialized(this)
}}.bind(this),10);
return 
}if(D==="wysiwyg"){this._setEditModeToWysiwyg()
}else{this._setEditModeToSource()
}var A=this.currentEditMode;
this.currentEditMode=D;
this._fireOnCurrentEditModeChanged(this,A,this.currentEditMode)
},_setEditModeToWysiwyg:function(){this.contentElement.style.display="none";
this.sourceEditorDiv.style.display="none";
if(this.currentEditMode==="source"){var B=this.getSourceContent(true);
var A=this.validator.invalidate(B);
A=this.removeUnnecessarySpaces(A);
if(A.isBlank()){this.rdom.clearRoot()
}else{this.rdom.getRoot().innerHTML=A;
this.rdom.wrapAllInlineOrTextNodesAs("P",this.rdom.getRoot(),true)
}}else{var A=this.validator.invalidate(this.getStaticContent());
A=this.removeUnnecessarySpaces(A);
if(A.isBlank()){this.rdom.clearRoot()
}else{this.rdom.getRoot().innerHTML=A;
this.rdom.wrapAllInlineOrTextNodesAs("P",this.rdom.getRoot(),true)
}}this.wysiwygEditorDiv.style.display="block";
this.outmostWrapper.style.display="block";
if(xq.Browser.isGecko){this.rdom.placeCaretAtStartOf(this.rdom.getRoot())
}if(this.toolbar){this.toolbar.enableButtons()
}},_setEditModeToSource:function(){var A=null;
if(this.currentEditMode==="wysiwyg"){A=this.getWysiwygContent()
}else{A=this.getStaticContent()
}this.sourceEditorTextarea.value=A;
this.contentElement.style.display="none";
this.wysiwygEditorDiv.style.display="none";
this.sourceEditorDiv.style.display="block";
this.outmostWrapper.style.display="block";
if(this.toolbar){this.toolbar.disableButtons(["html"])
}},loadStylesheet:function(C){var A=this.getDoc().getElementsByTagName("HEAD")[0];
var B=this.getDoc().createElement("LINK");
B.rel="Stylesheet";
B.type="text/css";
B.href=C;
A.appendChild(B)
},loadCurrentContentFromStaticContent:function(){if(this.getCurrentEditMode()=="wysiwyg"){var A=this.validator.invalidate(this.getStaticContent());
A=this.removeUnnecessarySpaces(A);
if(A.isBlank()){this.rdom.clearRoot()
}else{this.rdom.getRoot().innerHTML=A;
this.rdom.wrapAllInlineOrTextNodesAs("P",this.rdom.getRoot(),true)
}}else{this.sourceEditorTextarea.value=this.getStaticContent()
}this._fireOnCurrentContentChanged(this)
},removeUnnecessarySpaces:function(A){var C=this.rdom.tree.getBlockTags().join("|");
var B=new RegExp("\\s*<(/?)("+C+")>\\s*","img");
return A.replace(B,"<$1$2>")
},getCurrentContent:function(){if(this.getCurrentEditMode()==="source"){return this.getSourceContent(this.config.noValidationInSourceEditMode)
}else{return this.getWysiwygContent()
}},getWysiwygContent:function(){return this.validator.validate(this.rdom.getRoot())
},getSourceContent:function(C){var B=this.sourceEditorTextarea.value;
if(C){return B
}var A=document.createElement("div");
A.innerHTML=this.removeUnnecessarySpaces(B);
var D=xq.rdom.Base.createInstance();
D.wrapAllInlineOrTextNodesAs("P",A,true);
return this.validator.validate(A,true)
},setStaticContent:function(A){this.contentElement.value=A;
this._fireOnStaticContentChanged(this,A)
},getStaticContent:function(){return this.contentElement.value
},getStaticContentAsDOM:function(){var A=this.doc.createElement("DIV");
A.innerHTML=this.contentElement.value;
return A
},focus:function(){if(this.getCurrentEditMode()==="wysiwyg"){this.rdom.focus();
if(this.toolbar){this.toolbar.triggerUpdate()
}}else{if(this.getCurrentEditMode()==="source"){this.sourceEditorTextarea.focus()
}}},getWysiwygEditorDiv:function(){return this.wysiwygEditorDiv
},getSourceEditorDiv:function(){return this.sourceEditorDiv
},getOuterFrame:function(){return this.outerFrame
},getOuterDoc:function(){return this.outerFrame.contentWindow.document
},getFrame:function(){return this.editorFrame
},getWin:function(){return this.rdom.getWin()
},getDoc:function(){return this.rdom.getDoc()
},getBody:function(){return this.rdom.getRoot()
},getOutmostWrapper:function(){return this.outmostWrapper
},_createIFrame:function(C,B,A){var D=C.createElement("iframe");
D.style.width=B||"100%";
D.style.height=A||"100%";
D.setAttribute("frameBorder","0");
D.setAttribute("marginWidth","0");
D.setAttribute("marginHeight","0");
D.setAttribute("allowTransparency","auto");
return D
},_createDoc:function(A,F,J,C,H,I,D){var E=[];
if(!xq.Browser.isTrident){E.push('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">')
}E.push('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">');
E.push("<head>");
E.push('<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />');
if(F){E.push(F)
}if(J){for(var B=0;
B<J.length;
B++){E.push('<link rel="Stylesheet" type="text/css" href="'+J[B]+'" />')
}}if(C){for(var B=0;
B<C.length;
B++){E.push('<script type="text/javascript" src="'+C[B]+'"><\/script>')
}}E.push("</head>");
E.push("<body "+(I?'class="'+I+'"':"")+" "+(H?'id="'+H+'"':"")+">");
if(D){E.push(D)
}E.push("</body>");
E.push("</html>");
var G=A.contentWindow.document;
G.open();
G.write(E.join(""));
G.close();
return G
},_createEditorFrame:function(E){this.contentElement.style.display="none";
this.outmostWrapper=this.doc.createElement("div");
this.outmostWrapper.className="xquared";
this.outmostWrapper.style.position="relative";
this.contentElement.parentNode.insertBefore(this.outmostWrapper,this.contentElement);
if(this.toolbarContainer||this.config.generateDefaultToolbar||this.config.defaultToolbarButtonList.length>0){this.toolbar=new xq.ui.Toolbar(this,this.toolbarContainer,this.outmostWrapper,this.config.defaultToolbarButtonMap,this.config.defaultToolbarButtonList,this.config.imagePathForDefaultToolbar,function(){var F=this.getCurrentEditMode()==="wysiwyg"?this.lastFocusElement:null;
return F&&F.nodeName!="BODY"?this.rdom.collectStructureAndStyle(F):null
}.bind(this))
}this.sourceEditorDiv=this.doc.createElement("div");
this.sourceEditorDiv.className="editor source_editor";
this.sourceEditorDiv.style.display="none";
this.outmostWrapper.appendChild(this.sourceEditorDiv);
this.sourceEditorTextarea=this.doc.createElement("textarea");
this.sourceEditorDiv.appendChild(this.sourceEditorTextarea);
this.wysiwygEditorDiv=this.doc.createElement("div");
this.wysiwygEditorDiv.className="editor wysiwyg_editor";
this.outmostWrapper.appendChild(this.wysiwygEditorDiv);
this.outerFrame=this._createIFrame(document);
this.wysiwygEditorDiv.appendChild(this.outerFrame);
var C=this._createDoc(this.outerFrame,'<style type="text/css">html, body {margin:0px; padding:0px; background-color: transparent; width: 100%; height: 100%; overflow: hidden;}</style>');
this.editorFrame=this._createIFrame(C);
C.body.appendChild(this.editorFrame);
var D=this._createDoc(this.editorFrame,'<style type="text/css">html, body {margin:0px; padding:0px; background-color: transparent;}</style>'+(!xq.Browser.isTrident?'<base href="./" />':"")+(this.config.changeCursorOnLink?"<style>.xed a {cursor: pointer !important;}</style>":""),this.config.contentCssList,this.config.contentJsList,this.config.bodyId,this.config.bodyClass,"");
this.rdom.setWin(this.editorFrame.contentWindow);
this.editHistory=new xq.EditHistory(this.rdom);
if(xq.Browser.isIE){this.getBody().contentEditable=true
}else{this.rdom.getDoc().designMode="On"
}if(xq.Browser.isFF3){this.rdom.getDoc().designMode="Off";
this.rdom.getDoc().designMode="On"
}if(xq.Browser.isGecko){try{this.rdom.getDoc().execCommand("enableInlineTableEditing",false,"false")
}catch(A){}}this._registerEventHandlers();
if(this.config.automaticallyHookSubmitEvent&&this.contentElement.form){var B=this.contentElement.form.onsubmit;
this.contentElement.form.onsubmit=function(){xed.config.enablePreventExit===false;
this.contentElement.value=this.getCurrentContent();
return B?B.bind(this.contentElement.form)():true
}.bind(this)
}},_registerEventHandlers:function(){var B=[this.platformDepedentKeyEventType,"click","keyup","mouseup","contextmenu"];
if(xq.Browser.isTrident&&this.config.changeCursorOnLink){B.push("mousemove")
}var C=this._handleEvent.bindAsEventListener(this);
for(var A=0;
A<B.length;
A++){xq.observe(this.getDoc(),B[A],C)
}if(xq.Browser.isGecko){xq.observe(this.getDoc(),"focus",C);
xq.observe(this.getDoc(),"blur",C);
xq.observe(this.getDoc(),"scroll",C);
xq.observe(this.getDoc(),"dragdrop",C)
}else{xq.observe(this.getWin(),"focus",C);
xq.observe(this.getWin(),"blur",C);
xq.observe(this.getWin(),"scroll",C)
}},_dummyLink:null,_makeDummyLink:function(A,D){var B=this.getOuterDoc().createElement("A");
B.href=A.href;
B.target="_top";
B.className=A.className;
B.title=A.title;
var C=this.getOuterDoc().createElement("IMG");
C.src=this.config.imagePathForContent+"blank.gif";
C.style.width=C.style.height="100%";
C.style.border="none";
B.appendChild(C);
this.getOuterDoc().body.appendChild(B);
this._dummyLink=B;
B.style.top=(D.clientY-5)+"px";
B.style.left=(D.clientX-5)+"px";
B.onfocus=function(){this.blur();
return false
};
xq.observe(B,"click",this._handleEvent.bindAsEventListener(this));
B.style.position="absolute";
B.style.display="block";
B.style.width="10px";
B.style.height="10px";
B.style.zIndex="4"
},_handleEvent:function(e){if(!this._fireOnBeforeEvent){return 
}this._fireOnBeforeEvent(this,e);
if(e.stopProcess){xq.stopEvent(e);
return false
}if(e.type==="mousemove"){if(!this.config.changeCursorOnLink){return true
}var link=this.rdom.getParentElementOf(e.srcElement,["A"]);
if(this._dummyLink&&this._dummyLink==link){return true
}if(this._dummyLink){this.getOuterDoc().body.removeChild(this._dummyLink);
this._dummyLink=null
}if(!!link&&!this.rdom.hasSelection()){this._makeDummyLink(link,e)
}return true
}var stop=false;
var modifiedByCorrection=false;
if(e.type===this.platformDepedentKeyEventType){var undoPerformed=false;
modifiedByCorrection=this.rdom.correctParagraph();
for(var key in this.config.shortcuts){if(!this.config.shortcuts[key].event.matches(e)){continue
}var handler=this.config.shortcuts[key].handler;
var xed=this;
stop=(typeof handler==="function")?handler(this):eval(handler);
if(key==="undo"){undoPerformed=true
}}}else{if(e.type==="click"&&e.button===0&&this.config.enableLinkClick){var a=this.rdom.getParentElementOf(e.target||e.srcElement,["A"]);
if(a){stop=this.handleClick(e,a)
}}else{if(["keyup","mouseup"].indexOf(e.type)!==-1){modifiedByCorrection=this.rdom.correctParagraph()
}else{if(["contextmenu"].indexOf(e.type)!==-1){this._handleContextMenu(e)
}else{if("focus"==e.type){this.rdom.focused=true
}else{if("blur"==e.type){this.rdom.focused=false
}}}}}}if(stop){xq.stopEvent(e)
}this._fireOnCurrentContentChanged(this);
this._fireOnAfterEvent(this,e);
if(!undoPerformed&&!modifiedByCorrection&&e.type!="scroll"){this.editHistory.onEvent(e)
}return !stop
},handleAutocorrection:function(){var block=this.rdom.getCurrentBlockElement();
var text=this.rdom.getInnerText(block).unescapeHTML();
var acs=this.config.autocorrections;
var performed=false;
var stop=false;
for(var key in acs){var ac=acs[key];
if(ac.criteria(text)){try{this.editHistory.onCommand();
this.editHistory.disable();
if(typeof ac.handler==="String"){var xed=this;
var rdom=this.rdom;
eval(ac.handler)
}else{stop=ac.handler(this,this.rdom,block,text)
}this.editHistory.enable()
}catch(ignored){}block=this.rdom.getCurrentBlockElement();
text=this.rdom.getInnerText(block);
performed=true;
if(stop){break
}}}return stop
},handleAutocompletion:function(){var acs=this.config.autocompletions;
if(xq.isEmptyHash(acs)){return 
}if(this.rdom.hasSelection()){var text=this.rdom.getSelectionAsText();
this.rdom.deleteSelection();
var wrapper=this.rdom.insertNode(this.rdom.createElement("SPAN"));
this.rdom.insertTextAt(text,wrapper,"start");
var marker=this.rdom.pushMarker();
var filtered=[];
for(var key in acs){filtered.push([key,acs[key].criteria(text)])
}filtered=filtered.findAll(function(elem){return elem[1]!==-1
});
if(filtered.length===0){this.rdom.popMarker(true);
return 
}var minIndex=0;
var min=filtered[0][1];
for(var i=0;
i<filtered.length;
i++){if(filtered[i][1]<min){minIndex=i;
min=filtered[i][1]
}}var ac=acs[filtered[minIndex][0]];
this.editHistory.disable();
this.rdom.selectElement(wrapper)
}else{var marker=this.rdom.pushMarker();
var filtered=[];
for(var key in acs){filtered.push([key,this.rdom.testSmartWrap(marker,acs[key].criteria).textIndex])
}filtered=filtered.findAll(function(elem){return elem[1]!==-1
});
if(filtered.length===0){this.rdom.popMarker(true);
return 
}var minIndex=0;
var min=filtered[0][1];
for(var i=0;
i<filtered.length;
i++){if(filtered[i][1]<min){minIndex=i;
min=filtered[i][1]
}}var ac=acs[filtered[minIndex][0]];
this.editHistory.disable();
var wrapper=this.rdom.smartWrap(marker,"SPAN",ac.criteria);
if(xq.Browser.isWebkit){this.rdom.popMarker(true);
this.rdom.pushMarker()
}}var block=this.rdom.getCurrentBlockElement();
var text=this.rdom.getInnerText(wrapper).unescapeHTML();
try{if(typeof ac.handler==="String"){var xed=this;
var rdom=this.rdom;
eval(ac.handler)
}else{ac.handler(this,this.rdom,block,wrapper,text)
}}catch(ignored){}try{this.rdom.unwrapElement(wrapper)
}catch(ignored){}if(this.rdom.isEmptyBlock(block)){this.rdom.correctEmptyElement(block)
}this.editHistory.enable();
this.editHistory.onCommand();
this.rdom.popMarker(true)
},handleClick:function(D,C){var B=decodeURI(C.href);
var A=C.className.indexOf("newWindow")!=-1;
if(A){window.open(B,"_blank");
return true
}if(!xq.Browser.isTrident){if(!D.ctrlKey&&!D.shiftKey&&D.button!==1){window.location.href=B;
return true
}}else{if(D.shiftKey){window.open(B,"_blank")
}else{window.location.href=B
}return true
}return false
},lastLinkDialog:null,handleLink:function(){var B=xq.$("linkDialog");
if(B&&B.style.display!="none"){this.lastLinkDialog.close()
}var D=this.rdom.getSelectionAsText()||"";
var A=new xq.ui.FormDialog(this,xq.ui_templates.basicLinkDialog,function(E){setTimeout(function(){if(D){E.form.text.value=D;
E.form.url.focus();
E.form.url.select()
}else{E.form.text.focus()
}},0)
},function(G){this.focus();
if(xq.Browser.isTrident){var E=this.rdom.rng();
E.moveToBookmark(C);
E.select()
}if(!G){return 
}var H=/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
if(!H.test(G.url)){alert(this._("Unknown URL pattern"));
A.form.url.focus();
return 
}if(G.newWindow){var F="newWindow"
}this.handleInsertLink(false,G.url,G.text,G.text,F);
A.close()
}.bind(this));
if(xq.Browser.isTrident){var C=this.rdom.rng().getBookmark()
}A.show({position:"centerOfEditor",mode:"lightweight",notSelfClose:true,dialogId:"linkDialog"});
this.lastLinkDialog=A;
return true
},handleInsertLink:function(H,C,G,F,D){if(H&&!this.rdom.hasSelection()){var B=this.rdom.pushMarker();
var A=this.rdom.smartWrap(B,"A",function(J){var I=J.lastIndexOf(" ");
return I===-1?I:I+1
});
A.href=C;
A.title=G;
if(D){A.className=D
}if(F){A.innerHTML="";
A.appendChild(this.rdom.createTextNode(F))
}else{if(!A.hasChildNodes()){this.rdom.deleteNode(A)
}}this.rdom.popMarker(true)
}else{F=F||(this.rdom.hasSelection()?this.rdom.getSelectionAsText():null);
if(!F){return 
}this.rdom.deleteSelection();
var A=this.rdom.createElement("A");
A.href=C;
A.title=G;
if(D){A.className=D
}A.appendChild(this.rdom.createTextNode(F));
this.rdom.insertNode(A)
}var E=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleSpace:function(){if(this.rdom.hasSelection()){return false
}if(!xq.Browser.isTrident){this.replaceUrlToLink()
}return false
},handleEnter:function(E,H){if(this.rdom.hasSelection()){return false
}if(xq.Browser.isTrident&&this.rdom.tree.isBlockOnlyContainer(this.rdom.getCurrentElement())&&this.rdom.recentHR){this.rdom.insertNodeAt(this.rdom.makeEmptyParagraph(),this.rdom.recentHR,"before");
this.rdom.recentHR=null;
return true
}if(!E&&this.handleAutocorrection()){return true
}var F=this.rdom.getCurrentBlockElement();
var D=this.rdom.collectStructureAndStyle(F);
if(!xq.Browser.isTrident){this.replaceUrlToLink()
}var B=this.rdom.isCaretAtEmptyBlock();
var A=B||this.rdom.isCaretAtBlockStart();
var I=B||(!A&&this.rdom.isCaretAtBlockEnd());
var C=B||A||I;
if(!C){var G=this.rdom.pushMarker();
if(this.rdom.isFirstLiWithNestedList(F)&&!H){var J=F.parentNode;
this.rdom.unwrapElement(F);
F=J
}else{if(F.nodeName!=="LI"&&this.rdom.tree.isBlockContainer(F)){F=this.rdom.wrapAllInlineOrTextNodesAs("P",F,true).first()
}}this.rdom.splitElementUpto(G,F);
this.rdom.popMarker(true)
}else{if(B){this._handleEnterAtEmptyBlock();
if(!xq.Browser.isWebkit){if(D.fontSize&&D.fontSize!=="2"){this.handleFontSize(D.fontSize)
}if(D.fontName){this.handleFontFace(D.fontName)
}}}else{this._handleEnterAtEdge(A,H);
if(!xq.Browser.isWebkit){if(D.fontSize&&D.fontSize!=="2"){this.handleFontSize(D.fontSize)
}if(D.fontName){this.handleFontFace(D.fontName)
}if(D.foregroundColor){this.handleForegroundColor(D.foregroundColor)
}}}}return true
},handleMoveBlock:function(A){var C=this.rdom.moveBlock(this.rdom.getCurrentBlockElement(),A);
if(C){this.rdom.selectElement(C,false);
if(this.rdom.isEmptyBlock(C)){this.rdom.collapseSelection(true)
}if(!this.isElementVisible(C)){C.scrollIntoView(false)
}var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this)
}return true
},handleTab:function(){var B=this.rdom.hasSelection();
var C=this.rdom.getParentElementOf(this.rdom.getCurrentBlockElement(),["TABLE"]);
var A=this.rdom.getParentElementOf(this.rdom.getCurrentBlockElement(),["LI"]);
if(B){this.handleIndent()
}else{if(C&&!A){this.handleMoveToNextCell()
}else{if(this.rdom.isCaretAtBlockStart()){this.handleIndent()
}else{this.handleInsertTab()
}}}return true
},handleShiftTab:function(){var B=this.rdom.hasSelection();
var C=this.rdom.getParentElementOf(this.rdom.getCurrentBlockElement(),["TABLE"]);
var A=this.rdom.getParentElementOf(this.rdom.getCurrentBlockElement(),["LI"]);
if(B){this.handleOutdent()
}else{if(C&&!A){this.handleMoveToPreviousCell()
}else{this.handleOutdent()
}}return true
},handleInsertTab:function(){this.rdom.insertHtml("&nbsp;");
this.rdom.insertHtml("&nbsp;");
this.rdom.insertHtml("&nbsp;");
return true
},handleDelete:function(){var D=this.rdom.getCurrentBlockElement();
if(this.rdom.hasSelection()){return false
}if(this.rdom.isEmptyBlock(D)&&this.rdom.isCaretAtBlockEnd()&&!D.nextSibling){if(this.rdom.tree.isListContainer(D.parentNode)){return false
}return true
}if(this.rdom.isCaretAtBlockEnd()){return this._handleMerge(true)
}if(!xq.Browser.isFF){return false
}var B=this.rdom.getCurrentElement();
for(var A=0;
A<B.childNodes.length;
A++){var C=B.childNodes[A];
if(C.nodeName=="#text"&&!C.nodeValue.length){xed.rdom.deleteNode(C)
}}return false
},handleBackspace:function(){if(this.rdom.hasSelection()||!this.rdom.isCaretAtBlockStart()){return false
}return this._handleMerge(false)
},_handleMerge:function(C){var F=this.rdom.getCurrentBlockElement();
if(this.rdom.isEmptyBlock(F)&&!this.rdom.tree.isBlockContainer(F.nextSibling)&&C){var D=this.rdom.removeBlock(F);
this.rdom.placeCaretAtStartOf(D);
if(!this.isElementVisible(D)){D.scrollIntoView(false)
}var E=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
}else{var B=this.rdom.pushMarker();
var A=this.rdom.mergeElement(F,C,C);
if(!A&&!C){this.rdom.extractOutElementFromParent(F)
}this.rdom.popMarker(true);
if(A){this.rdom.correctEmptyElement(A)
}var E=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return !!A
}},handleMoveToNextCell:function(){this._handleMoveToCell("next")
},handleMoveToPreviousCell:function(){this._handleMoveToCell("prev")
},handleMoveToAboveCell:function(){this._handleMoveToCell("above")
},handleMoveToBelowCell:function(){this._handleMoveToCell("below")
},_handleMoveToCell:function(B){var C=this.rdom.getCurrentBlockElement();
var H=this.rdom.getParentElementOf(C,["TD","TH"]);
var J=this.rdom.getParentElementOf(H,["TABLE"]);
var I=new xq.RichTable(this.rdom,J);
var E=null;
if(["next","prev"].indexOf(B)!==-1){var G=B==="next";
E=G?I.getNextCellOf(H):I.getPreviousCellOf(H)
}else{var F=B==="below";
E=F?I.getBelowCellOf(H):I.getAboveCellOf(H)
}if(!E){var A=function(K){return["TD","TH"].indexOf(K.nodeName)===-1&&this.tree.isBlock(K)&&!this.tree.hasBlocks(K)
}.bind(this.rdom);
var D=function(K){return this.tree.isBlock(K)&&!this.tree.isDescendantOf(this.getRoot(),K)
}.bind(this.rdom);
E=(G||F)?this.rdom.tree.findForward(H,A,D):this.rdom.tree.findBackward(J,A,D)
}if(E){this.rdom.placeCaretAtStartOf(E)
}},handleStrongEmphasis:function(){this.rdom.applyStrongEmphasis();
var A=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleEmphasis:function(){this.rdom.applyEmphasis();
var A=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleUnderline:function(){this.rdom.applyUnderline();
var A=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleStrike:function(){this.rdom.applyStrike();
var A=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleRemoveFormat:function(){this.rdom.applyRemoveFormat();
var A=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleRemoveLink:function(){this.rdom.applyRemoveLink();
var A=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleTable:function(B){if(B){var F=this.rdom.getCurrentBlockElement();
if(!F){F=this.getBody()
}if(this.rdom.getParentElementOf(F,["TABLE"])){return true
}var C=xq.RichTable.create(this.rdom,B);
C.setTableProperty(B);
if(this.rdom.tree.isBlockContainer(F)){var D=this.rdom.wrapAllInlineOrTextNodesAs("P",F,true);
F=D.last()
}var A=this.rdom.insertNodeAt(C.getDom(),F,"after");
this.rdom.placeCaretAtStartOf(C.getCellAt(0,0));
if(this.rdom.isEmptyBlock(F)){this.rdom.deleteNode(F,true)
}var E=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this)
}else{xq.RichTableController.openDialog("new")
}return true
},handleColorPicker:function(B){var D=this.lastAnchor;
if(D.className.indexOf("foreground")!=-1){xed.handleForegroundColor(B)
}else{if(D.className.indexOf("tableDialog")!=-1){var F=D.parentNode.parentNode.getElementsByTagName("A");
for(var E=0;
E<F.length;
E++){if(F[E].id.indexOf("ColorBoard")!=-1){F[E].style.backgroundColor=B
}}var A=D.parentNode.parentNode.getElementsByTagName("INPUT");
for(var C=0;
C<A.length;
C++){if(A[C].id.indexOf("ColorCode")!=-1){A[C].value=B
}}}}},handleInsertNewRowAt:function(A){var F=this.rdom.getCurrentBlockElement();
var D=this.rdom.getParentElementOf(F,["TR"]);
if(!D){return true
}var C=this.rdom.getParentElementOf(D,["TABLE"]);
var B=new xq.RichTable(this.rdom,C);
var E=B.insertNewRowAt(D,A);
this.rdom.placeCaretAtStartOf(E.cells[0]);
return true
},handleInsertNewColumnAt:function(A){var D=this.rdom.getCurrentBlockElement();
var E=this.rdom.getParentElementOf(D,["TD"],true);
if(!E){return true
}var C=this.rdom.getParentElementOf(E,["TABLE"]);
var B=new xq.RichTable(this.rdom,C);
B.insertNewCellAt(E,A);
this.rdom.placeCaretAtStartOf(D);
return true
},handleDeleteTable:function(){var D=this.rdom.getCurrentBlockElement();
var B=this.rdom.getParentElementOf(D,["TABLE"]);
if(!B){return true
}var A=new xq.RichTable(this.rdom,B);
var C=A.deleteTable(B);
this.rdom.placeCaretAtStartOf(C);
return true
},handleDeleteRow:function(){var E=this.rdom.getCurrentBlockElement();
var C=this.rdom.getParentElementOf(E,["TR"]);
if(!C){return true
}var B=this.rdom.getParentElementOf(C,["TABLE"]);
var A=new xq.RichTable(this.rdom,B);
var D=A.deleteRow(C);
this.rdom.placeCaretAtStartOf(D);
return true
},handleDeleteColumn:function(){var C=this.rdom.getCurrentBlockElement();
var D=this.rdom.getParentElementOf(C,["TD"],true);
if(!D){return true
}var B=this.rdom.getParentElementOf(D,["TABLE"]);
var A=new xq.RichTable(this.rdom,B);
A.deleteCell(D);
return true
},_tablePropFormSize:function(B,C,A){B[A+"Unit"].value=C[A].unit||"100%";
B[A].value=C[A].size||"";
B[A].style.display=(B[A+"Unit"].value=="auto")?"none":""
},handleTableProperty:function(D){var C=this.rdom.getCurrentBlockElement();
var B=this.rdom.getParentElementOf(C,["TABLE"],true);
if(!B){return true
}var A=new xq.RichTable(this.rdom,B);
if(D){A.setTableProperty(D)
}else{var D=A.getTableProperty()
}return D
},handleRowProperty:function(D){var C=this.rdom.getCurrentBlockElement();
var B=this.rdom.getParentElementOf(C,["TR"],true);
if(!B){return true
}var A=new xq.RichTable(this.rdom,B.offsetParent);
if(D){A.setRowProperty(B,D)
}else{var D=A.getRowProperty(B)
}return D
},handleColumnProperty:function(D){var C=this.rdom.getCurrentBlockElement();
var B=this.rdom.getParentElementOf(C,["TD"],true);
if(!B){return true
}var A=new xq.RichTable(this.rdom,B.offsetParent);
if(D){A.setColumnProperty(B,D)
}else{var D=A.getColumnProperty(B)
}return D
},handleIndent:function(){if(this.rdom.hasSelection(true)){var C=this.rdom.getBlockElementsAtSelectionEdge(true,true);
if(C.first()!==C.last()){var D=this.rdom.indentElements(C.first(),C.last());
this.rdom.selectBlocksBetween(D.first(),D.last());
var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
}}var A=this.rdom.getCurrentBlockElement();
var D=this.rdom.indentElement(A);
if(D&&!this.rdom.tree.isAtomic(this.rdom.getCurrentElement())){this.rdom.placeCaretAtStartOf(D);
var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this)
}return true
},handleOutdent:function(){if(this.rdom.hasSelection(true)){var C=this.rdom.getBlockElementsAtSelectionEdge(true,true);
if(C.first()!==C.last()){var D=this.rdom.outdentElements(C.first(),C.last());
this.rdom.selectBlocksBetween(D.first(),D.last());
var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
}}var A=this.rdom.getCurrentBlockElement();
var D=this.rdom.outdentElement(A);
if(D&&!this.rdom.tree.isAtomic(this.rdom.getCurrentElement())){this.rdom.placeCaretAtStartOf(D);
var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this)
}return true
},handleList:function(B,A){if(this.rdom.hasSelection(true)){var E=this.rdom.getBlockElementsAtSelectionEdge(true,true);
if(E.first()!==E.last()){E=this.rdom.applyLists(E.first(),E.last(),B,A)
}else{E[0]=E[1]=this.rdom.applyList(E.first(),B,A)
}this.rdom.selectBlocksBetween(E.first(),E.last())
}else{var D=this.rdom.applyList(this.rdom.getCurrentBlockElement(),B,A);
this.rdom.placeCaretAtStartOf(D)
}var C=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleJustify:function(A){if(this.rdom.hasSelection(true)){var D=this.rdom.getSelectedBlockElements();
var A=(A==="left"||A==="both")&&(D[0].style.textAlign==="left"||D[0].style.textAlign==="")?"both":A;
this.rdom.justifyBlocks(D,A);
this.rdom.selectBlocksBetween(D.first(),D.last())
}else{var C=this.rdom.getCurrentBlockElement();
var A=(A==="left"||A==="both")&&(C.style.textAlign==="left"||C.style.textAlign==="")?"both":A;
this.rdom.justifyBlock(C,A)
}var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleRemoveBlock:function(){var C=this.rdom.getCurrentBlockElement();
var A=this.rdom.removeBlock(C);
this.rdom.placeCaretAtStartOf(A);
if(!this.isElementVisible(A)){A.scrollIntoView(false)
}var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleBackgroundColor:function(A){if(A){this.rdom.applyBackgroundColor(A);
if(this.rdom.hasSelection()){var D=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this)
}}else{var B=new xq.ui.FormDialog(this,xq.ui_templates.basicColorPickerDialog,function(E){},function(F){this.focus();
if(xq.Browser.isTrident){var E=this.rdom.rng();
E.moveToBookmark(C);
E.select()
}if(!F){return 
}this.handleBackgroundColor(F.color)
}.bind(this));
if(xq.Browser.isTrident){var C=this.rdom.rng().getBookmark()
}B.show({position:"centerOfEditor"})
}return true
},handleForegroundColor:function(A){if(A){this.rdom.applyForegroundColor(A);
if(this.rdom.hasSelection()){var D=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this)
}}else{var B=new xq.ui.FormDialog(this,xq.ui_templates.basicColorPickerDialog,function(E){},function(F){this.focus();
if(xq.Browser.isTrident){var E=this.rdom.rng();
E.moveToBookmark(C);
E.select()
}if(!F){return 
}this.handleForegroundColor(F.color)
}.bind(this));
if(xq.Browser.isTrident){var C=this.rdom.rng().getBookmark()
}B.show({position:"centerOfEditor"})
}return true
},handleFontFace:function(A){if(A){this.rdom.applyFontFace(A);
if(this.rdom.hasSelection()){var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this)
}}else{}return true
},handleFontSize:function(A){if(A){this.rdom.applyFontSize(A);
if(this.rdom.hasSelection()){var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this)
}}else{}return true
},handleSuperscription:function(){this.rdom.applySuperscription();
var A=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleSubscription:function(){this.rdom.applySubscription();
var A=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleApplyBlock:function(B,C,A){if(!B&&!C){return true
}if(this.rdom.hasSelection()){var G=this.rdom.getBlockElementsAtSelectionEdge(true,true);
if(G.first()!==G.last()){var D=this.rdom.applyTagIntoElements(B,G.first(),G.last(),C);
this.rdom.selectBlocksBetween(D.first(),D.last());
var F=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
}}var E=this.rdom.getCurrentBlockElement();
this.rdom.pushMarker();
var D=this.rdom.applyTagIntoElement(B,E,C)||E;
if(A){this.rdom.setAttributes(D,A)
}this.rdom.popMarker(true);
if(this.rdom.isEmptyBlock(D)){this.rdom.correctEmptyElement(D);
this.rdom.placeCaretAtStartOf(D)
}var F=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleSeparator:function(){this.rdom.collapseSelection();
var C=this.rdom.getCurrentBlockElement();
var A=this.rdom.isCaretAtBlockStart();
if(this.rdom.tree.isBlockContainer(C)){C=this.rdom.wrapAllInlineOrTextNodesAs("P",C,true)[0]
}this.rdom.insertNodeAt(this.rdom.createElement("HR"),C,A?"before":"after");
this.rdom.placeCaretAtStartOf(C);
var B=this.editHistory.onCommand();
this._fireOnCurrentContentChanged(this);
return true
},handleUndo:function(){var A=this.editHistory.undo();
this._fireOnCurrentContentChanged(this);
var B=this.rdom.getCurrentBlockElement();
if(!xq.Browser.isTrident&&B&&!this.isElementVisible(B)){B.scrollIntoView(false)
}return true
},handleRedo:function(){var A=this.editHistory.redo();
this._fireOnCurrentContentChanged(this);
var B=this.rdom.getCurrentBlockElement();
if(!xq.Browser.isTrident&&B&&!this.isElementVisible(B)){B.scrollIntoView(false)
}return true
},handleEmoticon:function(B){var A=this.getDoc().createElement("IMG");
A.src=this.config.imagePathForEmoticon+B;
A.alt=B;
this.rdom.insertNode(A)
},handleCharacter:function(A){this.rdom.insertHtml(decodeURIComponent(A))
},_handleContextMenu:function(C){if(xq.Browser.isWebkit){if(C.metaKey||xq.isLeftClick(C)){return false
}}else{if(C.shiftKey||C.ctrlKey||C.altKey){return false
}}var I=xq.getEventPoint(C);
var G=I.x;
var E=I.y;
var F=xq.getCumulativeOffset(this.wysiwygEditorDiv);
G+=F.left;
E+=F.top;
this._contextMenuTargetElement=C.target||C.srcElement;
if(!xq.Browser.isTrident){var H=this.getDoc();
var B=this.getBody();
G-=H.documentElement.scrollLeft;
E-=H.documentElement.scrollTop;
G-=B.scrollLeft;
E-=B.scrollTop
}for(var A in this.config.contextMenuHandlers){var D=this.config.contextMenuHandlers[A].handler(this,this._contextMenuTargetElement,G,E);
if(D){xq.stopEvent(C);
return true
}}return false
},showContextMenu:function(C,A,D){if(!C||C.length<=0){return 
}if(!this.contextMenuContainer){this.contextMenuContainer=this.doc.createElement("UL");
this.contextMenuContainer.className="xqContextMenu";
this.contextMenuContainer.style.display="none";
xq.observe(this.doc,"click",this._contextMenuClicked.bindAsEventListener(this));
xq.observe(this.rdom.getDoc(),"click",this.hideContextMenu.bindAsEventListener(this));
this.body.appendChild(this.contextMenuContainer)
}else{while(this.contextMenuContainer.childNodes.length>0){this.contextMenuContainer.removeChild(this.contextMenuContainer.childNodes[0])
}}for(var B=0;
B<C.length;
B++){C[B]._node=this._addContextMenuItem(C[B])
}this.contextMenuContainer.style.display="block";
this.contextMenuContainer.style.left=Math.min(Math.max(this.doc.body.scrollWidth,this.doc.documentElement.clientWidth)-this.contextMenuContainer.offsetWidth,A)+"px";
this.contextMenuContainer.style.top=Math.min(Math.max(this.doc.body.scrollHeight,this.doc.documentElement.clientHeight)-this.contextMenuContainer.offsetHeight,D)+"px";
this.contextMenuItems=C
},hideContextMenu:function(){if(this.contextMenuContainer){this.contextMenuContainer.style.display="none"
}},_addContextMenuItem:function(B){if(!this.contextMenuContainer){throw"No conext menu container exists"
}var A=this.doc.createElement("LI");
if(B.disabled){A.className+=" disabled"
}if(B.title==="----"){A.innerHTML="&nbsp;";
A.className="separator"
}else{if(B.handler){A.innerHTML='<a href="#" onclick="return false;">'+(B.title.toString().escapeHTML())+"</a>"
}else{A.innerHTML=(B.title.toString().escapeHTML())
}}if(B.className){A.className=B.className
}this.contextMenuContainer.appendChild(A);
return A
},_contextMenuClicked:function(e){this.hideContextMenu();
if(!this.contextMenuContainer){return 
}var node=e.srcElement||e.target;
while(node&&node.nodeName!=="LI"){node=node.parentNode
}if(!node||!this.rdom.tree.isDescendantOf(this.contextMenuContainer,node)){return 
}for(var i=0;
i<this.contextMenuItems.length;
i++){if(this.contextMenuItems[i]._node===node){var handler=this.contextMenuItems[i].handler;
if(!this.contextMenuItems[i].disabled&&handler){var xed=this;
var element=this._contextMenuTargetElement;
if(typeof handler==="function"){handler(xed,element)
}else{eval(handler)
}}break
}}},insertTemplate:function(A){return this.rdom.insertHtml(this._processTemplate(A))
},insertTemplateAt:function(B,C,A){return this.rdom.insertHtmlAt(this._processTemplate(B),C,A)
},_processTemplate:function(B){var D=this.getTemplateProcessors();
for(var A in D){var C=D[A];
B=C.handler(B)
}return this.removeUnnecessarySpaces(B)
},_handleEnterAtEmptyBlock:function(){var A=this.rdom.getCurrentBlockElement();
if(this.rdom.tree.isTableCell(A)&&this.rdom.isFirstBlockOfBody(A)){A=this.rdom.insertNodeAt(this.rdom.makeEmptyParagraph(),this.rdom.getRoot(),"start")
}else{A=this.rdom.outdentElement(A)||this.rdom.extractOutElementFromParent(A)||this.rdom.replaceTag("P",A)||this.rdom.insertNewBlockAround(A)
}this.rdom.placeCaretAtStartOf(A);
if(!xq.Browser.isTrident&&!this.isElementVisible(A)){A.scrollIntoView(false)
}},_handleEnterAtEdge:function(B,A){var D=this.rdom.getCurrentBlockElement();
var C;
if(B&&this.rdom.isFirstBlockOfBody(D)){C=this.rdom.insertNodeAt(this.rdom.makeEmptyParagraph(),this.rdom.getRoot(),"start")
}else{if(this.rdom.tree.isTableCell(D)){A=true
}var E=this.rdom.insertNewBlockAround(D,B,A?"P":null);
C=!B?E:E.nextSibling
}this.rdom.placeCaretAtStartOf(C);
if(!xq.Browser.isTrident&&!this.isElementVisible(C)){C.scrollIntoView(false)
}},isElementVisible:function(D){var E=this.rdom.getDoc();
var C=this.rdom.getParentBlockElementOf(D);
var B=E.documentElement.scrollTop||E.body.scrollTop;
var A=B+this.outerFrame.offsetHeight-C.offsetHeight;
if(B<C.offsetTop&&A>C.offsetTop){return true
}},replaceUrlToLink:function(){if(this.rdom.getParentElementOf(this.rdom.getCurrentElement(),["A"])){return 
}var B=this.rdom.pushMarker();
var D=function(F){var E=/(http|https|ftp|mailto)\:\/\/[^\s]+$/.exec(F);
return E?E.index:-1
};
var C=this.rdom.testSmartWrap(B,D);
if(C.textIndex!==-1){var A=this.rdom.smartWrap(B,"A",D);
A.href=C.text
}this.rdom.getCurrentElement().normalize();
this.rdom.popMarker(true)
},_:function(B){if(xq._messages&&xq._messages[this.config.lang]&&typeof xq._messages[this.config.lang][B]!="undefined"){B=xq._messages[this.config.lang][B]
}if(arguments.length>1){for(var A=1;
A<arguments.length;
A++){B=B.replace("$"+A,arguments[A])
}}return B
}});
xq.moduleName="Minimal";