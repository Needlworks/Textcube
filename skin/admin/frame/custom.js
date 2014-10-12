/// Copyright (c) 2006-2011. Needlworks
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

//<![CDATA[
jQuery(document).ready(function(jQuery){
	// Side Menu
	var sMenu = jQuery('#main-menu');
	var sItem = sMenu.find('>li');
	var ssItem = sMenu.find('>li>ul>li');
	var lastEvent = null;
	
	function sMenuToggle(event){
		var mainmenuItem = jQuery(this);
		
		if (this == lastEvent) return false;
		lastEvent = this;
		setTimeout(function(){ lastEvent=null }, 200);
		
		if (mainmenuItem.next('ul').is(':hidden')) {
			sItem.find('>ul').slideUp(100);
			mainmenuItem.next('ul').slideDown(100);
		} else if(!mainmenuItem.next('ul').length) {
			sItem.find('>ul').slideUp(100);
		} else {
			mainmenuItem.next('ul').slideUp(100);
		}
		
		if (mainmenuItem.parent('li').hasClass('selected')){
			mainmenuItem.parent('li').removeClass('selected');
		} else {
			sItem.removeClass('selected');
			mainmenuItem.parent('li').addClass('selected');
		}
		return false;
	}
	
	function subMenuActive(){
		ssItem.removeClass('selected');
		jQuery(this).parent(ssItem).addClass('selected');
		return true;
	}; 
	sItem.find('>a').click(sMenuToggle).focus(sMenuToggle);
	sItem.find('>a').attr('href', '#');
	ssItem.find('>a').click(subMenuActive).focus(subMenuActive);
});
//]]>
