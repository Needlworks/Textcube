// Google Map Plugin UI Helper
// - depends on MooTools and Google AJAX API with Maps

var map;

function initialize() {
	initializeMap();
	$('toggleMarkerAddingMode').store('toggled', false);
	$('toggleMarkerAddingMode').addEvent('click', function() {
		this.store('toggled', !this.retrieve('toggled'));
		if (this.retrieve('toggled')) {
			this.setProperty('class', 'toggled');
			// TODO: attach click event handler to the GMap object.
		} else {
			this.setProperty('class', '');
			// TODO: detach click event handler to the GMap object.
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
			editor.command('Raw', '[##_GoogleMap|' + JSON.encode(options) + '|_##]');
			self.close();
		} catch (e) {
			alert('Parent window is not accessible. Is it closed?');
		}
	});
}
