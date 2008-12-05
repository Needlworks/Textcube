// Google Map Plugin UI Helper
// - depends on jQuery 1.2.6, jQuery UI plugin 1.6, and Google Maps API

var map;
var listener_onclick = null;
var user_markers = {};
//var accordion;

$(function() {
	initializeMap();
	var container = $(map.getContainer());
	container
		.resizable({
			minWidth:300, maxWidth:800,
			minHeight:200, maxHeight:800,
			handles:{e:$('#GMapResizerE'), s:$('#GMapResizerS')}
		})
		.bind('resize', function(ev) {
			map.checkResize();
			$('#inputWidth').value = container.width();
			$('#inputHeight').value = container.height();
		})
		.bind('mousewheel', function(ev) { ev.preventDefault(); });
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
	$('#inputQuery').bind('keypress', function(ev) { if (ev.code == 13) queryLocation(); });
	$('#applyBasicSettings').click(function() {
		var w = $('#inputWidth').val(), h = $('#inputHeight').val();
		container.width(w).height(h);
	});
	$('#doInsert').click(function() {
		if (!map)
			return;
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
		editor.command('Raw', '[##_GoogleMap|' + JSON.encode(options) + '|_##]');
		self.close();
	});
	//accordion = new Accordion($$('h2'), $$('.accordion-elem'));
});

function queryLocation() {
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
	var i;
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
	}
}

function GMarker_onClick(latlng) {
	var um = findUserMarker(this);
	var form = '<div class="GMapInfo">';
	form += '<p><label for="info_title">제목 : </label><input id="info_title" type="text" value="'+um.title+'" /></p>';
	form += '<p><label for="info_desc">설명 : </label><textarea id="info_desc" rows="3" cols="30">'+um.desc+'</textarea></p>';
	form += '<p style="text-align:right"><a href="javascript:void(0);" onclick="removeUserMarker(\''+um.id+'\');">삭제하기</p></div>';
	this.openInfoWindowHtml(form);
}
