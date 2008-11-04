<?php
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

function GoogleMap_View($target, $mother) {
	$matches = array();
	if (preg_match('/\[##_GoogleMap(\|[^|]+)+_##\]/', $target, &$matches) == 0)
		return $target;
	$id = 'GMapContainer'.$mother.rand();
	$width = 450; $height = 400;
	$lat = 37.5193; $lng = 126.9707; $zoom = 12;
	$default_type = 'G_HYBRID_MAP';
	ob_start();
?>
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
		c.innerHTML = '<p style="text-align:center; color:#c99;">Your web browser is not compatible with Google Maps.</p>';
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
	requireComponent('Textcube.Function.Setting');
	$config = setting::fetchConfigVal($data);
	return true;
}
?>
