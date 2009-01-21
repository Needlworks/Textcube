// requires jQuery's UI plugin 1.6
(function($){

function getWidgetPosition(id) {
	var all = $('#widget-container-0').sortable('toArray');
	all.push('TextcubeSeparator');
	$.merge(all, $('#widget-container-1').sortable('toArray'));
	all.push('TextcubeSeparator');
	$.merge(all, $('#widget-container-2').sortable('toArray'));

	var p = 0, i = 0;
	for (i = 0; i < all.length; i++) {
		if (all[i].trim() != '') {
			if (all[i]== id)
				return p;
			p++;
		}
	}
	return -1;
}

if (editMode) {
	$(function() { // dom ready
		$('.widget-container').sortable({
			connectWith: ['.widget-container'],
			start: function(ev, ui) {
				var prev_pos = getWidgetPosition(ui.item.attr('id'));
				$.data(ui.item[0], 'prev_pos', prev_pos);
				$.data(ui.item[0], 'sent', false);
			},
			update: function(ev, ui) {
				if ($.data(ui.item[0], 'sent')) // prevent duplicated triggering when moving between different lists
					return;
				var cur_pos = getWidgetPosition(ui.item.attr('id'));
				var prev_pos = $.data(ui.item[0], 'prev_pos');
				var rel = 0;
				if (prev_pos != undefined)
					rel = cur_pos - prev_pos;
				$.removeData(ui.item, 'prev_pos');

				var requestURL = "dashboard?ajaxcall=true&edit=true&pos=" + prev_pos + "&rel=" + rel;
				$.post(requestURL);
				$.data(ui.item[0], 'sent', true);
			},
			placeholder: 'widget-state-highlight',
			opacity: 0.65
		});
		$('.widget-container .section').css('cursor', 'pointer');
	});
}

})(jQuery);

