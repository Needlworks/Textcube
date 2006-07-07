window.addEventListener("load", insertDivClear, false);

function insertDivClear() {
	if (document.getElementById("part-statistics-visitor")) {
		tempDiv = document.createElement("DIV");
		tempDiv.id = "clear";
		document.getElementById("part-statistics-visitor").appendChild(tempDiv);
		document.getElementById("clear").style.clear = "both";
	}
}