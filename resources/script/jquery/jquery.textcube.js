/// Copyright (c) 2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

// Textcube-specific jQuery Plugin

// TODO: set resourceURL - local or remote resource storage URL (depending on settings)
// TODO: plugin version check?

// 이 로더는 중요 jQuery 플러그인들(예: json, ui, easing)은 함께 포함되어 배포되며, 이들을 여러
// 스크립트에서 중복해서 불러오지 않도록 하는 역할을 한다.
// 하지만 그렇지 않은 플러그인들은 사용하고자 하는 주체(예: 플러그인 제작자)가 자체적으로
// 포함시키는 것을 원칙으로 한다.

// 사용법 : $.plugin('ui.essentials');

(function($) {
	$.path = {}; // not callable, just namespace
	$.path.separator = $.path.default_separator = '/';
	$.path.join = function() {
		// Safely join directory names with single separators between them.
		var args = $.path.join.arguments;
		var joined = args[0];
		for (var i = 1; i < args.length; ++i) {
			var t1 = (joined.charAt(joined.length - 1) == $.path.separator);
			var t2 = (args[i].charAt(0) == $.path.separator);
			if (t1 && t2)
				joined += args[i].slice(1);
			else if (t1 || t2)
				joined += args[i];
			else
				joined += (joined ? $.path.separator : '') + args[i];
		}
		return joined;
	}

	$.plugin = function (name, version) {
		for (var i = 0; i < $.plugin.loaded_ones.length; ++i) {
			if ($.plugin.loaded_ones[i].name == name)
				return true;
		}
		for (var i = 0; i < $.plugin.locals.length; ++i) {
			if ($.plugin.locals[i].name == name) {
				$.path.separator = '.';
				var file = $.path.join('jquery', name, $.plugin.locals[i].version, 'js');
				$.path.separator = $.path.default_separator;
				var src = $.path.join(serviceURL, resoucreURL || $.plugin.defaultResourceURL, file);
				$('<script>').attr('type', 'text/javascript').attr('src', src).appendTo($('head'));
				// TODO: check if loaded correctly?
				$.plugins.loaded_ones.append({name: name, version: $.plugin.locals[i].version});
				// NOTE: Safari will not actually execute the new script until it gets the control flow.
				return true;
			}
		}
		return false; // load failed, should be included manually.
	}
	$.plugin.locals = [
		{name: 'json', version: ''},
		{name: 'easing', version: '1.3'},
		{name: 'ui.essentials', version: '1.6'}, // includes UI.Core, Draggable, Droppable, Resizable, Selectable, Sortable only
		{name: 'ui.effects', version: '1.6'} // includes Effeects Core and others
	];
	$.plugin.defaultResourceURL = '/resources/script';
	$.plugin.loaded_ones = [];
})(jQuery);
