<?php
function tinyMCE_handleconfig($configVal) {
	$config = Setting::fetchConfigVal($configVal);
	if (isset($config['defaultmode']) && $config['defaultmode'] != 'WYSIWYG' && $config['defaultmode'] != 'TEXTAREA') return false;
	if (isset($config['paragraphdelim']) && $config['paragraphdelim'] != 'P' && $config['paragraphdelim'] != 'BR') return false;
	return true;
}

function tinyMCE_editorinit(&$editor) {
	global $configVal, $entry, $pluginURL;
	$context = Model_Context::getInstance();
	$blogid = getBlogId();
	if (is_null($configVal) || empty($configVal)) {
		$config = array('paragraphdelim' => 'BR',
			'defaultmode' => 'WYSIWYG');
	} else {
		$config = Setting::fetchConfigVal($configVal);
	}
	if (in_array(Setting::getBlogSettingGlobal('defaultFormatter','html'),array('markdown','textile')) ||
		in_array($entry['contentformatter'],array('markdown','textile'))) {
		$config['defaultmode'] = 'TEXTAREA';
	} else if (!isset($config['defaultmode'])) {
		$config['defaultmode'] = 'WYSIWYG';
	}

	ob_start();
?>
			var editor = new tinymce.Editor('editWindow', {
				// General options
				mode : 'exact',
				theme : "advanced",
				plugins : "autolink,lists,pagebreak,style,layer,table,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,TTMLsupport",

				// Theme options
				theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",								theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
				theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
				theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,

				// Example content CSS (should be your site CSS)
				content_css : "<?php echo (file_exists(ROOT.'/skin/blog/'.$context->getProperty('skin.skin').'/wysiwyg.css') ? $context->getProperty('uri.default').'/skin/blog/'.$context->getProperty('skin.skin').'/wysiwyg.css' : '/css/content.css');?>",

				// Drop lists for link/image/media/template dialogs
				template_external_list_url : "lists/template_list.js",
				external_link_list_url : "lists/link_list.js",
				external_image_list_url : "lists/image_list.js",
				media_external_list_url : "lists/media_list.js",

				// Style formats
				style_formats : [
					{title : 'Bold text', inline : 'b'},
					{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
					{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
					{title : 'Example 1', inline : 'span', classes : 'example1'},
					{title : 'Example 2', inline : 'span', classes : 'example2'},
					{title : 'Table styles'},
					{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
				],
				forced_root_block : false, 
			});
			editor.initialize = function() {
				this.render();
			}
			editor.finalize = function() {
				this.syncTextarea();
				this.destroy();
			}
			editor.syncTextarea = function(){
				this.save();
			}
			editor.propertyFilePath = "<?php echo $context->getProperty('service.path');?>/attach/<?php echo $context->getProperty('blog.id');?>/";
			return editor;
<?php
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}

function tinyMCE_adminheader($target, $mother) {
	global $suri, $pluginURL;

	if ($suri['directive'] == '/owner/entry/post' || $suri['directive'] == '/owner/entry/edit') {
		$target .= "\t<script type=\"text/javascript\" src=\"$pluginURL/tiny_mce/tiny_mce.js\"></script>\n";
	}
	return $target;
}
?>
