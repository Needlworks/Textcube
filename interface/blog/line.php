<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

require ROOT . '/library/preprocessor.php';

$lineobj = Line::getInstance();
$lineobj->reset();
// If line comes.
if(isset($_GET['key']) && isset($_GET['content'])) {
	$password = Setting::getBlogSetting('LinePassword', null, true);
	if($password == $_GET['key']) {
		$lineobj->content = $_GET['content'];
		$lineobj->showResult($lineobj->add());
	}
} else {
	/// Prints public lines
	$lineobj->setFilter(array('created','bigger',(Timestamp::getUNIXTime()-86400)));
	$lineobj->setFilter(array('category','equals','public',true));

	$lines = $lineobj->get();

	fireEvent('OBStart');
	require ROOT . '/interface/common/blog/begin.php';
	require ROOT . '/interface/common/blog/line.php';
	require ROOT . '/interface/common/blog/end.php';
	fireEvent('OBEnd');
}
?>
