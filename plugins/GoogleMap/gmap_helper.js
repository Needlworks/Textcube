// Google Map Helper
//
// depends on EAF4.js and Google Map API v2.

function initializeGoogleMap() {
	// Nothing to do currently.
}

function GMapTool_Insert() {
	window.open(blogURL + '/plugin/GMapCustomInsert/', 'GMapTool_Insert', 'menubar=no,toolbar=no,width=500,height=500');
}

STD.addUnloadEventListener(function() { GUnload(); });
