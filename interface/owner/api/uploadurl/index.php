<?php
/// Copyright (c) 2004-2014, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$IV = array(
		'POST' => array(
				'target' => array('any', 'mandatory' => true)
		)
);
require ROOT . '/library/preprocessor.php';
require ROOT . '/library/error.php';
requireStrictRoute();

if (substr($_POST['target'], 0, 7) != '/owner/') {
	errorExit(404);
}
if (! defined('__TEXTCUBE_GAE__')) {
	Respond::PrintResult(array(
			'error' => 0,
			'url' => $_POST['target']
	));
	exit();
}

use google\appengine\api\cloud_storage\CloudStorageTools;

$options = ['gs_bucket_name' => $_SERVER['blog_fs_bucket']];
$upload_url = CloudStorageTools::createUploadUrl($_POST['target'], $options);

Respond::PrintResult(array(
		'error' => 0,
		'url' => $upload_url
));
?>
