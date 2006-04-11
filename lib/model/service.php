<?

function getOwner($name) {
	global $database;
	return fetchQueryCell("select owner from {$database['prefix']}BlogSettings where name = '$name'");
}

function getOwnerBySecondaryDomain($domain) {
	global $database;
	return fetchQueryCell("select owner from {$database['prefix']}BlogSettings where secondaryDomain = '$domain'");
}

function getBlogSetting($owner) {
	global $database;
	if ($result = mysql_query("select * from {$database['prefix']}BlogSettings where owner = $owner")) {
		return mysql_fetch_array($result);
	}
	return false;
}

function getSkinSetting($owner) {
	global $database;
	if ($result = mysql_query("select * from {$database['prefix']}SkinSettings where owner = $owner"))
		return mysql_fetch_array($result);
	return false;
}

function getBlogURL($name = null, $domain = null, $path = null, $type = null) {
	global $service, $blog;
	if ($type === null)
		$type = $service['type'];
	if ($path === null)
		$path = $service['path'];
	if ($domain === null)
		$domain = $service['domain'] . (isset($service['port']) ? ":{$service['port']}" : '');
	if ($name === null)
		$name = $blog['name'];
	switch ($type) {
		case 'domain':
			return "http://$name.$domain$path";
		case 'path':
			return "http://$domain$path/$name";
		case 'single':default:
			return "http://$domain$path";
	}
}

function getBlogURLRule($domain = null, $path = null, $type = null) {
	global $service, $blog;
	if ($type === null)
		$type = $service['type'];
	if ($path === null)
		$path = $service['path'];
	if ($domain === null)
		$domain = $service['domain'] . (isset($service['port']) ? ":{$service['port']}" : '');
	switch ($type) {
		case 'domain':
			return array('http://', ".$domain$path");
		case 'path':
			return array("http://$domain$path/", '');
		case 'single':default:
			return array("http://$domain$path", '');
	}
}
?>