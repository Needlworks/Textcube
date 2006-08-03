<?php 
function getAttachments($owner, $parent, $orderBy = null, $sort='ASC') {
	global $database;
	$attachments = array();
	if ($result = mysql_query("select * from {$database['prefix']}Attachments where owner = $owner and parent = $parent ".( is_null($orderBy ) ? '' : "ORDER BY $orderBy $sort"))) {
		while ($attachment = mysql_fetch_array($result))
			array_push($attachments, $attachment);
	}
	return $attachments;
}

function getAttachmentByName($owner, $parent, $name) {
	global $database;
	$name = mysql_real_escape_string($name);
	return fetchQueryRow("select * from {$database['prefix']}Attachments where owner = $owner and parent = $parent and name = '$name'");
}

function getAttachmentByOnlyName($owner, $name) {
	global $database;
	$name = mysql_real_escape_string($name);
	return fetchQueryRow("select * from {$database['prefix']}Attachments where owner = $owner and name = '$name'");
}

function getAttachmentByLabel($owner, $parent, $label) {
	global $database;
	if ($parent === false)
		$parent = 0;
	$label = mysql_real_escape_string($label);
	return fetchQueryRow("select * from {$database['prefix']}Attachments where owner = $owner and parent = $parent and label = '$label'");
}

function getAttachmentSize($owner=null, $parent = null) {
	global $database;	
	$ownerStr = '';
	$parentStr = '';	

	if (!empty($owner))
		$ownerStr = "owner = $owner ";
	if ($parent == 0 || !empty($parent))
		$parentStr = "and parent = $parent";
	return fetchQueryCell("select sum(size) from {$database['prefix']}Attachments where $ownerStr $parentStr");
}

function getAttachmentSizeLabel($owner=null, $parent = null) {
	//return number_format(ceil(getAttachmentSize($owner,$parent)/1024)).' / '.number_format(ceil(getAttachmentSize($owner)/1024)).' (KByte)';
	return number_format(ceil(getAttachmentSize($owner,$parent)/1024)).' (KByte)';
}

function addAttachment($owner, $parent, $file) {
	global $database;	
	if (empty($file['name']) || ($file['error'] != 0))
		return false;
	$filename = mysql_real_escape_string($file['name']);
	if (fetchQueryCell("SELECT count(*) FROM {$database['prefix']}Attachments WHERE owner=$owner AND parent=$parent AND label='$filename'")>0) {
		return false;
	}
	$attachment = array();
	$attachment['parent'] = $parent ? $parent : 0;
	$attachment['label'] = Path::getBaseName($file['name']);
	$attachment['size'] = $file['size'];
	$extension = getFileExtension($attachment['label']);
	switch (strtolower($extension)) {
		case 'exe':case 'php':case 'sh':case 'com':case 'bat':
			$extension = 'xxx';
			break;
	}
	if (strlen($extension) > 6) {
		$extension = 'xxx';
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
	$name = mysql_real_escape_string($attachment['name']);
	$label = mysql_real_escape_string($attachment['label']);
	
	$result = DBQuery::execute("insert into {$database['prefix']}Attachments values ($owner, {$attachment['parent']}, '$name', '$label', '{$attachment['mime']}', {$attachment['size']}, {$attachment['width']}, {$attachment['height']}, UNIX_TIMESTAMP(), 0,0)");
	if (!$result) {
		@unlink($attachment['path']);
		return false;
	}
	return $attachment;
}

function deleteAttachment($owner, $parent, $name) {
	global $database;
	if (!Validator::filename($name)) 
		return false;
	$origname = $name;
	$name = mysql_real_escape_string($name);
	if (DBQuery::execute("delete from {$database['prefix']}Attachments where owner = $owner and name = '$name'") && (mysql_affected_rows() == 1)) {
		@unlink(ROOT . "/attach/$owner/$origname");
		clearRSS();
		return true;
	}
	return false;
}

function deleteTotalAttachment($userid) {
	$d = dir(ROOT."/attach/$userid");
	while($file = $d->read()) {
		if(is_file(ROOT."/attach/$userid/$file"))
			unlink(ROOT."/attach/$userid/$file");
	}
	rmdir(ROOT."/attach/$userid/");
	clearRSS();
	return true;
}

function deleteAttachmentMulti($owner, $parent, $names) {
	global $database;
	$files = explode('!^|', $names);
	foreach ($files as $name) {
		if ($name == '')
			continue;
		if (!Validator::filename($name)) 
			continue;
		$origname = $name;
		$name = mysql_real_escape_string($name);
		if (DBQuery::execute("delete from {$database['prefix']}Attachments where owner = $owner and parent = $parent and name = '$name'") && (mysql_affected_rows() == 1)) {
			unlink(ROOT . "/attach/$owner/$origname");
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
	$name = mysql_real_escape_string($name);
	mysql_query("UPDATE {$database['prefix']}Attachments SET downloads = downloads + 1 WHERE owner = $owner AND name = '$name'");
}

function setEnclosure($name, $order) {
	global $database, $owner;
	$name = mysql_real_escape_string($name);
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

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}
?>
