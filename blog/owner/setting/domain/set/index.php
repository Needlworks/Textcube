<?php
define('ROOT', '../../../../..');
$IV = array(
	'POST' => array(
		'defaultDomain' => array(array('0', '1')),
		'primaryDomain' => array('string'),
		'secondaryDomain' => array('domain', 'default' => '')
	)
);
require ROOT . '/lib/includeForOwner.php';
requireStrictRoute();

if(empty($_POST['secondaryDomain']) && $_POST['defaultDomain'] == 1)
	respondResultPage(4);
else if( ($result = setPrimaryDomain($owner, $_POST['primaryDomain'])) > 0 )
	printRespond(array('error' => 2, 'msg' => $result));
else if( ($result = setSecondaryDomain($owner, $_POST['secondaryDomain'])) > 0 )
	printRespond(array('error' => 3, 'msg' => $result));
else if(!setDefaultDomain($owner, $_POST['defaultDomain']))
	respondResultPage(1);
else
	respondResultPage(0);
?>