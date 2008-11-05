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
		$target .= "<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$api_key\"></script><!-- Google Map Plugin -->\n";
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
		$target .= "<script type=\"text/javascript\" src=\"http://maps.google.com/maps?file=api&amp;v=2&amp;key=$api_key\"></script><!-- Google Map Plugin -->\n";
		$target .= "<script type=\"text/javascript\">
		//<![CDATA[
		var pluginURL = '$pluginURL';
		var blogURL = '$blogURL';
		//]]>
		</script>";
		$target .= "<script type=\"text/javascript\" src=\"$pluginURL/gmap_helper.js\"></script>\n";
	}
	return $target;
}

function GoogleMap_AddToolbox($target) {
	global $pluginURL;
	$target .= "<img src=\"$pluginURL/images/gmap_toolbar.png\" border=\"0\" alt=\"구글맵 추가하기\" onclick=\"GMapTool_Insert();\" style=\"cursor:pointer\" />\n";
	return $target;
}

function GoogleMap_View($target, $mother) {
	global $gmap_msg;
	global $configVal, $pluginURL;
	requireComponent('Textcube.Function.Setting');
	$config = setting::fetchConfigVal($configVal);
	$matches = array();
	$offset = 0;
	while (preg_match('/\[##_GoogleMap((\|[^|]+)+)?_##\]/', $target, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
		$parsed_params = explode('|', $matches[1][0]);
		$params = array();
		// TODO: customize these parameters in the WYSIWYG editor.
		$id = 'GMapContainer'.$mother.rand();
		$width = 450; $height = 400;
		$lat = !isset($params['latitude']) ? $config['latitude'] : $params['latitude'];
		$lng = !isset($params['longitude']) ? $config['longitude'] : $params['longitude'];
		$zoom = !isset($params['zoom']) ? 10 : $params['zoom'];
		$default_type = 'G_HYBRID_MAP';
		ob_start();
?>
		<!-- TOREMOVE: <?php print_r($parsed_params); ?> -->
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
	_GMap_printHeaderForUI('구글맵 삽입하기');
	// TODO: 각종 옵션 설정 UI
	// TODO: 주소 추출 UI
	// - TODO: 포스트 내용 텍스트 얻어오기 및 주소 정보 추출
	// - TODO: Google API를 이용한 geocoding 또는 map search
	_GMap_printFooterForUI();
}

function _GMap_printHeaderForUI($title) {
	global $pluginURL, $blogURL, $service, $adminSkinSetting;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Google Map Plugin: <?php echo $title;?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/basic.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/post.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/edit.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo $pluginURL;?>/ui.css" />
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
?>
