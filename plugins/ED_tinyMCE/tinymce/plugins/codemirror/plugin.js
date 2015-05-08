/**
 * plugin.js
 *
 * Copyright 2013 Web Power, www.webpower.nl
 * @author Arjan Haverkamp
 */

/*jshint unused:false */
/*global tinymce:true */

tinymce.PluginManager.requireLangPack('codemirror');

tinymce.PluginManager.add('codemirror', function(editor, url) {
	var t = this;
	t.show = false;
	function showSourceEditor() {
		// Insert caret marker
		editor.focus();
		editor.selection.collapse(true);
		editor.selection.setContent('<span class="CmCaReT" style="display:none">&#0;</span>');

		// Open editor window
		var win = editor.windowManager.open({
			title: 'HTML source code',
			url: url + '/source.html',
			width: 800,
			height: 550,
			resizable : true,
			maximizable : true,
			buttons: [
				{ text: 'Ok', subtype: 'primary', onclick: function(){
					var doc = document.querySelectorAll('.mce-container-body>iframe')[0];
					doc.contentWindow.submit();
					win.close();
				}},
				{ text: 'Cancel', onclick: 'close' }
			]
		});
	};

	function showSourceEditorFrame() {
		if (t.show == false) {
			// Insert caret marker
			editor.focus();
			editor.selection.collapse(true);
			editor.selection.setContent('<span class="CmCaReT" style="display:none">&#0;</span>');
			jQuery(".mce-edit-area").hide();
			jQuery(".mce-statusbar").hide();
			jQuery('<iframe />', {
				id: 'codeMirror',
				name:'codeMirror',
				src:url + '/source_frame.html',
				width:'100%',
				height:'450px',
				style:'width:100%; height:450px; border: 1px solid #ccc;'
			}).appendTo('.editorbox-container');
			alert(t.codeMirrorFrame);
			t.show = true;
		} else {
			node = document.getElementById('codeMirror');
			node.contentWindow.submit();
			node.parentNode.removeChild(node);
			jQuery(".mce-edit-area").show();
			jQuery(".mce-statusbar").show();
			t.show = false;
		}
	};

	// Add a button to the button bar
	editor.addButton('code', {
		title: 'Source code',
		icon: 'code',
		onclick: showSourceEditorFrame
	});

	// Add a menu item to the tools menu
	editor.addMenuItem('code', {
		icon: 'code',
		text: 'Source code',
		context: 'tools',
		onclick: showSourceEditorFrame
	});
});
