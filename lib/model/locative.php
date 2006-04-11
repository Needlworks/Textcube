<?

function getLocatives($owner) {
	return getEntries($owner, 'id, title, slogan, location', 'length(location) > 1', 'location');
}
?>