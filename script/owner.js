//<![CDATA[
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
	return name.match(/^[a-z0-9]+$/i);
}

function checkDomainName(name) {
	return name.match(/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z0-9]+(-[a-z0-9]+)*$/i);
}

function viewHelp(id) {
	id = (typeof("id") == "undefined") ? "" : "#" + id;
		id = "";
	var win = window.open(blogURL + "/owner/help/" + id, "tatter", "width=600, height=500, location=0, menubar=0, resizable=1, scrollbars=1, status=0, toolbar=0");
	try {
		win.focus();
		win.moveTo(screen.availWidth / 2 - 300, screen.availHeight / 2 - 250);
	} catch(e) { }
}

function rolloverTableTr(obj, type) {
	if (type == 'over') {
		obj.className = obj.className.replace(/overInactive/, 'overActive');
	} else {
		obj.className = obj.className.replace(/overActive/, 'overInactive');
	}
}
//]]>