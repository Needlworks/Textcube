<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
$IV = array(
	'POST' => array(
		'targets' => array ('list'),
		'userid' => array ('id')
	)
);

require ROOT . '/library/preprocessor.php';
requireStrictRoute();
importlib("model.blog.entry");

$username = User::getName($_POST['userid']);
if(Acl::check('group.administrators')) {
	if(!is_null($username) && changeAuthorOfEntries($blogid, $_POST['targets'], $_POST['userid'])) {
		Respond::PrintResult(array('error' => 0, 'name' => $username));
	} else
		Respond::PrintResult(array('error' => 1, 'message' => _t('존재하지 않은 사용자입니다')));
} else
	Respond::PrintResult(array('error' => 1, 'message' => _t('권한이 없습니다.')));

?>
