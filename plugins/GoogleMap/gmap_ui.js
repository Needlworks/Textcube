// Google Map Plugin UI Helper
// - depends on MooTools and Google AJAX API with Maps

var map;

function initialize() {
	initializeMap();
	$('toggleMarkerAddingMode').addEvent('click', function() {
		alert('not implemented yet');
	});
	$('applyBasicSettings').addEvent('click', function() {
		var gmp = $('GoogleMapPreview');
		// TODO: 현재 동작하지 않음
		gmp.setProperty('width', $('inputWidth').value);
		gmp.setProperty('height', $('inputHeight').value);
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
			editor.command('Raw', '[##_GoogleMap|' + JSON.encode(options) + '|_##]');
		} catch (e) {
			alert('Parent window is not accessible. Is it closed?');
		} finally {
			self.close();
		}
	});
}
