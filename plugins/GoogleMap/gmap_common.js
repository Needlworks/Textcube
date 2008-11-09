// Google Map Plugin Common Library
// depends on Google Maps API

var geocoder = null;
var locationMap = null;

function GMap_addLocationMark(location_path, title) {
	if (!geocoder)
		geocoder = new GClientGeocoder();
	var address = location_path.replace(/\//g, ' ').trim();
	geocoder.getLocations(address, function(response) {GMap_findLocationCallback(response, address, title);});
}

function GMap_findLocationCallback(response, address, title) {
	if (!response || response.Status.code != 200) {
		// alert('Can\'t retrieve this address "'+address+'"');
	} else {
		place = response.Placemark[0];
		point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
		marker = new GMarker(point);
		marker.bindInfoWindowHtml('<div class="GMapInfo"><h4>'+title+'</h4><p>'+address+'</p></div>');
		locationMap.addOverlay(marker);
	}
}
