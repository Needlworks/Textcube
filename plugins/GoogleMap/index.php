<?php

// TODO: i18n
$gmap_msg = array(
	'COMPAT_BROWSER_MISMATCH' => '이 웹브라우저는 구글맵 API와 호환되지 않습니다.',
	'VALIDATION_WRONG_LATLNG' => '잘못된 위도 또는 경도 값입니다.',
	'TOOLBOX_IMGALT' => '구글맵 삽입하기',
);

function GoogleMap_AddPost($target, $mother) {
	// TODO: Extract address information from the content
}

function GoogleMap_UpdatePost($target, $mother) {
	// TODO: Extract address information from the content
}

function GoogleMap_Header($target) {
	global $configVal, $pluginURL;
	requireComponent('Textcube.Function.Setting');
	$config = setting::fetchConfigVal($configVal);
	if (!is_null($config) && isset($config['apiKey'])) {
		$api_key = $config['apiKey'];
		$target .= "<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$api_key\"></script><!-- Google Map Plugin -->\n";
		$target .= "<script type=\"text/javascript\" src=\"$pluginURL/gmap_helper.js\"></script>\n";
	}
	return $target;
}

function GoogleMap_AdminHeader($target) {
	global $suri, $pluginURL, $serviceURL;
	if ($suri['directive'] == '/owner/entry/post' || $suri['directive'] == '/owner/entry/edit') {
		requireComponent('Textcube.Function.Setting');
		$config = setting::fetchConfigVal($configVal);
		$api_key = $config['apiKey']; // should exist here
		$target .= "<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$api_key\"></script><!-- Google Map Plugin -->\n";
		$target .= "<script type=\"text/javascript\" src=\"$pluginURL/gmap_helper.js\"></script>\n";
	}
	return $target;
}

function GoogleMap_AddToolbox($target) {
	global $pluginURL;
	$target .= "<img src=\"$pluginURL/images/gmap_toolbar.png\" border=\"0\" alt=\"{$gmap_msg['TOOLBOX_IMGALT']}\" onclick=\"GMapTool_Insert();\" style=\"cursor:pointer\" />\n";
	return $target;
}

function GoogleMap_View($target, $mother) {
	global $gmap_msg;
	global $configVal, $pluginURL;
	requireComponent('Textcube.Function.Setting');
	$config = setting::fetchConfigVal($configVal);
	$matches = array();
	// TODO: multiple matches in a single entry
	if (preg_match('/\[##_GoogleMap((\|[^|]+)+)_##\]/', $target, &$matches) == 0)
		return $target;
	$params = explode('|', $matches[1]);
	$id = 'GMapContainer'.$mother.rand();
	$width = 450; $height = 400;
	$lat = !isset($params['latitude']) ? $config['latitude'] : $params['latitude'];
	$lng = !isset($params['longitude']) ? $config['longitude'] : $params['longitude'];
	$zoom = !isset($params['zoom']) ? 10 : $params['zoom'];
	$default_type = 'G_HYBRID_MAP';
	ob_start();
?>
	<!-- TOREMOVE: <?php print_r($params); ?> -->
	<div id="<?php echo $id;?>" style="border: 1px solid #666; width:<?php echo $width;?>px; height:<?php echo $height;?>px;"></div>
	<script type="text/javascript">
	//<![CDATA[
	var c = document.getElementById('<?php echo $id;?>');
	if (GBrowserIsCompatible()) {
		var map = new GMap2(c);
		map.setMapType(<?php echo $default_type;?>);
		map.setCenter(new GLatLng(<?php echo $lat;?>, <?php echo $lng;?>), <?php echo $zoom;?>);
		map.addControl(new GHierarchicalMapTypeControl());
		map.addControl(new GLargeMapControl());
		map.addControl(new GScaleControl());
	} else {
		c.innerHTML = '<p style="text-align:center; color:#c99;"><?php echo htmlspecialchars($gmap_msg['COMPAT_BROWSER_MISMATCH'])?></p>';
	}
	//]]>
	</script>
<?php
	$result = ob_get_contents();
	ob_end_clean();
	$target = str_replace($matches[0], $result, $target);
	return $target;
}

function GoogleMap_ConfigHandler($data) {
	global $gmap_msg;
	requireComponent('Textcube.Function.Setting');
	$config = setting::fetchConfigVal($data);
	if (!is_numeric($config['latitude']) || !is_numeric($config['longitude']) ||
		$config['latitude'] < -90 || $config['latitude'] > 90 || $config['longitude'] < -180 || $config['longitude'] > 180)
		return $gmap_msg['VALIDATION_WRONG_LATLNG'];
	return true;
}
?>
