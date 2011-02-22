<?php 
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

global $__gCacheAttachment;
$__gCacheAttachment = array();

function getAttachments($blogid, $parent, $orderBy = null, $sort='ASC') {
	global $__gCacheAttachment;
	if(isset($__gCacheAttachment) && !empty($__gCacheAttachment)) {
		if($result = getAttachmentsFromCache($blogid, $parent, 'parent')) {
			return $result;
		}
	}
	$attachments = array();
	$pool = DBModel::getInstance();
	$pool->reset('Attachments');
	$pool->setQualifier('blogid','equals',intval($blogid));
	$pool->setQualifier('parent','equals',intval($parent));
	if(!is_null($orderBy)) {
		$pool->setOrder($orderBy,$sort);
	}
	if($result = $pool->getAll('*')) {	
		foreach($result as $attachment) {
			array_push($attachments, $attachment);
			array_push($__gCacheAttachment, $attachment);
		}
	}
	return $attachments;
}

function getAttachmentsFromCache($blogid, $value, $filter = 'parent') {
	global $__gCacheAttachment;
	$result = array();
	if (!empty($__gCacheAttachment)) {
		foreach($__gCacheAttachment as $id => $info) {
			$row = array_search($value, $info);
			if ($row !== FALSE)
				array_push($result,$__gCacheAttachment[$id]);
		}
	}
	return $result;
}

function getAttachmentFromCache($blogid, $value, $filter = 'name') {
	global $__gCacheAttachment;
	if (!empty($__gCacheAttachment)) {
		foreach($__gCacheAttachment as $id => $info) {
			$row = array_search($value, $info);
			//if($row && $row == $filter) return $__gCacheAttachment[$id];
			if ($row !== FALSE)
				return $__gCacheAttachment[$id];
		}
	}
	return false;
}

function getAttachmentByName($blogid, $parent, $name) {
	global $database, $__gCacheAttachment;
	if(!isset($__gCacheAttachment))
		getAttachments($blogid, $parent);
	if($result = getAttachmentFromCache($blogid, $name, 'name') && $result['parent'] == $parent) {
		return $result;
	}
	return false;
}

function getAttachmentByOnlyName($blogid, $name) {
	global $__gCacheAttachment;
	if(!empty($__gCacheAttachment) && $result = getAttachmentFromCache($blogid, $name, 'name')) {
		return $result;
	} else {
		$pool = DBModel::getInstance();
		$pool->reset('Attachments');
		$pool->setQualifier('blogid','equals',$blogid);
		$pool->setQualifier('name','equals',$name,true);
		$newAttachment = $pool->getRow('*');
		array_push($__gCacheAttachment,$newAttachment);
		return $newAttachment;
	}
}

function getAttachmentByLabel($blogid, $parent, $label) {
	if ($parent === false)
		$parent = 0;
	$pool = DBModel::getInstance();
	$pool->reset('Attachments');
	$pool->setQualifier('blogid','equals',$blogid);
	$pool->setQualifier('parent','equals',$parent);
	$pool->setQualifier('label','equals',$label,true);
	return $pool->getRow('*');
}

function getAttachmentSize($blogid=null, $parent = null) {
	$pool = DBModel::getInstance();
	$pool->reset('Attachments');
	if(!empty($blogid)) {
		$pool->setQualifier('blogid','equals',$blogid);	
	}	
	if ($parent == 0 || !empty($parent)) {
		$pool->setQualifier('parent','equals',$parent);	
	}	
	return $pool->getCell('SUM(size)');
}

function getAttachmentSizeLabel($blogid=null, $parent = null) {
	//return number_format(ceil(getAttachmentSize($blogid,$parent)/1024)).' / '.number_format(ceil(getAttachmentSize($blogid)/1024)).' (KByte)';
	return number_format(ceil(getAttachmentSize($blogid,$parent)/1024)).' (KByte)';
}

function addAttachment($blogid, $parent, $file) {
	global $database;
	if (empty($file['name']) || ($file['error'] != 0))
		return false;
	$filename = $file['name'];

	$pool = DBModel::getInstance();
	$pool->reset('Attachments');
	$pool->setQualifier('blogid','equals',$blogid);
	$pool->setQualifier('parent','equals',$parent);
	$pool->setQualifier('label','equals',$filename,true);
	if($pool->getCell('count(*)') > 0) {
		return false;
	}
	$attachment = array();
	$attachment['parent'] = $parent ? $parent : 0;
	$attachment['label'] = Path::getBaseName($file['name']);
	$attachment['size'] = $file['size'];
	$extension = Misc::getFileExtension($attachment['label']);
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
		$attachment['mime'] = Misc::getMIMEType($extension);
		$attachment['width'] = 0;
		$attachment['height'] = 0;
	}
	if (!move_uploaded_file($file['tmp_name'], $attachment['path']))
		return false;
	@chmod($attachment['path'], 0666);
	$attachment['label'] = UTF8::lessenAsEncoding($attachment['label'], 64);
	$attachment['mime']  = UTF8::lessenAsEncoding($attachment['mime'], 32);


	$pool->reset('Attachments');
	$pool->setAttribute('blogid',$blogid);
	$pool->setAttribute('parent',$attachment['parent']);
	$pool->setAttribute('name',$attachment['name'],true);
	$pool->setAttribute('label',$attachment['label'],true);
	$pool->setAttribute('mime',$attachment['mime'],true);
	$pool->setAttribute('size',$attachment['size'],true);
	$pool->setAttribute('width',$attachment['width']);
	$pool->setAttribute('height',$attachment['height']);
	$pool->setAttribute('attached',Timestamp::getUNIXtime());
	$pool->setAttribute('downloads',0);
	$pool->setAttribute('enclosure',0);
	$result = $pool->insert();
	if (!$result) {
		@unlink($attachment['path']);
		return false;
	}
	return $attachment;
}

function deleteAttachment($blogid, $parent, $name) {
	requireModel('blog.feed');
	if (!Validator::filename($name)) 
		return false;
	$origname = $name;
	$pool = DBModel::getInstance();
	$pool->reset('Attachments');
	$pool->setQualifier('blogid','equals',$blogid);
	$pool->setQualifier('name','equals',$name,true);
	if($pool->delete()) {	
		if( file_exists( ROOT . "/attach/$blogid/$origname") ) {
			@unlink(ROOT . "/attach/$blogid/$origname");
		}
		clearFeed();
		return true;
	}
	return false;
}

function copyAttachments($blogid, $originalEntryId, $targetEntryId) {
	global $database;
	$path = ROOT . "/attach/$blogid";
	$attachments = getAttachments($blogid, $originalEntryId);
	if(empty($attachments)) return true;

	$pool = DBModel::getInstance();
	$pool->reset('Entries');
	$pool->setQualifier('blogid','equals',$blogid);
	$pool->setQualifier('id','equals',$originalEntryId);
	if(!$pool->getCell('id')) return 2;	// original entry does not exists;
	$pool->setQualifier('id','equals',$targetEntryId);
	if(!$pool->getCell('id')) return 3; // target entry does not exists;
	
	foreach($attachments as $attachment) {
		$extension = Misc::getFileExtension($attachment['label']);
		$originalPath = "$path/{$attachment['name']}";
		do {
			$attachment['name'] = rand(1000000000, 9999999999) . ".$extension";
			$attachment['path'] = "$path/{$attachment['name']}";
		} while (file_exists($attachment['path']));
		if(!copy($originalPath, $attachment['path'])) return 4; // copy failed.
	
		$pool->reset('Attachments');
		$pool->setAttribute('blogid',$blogid);
		$pool->setAttribute('parent',$targetEntryId);
		$pool->setAttribute('name',$attachment['name'],true);
		$pool->setAttribute('label',$attachment['label'],true);
		$pool->setAttribute('mime',$attachment['mime'],true);
		$pool->setAttribute('size',$attachment['size'],true);
		$pool->setAttribute('width',$attachment['width']);
		$pool->setAttribute('height',$attachment['height']);
		$pool->setAttribute('attached',Timestamp::getUNIXtime());
		$pool->setAttribute('downloads',0);
		$pool->setAttribute('enclosure',0);		
		if(!$pool->insert()) {
			return false;
		}
	}
	return true;
}
function deleteTotalAttachment($blogid) {
	requireModel('blog.feed');
	$d = dir(ROOT."/attach/$blogid");
	while($file = $d->read()) {
		if(is_file(ROOT."/attach/$blogid/$file"))
			unlink(ROOT."/attach/$blogid/$file");
	}
	rmdir(ROOT."/attach/$blogid/");
	clearFeed();
	return true;
}

function deleteAttachmentMulti($blogid, $parent, $names) {
	requireModel('blog.feed');
	$pool = DBModel::getInstance();
	$files = explode('!^|', $names);
	foreach ($files as $name) {
		if ($name == '')
			continue;
		if (!Validator::filename($name)) 
			continue;
		$origname = $name;
		$pool->reset('Attachments');
		$pool->setQualifier('blogid','eq',$blogid);
		$pool->setQualifier('parent','eq',intval($parent));
		$pool->setQualifier('name','eq',$name,true);
		if($pool->delete()) {	
			unlink(ROOT . "/attach/$blogid/$origname");
		} else {
		}
	}
	clearFeed();
	return true;
}



function deleteAttachments($blogid, $parent) {
	$attachments = getAttachments($blogid, $parent);
	foreach ($attachments as $attachment)
		deleteAttachment($blogid, $parent, $attachment['name']);
}

function downloadAttachment($name) {
	requireModel('blog.feed');
	$pool = DBModel::getInstance();
	$blogid = getBlogId();

	$pool->reset('Attachments');
	$pool->setQualifier('blogid','eq',$blogid);
	$pool->setQualifier('name','eq',$name,true);
	$downloadCount = $pool->getCell('downloads');
	if($downloadCount !== false) {	
		$pool->reset('Attachments');
		$pool->setAttribute('downloads',$downloadCount + 1);
		$pool->setQualifier('blogid','eq',$blogid);
		$pool->setQualifier('name','eq',$name,true);
		$pool->update();
	}
}

function setEnclosure($name, $order) {
	requireModel('blog.feed');
	requireModel('blog.attachment');

	$pool = DBModel::getInstance();
	$blogid = getBlogId();

	$pool->reset('Attachments');
	$pool->setQualifier('blogid','eq',$blogid);
	$pool->setQualifier('name','eq',$name,true);
	$parent = $pool->getCell('parent');
	if($parent !== null) {
		$pool->setAttribute('enclosure',0);
		$pool->setQualifier('parent','eq',$parent);
		$pool->unsetQualifier('name');
		$pool->update();
		if($order) {
			clearFeed();
			$pool->setAttribute('enclosure',1);
			$pool->unsetQualifier('parent');
			$pool->setQualifier('name','eq',$name,true);
			return $pool->update();
		} else {
			return 0;
		}
	} else {
		return 3;	
	} 	
}

function getEnclosure($entry) {
	if ($entry < 0)
		return null;
	$pool = DBModel::getInstance();
	
	$pool->reset('Attachments');
	$pool->setQualifier('blogid','eq',getBlogId());
	$pool->setQualifier('parent','eq',$entry);
	$pool->setQualifier('enclosure','eq',1);
	return $pool->getCell('name');
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
