<?php

// TODO: i18n (Ticket #1133)

function GoogleMap_Header($target) {
	global $configVal, $pluginURL;
	requireComponent('Textcube.Function.Setting');
	$config = Setting::fetchConfigVal($configVal);
	if (!is_null($config) && isset($config['apiKey'])) {
		$api_key = $config['apiKey'];
		$use_sensor = $config['useSensor'] ? 'true' : 'false';
		$target .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$pluginURL/common.css\" />\n";
		$target .= "<script type=\"text/javascript\" src=\"http://maps.google.co.kr/maps?file=api&amp;v=2&amp;sensor=$use_sensor&amp;key=$api_key\"></script>\n";
		$target .= "<script type=\"text/javascript\" src=\"$pluginURL/scripts/common.js?".time()."\"></script>\n";
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
		$config = Setting::fetchConfigVal($configVal);
		$api_key = $config['apiKey']; // should exist here
		$use_sensor = $config['useSensor'] ? 'true' : 'false';
		$target .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$pluginURL/common.css\" />\n";
		$target .= "<script type=\"text/javascript\" src=\"http://maps.google.co.kr/maps?file=api&amp;v=2&amp;sensor=$use_sensor&amp;key=$api_key\"></script>\n";
		$target .= "<script type=\"text/javascript\">
		//<![CDATA[
		var pluginURL = '$pluginURL';
		var blogURL = '$blogURL';
		//]]>
		</script>";
		$target .= "<script type=\"text/javascript\" src=\"$pluginURL/scripts/common.js\"></script>\n";
		$target .= "<script type=\"text/javascript\" src=\"$pluginURL/scripts/editor.js\"></script>\n";
	}
	return $target;
}

function GoogleMap_AddToolbox($target) {
	global $pluginURL;
	$target .= "<dl id=\"toolbox-googlemap\">";
	$target .= "<dd class=\"command-box\"><a class=\"button\" id=\"gmap-insertMap\" href=\"#insertGoogleMap\" onclick=\"GMapTool_insertMap(); return false;\">"._t("구글맵 추가하기")."</a></dd>";
	$target .= "<dd class=\"command-box\"><a class=\"button\" href=\"#getLocation\" id=\"gmap-getLocation\" onclick=\"GMapTool_getLocation(); return false;\">"._t("현재 위치 알아내기")."</a></dd>";
	$target .= "</dl>";
	return $target;
}

function GoogleMap_View($target, $mother) {
	global $configVal, $pluginURL, $surl;
	requireComponent('Textcube.Function.Setting');
	requireComponent('Textcube.Function.Misc');
	$config = Setting::fetchConfigVal($configVal);
	$matches = array();
	$offset = 0;

	while (preg_match('/\[##_GoogleMap\|(([^|]+)\|)?_##\]/', $target, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
		// SUGGUEST: [##_GoogleMap|{JSON_REPRESENTATION_OF_PARAMETERS_WITHOUT_NEWLINES}|_##]
		$id = 'GMapContainer'.$mother.rand();
		ob_start();

		// Mobile & iPhone (differences between these will be handled later.)
		if (defined('__TEXTCUBE_MOBILE__') || defined('__TEXTCUBE_IPHONE__')) {
			$staticimg = "http://maps.google.co.kr/staticmap?";
			$json = json_decode($matches[2][0], true);
			switch ($json['type']) {
			case 'G_SATELLITE_MAP':
				$maptype = 'satellite';
				$imgformat = 'jpg';
				break;
			case 'G_HYBRID_MAP':
				$maptype = 'hybrid';
				$imgformat = 'jpg';
				break;
			case 'G_PHYSICAL_MAP':
				$maptype = 'terrain';
				$imgformat = 'jpg';
				break;
			default:
				$maptype = 'roadmap';
				$imgformat = 'png';
				break;
			}
			$markers = '';
			for ($i = 0; $i < count($json['user_markers']); $i++) {
				if ($i > 0)
					$markers .= '|';
				$markers .= "{$json['user_markers'][$i]['lat']},{$json['user_markers'][$i]['lng']}";
			}
			$use_sensor = $config['useSensor'] ? 'true' : 'false';
			echo "<div class=\"googlemap\"><img src=\"{$staticimg}center={$json['center']['latitude']},{$json['center']['longitude']}&amp;zoom={$json['zoom']}&amp;size={$json['width']}x{$json['height']}&amp;maptype={$maptype}&amp;format={$imgformat}&amp;markers={$markers}&amp;sensor={$use_sensor}&amp;key={$config['apiKey']}\"title=\"{$json['user_markers'][0]['title']} - {$json['user_markers'][0]['desc']}\" alt=\"Google Map Test\" /></div>";
		}
		// Desktop
		else {
?>
		<div id="<?php echo $id;?>" style="border: 1px solid #666;"></div>
		<script type="text/javascript">
		//<![CDATA[
		var c = document.getElementById('<?php echo $id;?>');
		if (GBrowserIsCompatible()) {
			var map = GMap_CreateMap(c, <?php echo $matches[2][0];?>);
		} else {
			c.innerHTML = '<p style="text-align:center; color:#c99;"><?php echo _t("이 웹브라우저는 구글맵과 호환되지 않습니다.");?></p>';
		}
		//]]>
		</script>
<?php
		}
		$output = ob_get_contents();

		ob_end_clean();
		$target = substr_replace($target, $output, $matches[0][1], strlen($matches[0][0]));
	}
	return $target;
}

function GoogleMap_LocationLogView($target) {
	global $blogid, $blog, $blogURL, $pluginURL, $configVal, $service, $database;
	requireComponent('Textcube.Function.Misc');
	$config = Setting::fetchConfigVal($configVal);
	$locatives =  getEntries($blogid, 'id, title, slogan, location, longitude, latitude','(length(location)>1 AND category > -1) OR (`longitude` IS NOT NULL AND `latitude` IS NOT NULL)', 'location');
	$width = Misc::getContentWidth();
	$height = intval($width * 1.2);
	$default_type = isset($config['locative_maptype']) ? $config['locative_maptype'] : 'G_HYBRID_MAP';
	$id = 'LocationMap';
	$lat = $config['latitude'];
	$lng = $config['longitude'];
	$zoom = 10;
	ob_start();
?>
	<div style="text-align:center;">
		<div id="<?php echo $id;?>" style="margin:0 auto;"></div>
	</div>
	<script type="text/javascript">
	//<![CDATA[
	//console.log('a');
	var process_count = 0;
	var polling_interval = 100; // ms
	var query_interval = 500; // ms
	var query_interval_handle = null;
	var progress = null;
	var boundary = null;
	var locationMap = null;
	function adjustToBoundary() {
		var z = locationMap.getBoundsZoomLevel(boundary);
		if (z > 8)
			z--;
		if (z > 12)
			z = 12;
		locationMap.setZoom(z);
		locationMap.setCenter(boundary.getCenter());
	}
	function locationFetch(tofind) {
		if (tofind.length == 0) {
			window.clearInterval(query_interval_handle);
			return;
		}
		GMap_addLocationMark.apply(this, tofind.pop());
	}
	function locationFetchPoller(target_count) {
		var e = document.getElementById('gmap-progress');
		var p = document.getElementById('gmap-progress-meter');
		if (process_count != target_count) {
			progress.setProgress(process_count / target_count);
			window.setTimeout('locationFetchPoller('+target_count+');', polling_interval);
			return;
		}
		progress.setProgress(1.0);
		window.setTimeout(function() {locationMap.removeControl(progress);}, 200); // eyecandy
		adjustToBoundary();
	}
	STD.addLoadEventListener(function() {
		var c = document.getElementById('<?php echo $id;?>');
		c.style.width = "<?php echo $width;?>px"
		c.style.height = "<?php echo $height;?>px";
		if (GBrowserIsCompatible()) {
			locationMap = new GMap2(c);
			locationMap.addMapType(G_PHYSICAL_MAP);
			locationMap.setMapType(<?php echo $default_type;?>);
			locationMap.addControl(new GHierarchicalMapTypeControl());
			locationMap.addControl(new GLargeMapControl());
			locationMap.addControl(new GScaleControl());
			locationMap.enableContinuousZoom();
			locationMap.setCenter(new GLatLng(<?php echo $lat;?>, <?php echo $lng;?>), <?php echo $zoom;?>);
			progress = new GProgressControl();
			locationMap.addControl(progress);
			boundary = new GLatLngBounds(locationMap.getCenter());
			var locations = new Array();
			var tofind = new Array();
<?php
	$count = 0;
	$countRemoteQuery = 0;
	foreach ($locatives as $locative) {
		//if ($count == 10) break; // for testing purpose
		$locative['link'] = "$blogURL/" . ($blog['useSloganOnPost'] ? 'entry/' . URL::encode($locative['slogan'],$service['useEncodedURL']) : $locative['id']);
		$found = false;

		if ($locative['longitude'] != NULL && $locative['latitude'] != NULL) {
			$found = true;
			$lat = $locative['latitude'];
			$lng = $locative['longitude'];
			$locative['location'] = _t("위도")." : " . $lat . ", "._t("경도")." : " . $lng;
		} else {
			$row = POD::queryRow("SELECT * FROM {$database['prefix']}GMapLocations WHERE blogid = ".getBlogId()." AND original_address = '".POD::escapeString($locative['location'])."'");
			if ($row == null || empty($row)) {
				$found = false;
			} else {
				$lat = $row['latitude'];
				$lng = $row['longitude'];
				$found = true;
			}
		}
		if ($found) // found, just output
			echo "\t\t\tGMap_addLocationMarkDirect(locationMap, {address:GMap_normalizeAddress('{$locative['location']}'), path:'{$locative['location']}', original_path:'{$locative['location']}'}, '".str_replace("'", "\\'", $locative['title'])."', encodeURI('".str_replace("'", "\\'", $locative['link'])."'), new GLatLng($lat, $lng), boundary, locations, false);\n";
		else // try to find in the client
			echo "\t\t\ttofind.push([locationMap, '{$locative['location']}', '".str_replace("'", "\\'", $locative['title'])."', encodeURI('".str_replace("'", "\\'", $locative['link'])."'), boundary, locations]);\n";
		$count++;
	}
?>
			progress.setLabel('Loading locations...');
			query_interval_handle = window.setInterval(function() {locationFetch(tofind);}, query_interval);
			window.setTimeout(function() {locationFetchPoller(<?php echo $count;?>);}, polling_interval);
		} else {
			c.innerHTML = '<p style="text-align:center; color:#c99;"><?php echo _t("이 웹브라우저는 구글맵과 호환되지 않습니다.");?></p>';
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
	requireComponent('Textcube.Function.Setting');
	$config = Setting::fetchConfigVal($data);
	if (!is_numeric($config['latitude']) || !is_numeric($config['longitude']) ||
		$config['latitude'] < -90 || $config['latitude'] > 90 || $config['longitude'] < -180 || $config['longitude'] > 180)
		return '위도 또는 경도의 값이 올바르지 않습니다.';
	$config['useSensor'] = !isset($config['useSensor']) ? true : false;
	return true;
}

function GoogleMap_Cache() {
	global $database;
	$IV = array(
		'POST' => array(
			'original_path' => array('string', 'default'=>''),
			'path' => array('string', 'default'=>''),
			'lat' => array('number', 'default'=>null),
			'lng' => array('number', 'default'=>null)
		)
	);
	Validator::validate($IV);
	if (empty($_POST['path']) || empty($_POST['original_path'])) {
		echo 'error: empty path';
		return;
	}
	$original_path_e = POD::escapeString($_POST['original_path']);
	$path_e = POD::escapeString($_POST['path']);
	$row = POD::queryRow("SELECT * FROM {$database['prefix']}GMapLocations WHERE blogid = ".getBlogId()." AND original_address = '$original_path_e'");
	if ($row == null || empty($row)) {
		if (POD::execute("INSERT INTO {$database['prefix']}GMapLocations VALUES (".getBlogId().", '$original_path_e', '$path_e', {$_POST['lng']}, {$_POST['lat']}, ".time().")"))
			echo 'ok';
		else
			echo 'error: cache failed';
	} else {
		echo 'duplicate';
	}
}

function GoogleMapUI_InsertMap() {
	global $configVal, $pluginURL;
	requireComponent('Textcube.Function.Misc');
	$config = Setting::fetchConfigVal($configVal);
	$lat = $config['latitude'];
	$lng = $config['longitude'];
	$default_type = 'G_HYBRID_MAP';
	$default_width = min(Misc::getContentWidth(), 500);
	$default_height = 400;
	$zoom = 10;
	_GMap_printHeaderForUI(_t('구글맵 삽입하기'), 'insert', $config['apiKey'], $config['useSensor'] ? 'true' : 'false');
?>
	<div id="controls">
		<button id="toggleMarkerAddingMode"><?php echo _t("마커 표시 모드");?></button>
		<button id="doInsert"><?php echo _t("본문에 삽입하기");?></button>
	</div>
	<div style="text-align:center;">
		<div class="ui-widget-content" id="GoogleMapPreview" style="width:<?php echo $default_width;?>px; height:<?php echo $default_height;?>px; margin:0 auto;"></div>
	</div>
	<script type="text/javascript">
	//<![CDATA[
	function initializeMap() {
		map = new GMap2($('#GoogleMapPreview')[0]);
		map.addMapType(G_PHYSICAL_MAP);
		map.setMapType(<?php echo $default_type;?>);
		map.addControl(new GHierarchicalMapTypeControl());
		map.addControl(new GLargeMapControl());
		map.addControl(new GScaleControl());
		map.enableScrollWheelZoom();
		map.enableContinuousZoom();
		map.setCenter(new GLatLng(<?php echo $lat;?>, <?php echo $lng;?>), <?php echo $zoom;?>);
	}
	//]]>
	</script>
	<h2><?php echo _t("지도 검색");?></h2>
	<div class="accordion-elem">
		<p><label><?php echo _t("위치 검색");?> : <input type="text" class="editControl" id="inputQuery" value="" /></label><button id="queryLocation"><?php echo _t("찾기");?></button></p>
	</div>
	<h2><?php echo _t("기본 설정");?></h2>
	<div class="accordion-elem">
		<p><label><?php echo _t("가로");?>(px) : <input type="text" class="editControl" id="inputWidth" value="<?php echo $default_width;?>" /></label></p>
		<p><label><?php echo _t("세로");?>(px) : <input type="text" class="editControl" id="inputHeight" value="<?php echo $default_height;?>" /></label></p>
		<p><button id="applyBasicSettings"><?php echo _t("적용");?></button></p>
	</div>
<?php
	// TODO: 주소 추출 UI
	// - TODO: 포스트 내용 텍스트 얻어오기 및 주소 정보 추출
	_GMap_printFooterForUI();
}

function GoogleMapUI_GetLocation() {
	global $configVal, $pluginURL;
	requireComponent('Textcube.Function.Misc');
	$config = Setting::fetchConfigVal($configVal);
	$lat = $config['latitude'];
	$lng = $config['longitude'];
	$default_type = 'G_HYBRID_MAP';
	$default_width = 500;
	$default_height = 400;
	$zoom = 10;
	_GMap_printHeaderForUI(_t('현재 위치 알아내기'), 'getlocation', $config['apiKey']);
?>
	<h2><?php echo _t("이용 안내");?></h2>
	<p><?php echo _t("웹브라우저가 제공하는 Geolocation 서비스를 이용하여 현재 위치 정보를 가져옵니다. 정확도는 사용하고 계신 기기나 지역에 따라 다를 수 있습니다.");?> <a href="#help">(<?php echo _t("자세히 알아보기");?>)</a></p>
	<p><span id="availability"></span><span id="status"></span></p>
	<h2>미리보기</h2>
	<div style="text-align:center;">
		<div id="GoogleMapPreview" style="width:<?php echo $default_width;?>px; height:<?php echo $default_height;?>px; margin:0 auto;"></div>
	</div>
	<script type="text/javascript">
	//<![CDATA[
	function initializeMap() {
		map = new GMap2($('#GoogleMapPreview')[0]);
		map.addMapType(G_PHYSICAL_MAP);
		map.setMapType(<?php echo $default_type;?>);
		map.addControl(new GHierarchicalMapTypeControl());
		map.addControl(new GLargeMapControl());
		map.addControl(new GScaleControl());
		map.enableScrollWheelZoom();
		map.enableContinuousZoom();
		map.setCenter(new GLatLng(<?php echo $lat;?>, <?php echo $lng;?>), <?php echo $zoom;?>);
	}
	//]]>
	</script>
<?php
	_GMap_printFooterForUI();
}

function _GMap_printHeaderForUI($title, $jsName, $api_key, $use_sensor) {
	global $pluginURL, $blogURL, $service, $adminSkinSetting;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Google Map Plugin: <?php echo $title;?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $pluginURL;?>/popup.css" />
	<script type="text/javascript" src="<?php echo $pluginURL;?>/scripts/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/scripts/jquery-ui-1.7.2.custom.min.js"></script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/scripts/jquery-mousewheel.min.js"></script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/scripts/jquery-json.js"></script>
	<script type="text/javascript" src="http://maps.google.co.kr/maps?file=api&amp;v=2&amp;sensor=<?php echo $use_sensor;?>&amp;key=<?php echo $api_key;?>"></script>
	<script type="text/javascript">
	//<![CDATA[
	var pluginURL = '<?php echo $pluginURL;?>';
	var blogURL = '<?php echo $blogURL;?>';
	$(window).unload(GUnload);
	//]]>
	</script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/scripts/common.js?<?php echo time();?>"></script>
	<script type="text/javascript" src="<?php echo $pluginURL;?>/scripts/<?php echo $jsName; ?>.js?<?php echo time();?>"></script>
</head>
<body>
<div id="all-wrap">
	<h1><?php echo $title;?></h1>
	<div id="layout-body">
<?php
}

function _GMap_printFooterForUI() {
?>
	</div>
</div>
</body>
</html>
<?php
}

function _GMap_normalizeAddress($address) {
	return trim(implode(' ', explode('/', $address)));
}
/* vim: set noet ts=4 sts=4 sw=4: */
?>
