/**
 *
 * Copyright (c) 2007 Tom Deater (http://www.tomdeater.com)
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 * 
 */
 


(function($) {
	$.fn.charCounter = function (max, settings) {
		max = max || 100;
		settings = $.extend({
			container: "<span></span>",
			classname: "charcounter",
			format: "(%1 characters remaining)",
			format2: "(%1 characters overflow)",
			pulse: false,
			delay: 0
		}, settings);
		var p, timeout;
	    var convText = '';
	    var tempText = '';
	    var tempLength = '';
		var disabledChk = '';
		var temp = '';
		disabledChk = $('#disabledChk').attr('value');
		function count(el, container) {
			el = $(el);
			convText = el.val();
			if (settings.delay > 0) {
				if (timeout) {
					window.clearTimeout(timeout);
				}
				timeout = window.setTimeout(function () {
					if (disabledChk == 'disabled') {
						$('#textLength').html(settings.format.replace(/%1/, (max - 0)));
						$('#link_button').attr('disabled','disabled').attr('className','tw_button_disabled');
						$('#update_button').attr('disabled','disabled').attr('className','tw_button_disabled');
					} else {
						if (convText.length <= max) {
							$('#textLength').html(settings.format.replace(/%1/, (max - convText.length)));
							$('#update_button').attr('disabled','').attr('className','tw_button');
							if (viewMode == "full") {
								if (nowmenu != "direct") {
									if (temp = convText.match(/^\s*@(\w+)\W+/)) {
										$('#update_button').val("답변하기");
										if (viewMode == "full") {
											$('.doing').html("Reply to " + temp[1]);
										}
									} else {
										$("#in_reply_to_status_id").val('');
										$('#update_button').val("전송하기");
										if (viewMode == "full") {
											$('.doing').html("What are you doing?");
										}
									}
								}
							}
						} else {
							$('#textLength').html(settings.format2.replace(/%1/, (convText.length - max)));
							$('#update_button').attr('disabled','disabled').attr('className','tw_button_disabled');
						}
					}
				}, settings.delay);
			} else {
				$('#textLength').html(settings.format.replace(/%1/, (max - convText.length)));
				$('#update_button').attr('disabled','').attr('className','tw_button');
			}
		};
		
		function pulse(el, again) {
			if (p) {
				window.clearTimeout(p);
				p = null;
			};
			el.animate({ opacity: 0.1 }, 100, function () {
				$(this).animate({ opacity: 1.0 }, 100);
			});
			if (again) {
				p = window.setTimeout(function () { pulse(el) }, 200);
			};
		};
		
		return this.each(function () {
			var container = (!settings.container.match(/^<.+>$/)) 
				? $(settings.container) 
				: $(settings.container)
					.insertAfter(this)
					.addClass(settings.classname);
			$(this)
				.bind("keydown", function () { count(this, container); })
				.bind("keypress", function () { count(this, container); })
				.bind("keyup", function () { count(this, container); })
				.bind("focus", function () { count(this, container); })
				.bind("mouseover", function () { count(this, container); })
				.bind("mouseout", function () { count(this, container); })
				.bind("paste", function () { 
					var me = this;
					setTimeout(function () { count(me, container); }, 10);
				});
			if (this.addEventListener) {
				this.addEventListener('input', function () { count(this, container); }, false);
			};
			count(this, container);
		});
	};

})(jQuery);

