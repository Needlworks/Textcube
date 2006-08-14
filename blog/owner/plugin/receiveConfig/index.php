<?php
define('ROOT', '../../../..');
$IV = array(	'POST' => array(	'Name' => array('string'),	
									'DATA' => array('string')
									)
		);
require ROOT . '/lib/includeForOwner.php';
if (false) {
    fetchConfigVal();
}
$pluginName = $_POST['Name'];
$DATA = $_POST['DATA'];
$result = handleDataSet($pluginName, $DATA );
printRespond($result);
?>
