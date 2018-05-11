<?php
/***
	TinyMCE Editor for Textcube

	Needlworks / Jeongkyu Shin (https://github.com/inureyes)
	CodeMirror plugin by zvuc (https://github.com/zvuc)
*/
function tinyMCE_handleconfig($configVal) {
	$config = Setting::fetchConfigVal($configVal);
	if (isset($config['editormode']) && $config['editormode'] != 'simple' && $config['editormode'] != 'advanced') return false;
	if (isset($config['paragraphdelim']) && $config['paragraphdelim'] != 'P' && $config['paragraphdelim'] != 'BR') return false;
	return true;
}

function tinyMCE_editorinit($editor) {
	global $configVal, $pluginURL, $pluginPath;
	$context = Model_Context::getInstance();
	$blogid = getBlogId();
	$config = Setting::fetchConfigVal($configVal);
	if(empty($config['editormode'])) $config['editormode'] = 'simple';
	if(empty($config['width'])) $config['width'] = 'skin';
	if(empty($config['srctheme'])) $config['srctheme'] = 'default';
	if($config['srctheme'] == 'default') $config['srctheme'] = 'elegant';
	$config['formatter'] = null;
    if(empty($config['paragraphdelim'])) $config['paragraphdelim'] = 'BR';
	if($context->getProperty('formatter.key') == 'markdown') {
		$config['formatter'] = 'markdown';
        $config['codemirror_jsfiles'] = array('mode/xml/xml.js','mode/markdown/markdown.js');
		$config['width'] = 'full';
	} else {
        $config['formatter'] = 'htmlmixed';
        $config['codemirror_jsfiles'] = array('mode/xml/xml.js','mode/javascript/javascript.js','mode/css/css.js','mode/htmlmixed/htmlmixed.js');
    }
	ob_start();
?>
			var editor = new tinymce.Editor('editWindow', {
				// General options
				selector : "textarea#editWindow",
				mode : 'exact',
				theme : 'modern',
				skin : 'light',
<?php
	if (file_exists($pluginPath.'/tinymce/langs/'.$context->getProperty('blog.language').'.js')) {
?>
				language : '<?php echo strtolower($context->getProperty('blog.language'));?>',
<?php
	}
?>
				popup_css_add: "<?php echo $pluginURL;?>/popup.css",
				menubar: false,
				fixed_toolbar_container: "#formatbox-container",
				toolbar_location : "external",
				toolbar_items_size: 'small',
				relative_urls: false,
				convert_urls: false,
				//schema: "html5",
        		extended_valid_elements : "div[*],img[*],span[*],pre[*],code[*],object,br",

<?php
	if($config['editormode'] == 'simple') {
?>
				plugins: [
					"TTMLsupport advlist link image lists print hr anchor autoresize",
					"media visualblocks",
					"table contextmenu directionality charmap textcolor",
					"codemirror"
				],
				toolbar1: "tcsave print | bold italic underline strikethrough | styleselect formatselect fontselect fontsizeselect forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote hr tcmoreless",
				toolbar2: "undo redo | tcattach image media charmap | hr link unlink anchor | table | removeformat | tcsourcecodeedit code visualblocks",
<?php
	} else {
?>
				plugins: [
					"TTMLsupport advlist link image lists charmap print hr anchor pagebreak table",
					"searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
					"table contextmenu directionality emoticons textcolor paste textcolor autoresize",
					"codemirror",

				],

				toolbar1: "tcsave print | bold italic underline strikethrough | styleselect formatselect fontselect fontsizeselect forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote hr tcmoreless",
				toolbar2: "undo redo | searchreplace | tcattach image media charmap insertdatetime | subscript superscript ltr rtl cite abbr acronym del ins | hr link unlink anchor | table | cut copy paste pastetext| removeformat | tcsourcecodeedit code visualblocks",
<?php
	}
?>
				// codemirror settings
				codemirror: {
					indentOnInit: false, // Whether or not to indent code on init.
					path: 'CodeMirror', // Path to CodeMirror distribution
					config: {           // CodeMirror config object
    					mode: '<?php echo $config['formatter'];?>',
    					lineNumbers: true,
    					tabSize: 4,
    					indentWithTabs: true,
    					theme: '<?php echo $config['srctheme'] ?>'
					},
					jsFiles: [          // Additional JS files to load
					'<?php echo implode('\',\'',$config['codemirror_jsfiles']);?>'
					],
					cssFiles: [
						'theme/<?php echo $config['srctheme'] ?>.css'
					],
                    showMarkdownLineBreaks: true
				},
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
				fontsize_formats: "8pt 9pt 10pt 11pt 12pt 14pt 18pt 24pt 36pt",
				paste_auto_cleanup_on_paste: true,
				paste_convert_headers_to_strong: false,
				paste_remove_spans: true,
<?php if ($config['formatter'] == 'markdown') { ?>
				apply_source_formatting: false,
				forced_root_block: false,
<?php } else if ($config['paragraphdelim'] == 'P') { ?>
				forced_root_block : 'p',
<?php } else { ?>
                forced_root_block : false,
<?php
	}
?>
				width : <?php echo ($config['width'] == 'full' ? '"100%"' : $context->getProperty('skin.contentWidth')+80);?>
			}, tinymce.EditorManager);
			editor.initialize = function() {
<?php if ($config['formatter'] == 'markdown') {
?>
			editor.on('postRender', function (e) { editor.plugins.codemirror.showSourceEditorFrame(); });
			editor.on('BeforeSetContent', function(e) {
				if (e.initial) {
					e.content = e.content.replace(/\r?\n/g, '<br />');
				}
			});
<?php
			}
?>
				this.render();
			};
			editor.addObject = function(data) {
				this.plugins.TTMLsupport.addObject(data);
			};
			editor.command = function(command, value1, value2) {
				this.plugins.TTMLsupport.command(command,value1,value2);
			};
			editor.finalize = function() {
                this.destroy();
            };
            editor.syncTextarea = function(){
                if (this.doesCodeMirrorEditorEnabled == true) {
                    this.plugins.codemirror.syncToTinyMCE();
                }
                this.save();
				if (entryManager.nowsaving == true && editor.tcformatter == 'markdown') {
                    var htmlcontent = document.getElementById('editWindow').value;
					htmlcontent = htmlcontent.replace(new RegExp("<br />", "gi"), "\r\n");
                    document.getElementById('editWindow').value = htmlcontent;
                }
			};
			editor.syncEditorWindow = function() {
				this.load();
			};
			editor.on('keyup',editorChanged);
			editor.on('mousedown',editorChanged);
			editor.propertyFilePath = "<?php echo $context->getProperty('uri.service');?>/attach/<?php echo $context->getProperty('blog.id');?>/";
            editor.tcformatter = '<?php echo $config['formatter'];?>';
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
		if ($context->getProperty('suri.directive') == '/owner/entry/post' || $suri['directive'] == '/owner/entry/edit') {
			$target .= "\t<script type=\"text/javascript\" src=\"$pluginURL/tinymce/tinymce.min.js\"></script>\n";
		}
	}
	return $target;
}
?>
