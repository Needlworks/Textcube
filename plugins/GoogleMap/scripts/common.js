// Google Map Plugin Common Library
// depends on Google Maps API v3+

window.plugin = window.plugin || {};
plugin.gmap = {
	activeInfoWindow: null,
	geocoder: null,
	closeActiveInfoWindow: function() {
		if (this.activeInfoWindow) {
			google.maps.event.trigger(this.activeInfoWindow, 'closeclick');
			this.activeInfoWindow.close();
		}
		this.activeInfoWindow = null;
	},
	detectMobileSafari: function() {
		return navigator.userAgent.indexOf('iPhone') != -1 || navigator.userAgent.indexOf('iPod') != -1 || navigator.userAgent.indexOf('iPad') != -1;
	},
	normalizeAddress: function(address) {
		return address.split('/').join(' ');
	},
	sendCache: function(original_path, path, lat, lng) {
		jQuery.ajax({
			'type': 'POST',
			'url': servicePath + '/plugin/GMapCache/',
			'data': {
				'original_path': original_path,
				'path': path,
				'lat': lat,
				'lng': lng
			}
		});
		// Here, we don't need the cache result actually.
	}
};


/**
 * @brief 지역로그와 연동되어 특정 위치와 연관된 엔트리 정보를 marker 형태로 맵에 추가한다.
 * @param Array.<GeocodeResults> results	Geocoder::geocode() 메소드 호출의 결과
 * @param GeocodeStatus status				Geocoder::geocode() 메소드 호출에 의한 서버 응답
 * @param Map gmap			Map object
 * @param string address	화면에 표시될 주소 문자열
 * @param string title		화면에 표시될 링크 이름 문자열
 * @param string link		엔트리의 링크 URL
 * @param LatLngBounds boundary	모든 marker를 포함하는 최소 영역을 알기 위한 LatLngBounds 객체
 * @param Array locations			같은 위치에 여러 엔트리가 관련된 경우를 처리하기 위해 이미 처리된 엔트리들과 marker 정보를 담은 배열
 */
function GMap_addLocationMark(gmap, location_path, title, link, boundary, locations) {
	if (!plugin.gmap.geocoder)
		plugin.gmap.geocoder = new google.maps.Geocoder();
	var address = plugin.gmap.normalizeAddress(location_path);
	plugin.gmap.geocoder.geocode({
		'address': address
	}, function(results, status) {
		GMap_findLocationCallback(results, status, gmap, {'address': address, 'path': location_path, 'original_path': location_path}, title, link, boundary, locations);
	});
}

function GMap_addLocationMarkDirect(gmap, location_info, title, link, point, boundary, locations, cache) {
	var prev = null;
	var i;
	// Retrieve if any duplicated location exists for the given.
	for (i = 0; i < locations.length; i++) {
		if (locations[i].point.equals(point)) {
			prev = locations[i];
			break;
		}
	}
	if (prev == null) {
		// Create a new marker for this location.
		var marker = new google.maps.Marker({
			'position': point,
			'title': location_info.address.split(' ').pop(),
			'map': gmap
		});
		var info = new google.maps.InfoWindow({
			'position': point
		});
		var locative = {
			'point': point,
			'marker': marker,
			'infoWindow': info,
			'address': location_info.address,
			'address_parts': location_info.path.split('/'),
			'entries': new Array({'title': title, 'link': link})
		};
		locations.push(locative);
		info.setContent(GMap_buildLocationInfoHTML(locative));
		google.maps.event.addListener(marker, 'click', function() {
			plugin.gmap.closeActiveInfoWindow();
			info.open(marker.getMap(), marker);
			plugin.gmap.activeInfoWindow = info;
		});
		boundary.extend(point);
	} else {
		// Add information to the existing marker for here.
		prev.entries.push({'title': title, 'link': link});
		prev.infoWindow.setContent(GMap_buildLocationInfoHTML(prev));
	}
	if (cache)
		plugin.gmap.sendCache(location_info.original_path, location_info.path, point.lat(), point.lng());
	if (process_count != undefined)
		process_count++;
}

/**
 * @brief 지정한 locative 오브젝트로부터 info window에 표시할 HTML을 작성한다.
 * @param Object locative	특정 위치에 대한 Marker 및 관련 정보와 엔트리들에 대한 정보를 담은 오브젝트
 */
function GMap_buildLocationInfoHTML(locative) {
	var html = '<div class="GMapInfo" style="text-align:left"><h4>' + locative.address_parts[locative.address_parts.length - 1] + '에 얽힌 이야기</h4><ul>';
	var i;
	for (i = 0; i < locative.entries.length; i++) {
		html += '<li><a href="'+locative.entries[i].link+'">'+locative.entries[i].title+'</a></li>';
	}
	html += '</ul><address>'+locative.address+'</address></div>';
	return html;
}

/**
 * @brief (내부용 함수) geocoder.getLocations()에 의해 호출되는 비동기 콜백 함수
 */
function GMap_findLocationCallback(results, status, gmap, location_info, title, link, boundary, locations) {
	if (status == google.maps.GeocoderStatus.ZERO_RESULTS) {
		var new_path_parts = location_info.path.split('/').slice(0,-1);
		if (new_path_parts.length < 2) {
			// give up search...
			plugin.gmap.sendCache(location_info.original_path, location_info.path, null, null);
			if (process_count != undefined)
				process_count++;
		} else {
			// recursive reducing
			var new_address = new_path_parts.join(' ');
			var new_path = new_path_parts.join('/');
			if (new_path[0] != '/') new_path = '/' + new_path;
			geocoder.geocode({
				'address': new_address
			}, function(results, status) {
				GMap_findLocationCallback(results, status, gmap, {'address': new_address, 'path': new_path, 'original_path': location_info.original_path}, title, link, boundary, locations);
			});
		}
	} else if (status == google.maps.GeocoderStatus.OK) {
		var point = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());
		GMap_addLocationMarkDirect(gmap, location_info, title, link, point, boundary, locations, true);
	}
}

function GMap_createMap(container, options) {
	container.style.width = options.width + 'px';
	container.style.height = options.height + 'px';
	var map = new google.maps.Map(container, {
		'center': new google.maps.LatLng(options.center.latitude, options.center.longitude),
		'zoom': options.zoom,
		'mapTypeId': eval('google.maps.MapTypeId.' + options.type) || google.maps.MapTypeId.ROADMAP,
		'mapTypeControl': true,
		'scaleControl': true
	});
	var i;
	if (options.user_markers != undefined) {
		var create_marker = function(um) {
			var marker = new google.maps.Marker({
				'map': map,
				'position': new google.maps.LatLng(um.lat, um.lng)
			});
			if (um.title.trim() != '') {
				var info = new google.maps.InfoWindow({
					'content': '<div class="GMapInfo"><h4>'+um.title+'</h4><p>'+um.desc+'</p></div>'
				});
				google.maps.event.addListener(marker, 'click', function() {
					plugin.gmap.closeActiveInfoWindow();
					info.open(map, marker);
					plugin.gmap.activeInfoWindow = info;
				});
			}
		};
		for (i = 0; i < options.user_markers.length; i++) {
			create_marker(options.user_markers[i]);
		}
	}
	return map;
}

GProgressControl = function(map) {
	var container = document.createElement('div');
	var label = document.createElement('p');
	var progress = document.createElement('div');
	var progress_meter = document.createElement('div');
	container.appendChild(label);
	container.appendChild(progress);
	progress.appendChild(progress_meter);

	container.style.width = '180px';
	container.style.textAlign = 'center';
	label.style.fontFamily = 'Tahoma, Arial, sans-serif';
	label.style.fontSize = '8pt';
	label.style.padding = '0';
	label.style.margin = '0';
	progress.style.marginLeft = '40px';
	progress.style.width = '100px';
	progress.style.height = '4px';
	progress.style.padding = '1px';
	progress.style.border = '1px solid #666';
	progress.style.backgroundColor = 'white';
	progress.style.textAlign = 'left';
	progress_meter.style.width = '0';
	progress_meter.style.height = '100%';
	progress_meter.style.backgroundColor = '#393';

	this._container = container;
	this._label = label;
	this._progress = progress;
	this._progress_meter = progress_meter;
	this._map = map;
	this._inserted_index = map.controls[google.maps.ControlPosition.RIGHT].push(container) - 1;
}
GProgressControl.prototype.setLabel = function(text) {
	this._label.innerHTML = text;
}
GProgressControl.prototype.setProgress = function(val) { // val in 0..1
	this._progress_meter.style.width = parseInt(val * 100) + '%';
}
GProgressControl.prototype.remove = function() {
	this._map.controls[google.maps.ControlPosition.RIGHT].removeAt(this._inserted_index);
}

/* vim: set noet ts=4 sts=4 sw=4: */
