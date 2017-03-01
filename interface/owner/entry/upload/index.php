<?php
require ROOT . '/library/preprocessor.php';
requireModel("blog.attachment");
requireStrictRoute();

if (isset($_FILES['attachment'])) {
	$file = $_FILES['attachment'];

    if (getAttachmentByLabel($blogid, $suri['id'], Path::getBaseName($file['name']))) {
		echo samename;
	} else if (($attachment = addAttachment($blogid, $suri['id'], $file)) === false) {
		echo error;
	} else if (!empty($attachment)) {
		echo success;
	}

} else {
    echo "no";
}
?>