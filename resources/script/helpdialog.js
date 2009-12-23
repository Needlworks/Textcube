/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

	djConfig.parseWidgets = false;
	dojo.require("dojo.widget.Dialog");	

	var helperDlg;
	
    dojo.widget.defineWidget( "dojo.widget.helperWindow", dojo.widget.Dialog,
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

	function initHelperPage() {
		alert('sss');
		helperDlg = dojo.widget.createWidget("helperWindow", {}, document.getElementById('temp-wrap').firstChild, 'after');
		helperDlg.domNode.className = 'ajax-popup-window';
		alert('xxx');
	}
