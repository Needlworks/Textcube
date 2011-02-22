<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$ajaxcall = (isset($_REQUEST['ajaxcall']) && $_REQUEST['ajaxcall'] == true)  ? true : false;
$ajaxmethod = isset($_REQUEST['ajaxcall']) ? $_REQUEST['ajaxcall'] : 'return false;';

$IV = array(
		'REQUEST' => array(
			'sidebarNumber' => array('int'),
			'modulePos' => array('int'),
			'viewMode' => array('string', 'default' => '')
			)
		);

require ROOT . '/library/preprocessor.php';
requireLibrary('blog.skin');
requireModel("blog.sidebar");
requireStrictRoute();

$skin = new Skin($skinSetting['skin']);
$sidebarCount = count($skin->sidebarBasicModules);
$sidebarOrderData = getSidebarModuleOrderData($sidebarCount);

$sidebarNumber = $_REQUEST['sidebarNumber'];
$modulePos = $_REQUEST['modulePos'];

if (($sidebarNumber < 0) || ($sidebarNumber >= $sidebarCount)) Respond::ErrorPage(null,null,null,$ajaxcall);
if (!isset($sidebarOrderData[$sidebarNumber]) || !isset($sidebarOrderData[$sidebarNumber][$modulePos])) Respond::ErrorPage(null,null,null,$ajaxcall);

$pluginData = $sidebarOrderData[$sidebarNumber][$modulePos];
if ($pluginData['type'] != 3) Respond::ErrorPage(null,null,null,$ajaxcall);

$plugin = $pluginData['id']['plugin'];
$handler = $pluginData['id']['handler'];
$oldParameters = $pluginData['parameters'];

$title = $plugin . '::' . $handler;

foreach($sidebarMappings as $sm)
{
	if (($sm['plugin'] == $plugin) && ($sm['handler'] == $handler))
		$title = $sm['display'] . '::' . $sm['title'];
}


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
        case 'textarea':
            $data['type'] = 'textarea';
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
        switch ($data['type']) {       
            case 'text':
			    echo '<div class="line">';
			    echo '<label ';
			    echo 'for="' , $item['name'] , '" ';
			    echo ' >';
			    echo $item['title'];
			    echo '</label>';
			    
			    echo '<input class="input-text" ';
			    echo 'type="' , $item['type'] , '" ';
			    echo 'name="' , $item['name'] , '" ';
			    echo 'value="' , htmlspecialchars($item['value'],ENT_QUOTES) , '" ';
			    echo ' />';
			    echo '</div>';
                break;
            case 'textarea':
                echo '<div class="line">';
                echo '<label for="' , $item['name'] , '">';
			    echo $item['title'];
			    echo '</label>';
                echo '<textarea id="' , $item['name'] , '" name="' , $item['name'] , '" cols="50" rows="10">';
                echo htmlspecialchars($item['value']);
                echo '</textarea>';
                echo '</div>';
                break;
		}
	}
}

$result = ob_get_contents();
ob_end_clean();

if ($ajaxcall == false) {
	require ROOT . '/interface/common/owner/header.php';
	
}

$modeParam = !empty($_REQUEST['viewMode']) ? '&' . $_REQUEST['viewMode'] : '';

echo '<h2 class="caption"><span class="main-text">' . $title . '</span></h2>';
echo '<form action="' . $blogURL . '/owner/skin/sidebar/setPlugin?sidebarNumber=', $sidebarNumber, '&modulePos=', $modulePos, $modeParam, '" method="POST" >';
echo '	<div class="field-box">';
echo $result;
echo '	</div>';
echo '	<div class="button-box">';
if ($ajaxcall == false) {
	echo '		<input class="input-button" type="submit" value="' , _t('전송') , '" />';
} else {
	echo '		<input class="input-button" type="submit" value="' , _t('전송') , '" onclick="',$ajaxmethod,'; return false" />';
}
echo '	</div>';
echo '</form>';

if ($ajaxcall == false) {
	require ROOT . '/interface/common/owner/footer.php';
}
?>
