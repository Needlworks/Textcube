// Google Map Plugin Common Library
// depends on Google Maps API

var geocoder = null;
var locationMap = null;

function GMap_addLocationMark(gmap, location_path, title, link, boundary, locations) {
	if (!geocoder)
		geocoder = new GClientGeocoder();
	var address = location_path.replace(/\//g, ' ').trim();
	geocoder.getLocations(address, function(response) {GMap_findLocationCallback(response, gmap, address, title, link, boundary, locations);});
}

function GMap_buildLocationInfoHTML(locative) {
	var html = '<div class="GMapInfo"><h4>이 곳에 얽힌 이야기</h4><ul>';
	for (i = 0; i < locative.entries.length; i++) {
		html += '<li><a href="'+locative.entries[i].link+'">'+locative.entries[i].title+'</a></li>'
	}
	html += '</ul><address>'+locative.address+'</address></div>';
	return html;
}

function GMap_findLocationCallback(response, gmap, address, title, link, boundary, locations) {
	if (!response || response.Status.code != 200) {
		// alert('Can\'t retrieve this address "'+address+'"');
	} else {
		var place = response.Placemark[0];
		var point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
		var prev = null;
		// Check duplicated locations
		for (i = 0; i < locations.length; i++) {
			if (locations[i].point.equals(point))
				prev = locations[i];
		}
		if (prev == null) {
			// Create a new marker for this location
			var marker = new GMarker(point);
			var locative = {
				'point': point,
				'marker': marker,
				'address': address,
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
	}
	if (process_count != undefined)
		process_count++;
}
