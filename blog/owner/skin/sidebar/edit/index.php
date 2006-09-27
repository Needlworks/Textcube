<?php
define('ROOT', '../../../../..');

$IV = array(
	'REQUEST' => array(
		'sidebarNumber' => array('int'),
		'modulePos' => array('int'),
		)
	);

require ROOT . '/lib/includeForOwner.php';
//requireStrictRoute();

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrderData = getSidebarModuleOrderData($sidebarCount);

$sidebarNumber = $_REQUEST['sidebarNumber'];
$modulePos = $_REQUEST['modulePos'];

if (($sidebarNumber < 0) || ($sidebarNumber >= $sidebarCount)) respondErrorPage();
if (!isset($sidebarOrderData[$sidebarNumber]) || !isset($sidebarOrderData[$sidebarNumber][$modulePos])) respondErrorPage();

$pluginData = $sidebarOrderData[$sidebarNumber][$modulePos];
if ($pluginData['type'] != 3) respondErrorPage();

$plugin = $pluginData['id']['plugin'];
$handler = $pluginData['id']['handler'];
$oldParameters = $pluginData['parameters'];

$identifier = $plugin . '/' . $handler;

$parameters = array();
foreach($sidebarMappings as $item) {
	if (($item['plugin'] == $plugin) && ($item['handler'] == $handler)) {
		$parameters = $item['parameters'];
		break;
	}
}

$params = array();
foreach($parameters as $item)
{
	$data = array();
	$data['name'] = $item['name'];
	switch($item['type']) {
		case 'string':
		case 'int':
			$data['type'] = 'text';
			break;
		default:
			$data['type'] = 'invalid';
			break;
	}	
	$data['title'] = $item['title'];
	if (isset($oldParameters[$item['name']])) {
		$data['value'] = $oldParameters[$item['name']];
	} else {
		$data['value'] = '';
	}
	
	array_push($params, $data);
}

ob_start();

if (count($params) > 0) {
	foreach($params as $item) {
		if ($data['type'] != 'invalid') {
			echo '<label ';
			echo 'for="' , $item['name'] , '" ';
			echo ' >';
			echo $item['title'];
			echo '</label>';
			
			echo '<input ';
			echo 'type="' , $item['type'] , '" ';
			echo 'name="' , $item['name'] , '" ';
			echo 'value="' , $item['value'] , '" ';
			echo ' />';
		}
	}
}

$result = ob_get_contents();
ob_end_clean();

echo '<form action="" method="POST" >';

echo $result;

echo '<input type="submit" value="' , _t('전송') , '" >';
echo '</form>';

?>