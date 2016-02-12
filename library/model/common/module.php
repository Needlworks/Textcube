<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/***** Modules *****/

/* Editor */
function getDefaultEditor() {
    $context = Model_Context::getInstance();
    $editorMappings = $context->getProperty('plugin.editorMappings');
    reset($editorMappings);
    return Setting::getBlogSettingGlobal('defaultEditor', key($editorMappings));
}

function& getAllEditors() {
    $context = Model_Context::getInstance();
	$editorMappings = $context->getProperty('plugin.editorMappings');
	return $editorMappings;
}

function getEditorInfo($editor) {
    global $pluginURL, $pluginName, $configVal, $pluginPath;
    $context = Model_Context::getInstance();
    $configMappings = $context->getProperty('plugin.configMappings');
    $editorMappings = $context->getProperty('plugin.editorMappings');
    if (!isset($editorMappings[$editor])) {
        reset($editorMappings);
        $editor = key($editorMappings); // gives first declared (thought to be default) editor
    }
    if (isset($editorMappings[$editor]['plugin'])) {
        $pluginURL = $context->getProperty('service.path') . '/plugins/' . $editorMappings[$editor]['plugin'];
        $pluginName = $editorMappings[$editor]['plugin'];
        $pluginPath = ROOT . "/plugins/{$pluginName}";
        $context->setProperty('plugin.url',$context->getProperty('service.path') . '/plugins/' . $editorMappings[$editor]['plugin']);
        $context->setProperty('plugin.name', $editorMappings[$editor]['plugin']);
        $context->setProperty('plugin.path',ROOT . "/plugins/{$pluginName}");
        if (!empty($configMappings[$pluginName]['config'])) {
			$configVal = getCurrentSetting($pluginName);
			$context->setProperty('plugin.config',Setting::fetchConfigVal($configVal));
        } else {
			$configVal = null;
			$context->setProperty('plugin.config',array());
        }
        include_once ROOT . "/plugins/{$editorMappings[$editor]['plugin']}/index.php";
    }
    return $editorMappings[$editor];
}

/* Formatter */
// default formatter functions.
function getDefaultFormatter() {
    $context = Model_Context::getInstance();
    $formatterMappings = $context->getProperty('plugin.formatterMappings');
    reset($formatterMappings);
    return Setting::getBlogSettingGlobal('defaultFormatter', key($formatterMappings));
}

function& getAllFormatters() {
    $context = Model_Context::getInstance();
	$formatters = $context->getProperty('plugin.formatterMappings');
	return $formatters;
}

function getFormatterInfo($formatter) {
    $context = Model_Context::getInstance();
    $formatterMappings = $context->getProperty('plugin.formatterMappings');
    if (!isset($formatterMappings[$formatter])) {
        reset($formatterMappings);
        $formatter = key($formatterMappings); // gives first declared (thought to be default) formatter
    }
    if (isset($formatterMappings[$formatter]['plugin'])) {
        include_once ROOT . "/plugins/{$formatterMappings[$formatter]['plugin']}/index.php";
    }
    return $formatterMappings[$formatter];
}

function getEntryFormatterInfo($id) {
    static $info;
    $context = Model_Context::getInstance();
    $blogid = intval($context->getProperty('blog.id'));

    if (!Validator::id($id)) {
        return NULL;
    } else {
        if (!isset($info[$blogid][$id])) {
            $context = Model_Context::getInstance();
            $pool = DBModel::getInstance();
            $pool->reset('Entries');
            $pool->setQualifier('blogid', 'equals', $blogid);
            $pool->setQualifier('id', 'equals', $id);
            $info[$blogid][$id] = $pool->getCell('contentformatter');
        }
    }

    return $info[$blogid][$id];
}

function formatContent($blogid, $id, $content, $formatter, $keywords = array(), $useAbsolutePath = false) {
    $info = getFormatterInfo($formatter);
    $func = (isset($info['formatfunc']) ? $info['formatfunc'] : 'FM_default_format');
    return $func($blogid, $id, $content, $keywords, $useAbsolutePath);
}

function summarizeContent($blogid, $id, $content, $formatter, $keywords = array(), $useAbsolutePath = false) {
    $info = getFormatterInfo($formatter);
    $func = (isset($info['summaryfunc']) ? $info['summaryfunc'] : 'FM_default_summary');
    // summary function is responsible for shortening the content if needed
    return $func($blogid, $id, $content, $keywords, $useAbsolutePath);
}

function FM_default_format($blogid, $id, $content, $keywords = array(), $useAbsolutePath = false) {
    $context = Model_Context::getInstance();
    $basepath = ($useAbsolutePath ? $context->getProperty('uri.host') : '');
    return str_replace('[##_ATTACH_PATH_##]', "$basepath" . $context->getProperty('service.path') . "/attach/$blogid", $content);
}

function FM_default_summary($blogid, $id, $content, $keywords = array(), $useAbsolutePath = false) {
    $context = Model_Context::getInstance();

    if (!$context->getProperty('blog.publishWholeOnRSS')) {
        $content = Utils_Unicode::lessen(removeAllTags(stripHTML($content)), 255);
    }
    return $content;
}

?>
