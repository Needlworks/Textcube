<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$context = Model_Context::getInstance();

$pluginListForCSS = array();
if (isset($eventMappings['AddPostEditorToolbox'])) {
	foreach ($eventMappings['AddPostEditorToolbox'] as $tempPlugin) {
		array_push($pluginListForCSS, $tempPlugin['plugin']);
	}
}
unset($tempPlugin);
?>
<!DOCTYPE html>
<html lang="<?php echo $context->getProperty('blog.language','ko');?>">
<head>
	<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($context->getProperty('blog.title'));?> &gt; <?php echo _t('글관리');?></title>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/basic.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/post.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/popup-editor.css" />
<?php
foreach ($pluginListForCSS as $tempPluginDir) {
	if (isset($tempPluginDir) && file_exists(ROOT . "/plugins/$tempPluginDir/plugin-main.css")) {
?>
	<link rel="stylesheet" type="text/css" href="<?php echo $context->getProperty('service.path');?>/plugins/<?php echo $tempPluginDir;?>/plugin-main.css" />
<?php
	}
}
?>
	<!--[if IE 7]>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/basic.ie7.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/post.ie7.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $context->getProperty('service.path').$context->getProperty('panel.skin');?>/popup-editor.ie7.css" />
<?php
foreach ($pluginListForCSS as $tempPluginDir) {
	if (isset($tempPluginDir) && file_exists(ROOT . "/plugins/$tempPluginDir/plugin-main.ie7.css")) {
?>
	<link rel="stylesheet" type="text/css" href="<?php echo $context->getProperty('service.path');?>/plugins/<?php echo $tempPluginDir;?>/plugin-main.ie7.css" />
<?php
	}
}

unset($pluginListForCSS);
unset($tempPluginDir);
?>
	<![endif]-->
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $context->getProperty('service.path');?>";
			var blogURL = "<?php echo $context->getProperty('uri.blog');?>";
			var adminSkin = "<?php echo $context->getProperty('panel.skin');?>";
<?php
if (file_exists(ROOT.$adminSkinSetting['editorTemplate'])) {
?>
			var editorCSS = "<?php echo $adminSkinSetting['editorTemplate'];?>";
<?php
} else {
?>
			var editorCSS = "/resources/style/default-wysiwyg.css";
<?php
}

include ROOT . '/resources/locale/messages.php';
?>
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.path');?>/resources/locale/messages.php"></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.jqueryURL');?>jquery-<?php echo JQUERY_VERSION;?>.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.path');?>/resources/script/EAF4.js"></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.path');?>/resources/script/common3.min.js"></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.path');?>/resources/script/gallery.min.js"></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.path');?>/resources/script/owner.js"></script>
	<script type="text/javascript" src="<?php echo $context->getProperty('service.path');?>/resources/script/editor3.js"></script>
<?php echo fireEvent('ShowAdminHeader', ''); ?>
</head>
<body id="body-entry" class="popup-editor">
	<div id="temp-wrap">
		<div id="all-wrap">
			<div id="layout-header">
				<h1><?php echo _t('텍스트큐브 관리 페이지');?></h1>
			</div>

			<hr class="hidden" />
