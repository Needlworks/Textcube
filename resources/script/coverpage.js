/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

	djConfig.parseWidgets = false;
	
	dojo.require("dojo.dnd.HtmlDragAndDrop");
	dojo.require("dojo.widget.Parse");
	dojo.require("dojo.widget.Dialog");	


	DragPanel = function(node, type) {
		dojo.dnd.HtmlDragSource.call(this, node, type);
		this.dragClass = "ajax-floating-panel";
		this.opacity = 0.9;
		
        if (this.domNode.coverpageNumber != null) decorateDragPanel(this.domNode);		
	}
	
	dojo.inherits(DragPanel, dojo.dnd.HtmlDragSource);

	DragPanelAdd = function(node, type) {
		dojo.dnd.HtmlDragSource.call(this, node, type);
		this.dragClass = "ajax-floating-panel";
		this.opacity = 0.9;
	}
	dojo.inherits(DragPanelAdd, dojo.dnd.HtmlDragSource);

	DropPanel = function(node, type) {
		dojo.dnd.HtmlDropTarget.call(this, node, type);
	}
	dojo.inherits(DropPanel, dojo.dnd.HtmlDropTarget);
	
	DropDeletePanel = function(node, type) {
		dojo.dnd.HtmlDropTarget.call(this, node, type);
	}
	dojo.inherits(DropDeletePanel, dojo.dnd.HtmlDropTarget);
	
	var globalChker = true;
	var globalNewNodeCounter = 0;

	dojo.lang.extend(DropPanel, {
		onDrop: function(e) {
			if ((e.dragObject.domNode.ajaxtype == 'register') && (e.dragObject.domNode.moduleCategory == 'plugin')) 
			{
				var newNode = document.createElement(e.dragObject.domNode.tagName);
				newNode.id = 'newDragPanel_' + globalNewNodeCounter++;
				newNode.className = 'coverpage-module coverpage-plugin-module';
				newNode.ajaxtype = 'register';
				newNode.moduleCategory = e.dragObject.domNode.moduleCategory;
				newNode.identifier = e.dragObject.domNode.identifier;
				newNode.innerHTML = e.dragObject.domNode.innerHTML;
				newNode.hasPropertyEdit = e.dragObject.domNode.hasPropertyEdit;

				e.dragObject.domNode = newNode;
				
				new DragPanel(newNode, ["coverpage"]);
			}
			if ((e.dragObject.domNode.ajaxtype == 'register') && (e.dragObject.domNode.moduleCategory == 'coverpage_element')) 
			{
				//decorateDragPanel(e.dragObject.domNode);
			}
			this.parentMethod = DropPanel.superclass.onDrop;
			var retVal = this.parentMethod(e);
			delete this.parentMethod;
			
			if ((retVal == true) && (globalChker == true)) {
				var targetCoverpage = this.domNode.coverpage;
				var targetPosition = 0;
				
				var prevNode = e.dragObject.domNode.previousSibling;
				while (prevNode != null) {
					if ((prevNode.nodeType != 3/* TEXT_NODE */) && (prevNode.className.indexOf("coverpage-module") != -1)) break;
					prevNode = prevNode.previousSibling;
				}
				if (prevNode != null) {
					targetPosition = prevNode.modulePos + 1;
				}
				
				if (e.dragObject.domNode.ajaxtype == 'reorder') {
					var sourceCoverpage = e.dragObject.domNode.coverpageNumber;
					var sourcePostion = e.dragObject.domNode.modulePos;
					e.dragObject.domNode.coverpageNumber = targetCoverpage;
				
					var requestURL = blogURL + "/owner/skin/coverpage/order?coverpageNumber=" + sourceCoverpage + "&targetCoverpageNumber=" + targetCoverpage + "&modulePos=" + sourcePostion + "&targetPos=" + targetPosition + viewMode;
					
					var request = new HTTPRequest("POST", requestURL);
					request.onSuccess = function () {
					    clearWaitServerResponse();
					}
					request.onError = function () {
						globalChker = false;
					    errorWaitServerResponse();
					}
					request.onVerify = function () {
						return true;
					}
					request.send();
					waitServerResponse();
				} else if (e.dragObject.domNode.ajaxtype == 'register') {
					e.dragObject.domNode.coverpageNumber = targetCoverpage;
					e.dragObject.domNode.ajaxtype = 'reorder';
					
					var requestURL = blogURL + "/owner/skin/coverpage/register?coverpageNumber=" + targetCoverpage + "&modulePos=" + targetPosition + "&moduleId=" + e.dragObject.domNode.identifier + viewMode;

					var request = new HTTPRequest("POST", requestURL);
					request.coverpage = targetCoverpage;
					request.modulepos = targetPosition;
					request.moduleCategory = e.dragObject.domNode.moduleCategory;
					request.onSuccess = function () {
					    clearWaitServerResponse();
						if (this.moduleCategory == 'plugin') previewPlugin(this.coverpage, this.modulepos);
						decorateDragPanel(e.dragObject.domNode);
					}
					request.onError = function () {
						globalChker = false;
					    errorWaitServerResponse();
					}
					request.onVerify = function () {
						return true;
					}
					request.send();
					waitServerResponse();
				} else {
					alert(e.dragObject.domNode.ajaxtype);
				}
				reordering();
			}
			return retVal;
		},
		
		createDropIndicator: function() {
			this.parentMethod = DropPanel.superclass.createDropIndicator;
			var retVal = this.parentMethod();
			delete this.parentMethod;
			
			with (this.dropIndicator.style) {
				borderTopWidth = "5px";
				borderTopColor = "silver";
				borderTopStyle = "solid";
			};

			return retVal;		
		}
	});

	dojo.lang.extend(DropDeletePanel, {
		onDrop: function(e) {
			if (e.dragObject.domNode.ajaxtype == 'register')
			{
		        if(this.dropIndicator) {
			        dojo.html.removeNode(this.dropIndicator);
			        delete this.dropIndicator;
		        }
		        return false;
			}
			
			var sourceCoverpage = e.dragObject.domNode.coverpageNumber;
			var sourcePostion = e.dragObject.domNode.modulePos;

			this.parentMethod = DropPanel.superclass.onDrop;
			var retVal = this.parentMethod(e);
			delete this.parentMethod;
			
			window.location.href = blogURL + "/owner/skin/coverpage/delete?coverpageNumber=" + sourceCoverpage + "&modulePos=" + sourcePostion + viewMode;
			
			return retVal;
		},
		createDropIndicator: function() {
			this.parentMethod = DropPanel.superclass.createDropIndicator;
			var retVal = this.parentMethod();
			delete this.parentMethod;
			
			with (this.dropIndicator.style) {
				borderTopWidth = "5px";
				borderTopColor = "silver";
				borderTopStyle = "solid";
			};

			return retVal;		
		}
	});

	var dlg;
	
    dojo.widget.defineWidget( "dojo.widget.popupWindow", dojo.widget.Dialog,
	    {
		    templatePath: "",
		    loadContents: function() {
		        this.containerNode = this.domNode;
			    return;
		    },
		    setContent: function(/*String*/ data){
			    this.domNode.innerHTML = data;
		    },
		    placeModalDialog: function() {
			    var scroll_offset = dojo.html.getScroll().offset;
			    var viewport_size = dojo.html.getViewport();
    			
			    // find the size of the dialog
			    var mb = dojo.html.getMarginBox(this.containerNode);
			    if (mb.width<200) mb.width = 200;
			    if (mb.height<200) mb.height = 200;
    			
			    var x = scroll_offset.x + (viewport_size.width - mb.width)/2;
			    var y = scroll_offset.y + (viewport_size.height - mb.height)/2;

			    with(this.domNode.style){
				    left = x + "px";
				    top = y + "px";
			    }
		    }
    	}
    );

	function submitCoverpagePlugin(coverpage, modulepos) {
		var pNode = dlg.domNode.firstChild;
		while (pNode != null) {
			if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'form')) {
				break;
			}
			pNode = pNode.nextSibling;
		}
		if (pNode != null) {
			var requestURL = blogURL + "/owner/skin/coverpage/setPlugin?coverpageNumber=" + coverpage + "&modulePos=" + modulepos + "&ajaxcall=true" + viewMode;
            var postData = "";
			pNode = pNode.firstChild;
			while (pNode != null) {
			    if ((pNode.className != null) && (pNode.className.toLowerCase() == 'field-box')) {
			        pNode = pNode.firstChild;
			        break;
			    }
			    pNode = pNode.nextSibling;
			}
			while (pNode != null) {
				if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'div')) {
					var p2Node = pNode.firstChild;
					while (p2Node != null) {
						if ((p2Node.tagName != null) && (p2Node.tagName.toLowerCase() == 'input') && p2Node.type.toLowerCase() == 'text') {
							requestURL += '&' + encodeURIComponent(p2Node.name) + '=' + encodeURIComponent(p2Node.value);
						} else if ((p2Node.tagName != null) && (p2Node.tagName.toLowerCase() == 'textarea')) {
                            if (postData.length > 0) postData += '&';
                            postData += p2Node.name + '=' + encodeURIComponent(p2Node.value);
                        }
						p2Node = p2Node.nextSibling;
					}
				}
				pNode = pNode.nextSibling;
			}
			var request = new HTTPRequest("POST", requestURL);
			request.coverpage = coverpage;
			request.modulepos = modulepos;
			request.onSuccess = function () {
				previewPlugin(this.coverpage, this.modulepos);
				return true;
			}
			request.onError = function () {
			    errorWaitServerResponse();
				globalChker = false;
			}
			request.onVerify = function () {
				return true;
			}
			request.send(postData);
		}

		dlg.hide();
	}

	function previewPlugin(coverpage, modulepos) {
		var requestURL = blogURL + "/owner/skin/coverpage/preview?coverpageNumber=" + coverpage + "&modulePos=" + modulepos + previewMode;
		
		var request = new HTTPRequest("GET", requestURL);
		request.coverpage = coverpage;
		request.modulepos = modulepos
		request.onSuccess = function () {
			var pNode = document.getElementById('coverpage-ul-' + this.coverpage);
			if (pNode != null) pNode = pNode.firstChild;
			
			while (pNode != null) {
				if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'li')) {
					if (this.modulepos <= 0) break;
					this.modulepos--;
				}
				pNode = pNode.nextSibling;
			}
			
			if (pNode != null) pNode = pNode.lastChild;
			while (pNode != null) {
				if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'div')) {
					break;
				}
				pNode = pNode.previousSibling;
			}
			if (pNode != null) pNode.innerHTML = this._request.responseText;
		}
		request.onError = function () {
			globalChker = false;
		}
		request.onVerify = function () {
			return true;
		}
		request.send();
	}

	function decorateDragPanel(node) {
		var sourceCoverpage = node.coverpageNumber;
		var sourcePostion = node.modulePos;
		var pNode = node.firstChild;
		while (pNode != null) {
			if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'h4')) break;
			pNode = pNode.nextSibling;
		}
		if (pNode != null) {
			var newNode = document.createElement('a');
			newNode.className = "module-close";
			newNode.href = blogURL + "/owner/skin/coverpage/delete/?coverpageNumber=" + sourceCoverpage + "&modulePos=" + sourcePostion + viewMode;
			newNode.title = decorateDragPanelString_deleteTitle;
			newNode.innerHTML = '<img src="' + servicePath + adminSkin + '/image/img_delete_module.gif" border="0" alt="'+ commonString_delete +'" />';
			if (pNode.nextSibling != null) {		
				node.insertBefore(newNode,pNode.nextSibling);
			} else {
				node.appendChild(newNode);
			}
		}
		var pNode = node.firstChild;
		while (pNode != null) {
			if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'div')) {
				pNode.style.clear = 'both';
			}
			pNode = pNode.nextSibling;
		}
	}

	function editCoverpagePlugin(coverpage, modulepos) {
		var requestURL = blogURL + "/owner/skin/coverpage/edit?coverpageNumber=" + coverpage + "&modulePos=" + modulepos + "&ajaxcall=submitCoverpagePlugin(" + coverpage + "," + modulepos + ")" + viewMode;

		var request = new HTTPRequest("GET", requestURL);
		request.onSuccess = function () {
			if (dlg != null) {
				dlg.setContent(this._request.responseText);
				var btn = document.createElement('input');
				btn.type = 'button';
				btn.value = commonString_cancel;
				btn.className = 'input-button';
				
				var pNode = dlg.domNode.firstChild;
				while (pNode != null) {
					if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'form')) {
					    pNode = pNode.firstChild;
						break;
					}
					pNode = pNode.nextSibling;
				}
				while (pNode != null) {
					if ((pNode.className != null) && (pNode.className.toLowerCase() == 'button-box')) {
        				pNode.appendChild(btn);
						break;
					}
					pNode = pNode.nextSibling;
				}
				
				dlg.setCloseControl(btn);
				dlg.show();
			}
		}
		request.onError = function () {
			globalChker = false;
		}
		request.onVerify = function () {
			return true;
		}
		request.send();
	}
	
	function waitServerResponse()
	{
		if (dlg != null) {
			dlg.setContent('<p class="waiting-string">' + commonString_saving + '</p>');
			dlg.show();
		}
	}
	
	function clearWaitServerResponse()
	{
	    dlg.hide();
	}
	
	function errorWaitServerResponse()
	{
	    dlg.setContent('<p class="error-string">' + commonString_error + '</p>');
		var btn = document.createElement('input');
		btn.type = 'button';
		btn.value = commonString_close;
		btn.className = 'input-button';
		btn.onclick = function () { window.location.reload(); return false; };
		
		var oDiv = document.createElement('div');
		oDiv.className = 'button-box';
		
		oDiv.appendChild(btn);
		var pNode = dlg.domNode;
		pNode.appendChild(oDiv);
			
		dlg.setCloseControl(btn);
		dlg.show();
	}
