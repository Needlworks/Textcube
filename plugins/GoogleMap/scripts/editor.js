// Google Map Plugin WYSISYG Helper
// - depends on EAF4.js, Google Map API v3, and jQuery 1.4 or higher included in Textcube 1.8 or higher.

function GMapTool_insertMap() {
	window.open(blogURL + '/plugin/GMapCustomInsert/', 'GMapTool_Insert', 'menubar=no,toolbar=no,width=550,height=680,scrollbars=yes');
}

function GMapTool_getLocation() {
	var $ = jQuery;
	var is_iphone = navigator.userAgent.toLowerCase().indexOf('iphone') != -1;
	if (navigator.geolocation) {
		var offset = $('#gmap-getLocation').offset();
		var height = $('#gmap-getLocation').outerHeight() - (is_iphone ? $(window).scrollTop() : 0);
		$('#googlemap-geolocation-preview')
		.css({'width': '240px'})
		.empty().append('<p style="text-align:center"><img src="' + pluginURL + '/images/icon_loading.gif" style="vertical-align:middle" />Loading...</p>');
		$('#googlemap-geolocation-container').css({'top': offset.top + height, 'left': offset.left - 75}).show();
		navigator.geolocation.getCurrentPosition(function(pos) {
			$('#googlemap-geolocation-preview')
			.empty().append($('<a>').attr('id', 'googlemap-geolocation-preview-close').attr('href', '#close').text('close'))
			.append($('<img>').attr('src', 'http://maps.google.com/maps/api/staticmap?center=' + pos.coords.latitude + ',' + pos.coords.longitude + '&zoom=12&size=240x160&maptype=roadmap&sensor=true&markers=color:red|' + pos.coords.latitude + ',' + pos.coords.longitude));
			$('input[name=latitude]').val(pos.coords.latitude);
			$('input[name=longitude]').val(pos.coords.longitude);
			$('#googlemap-geolocation-preview-close').click(function(e) {
				$('#googlemap-geolocation-container').hide();
				e.preventDefault();
				return false;
			});
		}, function(error) {
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
			$('#googlemap-geolocation-container').hide();
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
		$(document.body).append('<div id="googlemap-geolocation-container" style="display:none"><canvas id="googlemap-geolocation-container-arrow" width="10" height="7"></canvas><div id="googlemap-geolocation-preview"></div></div>');
		// Make a small arrow indicating the get locaction button.
		var e = document.getElementById('googlemap-geolocation-container-arrow');
		if (e.getContext) {
			var ctx = e.getContext('2d');
			ctx.fillStyle = '#fff';
			ctx.beginPath();
			ctx.moveTo(5, 0);
			ctx.lineTo(10, 7);
			ctx.lineTo(0, 7);
			ctx.fill();
			ctx.strokeStyle = '#ccc';
			ctx.beginPath();
			ctx.moveTo(5, 0);
			ctx.lineTo(10, 7);
			ctx.closePath();
			ctx.stroke();
			ctx.beginPath();
			ctx.moveTo(5, 0);
			ctx.lineTo(0, 7);
			ctx.closePath();
			ctx.stroke();
		}
	}
});

//STD.addUnloadEventListener(function() { GUnload(); });
