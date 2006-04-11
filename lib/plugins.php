<?
$activePlugins = array();
$eventMappings = array();
$tagMappings = array();
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
?>