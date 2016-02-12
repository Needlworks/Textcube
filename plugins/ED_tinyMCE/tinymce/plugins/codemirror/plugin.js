/**
 * CodeMirror plugin for Textcube editor
 * Needlworks / TNF (http://www.needlworks.org)
 *
 * Based on tinyMCE codemirror plugin.js, created by Arjan Havorkamp (www.webpower.nl)
 *
 * History
 * -------
 * 3.0.0 (06/07/2015) : add - file / object addition via toolbar button
 * 2.1.0 (05/10/2015) : add - complete markdown editing support
 * 2.0.0 (05/09/2015) : add - codemirror editor overlay mode
 */

/*jshint unused:false */
/*global tinymce:true */

tinymce.PluginManager.requireLangPack('codemirror');

tinymce.PluginManager.add('codemirror', function(editor, url) {
	var t = this;
	t.name="CodeMirrorPlugin";
	editor.doesCodeMirrorEditorEnabled = false;
	t.showSourceEditorFrame = function () {
		if (editor.doesCodeMirrorEditorEnabled == false) {
			// Insert caret marker
			editor.focus();
			editor.selection.collapse(true);
			editor.selection.setContent('<span class="CmCaReT" style="display:none">&#0;</span>');
			jQuery(".mce-edit-area").hide();
			jQuery(".mce-statusbar").hide();
			jQuery('<iframe />', {
				id: 'codeMirrorEditor',
				name:'codeMirrorEditor',
				src:url + '/source_frame.html',
				width:'100%',
				height:'450px',
				style:'width:100%; height:450px; border: 1px solid #eee; box-shadow:0 0 5px rgba(0,0,0,0.3);'
			}).appendTo('.editorbox-container');
			editor.doesCodeMirrorEditorEnabled = true;
			editor.editorMode = 'codemirror';
		} else {
			var node = document.getElementById('codeMirrorEditor');
			node.contentWindow.submit();
			node.parentNode.removeChild(node);
			var cmInst = document.getElementById('CodeMirrorInstruction');
			cmInst.parentNode.removeChild(cmInst);
			jQuery(".mce-edit-area").show();
			jQuery(".mce-statusbar").show();
			editor.doesCodeMirrorEditorEnabled = false;
			editor.editorMode = 'tinymce';
		}
	};
	t.syncToTinyMCE = function () {
		var node = document.getElementById('codeMirrorEditor');
		node.contentWindow.syncEditorContent();
	};
	// Add a button to the button bar
	function toggleEditor() {
		t.showSourceEditorFrame();
	};
	editor.addButton('code', {
		title: 'Source code',
		icon: 'code',
		onclick: toggleEditor
	});

	// Add a menu item to the tools menu
	editor.addMenuItem('code', {
		icon: 'code',
		text: 'Source code',
		context: 'tools',
		onclick: toggleEditor
	});
});
