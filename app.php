<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/// Prepare Textcube App storage / cache / attachment storage.
define('ROOT',dirname(__FILE__));
// $_SERVER['HOMEDRIVE'] and $_SERVER['HOMEPATH'] for windows app
$homedir = $_SERVER['HOME'];
$userdir = '/Library/Application Support/Textcube';
function remove_symlink($link) {
	if(file_exists($link) && is_link($link)) {
		echo($link."\n");
		unlink($link);
	}
}

if (!file_exists($homedir.$userdir)) {
	// Prepare the directory
	remove_symlink(ROOT.'/cache');
	remove_symlink(ROOT.'/attach');
	remove_symlink(ROOT.'/data');
	remove_symlink(ROOT.'/theme');
	if(mkdir($homedir.$userdir) &&
		mkdir($homedir.$userdir.'/attach') &&
		mkdir($homedir.$userdir.'/cache') &&
		mkdir($homedir.$userdir.'/data') &&
		mkdir($homedir.$userdir.'/theme')) {
		mkdir($homedir.$userdir.'/attach/1');
		copy(ROOT.'/resources/setup/textcube.db',$homedir.$userdir.'/data/textcube.db');
        echo ("prepared.");
    }
} else {
}
?>
