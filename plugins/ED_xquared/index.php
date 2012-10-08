<?php
function Xquared_handleconfig($configVal) {
	$config = Setting::fetchConfigVal($configVal);
	if (isset($config['defaultmode']) && $config['defaultmode'] != 'WYSIWYG' && $config['defaultmode'] != 'TEXTAREA') return false;
	if (isset($config['paragraphdelim']) && $config['paragraphdelim'] != 'P' && $config['paragraphdelim'] != 'BR') return false;
	return true;
}

function Xquared_editorinit($editor) {
	global $configVal, $entry, $pluginURL;
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
			var editor = new xq.Editor("editWindow");
			editor.config.contentCssList = ["<?php echo $pluginURL;?>/stylesheets/xq_contents.css"];
			editor.config.imagePathForDefaultToolbar = '<?php echo $pluginURL;?>/images/toolbar/';
			editor.setEditMode('wysiwyg');
			editor.origInitialize = editor.initialize;
			editor.origFinalize = editor.finalize;
			editor.initialize = function() {
				this.origInitialize();
			}
			editor.finalize = function() {
				this.origFinalize();
			}
			editor.syncTextarea = function(){
				var oForm = document.getElementById('editor-form');
				oForm.content.value = this.getCurrentContent();
			}

			return editor;
<?php
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}

function Xquared_adminheader($target, $mother) {
	global $suri, $pluginURL;

	if ($suri['directive'] == '/owner/entry/post' || $suri['directive'] == '/owner/entry/edit') {
		$target .= "\t<link rel=\"stylesheet\" media=\"screen\" type=\"text/css\" href=\"$pluginURL/stylesheets/xq_ui.css\" />\n";
		$target .= "\t<script type=\"text/javascript\" src=\"$pluginURL/javascripts/module/Full_merged_min.js\"></script>\n";
	}
	return $target;
}
