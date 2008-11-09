<?php

// TODO: i18n (Ticket #1133)

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
		$target .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$pluginURL/common.css\" />\n";
		$target .= "<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$api_key\"></script>\n";
		$target .= "<script type=\"text/javascript\" src=\"$pluginURL/gmap_common.js\"></script>\n";
		$target .= "<script type=\"text/javascript\">
		//<![CDATA[
		STD.addUnloadEventListener(function(){GUnload();});
		//]]>
		</script>\n";
	}
	return $target;
}

function GoogleMap_AdminHeader($target) {
	global $suri, $pluginURL, $blogURL, $serviceURL, $configVal;
	if ($suri['directive'] == '/owner/entry/post' || $suri['directive'] == '/owner/entry/edit') {
		requireComponent('Textcube.Function.Setting');
		$config = setting::fetchConfigVal($configVal);
		$api_key = $config['apiKey']; // should exist here
		$target .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$pluginURL/common.css\" />\n";
		$target .= "<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$api_key\"></script>\n";
		$target .= "<script type=\"text/javascript\">
		//<![CDATA[
		var pluginURL = '$pluginURL';
		var blogURL = '$blogURL';
		//]]>
		</script>";
		$target .= "<script type=\"text/javascript\" src=\"$pluginURL/gmap_common.js\"></script>\n";
		$target .= "<script type=\"text/javascript\" src=\"$pluginURL/gmap_editor.js\"></script>\n";
	}
	return $target;
}

function GoogleMap_AddToolbox($target) {
	global $pluginURL;
	$target .= "<img src=\"$pluginURL/images/gmap_toolbar.gif\" border=\"0\" alt=\"구글맵 추가하기\" onclick=\"GMapTool_Insert();\" style=\"cursor:pointer\" />\n";
	return $target;
}

function GoogleMap_View($target, $mother) {
	global $gmap_msg;
	global $configVal, $pluginURL;
	requireComponent('Textcube.Function.Setting');
	requireComponent('Textcube.Function.Misc');
	$config = setting::fetchConfigVal($configVal);
	$matches = array();
	$offset = 0;
	while (preg_match('/\[##_GoogleMap\|(([^|]+)\|)?_##\]/', $target, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
		$params = json_decode($matches[2][0]);
		// TODO: customize these parameters in the WYSIWYG editor.
		// SUGGUEST: [##_GoogleMap|{JSON_REPRESENTATION_OF_PARAMETERS_WITHOUT_NEWLINES}|_##]
		$id = 'GMapContainer'.$mother.rand();
		$width = !isset($params->{width}) ? misc::getContentWidth() : $params->{width};
		$height = !isset($params->{height}) ? 400 : $params->{height};
		$lat = !isset($params->{center}->{latitude}) ? $config['latitude'] : $params->{center}->{latitude};
		$lng = !isset($params->{center}->{longitude}) ? $config['longitude'] : $params->{center}->{longitude};
		$zoom = !isset($params->{zoom}) ? 10 : $params->{zoom};
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
			c.innerHTML = '<p style="text-align:center; color:#c99;">이 웹브라우저는 구글맵과 호환되지 않습니다.</p>';
		}
		//]]>
		</script>
<?php
		$output = ob_get_contents();
		ob_end_clean();
		$target = substr_replace($target, $output, $matches[0][1], strlen($matches[0][0]));
		$offset += $matches[0][1] + strlen($output);
	}
	return $target;
}

function GoogleMap_LocationLogView($target) {
	global $blogid, $blog, $blogURL, $pluginURL;
	requireComponent('Textcube.Function.Misc');
	$locatives = getLocatives($blogid);
	$width = misc::getContentWidth();
	$height = intval($width * 1.2);
	$default_type = 'G_HYBRID_MAP';
	$id = 'LocationMap';
	$lat = 37.52;
	$lng = 126.98;
	$zoom = 10;
	ob_start();
?>
	<div style="text-align:center;"><div id="<?php echo $id;?>" style="margin:0 auto;"></div></div>
	<script type="text/javascript">
	//<![CDATA[
	var process_count = 0;
	var polling_interval = 60; // ms
	var boundary;
	function locationFetchPoller(target_count) {
		if (process_count != target_count) {
			window.setTimeout('locationFetchPoller('+target_count+');', polling_interval);
			return;
		}
		var z = locationMap.getBoundsZoomLevel(boundary);
		if (z > 8)
			z--;
		if (z > 12)
			z = 12;
		locationMap.setZoom(z);
		locationMap.setCenter(boundary.getCenter());
	}
	STD.addLoadEventListener(function() {
		var c = document.getElementById('<?php echo $id;?>');
		c.style.width = "<?php echo $width;?>px"
		c.style.height = "<?php echo $height;?>px";
		if (GBrowserIsCompatible()) {
			locationMap = new GMap2(c);
			locationMap.setMapType(<?php echo $default_type;?>);
			locationMap.setCenter(new GLatLng(<?php echo $lat;?>, <?php echo $lng;?>), <?php echo $zoom;?>);
			locationMap.addControl(new GHierarchicalMapTypeControl());
			locationMap.addControl(new GLargeMapControl());
			locationMap.addControl(new GScaleControl());
			boundary = new GLatLngBounds(locationMap.getCenter(), locationMap.getCenter());
			var locations = new Array();
<?php
	$count = 0;
	foreach ($locatives as $locative) {
		$locative['link'] = "$blogURL/" . ($blog['useSloganOnPost'] ? 'entry/' . URL::encode($locative['slogan'],$service['useEncodedURL']) : $locative['id']);
		echo "\t\t\tGMap_addLocationMark(locationMap, '{$locative['location']}', '{$locative['title']}', '{$locative['link']}', boundary, locations);\n";
		$count++;
	}
?>
			window.setTimeout('locationFetchPoller(<?php echo $count;?>);', polling_interval);
		} else {
			c.innerHTML = '<p style="text-align:center; color:#c99;">이 웹브라우저는 구글맵과 호환되지 않습니다.</p>';
		}
	});
	//]]>
	</script>
<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function GoogleMap_ConfigHandler($data) {
	global $gmap_msg;
	requireComponent('Textcube.Function.Setting');
	$config = setting::fetchConfigVal($data);
	if (!is_numeric($config['latitude']) || !is_numeric($config['longitude']) ||
		$config['latitude'] < -90 || $config['latitude'] > 90 || $config['longitude'] < -180 || $config['longitude'] > 180)
		return '위도 또는 경도의 값이 올바르지 않습니다.';
	return true;
}

function GoogleMapUI_Customize($target) {
	global $configVal, $pluginURL;
	requireComponent('Textcube.Function.Misc');
	$config = setting::fetchConfigVal($configVal);
	$lat = $config['latitude'];
	$lng = $config['longitude'];
	$default_type = 'G_HYBRID_MAP';
	$default_width = min(misc::getContentWidth(), 500);
	$default_height = 400;
	$zoom = 10;
	_GMap_printHeaderForUI('구글맵 삽입하기', $config['apiKey']);
?>
	<div id="controls">
		<button id="toggleMarkerAddingMode">마커 표시 모드</button>
		<button id="doInsert">본문에 삽입하기</button>
	</div>
	<div style="text-align:center;"><div id="GoogleMapPreview" style="width:<?php echo $default_width;?>px; height:<?php echo $default_height;?>px; margin:0 auto;"></div></div>
	<script type="text/javascript">
	//<![CDATA[
	function initializeMap() {
		map = new GMap2($('GoogleMapPreview'));
		map.setCenter(new GLatLng(<?php echo $lat;?>, <?php echo $lng;?>), <?php echo $zoom;?>);
		map.setMapType(<?php echo $default_type;?>);
		map.addControl(new GHierarchicalMapTypeControl());
		map.addControl(new GLargeMapControl());
		map.addControl(new GScaleControl());
		map.enableScrollWheelZoom();
	}
	//]]>
	</script>
	<fieldset>
		<legend>기본 설정</legend>
		<p><label>가로(px) : <input type="text" class="editControl" id="inputWidth" value="<?php echo $default_width;?>" /></label></p>
		<p><label>세로(px) : <input type="text" class="editControl" id="inputHeight" value="<?php echo $default_height;?>" /></label></p>
		<button id="applyBasicSettings">적용</button>
	</fieldset>
<?php
	// TODO: 각종 옵션 설정 UI
	// TODO: 주소 추출 UI
	// - TODO: 포스트 내용 텍스트 얻어오기 및 주소 정보 추출
	// - TODO: Google API를 이용한 geocoding 또는 map search
	_GMap_printFooterForUI();
}

function _GMap_printHeaderForUI($title, $api_key) {
	global $pluginURL, $blogURL, $service, $adminSkinSetting;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Google Map Plugin: <?php echo $title;?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $pluginURL;?>/ui.css" />
	<script type="text/javascript" src="http://www.google.com/jsapi?key=<?php echo $api_key;?>"></script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/gmap_common.js"></script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/gmap_ui.js"></script>
	<script type="text/javascript">
	//<![CDATA[
	var pluginURL = '<?php echo $pluginURL;?>';
	var blogURL = '<?php echo $blogURL;?>';
	google.load('maps', '2');
	google.load('mootools', '1.2.1');
	google.setOnLoadCallback(function() {
		window.addEvent('unload', function() {GUnload();});
		initialize(); // should be declared somewhere. (for now, gmap_ui.js)
	});
	//]]>
	</script>
</head>
<body>
<div id="temp-wrap">
<div id="all-wrap">
	<h1><?php echo $title;?></h1>
	<div id="layout-body">
<?php
}

function _GMap_printFooterForUI() {
?>
	</div>
</div>
</div>
</body>
</html>
<?php
}
/* vim: set noet ts=4 sts=4 sw=4: */
?>
