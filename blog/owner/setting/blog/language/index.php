<?php
define('ROOT', '../../../../..');
$IV = array(
	'GET' => array(
		'language'=> array('string', 'default' => 'ko'),
		'blogLanguage'=> array('string', 'default' => 'ko')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();
if (!empty($_GET['language']) && setBlogLanguage($owner, $_GET['language'], $_GET['blogLanguage'])) {
	respondResultPage(true);
}
respondResultPage(false);
?>