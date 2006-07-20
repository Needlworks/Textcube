<?php
$activePlugins = array();
$eventMappings = array();
$tagMappings = array();
$sidebarMappings = array();
if (!empty($owner)) {
	$activePlugins = fetchQueryColumn("SELECT name FROM {$database['prefix']}Plugins WHERE owner = $owner");
	$xmls = new XMLStruct();
	foreach ($activePlugins as $plugin) {
		$manifest = @file_get_contents(ROOT . "/plugins/$plugin/index.xml");
		if ($manifest && $xmls->open($manifest)) {
			if ($xmls->doesExist('/plugin/binding/listener')) {
				foreach ($xmls->selectNodes('/plugin/binding/listener') as $listener) {
					if (!empty($listener['.attributes']['event']) && !empty($listener['.value'])) {
						if (!isset($eventMappings[$listener['.attributes']['event']]))
							$eventMappings[$listener['.attributes']['event']] = array();
						array_push($eventMappings[$listener['.attributes']['event']], array('plugin' => $plugin, 'listener' => $listener['.value']));
					}
				}
				unset($listener);
			}
			if ($xmls->doesExist('/plugin/binding/tag')) {
				foreach ($xmls->selectNodes('/plugin/binding/tag') as $tag) {
					if (!empty($tag['.attributes']['name']) && !empty($tag['.attributes']['handler'])) {
						if (!isset($tagMappings[$tag['.attributes']['name']]))
							$tagMappings[$tag['.attributes']['name']] = array();
						array_push($tagMappings[$tag['.attributes']['name']], array('plugin' => $plugin, 'handler' => $tag['.attributes']['handler']));
					}
				}
				unset($tag);
			}
			if ($xmls->doesExist('/plugin/binding/sidebar')) {
				foreach ($xmls->selectNodes('/plugin/binding/sidebar') as $sidebar) {
					if (!empty($sidebar['.attributes']['handler'])) {
						array_push($sidebarMappings, array('plugin' => $plugin, 'class' => $sidebar['.attributes']['class'], 'title' => $sidebar['.attributes']['title'], 'handler' => $sidebar['.attributes']['handler']));
					}
				}
				unset($sidebar);
			}
		} else {
			$plugin = mysql_escape_string($plugin);
			mysql_query("DELETE FROM {$database['prefix']}Plugins WHERE owner = $owner AND name = '$plugin'");
		}
	}
	unset($xmls);
	unset($plugin);
}

function fireEvent($event, $target = null, $mother = null, $condition = true) {
	global $service, $eventMappings, $pluginURL;
	if (!$condition)
		return $target;
	if (!isset($eventMappings[$event]))
		return $target;
	foreach ($eventMappings[$event] as $mapping) {
		include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
		if (function_exists($mapping['listener'])) {
			$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
			$target = call_user_func($mapping['listener'], $target, $mother);
		}
	}
	return $target;
}

function handleTags( & $content) {
	global $service, $tagMappings, $pluginURL;
	if (preg_match_all('/\[##_(\w+)_##\]/', $content, $matches)) {
		foreach ($matches[1] as $tag) {
			if (!isset($tagMappings[$tag]))
				continue;
			$target = '';
			foreach ($tagMappings[$tag] as $mapping) {
				include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
				if (function_exists($mapping['handler'])) {
					$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
					$target = call_user_func($mapping['handler'], $target);
				}
			}
			dress($tag, $target, $content);
		}
	}
}

function handleSidebar( & $obj) {
	global $service, $sidebarMappings, $pluginURL;
	
	$content_temp = '';

	foreach ($sidebarMappings as $mapping) {
		include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
		$content_temp .= $obj->sidebarItem;

		if (preg_match_all('/\[##_(\w+)_##\]/', $content_temp, $matches)) {
			foreach ($matches[1] as $tag) {
				$target = $title = '';

				switch($tag) {
					case 'sidebar_id':
						dress('sidebar_id', $mapping['plugin'], $content_temp);
						break;
					case 'sidebar_class':
						dress('sidebar_class', $mapping['class'], $content_temp);
						break;
					case 'sidebar_titles':
						if($mapping['title']) {
							dress('sidebar_titles', $obj->sidebarTitles, $content_temp);
							dress('sidebar_title', $mapping['title'], $content_temp);
						} else {
							dress('sidebar_titles', '', $content_temp);
						}
						break;
					case 'sidebar_contents':
						if (function_exists($mapping['handler'])) {
							$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
							$target = call_user_func($mapping['handler'], $target, $content);
						}
						dress('sidebar_contents', $target, $content_temp);
						break;
				}
			}
		}
	}
	$obj->sidebarItem = $content_temp;
}
?>