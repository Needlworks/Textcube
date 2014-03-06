<?php
function tinyMCE_handleconfig($configVal) {
	$config = Setting::fetchConfigVal($configVal);
	if (isset($config['editormode']) && $config['editormode'] != 'simple' && $config['editormode'] != 'advanced') return false;
	return true;
}

function tinyMCE_editorinit($editor) {
	global $configVal, $entry, $pluginURL;
	$context = Model_Context::getInstance();
	$blogid = getBlogId();
	$config = Setting::fetchConfigVal($configVal);
	if(empty($config['editormode'])) $config['editormode'] = 'simple';
	if(empty($config['width'])) $config['width'] = 'full';
	ob_start();
?>
			var editor = new tinymce.Editor('editWindow', {
				// General options
				selector : "textarea#editWindow",
				mode : 'exact',
				theme : 'modern',
				//language : '<?php echo strtolower($context->getProperty('blog.language'));?>',
				popup_css_add: "<?php echo $pluginURL;?>/popup.css",
				menubar: false,
				fixed_toolbar_container: "#formatbox-container",
				toolbar_location : "external",
				toolbar_items_size: 'small',
				//schema: "html5",
        extended_valid_elements : "div[class|style|align|width|height|id|more|less],img[class|src|border|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|longdesc|style],object",

<?php
	if($config['editormode'] == 'simple') {
?>
				plugins: [
					"TTMLsupport advlist autolink link image lists print preview hr anchor autoresize",
					"code fullscreen media visualblocks",
					"table contextmenu directionality charmap textcolor textcolor"
				],
				toolbar1: "tcsave print | bold italic underline strikethrough | styleselect formatselect fontselect fontsizeselect forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote hr tcmoreless",
				toolbar2: "undo redo | tcattach image media charmap | hr link unlink anchor | table | removeformat | code fullscreen  visualblocks",
<?php
	} else {
?>
				plugins: [
					"TTMLsupport advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker table",
					"searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
					"table contextmenu directionality emoticons textcolor paste textcolor"
				],

				toolbar1: "tcsave print | bold italic underline strikethrough | styleselect formatselect fontselect fontsizeselect forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote hr tcmoreless",
				toolbar2: "undo redo | searchreplace | tcattach image media charmap insertdatetime | subscript superscript ltr rtl cite abbr acronym del ins | hr link unlink anchor | table | cut copy paste pastetext| removeformat code fullscreen visualblocks",

<?php
	}
?>
				// content CSS
				content_css : "<?php echo (file_exists(__TEXTCUBE_SKIN_DIR__.'/'.$context->getProperty('skin.skin').'/wysiwyg.css') ? $context->getProperty('uri.service').'/skin/blog/'.$context->getProperty('skin.skin').'/wysiwyg.css' : $context->getProperty('uri.service').'/resources/style/default-wysiwyg.css');?>",

				// Style formats
				style_formats: [
				    {title: 'Headers', items: [
				        {title: 'h1', block: 'h1'},
				        {title: 'h2', block: 'h2'},
				        {title: 'h3', block: 'h3'},
				        {title: 'h4', block: 'h4'},
				        {title: 'h5', block: 'h5'},
				        {title: 'h6', block: 'h6'}
				    ]},

				    {title: 'Blocks', items: [
				        {title: 'p', block: 'p'},
				        {title: 'div', block: 'div'},
				        {title: 'pre', block: 'pre'}
				    ]},

				    {title: 'Containers', items: [
				        {title: 'section', block: 'section', wrapper: true, merge_siblings: false},
				        {title: 'article', block: 'article', wrapper: true, merge_siblings: false},
				        {title: 'blockquote', block: 'blockquote', wrapper: true},
				        {title: 'hgroup', block: 'hgroup', wrapper: true},
				        {title: 'aside', block: 'aside', wrapper: true},
				        {title: 'figure', block: 'figure', wrapper: true}
				    ]}
				],
				forced_root_block : false,
				width : "<?php echo ($config['width'] == 'full' ? '100%' : $context->getProperty('skin.contentWidth'));?>"
			}, tinymce.EditorManager);
			editor.initialize = function() {
				this.render();
			};
			editor.addObject = function(data) {
				this.plugins.TTMLsupport.addObject(data);
			};
			editor.finalize = function() {
				this.syncTextarea();
				this.destroy();
			};
			editor.syncTextarea = function(){
				this.save();
			};
			editor.syncEditorWindow = function() {
				this.load();
			};
			editor.on('keyup',editorChanged);
			editor.on('mousedown',editorChanged);
			editor.propertyFilePath = "<?php echo $context->getProperty('uri.service');?>/attach/<?php echo $context->getProperty('blog.id');?>/";
			editor.fixPosition = <?php echo Setting::getBlogSettingGlobal('editorPropertyPositionFix', 0);?>;
			return editor;
<?php
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}

function tinyMCE_adminheader($target, $mother) {
	global $suri, $pluginURL;
    $context = Model_Context::getInstance();
	if ($context->getProperty('editor.key') == 'tinyMCE') {
		if ($suri['directive'] == '/owner/entry/post' || $suri['directive'] == '/owner/entry/edit') {
			$target .= "\t<script type=\"text/javascript\" src=\"$pluginURL/tinymce/tinymce.min.js\"></script>\n";
			$target .= "\t<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"$pluginURL/override.css\" />\n";
		}
	}
	return $target;
}
?>
