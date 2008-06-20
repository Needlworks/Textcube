/// Copyright (c) 2004-2008, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

var szFull = 100; 
var origMenuSize = {};

var mainMenuItems = $$("#main-menu li");
var mainMenuFx = new Fx.Elements(mainMenuItems, {wait: false, duration: 200, transition: Fx.Transitions.quadOut});

mainMenuItems.each(function(menus, i) {
	origMenuSize[i] = menus.getStyle("width").toInt();
});
	
mainMenuItems.each(function(kwick, i) {
	kwick.addEvent("mouseenter", function(event) {
		var obj = {};
		if(i != 0) {
			obj[i] = {'width': [origMenuSize[i], origMenuSize[i]+30]};
		} else {
			obj[i] = origMenuSize[i];
		}
		mainMenuItems.each(function(other, j) {
			if(i != j) {
				var w = other.getStyle("width").toInt();
				obj[j] = {'width': [w, origMenuSize[j]]};
			}
		});
		mainMenuFx.start(obj);
	});
});
 
$("mainMenuItems").addEvent("mouseleave", function(event) {
	var obj = {};
	mainMenuItems.each(function(kwick, i) {
		obj[i] = {'width': [kwick.getStyle("width").toInt(), origMenuSize[i]]};
	});
	mainMenuFx.start(obj);
});
