<?php
define('ROOT', '../../../../..');
$IV = array(  
	'GET' => array(  
		'language' => array('language')      
	)  
);  
require ROOT . '/lib/includeForOwner.php';
if (!empty($_GET['language']) && setBlogLanguage($owner, $_GET['language'], $_GET['blogLanguage'])) {
	respondResultPage(0);
}
respondResultPage( - 1);
?>
