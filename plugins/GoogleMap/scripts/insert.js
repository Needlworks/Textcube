// Google Map Plugin UI Helper
// - depends on jQuery 1.11+, jQuery UI plugin 1.11+, and Google Maps API v3+

var map;
var listener_onclick = null;
var user_markers = {};
var query_markers = {};
var icon_blue;

function initializeCustomizableMap() {
	var $container = $(map.getDiv());
	$container
		.resizable({
			minWidth:300, maxWidth:800,
			minHeight:200, maxHeight:800,
			handles:'e,s,w,se,sw'
		})
		.bind('resize', function(ev) {
			map.checkResize();
			$('#inputWidth').val($container.width());
			$('#inputHeight').val($container.height());
		})
		.bind('scroll', function(ev) { ev.stopPropagation(); });
	$('#toggleMarkerAddingMode')
		.removeClass('toggled')
		.click(function(ev) {
			$(ev.target).toggleClass('toggled');
			if ($(ev.target).hasClass('toggled')) {
				listener_onclick = google.maps.event.addListener(map, 'click', GMap_onClick);
			} else {
				google.maps.event.removeListener(listener_onclick);
			}
		});
	$('#queryLocation').click(queryLocation);
	$('#inputQuery').bind('keypress', function(ev) { if (ev.which == 13) queryLocation(); });
	$('#applyBasicSettings').click(function() {
		var w = $('#inputWidth').val(), h = $('#inputHeight').val();
		$container.width(w).height(h);
	});
	$('#doInsert').click(function() {
		if (!map)
			return;
		plugin.gmap.closeActiveInfoWindow();
		var editor = window.opener.editor;
		if (!editor) {
			alert('The editor is not accessible.');
			return;
		}
		var options = {};
		var center = map.getCenter();
		options.center = {latitude: center.lat(), longitude: center.lng()};
		options.zoom = map.getZoom();
		options.width = $container.width();
		options.height = $container.height();
		options.type = getMapTypeStr();
		var compact_user_markers = new Array();
		var i = 0, id = '';
		for (id in user_markers) {
			compact_user_markers[i] = {
				'title': user_markers[id].title,
				'desc': user_markers[id].desc,
				'lat': user_markers[id].marker.getPosition().lat(),
				'lng': user_markers[id].marker.getPosition().lng()
			};
			i++;
		}
		options.user_markers = compact_user_markers;
		editor.command('Raw', '[##_GoogleMap|' + JSON.stringify(options) + '|_##]');
		self.close();
	});
	icon_blue = new google.maps.MarkerImage(pluginURL + '/images/marker_blue.png');
}

function queryLocation() {
	if (!plugin.gmap.geocoder)
		plugin.gmap.geocoder = new google.maps.Geocoder();
	var q = $('#inputQuery').val();
	closeQueryResult();
	plugin.gmap.geocoder.geocode({'address': q}, function(results, status) {
		if (status == google.maps.GeocoderStatus.ZERO_RESULTS) {
			$('<div id="queryResult">검색 결과가 없습니다.</div>').insertAfter('#GoogleMapPreview');
		} else if (status == google.maps.GeocoderStatus.OK) {
			$('<div id="queryResult"><ol></ol></div>').insertAfter('#GoogleMapPreview');
			for (var i = 0; i < results.length; i++) {
				var position = results[i].geometry.location;
				var id = 'qm' + (new Date).valueOf() + (Math.ceil(Math.random()*90)+10);
				var address = '', j;
				for (j = 0; j < results[i].address_components.length; j++)
					address += results[i].address_components[j].long_name + ' ';
				$('<li><a href="#" onclick="map.panTo(new google.maps.LatLng('+position.lat()+','+position.lng()+'))">'+address.trim()+'</a></li>').appendTo('#queryResult ol');
				map.setCenter(position);
				map.setZoom(15);
				var marker = new google.maps.Marker({
					'position': position,
					'title': '['+(i+1)+'] ' + address,
					'icon': icon_blue
				});
				var info = new google.maps.InfoWindow({
					'content': '<div class="queryMarkerInfo"><p><address>'+address+'</address></p><p><a href="#" onclick="convertToUserMarker(\''+id+'\')">마커 고정시키기</a></p></div>'
				});
				google.maps.event.addListener(marker, 'click', function() {
					plugin.gmap.closeActiveInfoWindow();
					info.open(map, marker);
					plugin.gmap.activeInfoWindow = info;
				});
				query_markers[id] = {
					'id': id,
					'marker': marker,
					'info': info,
					'address': address,
					'query': q
				};
			}
		} else {
			$('<div id="queryResult">오류가 발생하였습니다. (' + status + ')</div>').insertAfter('#GoogleMapPreview');
		}
		var $container = $(map.getDiv());
		var pos = $container.offset();
		// TODO: get the height of the whole document in a cross-browsing way
		var from_bottom = document.body.scrollHeight - (pos.top + $container.height());
		$('<div style="text-align:right"><a href="#" class="ui-action" onclick="closeQueryResult();return false;">닫기</a></div>').appendTo('#queryResult');
		$('#queryResult').css({'z-index':100000, bottom:(from_bottom + 40)+'px', left:(pos.left + 60)+'px'}).fadeIn(400);
	});
}

function closeQueryResult() {
	$('#queryResult').remove();
	for (id in query_markers) {
		query_markers[id].marker.setMap(null);
	}
	query_markers = {};
}

function convertToUserMarker(id) {
	var um = GMap_onClick({latLng: query_markers[id].marker.getPosition()});
	query_markers[id].marker.setMap(null);
	um.title = query_markers[id].query;
	um.desc = query_markers[id].address;
	delete query_markers[id];
}

function getMapTypeStr() {
	switch (map.getMapTypeId()) {
	case google.maps.MapTypeId.TERRAIN:
		return 'TERRAIN';
	case google.maps.MapTypeId.SATELLITE:
		return 'SATELLITE';
	case google.maps.MapTypeId.HYBRID:
		return 'HYBRID';
	case google.maps.MapTypeId.ROADMAP:
	default:
		return 'ROADMAP';
	}
}

function findUserMarker(marker) {
	var id;
	for (id in user_markers) {
		if (user_markers[id].marker == marker)
			return user_markers[id];
	}
	return null;
}

function findUserMarkerById(id) {
	try {
		return user_markers[id];
	} catch (e) {
		return null;
	}
}

function removeUserMarker(id) {
	plugin.gmap.activeInfoWindow = null;
	user_markers[id].marker.setMap(null);
	delete user_markers[id];
}

function GMap_onClick(e) {
	var latlng = e.latLng;
	if (user_markers.length == 20) {
		alert('Too many markers!');
		return;
	}
	var id = 'um' + (new Date).valueOf() + (Math.ceil(Math.random()*90)+10);
	var marker = new google.maps.Marker({
		'position': latlng,
		'map': map,
		'title': 'Click to edit',
		'draggable': true
	});
	var info = new google.maps.InfoWindow({
		'content' : ''
	});
	google.maps.event.addListener(marker, 'click', GMarker_onClick);
	google.maps.event.addListener(info, 'closeclick', function() {
		user_markers[id].title = $(user_markers[id].iw_dom).find('#info_title').val();
		user_markers[id].desc = $(user_markers[id].iw_dom).find('#info_desc').val();
	});
	user_markers[id] = {
		'id': id,
		'marker': marker,
		'info': info,
		'title': '',
		'desc': ''
	};
	return user_markers[id];
}

function GMarker_onClick(e) {
	plugin.gmap.closeActiveInfoWindow();
	var um = findUserMarker(this);
	var form_str = '<div class="GMapInfo">';
	form_str += '<p><label for="info_title">제목 : </label><input id="info_title" type="text" value="'+um.title+'" /></p>';
	form_str += '<p><label for="info_desc">설명 : </label><textarea id="info_desc" rows="3" cols="30">'+um.desc+'</textarea></p>';
	form_str += '<div style="text-align:right"><a href="javascript:void(0);" onclick="removeUserMarker(\''+um.id+'\');">삭제하기</div></div>';
	um.info.setContent(form_str);
	um.info.open(map, um.marker);
	plugin.gmap.activeInfoWindow = um.info;
	// Preserve reference to DOM
	um.iw_dom = $('.GMapInfo')[0];
}

/* vim: set ts=4 sts=4 sw=4 noet: */
