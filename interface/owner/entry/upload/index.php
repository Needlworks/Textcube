<?php
require ROOT . '/library/preprocessor.php';
requireModel("blog.attachment");
requireStrictRoute();

if (isset($_FILES['attachment'])) {
	$file = $_FILES['attachment'];

 	if (($attachment = addAttachment($blogid, $_GET['postId'], $file)) === false) {
		echo error;
	} else if (!empty($attachment)) {

		$oOptionInnerHTML = getPrettyAttachmentLabel($attachment);
		$oOptionValue = getAttachmentValue($attachment);

		echo json_encode(array($attachment, $oOptionInnerHTML, $oOptionValue));
	}

} else {
    echo "no";
}
?>