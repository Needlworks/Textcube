<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function GoogleMap_generateTranslationJavascript($messages) {
	// The language setting follows in which context this function is called.
	ob_start();
	echo "<script type=\"text/javascript\">\n";
	echo "__text == __text || {};\n";
	foreach ($messages as $text) {
		$translated_text = _t($text);
		echo "__text['$text'] = '$translated_text';\n";
	};
	echo "</script>\n";
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function GoogleMap_Header($target) {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');
    $plugin_uri = $context->getProperty('plugin.uri');
	if (!is_null($config)) {
		$use_sensor = (isset($config['useSensor']) && $config['useSensor']) ? 'true' : 'false';
		$target .= <<<EOS
<link rel="stylesheet" type="text/css" href="$plugin_uri/styles/common.css" />
<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=$use_sensor"></script>
<script type="text/javascript" src="$plugin_uri/scripts/common.js"></script>
<script type="text/javascript">
//<![CDATA[
	var GMapOnLoadCallbacks = [];
//]]>
</script>
EOS;
	}
	return $target;
}

function GoogleMap_AdminHeader($target) {
	$context = Model_Context::getInstance();
	$blog_uri = $context->getProperty('uri.blog');
	if ($context->getProperty('suri.directive') == '/owner/entry/post' || $context->getProperty('suri.directive') == '/owner/entry/edit') {
        $config = $context->getProperty('plugin.config');
        $plugin_uri = $context->getProperty('plugin.uri');
		$use_sensor = $config['useSensor'] ? 'true' : 'false';
		$target .= <<<EOS
<link rel="stylesheet" type="text/css" href="$plugin_uri/styles/common.css" />
<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=$use_sensor"></script>
<script type="text/javascript" src="$plugin_uri/scripts/common.js"></script>
<script type="text/javascript" src="$plugin_uri/scripts/editor.js"></script>
<script type="text/javascript">
//<![CDATA[
	var plugin_uri = '$plugin_uri';
	var blogURL = '$blog_uri';
	var GMapOnLoadCallbacks = [];
//]]>
</script>
EOS;
	}
	return $target;
}

function GoogleMap_Footer($target) {
	$context= Model_Context::getInstance();
	if ($context->getProperty('is_used')) {
        $config = $context->getProperty('plugin.config');
		$use_sensor = $config['useSensor'] ? 'true' : 'false';
		if (!is_null($config)) {
			$target .= <<<EOS
<script type="text/javascript">
//<![CDATA[
	(function($) {
	$(document).ready(function() {
		//STD.addUnloadEventListener(function(){GUnload();}); // not available in v3
		var i;
		for (i = 0; i < GMapOnLoadCallbacks.length; i++)
			GMapOnLoadCallbacks[i]();
	});
	})(jQuery);
//]]>
</script>
EOS;
		}
	}
	return $target;
}

function GoogleMap_AdminFooter($target) {
	$context = Model_Context::getInstance();
	if ($context ->getProperty('is_used')) {
        $config = $context->getProperty('plugin.config');
		$use_sensor = $config['useSensor'] ? 'true' : 'false';
		if (!is_null($config)) {
			$target .= <<<EOS
<script type="text/javascript">
//<![CDATA[
	(function($) {
	$(document).ready(function() {
		//STD.addUnloadEventListener(function(){GUnload();}); // not available in v3
		var i;
		for (i = 0; i < GMapOnLoadCallbacks.length; i++)
			GMapOnLoadCallbacks[i]();
	});
	})(jQuery);
//]]>
</script>;
EOS;
		}
	}
	return $target;
}

function GoogleMap_AddToolbox($target) {
	$m_addGoogleMap = _t("지도 삽입하기");
	$m_attachLocation = _t("현재 위치 첨부하기");
	$target .= GoogleMap_generateTranslationJavascript(array(
		'지도 삽입하기',
		'현재 위치 첨부하기',
		'첨부된 위치 제거하기',
		'첨부된 위치 정보를 제거하시겠습니까?',
		'위치 정보를 가져오지 못하였습니다.',
		'권한 없음',
		'위치정보 없음',
		'시간 제한 초과',
		'알 수 없는 오류',
		'현재 웹브라우저는 Geolocation 기능을 지원하지 않습니다.'
	));
	$target .= <<<EOS
	<dl id="toolbox-googlemap">
		<dd class="command-box"><a class="button" id="googlemap-insertMap" href="#insertGoogleMap" onclick="GMapTool_insertMap(); return false;">$m_addGoogleMap</a></dd>
		<dd class="command-box"><a class="button" id="googlemap-attachLocation" href="#getLocation" onclick="GMapTool_attachLocation(); return false;">$m_attachLocation</a></dd>
	</dl>
EOS;
	return $target;
}

function GoogleMap_View($target, $mother) {
	$context= Model_Context::getInstance();
	if ($context->getProperty('is_used') === null)
		$context->setProperty('is_used', false);
	$dbPrefix = $context->getProperty('database.prefix');
	$blogId = $context->getProperty('blog.id');
    $config = $context->getProperty('plugin.config');
	$matches = array();
	$offset = 0;


	while (preg_match('/\[##_GoogleMap\|(([^|]+)\|)?_##\]/', $target, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
		$context->setProperty('is_used', true);
		// SUGGUEST: [##_GoogleMap|{JSON_REPRESENTATION_OF_PARAMETERS_WITHOUT_NEWLINES}|_##]
		$id = 'GMapContainer'.$mother.rand();
		ob_start();

		// Mobile & iPhone (differences between these will be handled later.)
		if (defined('__TEXTCUBE_MOBILE__') || defined('__TEXTCUBE_IPHONE__')) {
			$staticimg = "//maps.google.co.kr/staticmap?";
			$json = json_decode($matches[2][0], true);
			switch ($json['type']) {
			case 'G_SATELLITE_MAP':
			case 'SATELLITE':
				$maptype = 'satellite';
				$imgformat = 'jpg';
				break;
			case 'G_HYBRID_MAP':
			case 'HYBRID':
				$maptype = 'hybrid';
				$imgformat = 'jpg';
				break;
			case 'G_PHYSICAL_MAP':
			case 'TERRAIN':
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
			echo "<div class=\"googlemap\"><img src=\"{$staticimg}center={$json['center']['latitude']},{$json['center']['longitude']}&amp;zoom={$json['zoom']}&amp;size={$json['width']}x{$json['height']}&amp;maptype={$maptype}&amp;format={$imgformat}&amp;markers={$markers}&amp;sensor={$use_sensor}\"title=\"{$json['user_markers'][0]['title']} - {$json['user_markers'][0]['desc']}\" alt=\"User-inserted Map\" /></div>";
		}
		// Desktop
		else {
?>
		<div id="<?php echo $id;?>" class="GMapContainer"></div>
		<script type="text/javascript">
		//<![CDATA[
		GMapOnLoadCallbacks.push(function() {
			var c = document.getElementById('<?php echo $id;?>');
			var map = GMap_createMap(c, <?php echo $matches[2][0];?>);
		});
		//]]>
		</script>
<?php
		}
		$output = ob_get_contents();

		ob_end_clean();
		$target = substr_replace($target, $output, $matches[0][1], strlen($matches[0][0]));
	}
	// Check if location is attached to this post.
	$row = POD::queryRow("SELECT latitude, longitude FROM {$dbPrefix}Entries WHERE blogid = {$blogId} AND id = {$mother}");
	if ($row['latitude'] && $row['longitude']) {
		$target .= <<<EOS
<div class="googlemap-geolocation-attached">
	<h5>Location</h5>
	<a href="//maps.google.com/maps?iwloc=exact&amp;q={$row['latitude']},{$row['longitude']}&amp;z=15"><img src="//maps.google.com/maps/api/staticmap?center={$row['latitude']},{$row['longitude']}&zoom=12&size=260x120&maptype=roadmap&sensor=true&markers=color:red|size:small|{$row['latitude']},{$row['longitude']}" /></a>
</div>
EOS;
	}
	return $target;
}

function GoogleMap_LocationLogView($target) {
	$context = Model_Context::getInstance();
	$blogId = $context->getProperty('blog.id');
	$blog_uri = $context->getProperty('uri.blog');
	$context->setProperty('is_used', true);
    $config = $context->getProperty('plugin.config');
	$locatives =  getEntries($blogId, 'id, title, slogan, location, longitude, latitude','((length(location)>1 AND category > -1) OR (`longitude` IS NOT NULL AND `latitude` IS NOT NULL))', 'location');
	$width = Utils_Misc::getContentWidth();
	$height = intval($width * 1.2);
	$default_type = isset($config['locative_maptype']) ? _GMap_convertLegacyMapType($config['locative_maptype']) : 'ROADMAP';
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
	var process_count = 0;
	var polling_interval = 100; // ms
	var query_interval = 500; // ms
	var query_interval_handle = null;
	var progress = null;
	var boundary = null;
	var locationMap = null;
	function locationFetch(tofind) {
		if (tofind.length == 0) {
			window.clearInterval(query_interval_handle);
			return;
		}
		GMap_addLocationMark.apply(this, tofind.pop());
	}
	function locationFetchPoller(target_count) {
		if (process_count != target_count) {
			progress.setProgress(process_count / target_count);
			window.setTimeout('locationFetchPoller('+target_count+');', polling_interval);
			return;
		}
		progress.setProgress(1.0);
		window.setTimeout(function() {progress.remove();}, 200); // eyecandy
		locationMap.fitBounds(boundary);
	}
	GMapOnLoadCallbacks.push(function() {
		var c = document.getElementById('<?php echo $id;?>');
		c.style.width = "<?php echo $width;?>px"
		c.style.height = "<?php echo $height;?>px";
		locationMap = new google.maps.Map(c, {
			'center': new google.maps.LatLng(<?php echo $lat;?>, <?php echo $lng;?>),
			'zoom': <?php echo $zoom;?>,
			'mapTypeId': google.maps.MapTypeId.<?php echo $default_type;?>,
			'mapTypeControl': true,
			'navigationControl': true,
			'scaleControl': true
		});
		progress = new GProgressControl(locationMap);
		google.maps.event.addListenerOnce(locationMap, 'idle', function() {
			boundary = locationMap.getBounds();
			var locations = new Array();
			var tofind = new Array();
<?php
	$count = 0;
	$countRemoteQuery = 0;
	$dbPrefix = $context->getProperty('database.prefix');
	foreach ($locatives as $locative) {
		//if ($count == 10) break; // for testing purpose
		$locative['link'] = "$blog_uri/" . ($context->getProperty('blog.useSloganOnPost') ? 'entry/' . URL::encode($locative['slogan'],$context->getProperty('service.useEncodedURL')) : $locative['id']);
		$found = false;

		if ($locative['longitude'] != NULL && $locative['latitude'] != NULL) {
			$found = true;
			$lat = $locative['latitude'];
			$lng = $locative['longitude'];
			$locative['location'] = _t("위도")." : " . $lat . ", "._t("경도")." : " . $lng;
		} else {
			$row = POD::queryRow("SELECT * FROM {$dbPrefix}GMapLocations WHERE blogid = {$blogId} AND original_address = '".POD::escapeString($locative['location'])."'");
			if ($row == null || empty($row)) {
				$found = false;
			} else {
				$lat = $row['latitude'];
				$lng = $row['longitude'];
				$found = true;
			}
		}
		if ($found) // found, just output
			echo "\t\t\tGMap_addLocationMarkDirect(locationMap, {address:plugin.gmap.normalizeAddress('{$locative['location']}'), path:'{$locative['location']}', original_path:'{$locative['location']}'}, '".str_replace("'", "\\'", $locative['title'])."', encodeURI('".str_replace("'", "\\'", $locative['link'])."'), new google.maps.LatLng($lat, $lng), boundary, locations, false);\n";
		else // try to find in the client
			echo "\t\t\ttofind.push([locationMap, '".str_replace("'", "\\'",$locative['location'])."', '".str_replace("'", "\\'", $locative['title'])."', encodeURI('".str_replace("'", "\\'", $locative['link'])."'), boundary, locations]);\n";
		$count++;
	}
?>
			progress.setLabel('Loading locations...');
			query_interval_handle = window.setInterval(function() {locationFetch(tofind);}, query_interval);
			window.setTimeout(function() {locationFetchPoller(<?php echo $count;?>);}, polling_interval);
		});
	});
	//]]>
	</script>
<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function GoogleMap_ConfigHandler($data) {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');
	if (!is_numeric($config['latitude']) || !is_numeric($config['longitude']) ||
		$config['latitude'] < -90 || $config['latitude'] > 90 || $config['longitude'] < -180 || $config['longitude'] > 180)
		return _t('위도 또는 경도의 값이 올바르지 않습니다.');
	$config['useSensor'] = !isset($config['useSensor']) ? true : false;
	return true;
}

function GoogleMap_Cache() {
	$context = Model_Context::getInstance();
	$dbPrefix = $context->getProperty('database.prefix');
	$blogId = $context->getProperty('blog.id');
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
	$row = POD::queryRow("SELECT * FROM {$dbPrefix}GMapLocations WHERE blogid = {$blogId} AND original_address = '$original_path_e'");
	if ($row == null || empty($row)) {
		if (POD::execute("INSERT INTO {$dbPrefix}GMapLocations VALUES ({$blogId}, '$original_path_e', '$path_e', {$_POST['lng']}, {$_POST['lat']}, ".time().")"))
			echo 'ok';
		else
			echo 'error: cache failed';
	} else {
		echo 'duplicate';
	}
}

function GoogleMapUI_InsertMap() {
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');
	$lat = $config['latitude'];
	$lng = $config['longitude'];
	$default_type = 'ROADMAP';
	$default_width = min(Utils_Misc::getContentWidth(), 500);
	$default_height = 400;
	$zoom = 10;
	_GMap_printHeaderForUI(_t('구글맵 삽입하기'), 'insert', $config['useSensor'] ? 'true' : 'false');
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
	GMapOnLoadCallbacks.push(function() {
		map = new google.maps.Map($('#GoogleMapPreview')[0], {
			'center':new google.maps.LatLng(<?php echo $lat;?>, <?php echo $lng;?>),
			'zoom': <?php echo $zoom;?>,
			'mapTypeId': google.maps.MapTypeId.<?php echo $default_type;?>,
			'mapTypeControl': true,
			'navigationControl': true,
			'scaleControl': true
		});
		google.maps.event.addListenerOnce(map, 'idle', initializeCustomizableMap);
	});
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
	_GMap_printFooterForUI('insert');
}

function _GMap_printHeaderForUI($title, $jsName, $use_sensor) {
	$context = Model_Context::getInstance();
	$blog_uri = $context->getProperty('uri.blog');
    $plugin_uri = $context->getProperty('plugin.uri');
	header('Content-Type: text/html; charset=utf-8');
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Google Map Plugin: <?php echo $title;?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $plugin_uri;?>/styles/popup.css">
	<script type="text/javascript" src="//code.jquery.com/jquery-1.11.2.min.js"></script>
	<script type="text/javascript" src="//code.jquery.com/ui/1.11.2/jquery-ui.min.js"></script>
	<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=true"></script>
	<script type="text/javascript" src="<?php echo $plugin_uri;?>/scripts/common.js"></script>
	<script type="text/javascript" src="<?php echo $plugin_uri;?>/scripts/<?php echo $jsName;?>.js"></script>
	<script type="text/javascript">
	//<![CDATA[
	var plugin_uri = '<?php echo $plugin_uri;?>';
	var blogURL = '<?php echo $blog_uri;?>';
	var GMapOnLoadCallbacks = [];
	//]]>
	</script>
</head>
<body>
<div id="all-wrap">
	<h1><?php echo $title;?></h1>
	<div id="layout-body">
<?php
}

function _GMap_printFooterForUI($jsName) {
?>
	<script type="text/javascript">
	//<![CDATA[
	(function($) {
	$(document).ready(function() {
		//$(window).unload(function() {GUnload();}); // not available in v3
		var i;
		for (i = 0; i < GMapOnLoadCallbacks.length; i++)
			GMapOnLoadCallbacks[i]();
	});
	})(jQuery);
	//]]>
	</script>
	</div>
</div>
</body>
</html>
<?php
}

function _GMap_normalizeAddress($address) {
	return trim(implode(' ', explode('/', $address)));
}

function _GMap_convertLegacyMapType($type) {
	$names = Array(
		'G_NORMAL_MAP' => 'ROADMAP',
		'G_SATELLITE_MAP' => 'SATELLITE',
		'G_HYBRID_MAP' => 'HYBRID',
		'G_PHYSICAL_MAP' => 'TERRAIN'
	);
	if ($names[$type])
		return $names[$type];
	return $type;
}

/* vim: set noet ts=4 sts=4 sw=4: */
?>
