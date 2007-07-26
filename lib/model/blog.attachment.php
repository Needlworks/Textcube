<?php 
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

function getAttachments($blogid, $parent, $orderBy = null, $sort='ASC') {
	global $database;
	$attachments = array();
	if ($result = DBQuery::query("select * from {$database['prefix']}Attachments where blogid = $blogid and parent = $parent ".( is_null($orderBy ) ? '' : "ORDER BY $orderBy $sort"))) {
		while ($attachment = mysql_fetch_array($result))
			array_push($attachments, $attachment);
	}
	return $attachments;
}

function getAttachmentByName($blogid, $parent, $name) {
	global $database;
	$name = mysql_tt_escape_string($name);
	return DBQuery::queryRow("select * from {$database['prefix']}Attachments where blogid = $blogid and parent = $parent and name = '$name'");
}

function getAttachmentByOnlyName($blogid, $name) {
	global $database;
	$name = mysql_tt_escape_string($name);
	return DBQuery::queryRow("select * from {$database['prefix']}Attachments where blogid = $blogid and name = '$name'");
}

function getAttachmentByLabel($blogid, $parent, $label) {
	global $database;
	if ($parent === false)
		$parent = 0;
	$label = mysql_tt_escape_string($label);
	return DBQuery::queryRow("select * from {$database['prefix']}Attachments where blogid = $blogid and parent = $parent and label = '$label'");
}

function getAttachmentSize($blogid=null, $parent = null) {
	global $database;	
	$blogidStr = '';
	$parentStr = '';	

	if (!empty($blogid))
		$blogidStr = "blogid = $blogid ";
	if ($parent == 0 || !empty($parent))
		$parentStr = "and parent = $parent";
	return DBQuery::queryCell("select sum(size) from {$database['prefix']}Attachments where $blogidStr $parentStr");
}

function getAttachmentSizeLabel($blogid=null, $parent = null) {
	//return number_format(ceil(getAttachmentSize($blogid,$parent)/1024)).' / '.number_format(ceil(getAttachmentSize($blogid)/1024)).' (KByte)';
	return number_format(ceil(getAttachmentSize($blogid,$parent)/1024)).' (KByte)';
}

function addAttachment($blogid, $parent, $file) {
	global $database;	
	if (empty($file['name']) || ($file['error'] != 0))
		return false;
	$filename = mysql_tt_escape_string($file['name']);
	if (DBQuery::queryCell("SELECT count(*) 
		FROM {$database['prefix']}Attachments 
		WHERE blogid=$blogid 
			AND parent=$parent 
			AND label='$filename'")>0) {
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
	if ((strlen($extension) > 6) || ($extension == '')) {
		$extension = 'xxx';
	}
	$path = ROOT . "/attach/$blogid";
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
	$name = mysql_tt_escape_string($attachment['name']);
	$label = mysql_tt_escape_string(mysql_lessen($attachment['label'], 64));
	$attachment['mime'] = mysql_lessen($attachment['mime'], 32);
	
	$result = DBQuery::execute("insert into {$database['prefix']}Attachments values ($blogid, {$attachment['parent']}, '$name', '$label', '{$attachment['mime']}', {$attachment['size']}, {$attachment['width']}, {$attachment['height']}, UNIX_TIMESTAMP(), 0,0)");
	if (!$result) {
		@unlink($attachment['path']);
		return false;
	}
	return $attachment;
}

function deleteAttachment($blogid, $parent, $name) {
	global $database;
	requireModel('blog.rss');
	if (!Validator::filename($name)) 
		return false;
	$origname = $name;
	$name = mysql_tt_escape_string($name);
	if (DBQuery::execute("delete from {$database['prefix']}Attachments where blogid = $blogid and name = '$name'") && (mysql_affected_rows() == 1)) {
		@unlink(ROOT . "/attach/$blogid/$origname");
		clearRSS();
		return true;
	}
	return false;
}

function deleteTotalAttachment($blogid) {
	requireModel('blog.rss');
	$d = dir(ROOT."/attach/$blogid");
	while($file = $d->read()) {
		if(is_file(ROOT."/attach/$blogid/$file"))
			unlink(ROOT."/attach/$blogid/$file");
	}
	rmdir(ROOT."/attach/$blogid/");
	clearRSS();
	return true;
}

function deleteAttachmentMulti($blogid, $parent, $names) {
	global $database;
	requireModel('blog.rss');
	$files = explode('!^|', $names);
	foreach ($files as $name) {
		if ($name == '')
			continue;
		if (!Validator::filename($name)) 
			continue;
		$origname = $name;
		$name = mysql_tt_escape_string($name);
		if (DBQuery::execute("delete from {$database['prefix']}Attachments where blogid = $blogid and parent = $parent and name = '$name'") && (mysql_affected_rows() == 1)) {
			unlink(ROOT . "/attach/$blogid/$origname");
		} else {
		}
	}
	clearRSS();
	return true;
}



function deleteAttachments($blogid, $parent) {
	$attachments = getAttachments($blogid, $parent);
	foreach ($attachments as $attachment)
		deleteAttachment($blogid, $parent, $attachment['name']);
}

function downloadAttachment($name) {
	requireModel('blog.rss');
	global $database;
	$name = mysql_tt_escape_string($name);
	DBQuery::query("UPDATE {$database['prefix']}Attachments SET downloads = downloads + 1 WHERE blogid = ".getBlogId()." AND name = '$name'");
}

function setEnclosure($name, $order) {
	global $database;
	$name = mysql_tt_escape_string($name);
	if (($parent = DBQuery::queryCell("SELECT parent FROM {$database['prefix']}Attachments WHERE blogid = ".getBlogId()." AND name = '$name'")) !== null) {
		DBQuery::execute("UPDATE {$database['prefix']}Attachments SET enclosure = 0 WHERE parent = $parent");
		if ($order) {
			clearRSS();
			return DBQuery::execute("UPDATE {$database['prefix']}Attachments SET enclosure = 1 WHERE blogid = ".getBlogId()." AND name = '$name'") ? 1 : 2;
		} else
			return 0;
	} else
		return 3;
}

function getEnclosure($entry) {
	global $database;
	if ($entry < 0)
		return null;
	return DBQuery::queryCell("SELECT name FROM {$database['prefix']}Attachments WHERE parent = $entry AND enclosure = 1 AND blogid = ".getBlogId());
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
