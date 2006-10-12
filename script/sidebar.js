	djConfig.parseWidgets = false;
	
	dojo.require("dojo.dnd.HtmlDragAndDrop");
	dojo.require("dojo.widget.Parse");
	dojo.require("dojo.widget.Dialog");	


	DragPanel = function(node, type) {
		dojo.dnd.HtmlDragSource.call(this, node, type);
		this.dragClass = "ajax-floating-panel";
		this.opacity = 0.9;
		
		decorateDragPanel(this.domNode);
		
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
	
	var globalChker = true;
	var globalNewNodeCounter = 0;

	dojo.lang.extend(DropPanel, {
		onDrop: function(e) {
			if ((e.dragObject.domNode.ajaxtype == 'register') && (e.dragObject.domNode.moduleCategory == 'plugin')) 
			{
				var newNode = document.createElement(e.dragObject.domNode.tagName);
				newNode.id = 'newDragPanel_' + globalNewNodeCounter++;
				newNode.className = 'sidebar-module sidebar-plugin-module';
				newNode.ajaxtype = 'register';
				newNode.moduleCategory = e.dragObject.domNode.moduleCategory;
				newNode.identifier = e.dragObject.domNode.identifier;
				newNode.innerHTML = e.dragObject.domNode.innerHTML;
				newNode.hasPropertyEdit = e.dragObject.domNode.hasPropertyEdit;
				
				e.dragObject.domNode = newNode;
				
				new DragPanel(newNode, ["sidebar"]);
			}
			if ((e.dragObject.domNode.ajaxtype == 'register') && (e.dragObject.domNode.moduleCategory == 'sidebar_element')) 
			{
				decorateDragPanel(e.dragObject.domNode);
			}
			this.parentMethod = DropPanel.superclass.onDrop;
			var retVal = this.parentMethod(e);
			delete this.parentMethod;
			
			if ((retVal == true) && (globalChker == true)) {
				var targetSidebar = this.domNode.sidebar;
				var targetPosition = 0;
				
				var prevNode = e.dragObject.domNode.previousSibling;
				while (prevNode != null) {
					if ((prevNode.nodeType != 3/* TEXT_NODE */) && (prevNode.className.indexOf("sidebar-module") != -1)) break;
					prevNode = prevNode.previousSibling;
				}
				if (prevNode != null) {
					targetPosition = prevNode.modulePos + 1;
				}
				
				if (e.dragObject.domNode.ajaxtype == 'reorder') {
					var sourceSidebar = e.dragObject.domNode.sidebarNumber;
					var sourcePostion = e.dragObject.domNode.modulePos;
					e.dragObject.domNode.sidebarNumber = targetSidebar;
				
					var requestURL = "sidebar/order?sidebarNumber=" + sourceSidebar + "&targetSidebarNumber=" + targetSidebar + "&modulePos=" + sourcePostion + "&targetPos=" + targetPosition;
					
					var request = new HTTPRequest("POST", requestURL);
					request.onSuccess = function () {
					}
					request.onError = function () {
						globalChker = false;
					}
					request.onVerify = function () {
						return true;
					}
					request.send();
				} else if (e.dragObject.domNode.ajaxtype == 'register') {
					e.dragObject.domNode.sidebarNumber = targetSidebar;
					e.dragObject.domNode.ajaxtype = 'reorder';
					
					var requestURL = "sidebar/register?sidebarNumber=" + targetSidebar + "&modulePos=" + targetPosition + "&moduleId=" + e.dragObject.domNode.identifier;

					var request = new HTTPRequest("POST", requestURL);
					request.sidebar = targetSidebar;
					request.modulepos = targetPosition;
					request.moduleCategory = e.dragObject.domNode.moduleCategory;
					request.onSuccess = function () {
						if (this.moduleCategory == 'plugin') previewPlugin(this.sidebar, this.modulepos);
					}
					request.onError = function () {
						globalChker = false;
					}
					request.onVerify = function () {
						return true;
					}
					request.send();
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

	var dlg;
	
    dojo.widget.defineWidget( "dojo.widget.popupWindow", dojo.widget.html.Dialog,
	    {
		    isContainer: false, // can we contain other widgets?
		    templatePath: "",
		    loadContents: function() {
			    return;
		    },
		    setContent: function(/*String*/ data){
			    this.domNode.innerHTML = data;
		    },
		    placeDialog: function() {
			    var scroll_offset = dojo.html.getScrollOffset();
			    var viewport_size = dojo.html.getViewportSize();

			    // find the size of the dialog
			    var w = dojo.style.getOuterWidth(this.domNode);
			    var h = dojo.style.getOuterHeight(this.domNode);
			    if (w<200) w = 200;
			    if (h<200) h = 200;

			    var x = scroll_offset[0] + (viewport_size[0] - w)/2;
			    var y = scroll_offset[1] + (viewport_size[1] - h)/2;

			    with(this.domNode.style) {
				    left = x + "px";
				    top = y + "px";
			    }
		    }
    	}
    );

	function submitSidebarPlugin(sidebar, modulepos) {
		var pNode = dlg.domNode.firstChild;
		while (pNode != null) {
			if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'form')) {
				break;
			}
			pNode = pNode.nextSibling;
		}
		if (pNode != null) {
			var requestURL = "sidebar/setPlugin?sidebarNumber=" + sidebar + "&modulePos=" + modulepos + "&ajaxcall=true";
			pNode = pNode.firstChild;
			while (pNode != null) {
				if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'div')) {
					var p2Node = pNode.firstChild;
					while (p2Node != null) {
						if ((p2Node.tagName != null) && (p2Node.tagName.toLowerCase() == 'input') && p2Node.type.toLowerCase() == 'text') {
							requestURL += '&' + encodeURIComponent(p2Node.name) + '=' + encodeURIComponent(p2Node.value);
						}
						p2Node = p2Node.nextSibling;
					}
				}
				pNode = pNode.nextSibling;
			}
			var request = new HTTPRequest("POST", requestURL);
			request.sidebar = sidebar;
			request.modulepos = modulepos;
			request.onSuccess = function () {
				previewPlugin(this.sidebar, this.modulepos);
				return true;
			}
			request.onError = function () {
				globalChker = false;
			}
			request.onVerify = function () {
				return true;
			}
			request.send();
		}

		dlg.hide();
	}

	function previewPlugin(sidebar, modulepos) {
		var requestURL = "sidebar/preview?sidebarNumber=" + sidebar + "&modulePos=" + modulepos;
		
		var request = new HTTPRequest("GET", requestURL);
		request.sidebar = sidebar;
		request.modulepos = modulepos
		request.onSuccess = function () {
			var pNode = document.getElementById('sidebar-ul-' + this.sidebar);
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
		var sourceSidebar = node.sidebarNumber;
		var sourcePostion = node.modulePos;
		var pNode = node.firstChild;
		while (pNode != null) {
			if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'h5')) break;
			pNode = pNode.nextSibling;
		}
		if (pNode != null) {
			pNode.style.display = 'inline';
			if (dojo.render.html.ie55||dojo.render.html.ie60) {
				pNode.style.styleFloat = 'left';
			} else {
				pNode.style.setProperty('float', 'left', '');
			}
			var newNode = document.createElement('a');
			newNode.href = "sidebar/delete/?sidebarNumber=" + sourceSidebar + "&modulePos=" + sourcePostion;
			newNode.title = decorateDragPanelString_deleteTitle;
			newNode.innerHTML = '<img style="float:right" src="' + servicePath + adminSkin + '/image/img_delete_module.jpg" border="0" alt="'+ commonString_delete +'" />';
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

	function editSidebarPlugin(sidebar, modulepos) {
		var requestURL = "sidebar/edit?sidebarNumber=" + sidebar + "&modulePos=" + modulepos + "&ajaxcall=submitSidebarPlugin(" + sidebar + "," + modulepos + ")";

		var request = new HTTPRequest("GET", requestURL);
		request.onSuccess = function () {
			if (dlg != null) {
				dlg.setContent(this._request.responseText);
				var btn = document.createElement('input');
				btn.type = 'button';
				btn.value = commonString_cancel;
				btn.className = 'button';
				
				var pNode = dlg.domNode.firstChild;
				while (pNode != null) {
					if ((pNode.tagName != null) && (pNode.tagName.toLowerCase() == 'form')) {
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
