tt_init_funcs.push(function() { insertDivClear(); });

function insertDivClear() {
	if (document.getElementById("part-statistics-visitor")) {
		tempDiv = document.createElement("DIV");
		tempDiv.id = "clear";
		document.getElementById("part-statistics-visitor").appendChild(tempDiv);
		document.getElementById("clear").style.clear = "both";
	}
}