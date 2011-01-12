/// Copyright (c) 2006-2011. Needlworks
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

//<![CDATA[
jQuery(function($){
	// Side Menu
	var sMenu = $('#main-menu');
	var sItem = sMenu.find('>li');
	var ssItem = sMenu.find('>li>ul>li');
	var lastEvent = null;
	
	function sMenuToggle(event){
		var t = $(this);
		
		if (this == lastEvent) return false;
		lastEvent = this;
		setTimeout(function(){ lastEvent=null }, 200);
		
		if (t.next('ul').is(':hidden')) {
			sItem.find('>ul').slideUp(100);
			t.next('ul').slideDown(100);
		} else if(!t.next('ul').length) {
			sItem.find('>ul').slideUp(100);
		} else {
			t.next('ul').slideUp(100);
		}
		
		if (t.parent('li').hasClass('selected')){
			t.parent('li').removeClass('selected');
		} else {
			sItem.removeClass('selected');
			t.parent('li').addClass('selected');
		}
	}
	sItem.find('>a').click(sMenuToggle).focus(sMenuToggle);
	sItem.find('>a').attr ('href', '#');
	
	function subMenuActive(){
		ssItem.removeClass('selected');
		$(this).parent(ssItem).addClass('selected');
	}; 
	ssItem.find('>a').click(subMenuActive).focus(subMenuActive);
});
//]]>