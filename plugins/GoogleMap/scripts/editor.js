// Google Map Plugin WYSISYG Helper
// - depends on EAF4.js, Google Map API v2, and jQuery 1.3.2 or higher included in Textcube 1.8 or higher.

function GMapTool_insertMap() {
	window.open(blogURL + '/plugin/GMapCustomInsert/', 'GMapTool_Insert', 'menubar=no,toolbar=no,width=550,height=680,scrollbars=yes');
}

function GMapTool_getLocation() {
	var $ = jQuery;
	if (navigator.geolocation) {
		$('#googlemap-geolocation-preview').empty()
		.append('<p style="text-align:center">Loading...</p>')
		.show()
		.css({
			'top': ($('#gmap-getLocation').offset().top - 85) + 'px',
			'left': ($('#gmap-getLocation').offset().left) + 'px',
			'width': '300px'
		});
		navigator.geolocation.getCurrentPosition(function(pos) {
			$('#googlemap-geolocation-preview').empty()
			.append($('<a>').attr('id', 'googlemap-geolocation-preview-close').attr('href', '#close').text('close'))
			.append($('<img>').attr('src', 'http://maps.google.com/maps/api/staticmap?center=' + pos.coords.latitude + ',' + pos.coords.longitude + '&zoom=12&size=300x180&maptype=roadmap&sensor=true&markers=color:red|' + pos.coords.latitude + ',' + pos.coords.longitude));
			$('input[name=latitude]').val(pos.coords.latitude);
			$('input[name=longitude]').val(pos.coords.longitude);
			$('#googlemap-geolocation-preview-close').click(function(e) {
				$('#googlemap-geolocation-preview').hide();
				e.preventDefault();
				return false;
			});
		}, function(error) {
			$('#googlemap-geolocation-preview').hide();
			switch (error.code) {
			case 1:
				msg = '권한 없음';
				break;
			case 2:
				msg = '위치정보 없음'
				break;
			case 3:
				msg = '시간 제한 초과'
				break;
			default:
				msg = '알 수 없는 오류';
			}
			alert('위치 정보를 가져오는 중 오류가 발생하였습니다. (' + msg + ')');
		});
	} else {
		alert('현재 웹브라우저는 Geolocation 기능을 지원하지 않습니다.');
	}
}

jQuery(document).ready(function() {
	var $ = jQuery;
	// Create the hiidden input fields for location coordinates.
	if ($('input[name=latitude]').length == 0) {
		$('#editor-form').append('<input type="hidden" name="latitude" value="" /><input type="hidden" name="longitude" value="" />');	
		$('#editor-form').append('<div id="googlemap-geolocation-preview" style="display:none"></div>');
	}
});

//STD.addUnloadEventListener(function() { GUnload(); });
