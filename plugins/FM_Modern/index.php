<?php
function FM_Modern_handleconfig($config) {
	$context = Model_Context::getInstance();
	$config = $context->getProperty('plugin.config');
	if (isset($config['defaultmode']) && $config['defaultmode'] != 'WYSIWYG' && $config['defaultmode'] != 'TEXTAREA') return false;
	if (isset($config['paragraphdelim']) && $config['paragraphdelim'] != 'P' && $config['paragraphdelim'] != 'BR') return false;
	return true;
}

function FM_Modern_editorinit($editor) {
	global $entry;
	$context = Model_Context::getInstance();

	$blogid = getBlogId();
	if (is_null($context->getProperty('plugin.config',null))) {
		$config = array('paragraphdelim' => 'BR',
			'defaultmode' => 'WYSIWYG');
	} else {
		$config = $context->getProperty('plugin.config');
	}
	if (in_array(Setting::getBlogSettingGlobal('defaultFormatter','html'),array('markdown','textile')) ||
		in_array($entry['contentformatter'],array('markdown','textile'))) {
		$config['defaultmode'] = 'TEXTAREA';
	} else if (!isset($config['defaultmode'])) {
		$config['defaultmode'] = 'WYSIWYG';
	}

	ob_start();
?>
			if (typeof(document.execCommand) == "undefined" || !(STD.isIE || STD.isFirefox || (STD.isWebkit && STD.engineVersion >= 419.3))) return null;
			var editor = new TTModernEditor();
			editor.fixPosition = <?php echo Setting::getBlogSettingGlobal('editorPropertyPositionFix', 0);?>;
			editor.hasGD = <?php echo extension_loaded('gd') ? 'true' : 'false';?>;
			editor.propertyFilePath = "<?php echo $context->getProperty('service.path');?>/attach/<?php echo $blogid;?>/";
			editor.editMode = "<?php echo $config['defaultmode'];?>";
			editor.newLineToParagraph = <?php echo (isset($config['paragraphdelim']) && $config['paragraphdelim'] == 'P' ? 'true' : 'false');?>;
			return editor;
<?php
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}

function FM_Modern_adminheader($target, $mother) {
    $context = Model_Context::getInstance();
	if ($context->getProperty('editor.key') == 'modern') {
		if ($context->getProperty('suri.directive') == '/owner/entry/post' || $context->getProperty('suri.directive') == '/owner/entry/edit') {
			$target .= "\t<link rel=\"stylesheet\" media=\"screen\" type=\"text/css\" href=\"".$context->getProperty('plugin.uri')."/editor.css\" />\n";
			$target .= "\t<script type=\"text/javascript\" src=\"".$context->getProperty('plugin.uri')."/editor.js\"></script>\n";
		}
	}
	return $target;
}

