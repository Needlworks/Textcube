// Google Map Plugin UI Helper
// - depends on MooTools and Google AJAX API with Maps

// Array Remove - By John Resig (MIT Licensed)
if (!Array.prototype.remove) {
	Array.prototype.remove = function(from, to) {
	  var rest = this.slice((to || from) + 1 || this.length);
	  this.length = from < 0 ? this.length + from : from;
	  return this.push.apply(this, rest);
	};
}

var map;
var listener_onclick = null;
var user_markers = new Array();

function initialize() {
	initializeMap();
	$('toggleMarkerAddingMode').store('toggled', false);
	$('toggleMarkerAddingMode').addEvent('click', function() {
		this.store('toggled', !this.retrieve('toggled'));
		if (this.retrieve('toggled')) {
			this.setProperty('class', 'toggled');
			listener_onclick = GEvent.addListener(map, 'click', GMap_onClick);
		} else {
			this.setProperty('class', '');
			GEvent.removeListener(listener_onclick);
		}
	});
	$('applyBasicSettings').addEvent('click', function() {
		var gmp = $(map.getContainer());
		var w = $('inputWidth').value, h = $('inputHeight').value;
		if (w < 150 || h < 150) {
			alert('지도 크기가 너무 작습니다.');
			return;
		}
		gmp.set('styles', {
			'width': w + 'px',
			'height': h + 'px'
		});
		map.checkResize();
	});
	$('doInsert').addEvent('click', function() {
		if (!map)
			return;
		try {
			var editor = window.opener.editor;
			var options = {};
			var center = map.getCenter();
			options.center = {};
			options.center.latitude = center.lat();
			options.center.longitude = center.lng();
			options.zoom = map.getZoom();
			options.width = $('GoogleMapPreview').getSize().x;
			options.height = $('GoogleMapPreview').getSize().y;
			var compact_user_markers = new Array(), i;
			for (i = 0; i < user_markers.length; i++) {
				compact_user_markers[i] = {
					'title': user_markers[i].title,
					'desc': user_markers[i].desc,
					'lat': user_markers[i].marker.getLatLng().lat(),
					'lng': user_markers[i].marker.getLatLng().lng()
				};
			}
			options.user_markers = compact_user_markers;
			editor.command('Raw', '[##_GoogleMap|' + JSON.encode(options) + '|_##]');
			self.close();
		} catch (e) {
			alert('Parent window is not accessible. Is it closed?');
		}
	});
}

function findUserMarker(marker) {
	var i;
	for (i = 0; i < user_markers.length; i++) {
		if (user_markers[i].marker == marker)
			return user_markers[i];
	}
	return null;
}

function findUserMarkerById(id) {
	var i;
	for (i = 0; i < user_markers.length; i++) {
		if (user_markers[i].id == id)
			return user_markers[i];
	}
	return null;
}

function removeUserMarker(id) {
	var i;
	for (i = 0; i < user_markers.length; i++) {
		if (user_markers[i].id == id) {
			map.removeOverlay(user_markers[i].marker);
			user_markers.remove(i);
			break;
		}
	}
}

function GMap_onClick(overlay, latlng, overlaylatlng) {
	if (overlay == null) { // when empty area is clicked
		if (user_markers.length == 20) {
			alert('Too many markers!');
			return;
		}
		var marker = new GMarker(latlng, {'clickable': true, 'draggable': true, 'bouncy': true, 'title': 'Click to edit'});
		GEvent.addListener(marker, 'click', GMarker_onClick);
		GEvent.addListener(marker, 'infowindowbeforeclose', function() {
			var um = findUserMarker(this);
			um.title = $('info_title').value;
			um.desc = $('info_desc').value;
		});
		user_markers.push({'marker': marker, 'title': '', 'desc': '', 'id': 'um'+(new Date).valueOf()+(Math.ceil(Math.random()*90)+10)});
		map.addOverlay(marker);
	}
}

function GMarker_onClick(latlng) {
	var um = findUserMarker(this);
	var form = '<div class="GMapInfo">';
	form += '<p><label for="info_title">제목 : </label><input id="info_title" type="text" value="'+um.title+'" /></p>';
	form += '<p><label for="info_desc">설명 : </label><textarea id="info_desc" rows="5" cols="30">'+um.desc+'</textarea></p>';
	form += '<p style="text-align:right"><a href="javascript:void(0);" onclick="removeUserMarker(\''+um.id+'\');">삭제하기</p></div>';
	this.openInfoWindowHtml(form);
}
