// Google Map Plugin Common Library
// depends on Google Maps API

var geocoder = null;

function GMap_normalizeAddress(address) {
	return address.split('/').join(' ');
}

function GMap_sendCache(original_path, path, lat, lng) {
	var xh = GXmlHttp.create();
	xh.open('POST', servicePath + '/plugin/GMapCache/', true);
	xh.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xh.send('original_path=' + encodeURIComponent(original_path) + '&path=' + encodeURIComponent(path) + (lat != null ? '&lat='+lat+'&lng='+lng : ''));
	// Here, we don't need the cache result actually.
}

/**
 * @brief 지역로그와 연동되어 특정 위치와 연관된 엔트리 정보를 marker 형태로 맵에 추가한다.
 * @param Object response	GGlientGeocoder::getLocations() 메소드 호출에 의한 서버 응답 오브젝트
 * @param GMap2 gmap		GMap2 타입의 오브젝트
 * @param string address	화면에 표시될 주소 문자열
 * @param string title		화면에 표시될 링크 이름 문자열
 * @param string link		엔트리의 링크 URL
 * @param GLatLngBounds boundary	모든 marker를 포함하는 최소 영역을 알기 위한 GLatLngBounds 객체
 * @param Array locations			같은 위치에 여러 엔트리가 관련된 경우를 처리하기 위해 이미 처리된 엔트리들과 marker 정보를 담은 배열
 */
function GMap_addLocationMark(gmap, location_path, title, link, boundary, locations) {
	if (!geocoder)
		geocoder = new GClientGeocoder();
	var address = GMap_normalizeAddress(location_path);
	geocoder.getLocations(address, function(response) {GMap_findLocationCallback(response, gmap, {'address': address, 'path': location_path, 'original_path': location_path}, title, link, boundary, locations);});
}

function GMap_addLocationMarkDirect(gmap, location_info, title, link, point, boundary, locations, cache) {
	var prev = null;
	var i;
	// Check duplicated locations
	for (i = 0; i < locations.length; i++) {
		if (locations[i].point.equals(point)) {
			prev = locations[i];
			break;
		}
	}
	if (prev == null) {
		// Create a new marker for this location
		var marker = new GMarker(point, {'title': location_info.address.split(' ').pop()});
		var locative = {
			'point': point,
			'marker': marker,
			'address': location_info.address,
			'address_parts': location_info.path.split('/'),
			'entries': new Array({'title': title, 'link': link})
		};
		locations.push(locative);
		marker.bindInfoWindowHtml(GMap_buildLocationInfoHTML(locative));
		gmap.addOverlay(marker);
		boundary.extend(point);
	} else {
		// Add information to the existing marker for here
		prev.entries.push({'title': title, 'link': link});
		prev.marker.bindInfoWindowHtml(null);
		prev.marker.bindInfoWindowHtml(GMap_buildLocationInfoHTML(prev));
	}
	if (cache)
		GMap_sendCache(location_info.original_path, location_info.path, point.lat(), point.lng());
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
function GMap_findLocationCallback(response, gmap, location_info, title, link, boundary, locations) {
	if (!response || response.Status.code != 200) {
		var new_path_parts = location_info.path.split('/').slice(0,-1);
		if (new_path_parts.length < 2) {
			// give up search...
			GMap_sendCache(location_info.original_path, location_info.path, null, null);
			if (process_count != undefined)
				process_count++;
		} else {
			// recursive reducing
			var new_address = new_path_parts.join(' ');
			var new_path = new_path_parts.join('/');
			if (new_path[0] != '/') new_path = '/' + new_path;
			geocoder.getLocations(new_address, function(response) {
				GMap_findLocationCallback(response, gmap, {'address': new_address, 'path': new_path, 'original_path': location_info.original_path}, title, link, boundary, locations);
			});
		}
	} else {
		var place = response.Placemark[0];
		var point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
		GMap_addLocationMarkDirect(gmap, location_info, title, link, point, boundary, locations, true);
	}
}

function GMap_CreateMap(container, options) {
	container.style.width = options.width + 'px';
	container.style.height = options.height + 'px';
	var map = new GMap2(container);
	var i;
	map.setMapType(eval(options.type) || G_HYBRID_MAP);
	map.addMapType(G_PHYSICAL_MAP);
	map.addControl(new GHierarchicalMapTypeControl());
	map.addControl(new GLargeMapControl());
	map.addControl(new GScaleControl());
	map.setCenter(new GLatLng(options.center.latitude, options.center.longitude), options.zoom);
	if (options.user_markers != undefined) {
		for (i = 0; i < options.user_markers.length; i++) {
			var um = options.user_markers[i];
			var marker = new GMarker(new GLatLng(um.lat, um.lng));
			if (um.title.trim() != '')
				marker.bindInfoWindowHtml('<div class="GMapInfo"><h4>'+um.title+'</h4><p>'+um.desc+'</p></div>');
			map.addOverlay(marker);
		}
	}
	return map;
}

GProgressControl = function() {}
GProgressControl.prototype = new GControl();
GProgressControl.prototype.initialize = function(map) {
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
	map.getContainer().appendChild(container);
	return container;
}
GProgressControl.prototype.getDefaultPosition = function() {
	return new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(10, 50));
}
GProgressControl.prototype.setLabel = function(text) {
	this._label.innerHTML = text;
}
GProgressControl.prototype.setProgress = function(val) { // val in 0..1
	this._progress_meter.style.width = parseInt(val * 100) + '%';
}

/* vim: set noet ts=4 sts=4 sw=4: */
