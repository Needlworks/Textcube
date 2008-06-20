
	dojo.require("dojo.dnd.HtmlDragAndDrop");
	
	DragPanel = function(node, type) {
		dojo.dnd.HtmlDragSource.call(this, node, type);
		this.dragClass = "ajax-floating-panel";
		this.opacity = 0.9;
	}
	dojo.inherits(DragPanel, dojo.dnd.HtmlDragSource);
	
	DropPanel = function(node, type) {
		dojo.dnd.HtmlDropTarget.call(this, node, type);
	}
	dojo.inherits(DropPanel, dojo.dnd.HtmlDropTarget);
	var globalChker = true;
	
	function reordering() {
		var pos = 0;
		var pNode = document.getElementById('dojo_boardbar0').firstChild;
		while (pNode != null) {
			if (pNode.className == "section") pNode.pos = pos++;
			pNode = pNode.nextSibling;
		}
		document.getElementById('dojo_boardbar1').plusposition = pos++;
		pNode = document.getElementById('dojo_boardbar1').firstChild;
		while (pNode != null) {
			if (pNode.className == "section") pNode.pos = pos++;
			pNode = pNode.nextSibling;
		}
		document.getElementById('dojo_boardbar2').plusposition = pos++;
		pNode = document.getElementById('dojo_boardbar2').firstChild;
		while (pNode != null) {
			if (pNode.className == "section") pNode.pos = pos++;
			pNode = pNode.nextSibling;
		}
	}
	
	dojo.lang.extend(DropPanel, {
		onDrop: function(e) {
			this.parentMethod = DropPanel.superclass.onDrop;
			var retVal = this.parentMethod(e);
			delete this.parentMethod;
			
			if ((retVal == true) && (globalChker == true)) {
				var node = e.dragObject.domNode;
				var prevNode = node.previousSibling;
				var insertposition = 0;
				while (prevNode != null) {
					if (prevNode.className == "section") break;
					prevNode = prevNode.previousSibling;
				}
				if (prevNode != null) {
					insertposition = prevNode.pos + 1;
				} else {
					insertposition = this.domNode.plusposition + 1;
				}
				var rel = insertposition - node.pos;
				if (insertposition > node.pos) rel--;
				if (rel == 0) return retVal;
				var requestURL = "dashboard?ajaxcall=true&edit=true&pos=" + node.pos.toString() + "&rel=" + rel.toString();
				
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
				reordering();
			}
			return retVal;
		}
	});
