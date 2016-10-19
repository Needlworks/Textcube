/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function check_all_Checkbox(form, checked) {
	var objects = form.elements.tags("input");
	for (i = 0; objects[i]; i ++) {
		if (objects[i].type == "Checkbox")
			objects[i].checked = checked;
	}
}

function change_layer(prefix, specific) {
	var layers = document.body.getElementsByTagName("div");
	for (i = 0; layers[i]; i ++) {
		if (layers[i].id.substr(0, prefix.length) == prefix)
			if (layers[i].id == (prefix + specific))
				layers[i].style.display = "block";
			else
				layers[i].style.display = "none";
	}
}

function checkTimestamp(value) {
	var time = Date.parse(value) / 1000;
	if (isNaN(time) || (time < 0) || (time > 2147483647))
		return false;
	return true;
}

function checkBlogName(name) {
	return name.match(/^[a-z0-9]+(-[a-z0-9]+)*$/i);
}

function checkDomainName(name) {
	return name.match(/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z0-9]+(-[a-z0-9]+)*$/i);
}

function viewHelp(id) {
	id = (typeof("id") == "undefined") ? "" : "#" + id;
		id = "";
	var win = window.open(blogURL + "/owner/help/" + id, "TextcubeHelper", "width=600, height=500, location=0, menubar=0, resizable=1, scrollbars=1, status=0, toolbar=0");
	try {
		win.focus();
		win.moveTo(screen.availWidth / 2 - 300, screen.availHeight / 2 - 250);
	} catch(e) { }
}

var extraClass = ''; // 이 변수는 reader에서도 사용됨. 임의로 변경하지 말 것.

function rolloverClass(obj, type) {
	agent = navigator.userAgent.toLowerCase();
	if (!(agent.indexOf('opera') + 1)) {
		if (type == 'over') {
			if (obj.tagName == 'TR') {
				for (i=0; i<obj.cells.length; i++) {
					obj.cells[i].className += ' rollover-class';
				}
			} else {
				extraClass = obj.className;
				obj.className = obj.className.replace(/(active|inactive)/ig, 'rollover');
			}
		} else {
			if (obj.tagName == 'TR') {
				for (i=0; i<obj.cells.length; i++) {
					obj.cells[i].className = obj.cells[i].className.replace(/( )*rollover\-class/ig, '');
				}
			} else {
				obj.className = extraClass;
				extraClass = '';
			}
		}
	}
}

function toggleDialog(content,popWidth) {
    var dialogID = 'dialog-box'; // Default dialog ID
	document.getElementById(dialogID).innerHTML = content;
    //Fade in the Popup and add close button
    jQuery('#' + dialogID).fadeIn().css({ 'width': Number( popWidth ) }).prepend('<a href="#" class="close"><span>Close</span></a>');

    var popMargTop = (jQuery('#' + dialogID).height() + 20) / 2;
    var popMargLeft = (jQuery('#' + dialogID).width() + 20) / 2;

    //Apply Margin to Popup
    jQuery('#' + dialogID).css({
        'margin-top' : -popMargTop,
        'margin-left' : -popMargLeft
    });

    //Fade in Background
    jQuery('body').append('<div id="fade"></div>');
    jQuery('#fade').css({'filter' : 'alpha(opacity=80)'}).fadeIn(); //Fade in the fade layer - .css({'filter' : 'alpha(opacity=80)'}) is used to fix the IE Bug on fading transparencies 
	
	//Close Popups and Fade Layer
	jQuery('a.close, #fade').live('click', function() { //When clicking on the close or fade layer.
	    jQuery('#fade , .dialog').fadeOut(function() {
	        jQuery('#fade, a.close').remove();  //fade them both out
	    });
	    return false;
	});
	return false;
}
