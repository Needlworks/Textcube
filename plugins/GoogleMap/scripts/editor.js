// Google Map Plugin WYSISYG Helper
// - depends on EAF4.js, Google Map API v3, and jQuery 1.4 or higher included in Textcube 1.8 or higher.

(function ($) {
window.GMapTool_insertMap = function() {
	window.open(blogURL + '/plugin/GMapCustomInsert/', 'GMapTool_Insert', 'menubar=no,toolbar=no,width=550,height=680,scrollbars=yes');
}

window.GMapTool_attachLocation = function() {
	if (plugin.gmap.isLocationAttached) {
		if (confirm(_t('첨부된 위치 정보를 제거하시겠습니까?'))) {
			$('input[name=latitude]').val('');
			$('input[name=longitude]').val('');
			plugin.gmap.isLocationAttached = false;
			$('#googlemap-attachLocation').text(_t('현재 위치 첨부하기'));
		}
	} else {
		if (navigator.geolocation) {
			var offset = $('#googlemap-attachLocation').offset();
			var height = parseInt($('#googlemap-attachLocation').outerHeight() - (plugin.gmap.detectMobileSafari() ? $(window).scrollTop() : 0));
			$('#googlemap-geolocation-preview')
			.css({'width': '240px'})
			.empty().append('<p style="text-align:center"><img src="' + pluginURL + '/images/icon_loading.gif" style="vertical-align:middle" />&nbsp;Loading...</p>');
			$('#googlemap-geolocation-container').css({'top': parseInt(offset.top + height), 'left': parseInt(offset.left - 75)}).show();
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
				plugin.gmap.isLocationAttached = true;
				$('#googlemap-attachLocation').text(_t('첨부된 위치 제거하기'));
			}, function(error) {
				switch (error.code) {
				case 1:
					msg = _t('권한 없음');
					break;
				case 2:
					msg = _t('위치정보 없음');
					break;
				case 3:
					msg = _t('시간 제한 초과');
					break;
				default:
					msg = _t('알 수 없는 오류');
				}
				alert(_t('위치 정보를 가져오지 못하였습니다.') + ' (' + msg + ')');
				$('#googlemap-geolocation-container').hide();
			});
		} else {
			alert(_t('현재 웹브라우저는 Geolocation 기능을 지원하지 않습니다.'));
		}
	}
}

$(document).ready(function() {
	// Create the hiidden input fields for location coordinates.
	plugin.gmap.isLocationAttached = false;
	if ($('input[name=latitude]').length == 0) {
		$('#editor-form').append('<input type="hidden" name="latitude" value="" /><input type="hidden" name="longitude" value="" />');
	} else {
		if ($('input[name=latitude]').val() && $('input[name=longitude]').val()) {
			plugin.gmap.isLocationAttached = true;
			$('#googlemap-attachLocation').text(_t('첨부된 위치 제거하기'));
		}
	}
	$('body').append('<div id="googlemap-geolocation-container" style="display:none"><canvas id="googlemap-geolocation-container-arrow" width="10" height="7"></canvas><div id="googlemap-geolocation-preview"></div></div>');
	// Make a small arrow indicating the get locaction button.
	var e = document.getElementById('googlemap-geolocation-container-arrow');
	if (e.getContext) {
		var ctx = e.getContext('2d');
		ctx.fillStyle = $('#googlemap-geolocation-preview').css('background-color');
		ctx.beginPath();
		ctx.moveTo(5, 0); ctx.lineTo(10, 7); ctx.lineTo(0, 7);
		ctx.fill();
		ctx.strokeStyle = $('#googlemap-geolocation-preview').css('border-top-color');
		ctx.beginPath();
		ctx.moveTo(5, 0); ctx.lineTo(10, 7);
		ctx.closePath(); ctx.stroke();
		ctx.beginPath();
		ctx.moveTo(5, 0); ctx.lineTo(0, 7);
		ctx.closePath(); ctx.stroke();
	}
});
})(jQuery);
