/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
//<![CDATA[
var IE  = (document.all) ? true : false;

function trim(st) {
	while(st) { if (st.indexOf(" ")==0) st = st.substring(1); else break; }
	while(st) { if (st.lastIndexOf(" ")==st.length-1) st = st.substring(0, st.length-1); else break; }
	return st;
}

function trim_all(form) {
	var i=0;
	while (i < form.length) {
		if (form.elements[i].type != 'file') {
			form.elements[i].value = trim(form.elements[i].value);
		}
		i++;
	}
	return true;
}

function submit_type1(act) {
	form.method = 'post';
	form.action = act;
	form.target = '_self';
	form.submit();
}

function submit_type2(act) {
	form.method = 'post';
	form.action = act;
	form.target = 'exe_frame';
	form.submit();
}

function submit_type3(act) {
	form.method = 'get';
	form.action = act;
	form.target = '_self';
	form.submit();
}

function str_innerHTML(str) {
	str = str.replace(/-\_t-t\_-/g, '\n');
	return str;
}

function isNull(field,message) {
	if (field.value.length==0) {
		alert(message + '\t');
		if (field.type != 'hidden') field.focus();
		return true;
	}
	return false;
}

function layer_toggle(obj) {
	if (obj.style.display == 'none') obj.style.display = 'block';
	else if (obj.style.display == 'block') obj.style.display = 'none';
}

function layer_toggle2(obj, obj2) {
	obj.style.display = 'block';
	obj2.style.display = 'none';
}

function has_leaves(d_ary, num) {
	for(i = 0; i < d_ary.length; i++) {
		if (num == d_ary[i][0] && d_ary[i][1] == '0') return false;
	}
	return true;
}

function onclick_setimp(obj, d_ary) {

	obj.style.color = c2;
	obj.style.backgroundColor = bc2;

	for(i = 0; i < d_ary.length; i++){
		var set_area = document.getElementById('imp'+d_ary[i]);
		set_area.style.color = c1;
		set_area.style.backgroundColor = bc1;
	}

	obj.style.color = c2;
	obj.style.backgroundColor = bc2;

}

function onclick_folder(sn, hc, ic, path) {

	hc = document.getElementById(hc);
	ic = document.getElementById(ic);

	if (hc == null) return false;

	sn_set = f_ary[sn].toString();
	sn_set = sn_set.split(',');

	var type = sn_set[0];
	var hl = sn_set[1];
	var il = sn_set[2];

	if (hc.style.display == 'none') {
		if (hl == '0') {
			if (il == '1') ic.src = path + 'tab' + type + '_opened6.gif';
			else ic.src = path + 'tab' + type + '_opened5.gif';
		} else {
			if (il == '1') ic.src = path + 'tab' + type + '_opened2.gif';
			else ic.src = path + 'tab' + type + '_opened1.gif';
		}
	} else {
		if (il == '1') {
			if (hl == '0') ic.src = path + 'tab' + type + '_opened6.gif';
			else ic.src = path + 'tab' + type + '_closed2.gif';
		} else {
			if (hl == '0') ic.src = path + 'tab' + type + '_opened5.gif';
			else ic.src = path + 'tab' + type + '_closed1.gif';
		}
	}

	layer_toggle(hc);

	return false;
}

function set_tree_status() {
	var ct_div, tree_status = '';
	for(i=0; i<d_ary.length; i++) { ct_div = document.getElementById('h_ct'+d_ary[i]); if (ct_div && ct_div.style.display == 'block') { tree_status += ',' + d_ary[i]; } }
	return tree_status;
}

function add_list(object, text, value) {
	loc=object.length;
	object.options[loc] = new Option(text,value);
	object.selectedIndex = loc;
}

function del_list(object) {
	var buffer = '';
	for (var i=0; i < object.options.length; i++) {
		if (object.options[i].selected == true) {
			buffer = buffer + '^' + i + '\|' + object.options[i].value;
		}
	}
	return buffer;
}

function onclick_insert_guest(form, mode, num, page, sort) {
	trim_all(form);
	if (isNull(form.guest_input_name, '이름을 입력해 주십시오.')) return false;
	if (isNull(form.guest_textarea_body, '본문을 입력해 주십시오')) return false;
	if (form.guest_input_password.value == '') {
		if (!confirm('비밀번호를 입력하지 않으면 나중에 수정하실 수 없습니다.\t\n\n계속 진행하시겠습니까?')) return false;
	}
	form.md.value = 'guest_db';
	form.act.value = mode;
	form.num.value = num;
	form.page.value = page;
	form.sort.value = sort;
	form.submit();
}

function onclick_edit_guest(form, pnum, num, page, sort) {
	trim_all(form);
	if (isNull(form.guest_input_name, '이름을 입력해 주십시오.')) return false;
	if (isNull(form.guest_textarea_body, '본문을 입력해 주십시오')) return false;
	form.md.value = 'guest_db';
	form.act.value = 'edit';
	form.pnum.value = pnum;
	form.num.value = num;
	form.page.value = page;
	form.sort.value = sort;
	form.submit();
}

function onclick_insert_comment(form, num){

	trim_all(form);

	if (isNull(eval('form.c_name_'+num), '이름을 입력해 주십시오.')) return false;
	// if (isNull(eval('form.c_password_'+num), '패스워드를 입력해 주십시오')) return false;
	if (isNull(eval('form.c_body_'+num), '답글을 입력해 주십시오')) return false;

	if (!confirm('답글을 올리시겠습니까?\t')) return false;

	form.target = 'exe_frame';
	form.action = 'add_exe.php';
	form.md.value = 'insert';
	form.num.value = num;

	form.c_name.value = eval('form.c_name_'+num).value;
	form.c_homepage.value = eval('form.c_homepage_'+num).value;
	form.c_password.value = eval('form.c_password_'+num).value;
	form.c_body.value = eval('form.c_body_'+num).value;
	form.d_target.value = 'post_'+num;
	form.submit();

	form.target = '_self';
	form.action = 'index.php';
	form.md.value = '';
	form.num.value = '';
}

function onclick_addexe(num, d_target, obj1, obj2, mode) {
	if (obj1.value == 0) {
		obj1.value = '1';
		obj2.value = '0';
		exe_frame.location.href='add_exe.php?md='+mode+'&num='+num+'&d_target='+d_target;
	} else {
		obj1.value = '0';
		var ele = eval(document.getElementById(d_target));
		ele.style.display = 'none';
		ele.innerHTML = '';
	}
	return true;
}

function onclick_delete(mode, pnum, num){
	window.open('del_exe.php?mode='+mode+'&pnum='+pnum+'&num='+num, 'del', 'width=350,height=200,location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0');
}

function onclick_to_article_1l(obj1, obj2, msg) {
	if (obj1.selectedIndex == -1) { alert(msg); return false; }
	var buffer = '';
	temp = obj1.options[obj1.selectedIndex].value.split("|");
	buffer = '[##_1L|' + temp[2] + '|' + temp[1] + '|_##]';
	form.buffer.value = buffer;
	window.open('post_pop.php?view=1&mode=1L','post_pop','width=400, height=300, scrollbars=1, status=1');
	return true;
	set_tag_support(obj2, buffer, '');
	return true;
}

function onclick_to_article_1c(obj1, obj2, msg) {
	if (obj1.selectedIndex == -1) { alert(msg); return false; }
	var buffer = '';
	temp = obj1.options[obj1.selectedIndex].value.split("|");
	buffer = '[##_1C|' + temp[2] + '|' + temp[1] + '|_##]';
	form.buffer.value = buffer;
	window.open('post_pop.php?view=1&mode=1C','post_pop','width=400, height=300, scrollbars=1, status=1');
	return true;
	set_tag_support(obj2, buffer, '');
	return true;
}

function onclick_to_article_1r(obj1, obj2, msg) {
	if (obj1.selectedIndex == -1) { alert(msg); return false; }
	var buffer = '';
	temp = obj1.options[obj1.selectedIndex].value.split("|");
	buffer = '[##_1R|' + temp[2] + '|' + temp[1] + '|_##]';
	form.buffer.value = buffer;
	window.open('post_pop.php?view=1&mode=1R','post_pop','width=400, height=300, scrollbars=1, status=1');
	return true;
	set_tag_support(obj2, buffer, '');
	return true;
}

function onclick_to_article_2c(obj1, obj2, msg) {
	var count = 0;
	var buffer = '';

	for (var i=0; i < obj1.options.length; i++) {
		if (obj1.options[i].selected == true) {
			temp = obj1.options[i].value.split("|");
			buffer = buffer + '^' + temp[2] + '|' + temp[1] + '|';
			count++;
		}
	}

	if (count != 2) {
		alert(msg);
		return false;
	} else {
		var imageinfo;
		if (trim(buffer) != "") buffer = buffer.substr(1);
		imageinfo = buffer.split("^");
		buffer = '[##_2C|' + imageinfo[0] + ' |' + imageinfo[1] + ' _##]';
		form.buffer.value = buffer;
		window.open('post_pop.php?view=1&mode=2C','post_pop','width=400, height=300, scrollbars=1, status=1');
		return true;
		set_tag_support(obj2, buffer, '');
	}

	return true;
}

function onclick_to_article_3c(obj1, obj2, msg) {
	var count = 0;
	var buffer = '';
	for (var i=0; i < obj1.options.length; i++) {
		if (obj1.options[i].selected == true) {
			temp = obj1.options[i].value.split("|");
			buffer = buffer + '^' + temp[2] + '|' + temp[1] + '|';
			count++;
		}
	}
	if (count != 3) {
		alert(msg);
		return false;
	} else {
		var imageinfo;
		if (trim(buffer) != "") buffer = buffer.substr(1);
		imageinfo = buffer.split("^");
		buffer = '[##_3C|' + imageinfo[0] + '|' + imageinfo[1] + '|' + imageinfo[2] + '_##]';
		form.buffer.value = buffer;
		window.open('post_pop.php?view=1&mode=3C','post_pop','width=400, height=300, scrollbars=1, status=1');
		return true;
		set_tag_support(obj2, buffer, '');
		return false;
	}
}

function onclick_to_article_slide(obj1, obj2, msg) {
	var buffer = '';
	for (var i=0; i < obj1.options.length; i++) {
		if (obj1.options[i].selected == true) {
			temp = obj1.options[i].value.split("|");
			buffer = buffer + '^' + temp[2] + '|' + temp[1] + '|';
		}
	}
	var imageinfo;
	if (trim(buffer) != "") buffer = buffer.substr(1);
	imageinfo = buffer.split("^");

	buffer = '[##_S';
	for (i=0; i < imageinfo.length; i++) {
		buffer = buffer + '|' + imageinfo[i];
	}
	buffer = buffer + '_##]';
	form.buffer.value = buffer;
	window.open('post_pop.php?view=1&mode=s','post_pop','width=400, height=300, scrollbars=1, status=1');
	return true;
	set_tag_support(obj2, buffer, '');
	return false;
}

function onclick_to_article_free(obj1, obj2, path1, path2) {
	var buffer = '';
	for (var i=0; i < obj1.options.length; i++) {
		if (obj1.options[i].selected == true) {
			temp = obj1.options[i].value.split("|");
			buffer = buffer + '<img src="' + path1 + path2 + temp[2] + '" ' + temp[1] + '>';
		}
	}
	set_tag_support(obj2, buffer, '');
	return true;
}

function image_view(obj, path, title){
	window.open(path+'image_pop.php?p_title='+title+'&imagefile='+obj.src+'&width='+obj.style.width+'&height='+obj.style.height, '_blank','width='+obj.style.width+',height='+obj.style.height+',location=0,menubar=0,resizable=0,scrollbars=0,status=0,toolbar=0');
}

function is_valid_time(tval) {
    var year  = tval.substring(0,4).replace(/\D/g,"");
    var month = tval.substring(5,7).replace(/\D/g,"");
    var day   = tval.substring(8,10).replace(/\D/g,"");
    var hour  = tval.substring(11,13).replace(/\D/g,"");
    var min   = tval.substring(14,16).replace(/\D/g,"");
    var sec   = tval.substring(17,19).replace(/\D/g,"");

	if (check_date(year, month, day, hour, min, sec)) return true;
    alert('시각을 바르게 입력해 주십시오');
    return false;
}

function check_cb_set(obj) {
	var el_ary = form.elements, flag = '';
	if (obj.checked) flag = true; else flag = false;

	for(i=0; i<el_ary.length; i++) {
		if (el_ary[i].type == 'Checkbox' && el_ary[i].name.substring(0,4) == 'cb_r') el_ary[i].checked = flag;
	}
}


function set_preview_image(obj1, obj2, path1, path2) {
	temp = obj1.options[obj1.selectedIndex].value.split("|");
	var ext = temp[2].substring(temp[2].length-3).toLowerCase();
	if (!(ext == 'jpg' || ext == 'gif' || ext == 'bmp' || ext == 'png')) set_preview_no_image(obj2);
	else obj2.src = servicePath + '/attach/' + path1 + path2 + temp[2];
	return true;
}

function set_preview_no_image(obj) {
	obj.src = servicePath + adminSkin + '/image/spacer.gif';
}

function check_date(year, month, day, hour, min, sec) {
	if (year < 1900 || year == '') return false;
	if (month > 12 || month == '') return false;
	if (day > count_days(year, month) || day == '') return false;
	if (hour > 23 || hour == '') return false;
	if (min > 60 || min == '') return false;
	if (sec > 60 || sec == '') return false;
	return true;
}

function count_days(year, month) {
	var days;
	if ((month == 1) || (month == 3) || (month == 5) || (month == 7) || (month == 8) || (month == 10) || (month == 12)) days = 31;
	else if ((month == 4) || (month == 6) || (month == 9) || (month == 11)) days = 30;
	else if (month == 2) {
		if (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) days = 29;
		else  days = 28;
	}
	return days;
}

function save_pos(obj) {
	if (obj.createTextRange) obj.currentPos = document.selection.createRange().duplicate();
	return true;
}

function set_tag_support(obj, prefix, postfix) {
	if (document.selection) {
		if (obj.createTextRange && obj.currentPos) {
			obj.currentPos.text = prefix + obj.currentPos.text + postfix;
			obj.focus();
			save_pos(obj);
		} else obj.value = obj.value + prefix + postfix;
	} else if (obj.selectionStart && obj.selectionEnd) {
		var s1 = obj.value.substring(0, obj.selectionStart);
		var s2 = obj.value.substring(obj.selectionStart, obj.selectionEnd);
		var s3 = obj.value.substring(obj.selectionEnd);
		obj.value = s1 + prefix + s2 + postfix + s3;
	} else obj.value += prefix + postfix;
	return true;
}

function open_set(val) {
	if (val == 'mark_set') document.getElementById('color_set').style.display = 'none';
	else document.getElementById('mark_set').style.display = 'none';
	layer_toggle(document.getElementById(val));
}

function close_color_set(obj, col1) {
	layer_toggle(document.getElementById('color_set'));
	set_tag_support(obj, '<span style="color: ' + col1 + '">', '</span>');
}

function close_mark_set(obj, col1, col2) {
	layer_toggle(document.getElementById('mark_set'));
	set_tag_support(obj, '<span style="color: '+col1+'; background-color: '+col2+'; padding: 3px 1px 0px">', '</span>');
}

//]]>
