<?php
function FM_Modern_handleconfig($configVal) {
	$config = Setting::fetchConfigVal($configVal);
	if (isset($config['defaultmode']) && $config['defaultmode'] != 'WYSIWYG' && $config['defaultmode'] != 'TEXTAREA') return false;
	if (isset($config['paragraphdelim']) && $config['paragraphdelim'] != 'P' && $config['paragraphdelim'] != 'BR') return false;
	return true;
}

function FM_Modern_editorinit($editor) {
	global $service, $configVal, $entry;
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
			if (typeof(document.execCommand) == "undefined" || !(STD.isIE || STD.isFirefox || (STD.isWebkit && STD.engineVersion >= 419.3))) return null;
			var editor = new TTModernEditor();
			editor.fixPosition = <?php echo Setting::getBlogSettingGlobal('editorPropertyPositionFix', 0);?>;
			editor.hasGD = <?php echo extension_loaded('gd') ? 'true' : 'false';?>;
			editor.propertyFilePath = "<?php echo $service['path'];?>/attach/<?php echo $blogid;?>/";
			editor.editMode = "<?php echo $config['defaultmode'];?>";
			editor.newLineToParagraph = <?php echo (isset($config['paragraphdelim']) && $config['paragraphdelim'] == 'P' ? 'true' : 'false');?>;
			return editor;
<?php
	$result = ob_get_contents();
	ob_end_clean();
	return $result;
}

function FM_Modern_adminheader($target, $mother) {
	global $suri, $pluginURL;
    $context = Model_Context::getInstance();
	if ($context->getProperty('editor.key') == 'modern') {
		if ($suri['directive'] == '/owner/entry/post' || $suri['directive'] == '/owner/entry/edit') {
			$target .= "\t<link rel=\"stylesheet\" media=\"screen\" type=\"text/css\" href=\"$pluginURL/editor.css\" />\n";
			$target .= "\t<script type=\"text/javascript\" src=\"$pluginURL/editor.js\"></script>\n";
		}
	}
	return $target;
}

