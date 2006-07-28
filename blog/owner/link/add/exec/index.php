<?php
define('ROOT', '../../../../..');
$IV = array(  
	'POST' => array(  
		'name' => array( 'string' , 'min' => 0 ,  'max' => 255),  
		'rss' => array( 'string' , 'min' => 0 ,  'max' => 255 , 'mandatory' => false),  
		'url' => array( 'string' , 'min' => 0 ,  'max' => 255)  
	)  
); 
require ROOT . '/lib/includeForOwner.php';
respondResultPage(addLink($owner, $_POST));
?>