// Google Map Plugin UI Helper
// - depends on jQuery 1.2.6, jQuery UI plugin 1.6, and Google Maps API

var map;
var listener_onclick = null;
var user_markers = {};
var query_markers = {};
var icon_blue;

$(document).ready(function() {
	initializeMap();
	var container = $(map.getContainer());
	container
		.resizable({
			minWidth:300, maxWidth:800,
			minHeight:200, maxHeight:800,
			handles:'e,s,w,se,sw'
		})
		.bind('resize', function(ev) {
			map.checkResize();
			$('#inputWidth').val(container.width());
			$('#inputHeight').val(container.height());
		})
		.bind('mousewheel', function(ev) { ev.stopPropagation(); });
	$('#toggleMarkerAddingMode')
		.removeClass('toggled')
		.click(function(ev) {
			$(ev.target).toggleClass('toggled');
			if ($(ev.target).hasClass('toggled')) {
				listener_onclick = GEvent.addListener(map, 'click', GMap_onClick);
			} else {
				GEvent.removeListener(listener_onclick);
			}
		});
	$('#queryLocation').click(queryLocation);
	$('#inputQuery').bind('keypress', function(ev) { if (ev.which == 13) queryLocation(); });
	$('#applyBasicSettings').click(function() {
		var w = $('#inputWidth').val(), h = $('#inputHeight').val();
		container.width(w).height(h);
	});
	$('#doInsert').click(function() {
		if (!map)
			return;
		map.closeInfoWindow();
		var editor = window.opener.editor;
		if (!editor) {
			alert('The editor is not accessible.');
			return;
		}
		var options = {};
		var center = map.getCenter();
		options.center = {latitude: center.lat(), longitude: center.lng()};
		options.zoom = map.getZoom();
		options.width = container.width();
		options.height = container.height();
		options.type = getMapTypeStr();
		var compact_user_markers = new Array();
		var i = 0, id = '';
		for (id in user_markers) {
			compact_user_markers[i] = {
				'title': user_markers[id].title,
				'desc': user_markers[id].desc,
				'lat': user_markers[id].marker.getLatLng().lat(),
				'lng': user_markers[id].marker.getLatLng().lng()
			};
			i++;
		}
		options.user_markers = compact_user_markers;
		editor.command('Raw', '[##_GoogleMap|' + $.toJSON(options) + '|_##]');
		self.close();
	});
	//accordion = new Accordion($$('h2'), $$('.accordion-elem'));
	
	icon_blue = new GIcon(G_DEFAULT_ICON, pluginURL + '/images/marker_blue.png');
});

function queryLocation() {
	if (!geocoder)
		geocoder = new GClientGeocoder();
	var q = $('#inputQuery').val();
	closeQueryResult();
	geocoder.getLocations(q, function(response) {
		if (!response || response.Status.code != 200) {
			$('<div id="queryResult">검색 결과가 없습니다.</div>').insertAfter('#GoogleMapPreview');
		} else {
			$('<div id="queryResult"><ol></ol></div>').insertAfter('#GoogleMapPreview');
			for (var i = 0; i < response.Placemark.length; i++) {
				var place = response.Placemark[i];
				var point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
				var id = 'qm' + (new Date).valueOf() + (Math.ceil(Math.random()*90)+10);
				$('<li><a href="#" onclick="map.panTo(new GLatLng('+point.lat()+','+point.lng()+'))">'+place.address+'</a></li>').appendTo('#queryResult ol');
				map.setCenter(point);
				map.setZoom(17);
				var marker = new GMarker(point, {'title': '['+(i+1)+'] ' + place.address, 'icon': icon_blue});
				marker.bindInfoWindowHtml('<div class="queryMarkerInfo"><p><address>'+place.address+'</address></p><p><a href="#" onclick="convertToUserMarker(\''+id+'\')">마커 고정시키기</a></p></div>');
				map.addOverlay(marker);
				query_markers[id] = {'marker': marker, 'id': id, 'address': place.address, 'query': q};
			}
		}
		var container = map.getContainer();
		var pos = $(container).offset();
		// TODO: get the height of the whole document in a cross-browsing way
		var from_bottom = document.body.scrollHeight - (pos.top + $(container).height());
		$('<div style="text-align:right"><a href="#" class="ui-action" onclick="closeQueryResult();return false;">닫기</a></div>').appendTo('#queryResult');
		$('#queryResult').css({bottom:(from_bottom + 40)+'px', left:(pos.left + 60)+'px'}).fadeIn(400).fadeTo(200, 1);
	});
}

function closeQueryResult() {
	$('#queryResult').remove();
	for (id in query_markers) {
		map.removeOverlay(query_markers[id].marker);
	}
	query_markers = {};
}

function convertToUserMarker(id) {
	var um = GMap_onClick(null, query_markers[id].marker.getLatLng(), null);
	map.removeOverlay(query_markers[id].marker);
	um.title = query_markers[id].query;
	um.desc = query_markers[id].address;
	delete query_markers[id];
}

function getMapTypeStr() {
	switch (map.getCurrentMapType()) {
	case G_PHYSICAL_MAP:
		return 'G_PHYSICAL_MAP';
	case G_SATELLITE_MAP:
		return 'G_SATELLITE_MAP';
	case G_HYBRID_MAP:
		return 'G_HYBRID_MAP';
	case G_NORMAL_MAP:
	default:
		return 'G_NORMAL_MAP';
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
	map.removeOverlay(user_markers[id].marker);
	delete user_markers[id];
}

function GMap_onClick(overlay, latlng, overlaylatlng) {
	if (overlay == null) { // when empty area is clicked
		if (user_markers.length == 20) {
			alert('Too many markers!');
			return;
		}
		var marker = new GMarker(latlng, {'clickable': true, 'draggable': true, 'bouncy': true, 'title': 'Click to edit'});
		var id = 'um' + (new Date).valueOf() + (Math.ceil(Math.random()*90)+10);
		GEvent.addListener(marker, 'click', GMarker_onClick);
		GEvent.addListener(marker, 'infowindowbeforeclose', function() {
			user_markers[id].title = $('#info_title').val();
			user_markers[id].desc = $('#info_desc').val();
		});
		user_markers[id] = {'marker': marker, 'title': '', 'desc': '', 'id': id};
		map.addOverlay(marker);
		return user_markers[id];
	}
}

function GMarker_onClick(latlng) {
	var um = findUserMarker(this);
	var form = '<div class="GMapInfo">';
	form += '<p><label for="info_title">제목 : </label><input id="info_title" type="text" value="'+um.title+'" /></p>';
	form += '<p><label for="info_desc">설명 : </label><textarea id="info_desc" rows="3" cols="30">'+um.desc+'</textarea></p>';
	form += '<div style="text-align:right"><a href="javascript:void(0);" onclick="removeUserMarker(\''+um.id+'\');">삭제하기</div></div>';
	this.openInfoWindowHtml(form);
}
