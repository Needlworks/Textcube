// Google Map Plugin WYSISYG Helper
// - depends on EAF4.js and Google Map API v2.

function initializeGoogleMap() {
	// Nothing to do currently.
}

function GMapTool_insertMap() {
	window.open(blogURL + '/plugin/GMapCustomInsert/', 'GMapTool_Insert', 'menubar=no,toolbar=no,width=550,height=680,scrollbars=yes');
}

function GMapTool_getLocation() {
	window.open(blogURL + '/plugin/GMapGetLocation/', 'GMapTool_GetLocation', 'menubar=no,toolbar=no,width=550,height=600,scrollbars=no');
}

STD.addUnloadEventListener(function() { GUnload(); });
