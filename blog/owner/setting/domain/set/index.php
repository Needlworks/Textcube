<?
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
else if(!setPrimaryDomain($owner, $_POST['primaryDomain']))
	respondResultPage(2);
else if(!setSecondaryDomain($owner, $_POST['secondaryDomain']))
	respondResultPage(3);
else if(!setDefaultDomain($owner, $_POST['defaultDomain']))
	respondResultPage(1);
else
	respondResultPage(0);
?>