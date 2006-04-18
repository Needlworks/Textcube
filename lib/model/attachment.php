<?

function getAttachments($owner, $parent) {
	global $database;
	$attachments = array();
	if ($result = mysql_query("select * from {$database['prefix']}Attachments where owner = $owner and parent = $parent")) {
		while ($attachment = mysql_fetch_array($result))
			array_push($attachments, $attachment);
	}
	return $attachments;
}

function getAttachmentByName($owner, $parent, $name) {
	global $database;
	$name = mysql_escape_string($name);
	return fetchQueryRow("select * from {$database['prefix']}Attachments where owner = $owner and parent = $parent and name = '$name'");
}

function getAttachmentByOnlyName($owner, $name) {
	global $database;
	$name = mysql_escape_string($name);
	return fetchQueryRow("select * from {$database['prefix']}Attachments where owner = $owner and name = '$name'");
}

function getAttachmentByLabel($owner, $parent, $label) {
	global $database;
	if ($parent === false)
		$parent = 0;
	$label = mysql_escape_string($label);
	return fetchQueryRow("select * from {$database['prefix']}Attachments where owner = $owner and parent = $parent and label = '$label'");
}

function addAttachment($owner, $parent, $file) {
	global $database;
	if (empty($file['name']) || ($file['error'] != 0))
		return false;
	$attachment = array();
	$attachment['parent'] = $parent ? $parent : 0;
	$attachment['label'] = Path::getBaseName($file['name']);
	$label = mysql_escape_string($attachment['label']);
	$attachment['size'] = $file['size'];
	$extension = getFileExtension($attachment['label']);
	switch (strtolower($extension)) {
		case 'exe':case 'php':case 'sh':case 'com':case 'bat':
			$extension = 'xxx';
			break;
	}
	$path = ROOT . "/attach/$owner";
	if (!is_dir($path)) {
		mkdir($path);
		if (!is_dir($path))
			return false;
		@chmod($path, 0777);
	}
	do {
		$attachment['name'] = rand(1000000000, 9999999999) . ".$extension";
		$attachment['path'] = "$path/{$attachment['name']}";
	} while (file_exists($attachment['path']));
	if ($imageAttributes = @getimagesize($file['tmp_name'])) {
		$attachment['mime'] = $imageAttributes['mime'];
		$attachment['width'] = $imageAttributes[0];
		$attachment['height'] = $imageAttributes[1];
	} else {
		$attachment['mime'] = getMIMEType($extension);
		$attachment['width'] = 0;
		$attachment['height'] = 0;
	}
	if (!move_uploaded_file($file['tmp_name'], $attachment['path']))
		return false;
	@chmod($attachment['path'], 0666);
	$result = mysql_query("insert into {$database['prefix']}Attachments values ($owner, {$attachment['parent']}, '{$attachment['name']}', '$label', '{$attachment['mime']}', {$attachment['size']}, {$attachment['width']}, {$attachment['height']}, UNIX_TIMESTAMP(), 0,0)");
	if (!$result) {
		@unlink($attachment['path']);
		return false;
	}
	return $attachment;
}

function deleteAttachment($owner, $parent, $name) {
	global $database;
	@unlink(ROOT . "/attach/$owner/$name");
	clearRSS();
	if ($parent === false) {
		return true;
	}
	$name = mysql_escape_string($name);
	if (mysql_query("delete from {$database['prefix']}Attachments where owner = $owner and parent = $parent and name = '$name'") && (mysql_affected_rows() == 1)) {
		return true;
	}
	return false;
}

function deleteAttachmentMulti($owner, $parent, $names) {
	global $database;
	$files = explode('!^|', $names);
	foreach ($files as $name) {
		if ($name == '')
			continue;
		unlink(ROOT . "/attach/$owner/$name");
		if ($parent === false) {
			continue;
		}
		$name = mysql_escape_string($name);
		if (mysql_query("delete from {$database['prefix']}Attachments where owner = $owner and parent = $parent and name = '$name'") && (mysql_affected_rows() == 1)) {
		} else {
		}
	}
	clearRSS();
	return true;
}

function deleteAttachments($owner, $parent) {
	$attachments = getAttachments($owner, $parent);
	foreach ($attachments as $attachment)
		deleteAttachment($owner, $parent, $attachment['name']);
}

function downloadAttachment($name) {
	global $database, $owner;
	$name = mysql_escape_string($name);
	mysql_query("UPDATE {$database['prefix']}Attachments SET downloads = downloads + 1 WHERE owner = $owner AND name = '$name'");
}

function setEnclosure($name, $order) {
	global $database, $owner;
	$name = mysql_escape_string($name);
	if (($parent = fetchQueryCell("SELECT parent FROM {$database['prefix']}Attachments WHERE owner = $owner AND name = '$name'")) !== null) {
		executeQuery("UPDATE {$database['prefix']}Attachments SET enclosure = 0 WHERE parent = $parent");
		if ($order) {
			clearRSS();
			return executeQuery("UPDATE {$database['prefix']}Attachments SET enclosure = 1 WHERE owner = $owner AND name = '$name'") ? 1 : 2;
		} else
			return 0;
	} else
		return 3;
}

function getEnclosure($owner, $entry) {
	global $database, $owner;
	return fetchQueryAll("SELECT name {$database['prefix']}Attachments SET enclosure = $order WHERE owner = $owner AND name = '$name'");
}
?>