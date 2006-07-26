<?php
$activePlugins = array();
$eventMappings = array();
$tagMappings = array();
$sidebarMappings = array();
$centerMappings = array();

$configMappings = array();
$baseConfigPost = $service['path'].'/owner/setting/plugins/currentSetting';
$configPost  = '';
$configVal = '';


if (!empty($owner)) {
	$activePlugins = fetchQueryColumn("SELECT name FROM {$database['prefix']}Plugins WHERE owner = $owner");
	$xmls = new XMLStruct();
	foreach ($activePlugins as $plugin) {
		$manifest = @file_get_contents(ROOT . "/plugins/$plugin/index.xml");
		if ($manifest && $xmls->open($manifest)) {
			if ($xmls->doesExist('/plugin/binding/listener')) {
				foreach ($xmls->selectNodes('/plugin/binding/listener') as $listener) {
					if (!empty($listener['.attributes']['event']) && !empty($listener['.value'])) {
						if (!isset($eventMappings[$listener['.attributes']['event']]))
							$eventMappings[$listener['.attributes']['event']] = array();
						array_push($eventMappings[$listener['.attributes']['event']], array('plugin' => $plugin, 'listener' => $listener['.value']));
					}
				}
				unset($listener);
			}
			if ($xmls->doesExist('/plugin/binding/tag')) {
				foreach ($xmls->selectNodes('/plugin/binding/tag') as $tag) {
					if (!empty($tag['.attributes']['name']) && !empty($tag['.attributes']['handler'])) {
						if (!isset($tagMappings[$tag['.attributes']['name']]))
							$tagMappings[$tag['.attributes']['name']] = array();
						array_push($tagMappings[$tag['.attributes']['name']], array('plugin' => $plugin, 'handler' => $tag['.attributes']['handler']));
					}
				}
				unset($tag);
			}
			if ($xmls->doesExist('/plugin/binding/center')) {
				$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
				foreach ($xmls->selectNodes('/plugin/binding/center') as $center) {
					if (!empty($center['.attributes']['handler'])) {
						array_push($centerMappings, array('plugin' => $plugin, 'handler' => $center['.attributes']['handler'], 'title' => $title));
					}
				}
				unset($title);
				unset($center);
			}
			if ($xmls->doesExist('/plugin/binding/sidebar')) {
				foreach ($xmls->selectNodes('/plugin/binding/sidebar') as $sidebar) {
					if (!empty($sidebar['.attributes']['handler'])) {
						array_push($sidebarMappings, array('plugin' => $plugin, 'class' => $sidebar['.attributes']['class'], 'title' => $sidebar['.attributes']['title'], 'handler' => $sidebar['.attributes']['handler']));
					}
				}
				unset($sidebar);
			}
			if($xmls->doesExist('/plugin/binding/config')){
				$config = $xmls->selectNode('/plugin/binding/config');
				if( !empty( $config['.attributes']['dataValHandler'] ) )
					$configMappings[$plugin] = 
					array( 'config' => 'ok' , 'dataValHandler' => $config['.attributes']['dataValHandler'] );
				else
					$configMappings[$plugin] = array( 'config' => 'ok') ;
			}
		} else {
			$plugin = mysql_escape_string($plugin);
			mysql_query("DELETE FROM {$database['prefix']}Plugins WHERE owner = $owner AND name = '$plugin'");
		}
	}
	unset($xmls);
	unset($plugin);
}

function fireEvent($event, $target = null, $mother = null, $condition = true) {
	global $service, $eventMappings, $pluginURL,  $configMappings , $configVal;
	if (!$condition)
		return $target;
	if (!isset($eventMappings[$event]))
		return $target;
	foreach ($eventMappings[$event] as $mapping) {
		include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
		if (function_exists($mapping['listener'])) {
			if( !empty( $configMappings[$mapping['plugin']]['config'] ) ) 				
				$configVal = & getCurrentSetting($mapping['plugin']);
			else
				$configVal =null;
			$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
			$target = call_user_func($mapping['listener'], $target, $mother);
		}
	}
	return $target;
}

function handleTags( & $content) {
	global $service, $tagMappings, $pluginURL, $configMappings, $configVal;
	if (preg_match_all('/\[##_(\w+)_##\]/', $content, $matches)) {
		foreach ($matches[1] as $tag) {
			if (!isset($tagMappings[$tag]))
				continue;
			$target = '';
			foreach ($tagMappings[$tag] as $mapping) {
				include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
				if (function_exists($mapping['handler'])) {
					if( !empty( $configMappings[$mapping['plugin']]['config'] ) ) 				
						$configVal = getCurrentSetting($mapping['plugin']);
					else
						$configVal ='';
					$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
					$target = call_user_func($mapping['handler'], $target);
				}
			}
			dress($tag, $target, $content);
		}
	}
}

function handleCenters($mapping) {
	global $service, $pluginURL, $configMappings, $configVal;

	include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
	if (function_exists($mapping['handler'])) {
		if( !empty( $configMappings[$mapping['plugin']]['config'] ) ) 				
			$configVal = getCurrentSetting($mapping['plugin']);
		else
			$configVal ='';
		$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
		$target = call_user_func($mapping['handler'], $target);
	}

	return $target;
}

function handleSidebars( & $obj) {
	global $service, $sidebarMappings, $pluginURL;
	
	$content_temp = '';

	foreach ($sidebarMappings as $mapping) {
		include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
		$content_temp .= $obj->sidebarItem;

		if (preg_match_all('/\[##_(\w+)_##\]/', $content_temp, $matches)) {
			foreach ($matches[1] as $tag) {
				$target = $title = '';

				switch($tag) {
					case 'sidebar_id':
						dress('sidebar_id', $mapping['plugin'], $content_temp);
						break;
					case 'sidebar_class':
						dress('sidebar_class', $mapping['class'], $content_temp);
						break;
					case 'sidebar_titles':
						if($mapping['title']) {
							dress('sidebar_titles', $obj->sidebarTitles, $content_temp);
							dress('sidebar_title', $mapping['title'], $content_temp);
						} else {
							dress('sidebar_titles', '', $content_temp);
						}
						break;
					case 'sidebar_contents':
						if (function_exists($mapping['handler'])) {
							$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
							$target = call_user_func($mapping['handler'], $target, $content);
						}
						dress('sidebar_contents', $target, $content_temp);
						break;
				}
			}
		}
	}
	$obj->sidebarItem = $content_temp;
}

function handleDataSet( $plugin , $DATA ){
	global $configMappings;
	$reSetting = true;
	if( !empty( $configMappings[$plugin]['dataValHandler'] ) ){
		include_once (ROOT . "/plugins/$plugin/index.php");
		if( function_exists( $configMappings[$plugin]['dataValHandler'] ) )
			$reSetting = call_user_func( $configMappings[$plugin]['dataValHandler'] , $DATA);
		if( true !== $reSetting )	
			return array( 'error' => '9', 'customError' => $reSetting)	;
	}
	$result = updatePluginConfig($plugin, $DATA);
	return array('error' => $result , 'customError'=> '' ) ;
}
function fetchConfigVal( $DATA ){
	$xmls = new XMLStruct();
	$outVal = array();
	if( ! $xmls->open($DATA) ) return null;
	if( is_null(  $xmls->selectNodes('/config/fieldset') )) return null;
	foreach ($xmls->selectNodes('/config/fieldset') as $fieldset) {
		if (empty($fieldset['.attributes']['name']) )  return null;
		$outVal[$fieldset['.attributes']['name']] = array();
		foreach( $fieldset['field'] as $field ){
			if( empty( $field['.attributes']['name'] )  || empty( $field['.attributes']['type'] ) )
				return null;
			if( 'checkbox' != $field['.attributes']['type'] ) 
				$outVal[$fieldset['.attributes']['name']][$field['.attributes']['name']] = $field['.value'] ;
			else{
				$outVal[$fieldset['.attributes']['name']][$field['.attributes']['name']] = array();
				if( !empty( $field['vals'] ))
					foreach( $field['vals'] as $val )
						array_push( $outVal[$fieldset['.attributes']['name']][$field['.attributes']['name']] , $val['.value']);
			}
		}
	}
	$xmls= null;	
	return ( $outVal);
}

function handleConfig( $plugin){
	global $service;
	$manifest = @file_get_contents(ROOT . "/plugins/$plugin/index.xml");
	$xmls = new XMLStruct();	
	$CDSPval = '';
	$i=0;
	$dfVal =  fetchConfigVal(getCurrentSetting($plugin));
	if ($manifest && $xmls->open($manifest)) {
	
		if( is_null( $xmls->selectNodes ( '/plugin/binding/config/fieldset' )) ) return  _t('?xr?? ?|  ????H.'); 	
		foreach ($xmls->selectNodes('/plugin/binding/config/fieldset') as $fieldset) {
			$legend = !empty($fieldset['.attributes']['legend']) ? htmlspecialchars($fieldset['.attributes']['legend']) :'';
			if( empty($fieldset['.attributes']['name']) ) continue;
			$CDSPval .= "<fieldset name='{$fieldset['.attributes']['name']}' ><legend>$legend</legend>";
			if( !empty( $fieldset['field'] ) ){
				foreach( $fieldset['field'] as $field ){
					$CDSPval .=  TreatType( $field , empty($dfVal[$fieldset['.attributes']['name']][$field['.attributes']['name']]) ? null : $dfVal[$fieldset['.attributes']['name']][$field['.attributes']['name']]) ;	
				}
			}
			$CDSPval .= '</fieldset>';
			$i++;
		}
	}else	$CDSPval = _t('?xr?? ?|  ????H.'); 	
	return $CDSPval;
}

function TreatType(  $cmd , $dfVal ){
	$typeSchema = array(
		'text' 
	,	'textarea'
	,	'select'
	,	'checkbox'
	,	'radio'
	);
	if( empty($cmd['.attributes']['type']) || !in_array($cmd['.attributes']['type'] , $typeSchema  ) ) return '';
	if( empty($cmd['.attributes']['title']) || empty($cmd['.attributes']['name'])) return '';
	return	'<field name="'.htmlspecialchars($cmd['.attributes']['name']).'" type="'.$cmd['.attributes']['type'].'"><fieldTitle >'.htmlspecialchars($cmd['.attributes']['title']) . '</fieldTitle ><fieldControl >' .  call_user_func($cmd['.attributes']['type'].'Treat' , $cmd, $dfVal) .'</fieldControl ></field>';
}
function textTreat( $cmd , $dfVal ){
	$dfVal = ( !is_null( $dfVal  ) ) ? $dfVal  :  (empty($cmd['.attributes']['value'] )?null:$cmd['.attributes']['value'] );
	$DSP = '<input type="text" name="'.htmlspecialchars($cmd['.attributes']['name']).'" ';
	$DSP .= empty( $cmd['.attributes']['size'] ) ? '' : 'size="'. $cmd['.attributes']['size'] . '"' ;
	$DSP .= is_null( $dfVal  ) ? '' : 'value="'. htmlspecialchars($dfVal). '"' ;
	$DSP .= ' />' ;
	return $DSP;
}
function textareaTreat( $cmd, $dfVal){
	$dfVal = ( !is_null( $dfVal  ) ) ? $dfVal  :  (empty($cmd['.attributes']['value'] )?null:$cmd['.attributes']['value'] );
	$DSP = '<textarea ';
	$DSP .= empty( $cmd['.attributes']['rows'] ) ? '' : 'rows="'. $cmd['.attributes']['rows'] . '"' ;
	$DSP .= empty( $cmd['.attributes']['cols'] ) ? '' : 'cols="'. $cmd['.attributes']['cols'] . '"' ;
	$DSP .= '>';
	$DSP .= is_null( $dfVal  )  ? '' : htmlspecialchars($dfVal);
	$DSP .= '</textarea>' ;
	return $DSP;
}
function selectTreat( $cmd, $dfVal ){
	$DSP = '<select >';
	foreach( $cmd['op']  as $option ){
		$DSP .= '<option ';
		$DSP .= empty( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= !empty( $option['.attributes']['checked'] ) && 'true' == $option['.attributes']['checked'] && is_null($dfVal) ? 'selected="true" ' : '';
		$DSP .= !is_null($dfVal) && (empty( $option['.attributes']['value'] ) ? '' : $option['.attributes']['value']== $dfVal ) ? 'selected="true" ' : '';
		$DSP .= '>';
		$DSP .= $option['.value'];
		$DSP .= '</option>';
	}
	$DSP .= '</select>' ;
	return $DSP;
}
function checkboxTreat( $cmd, $dfVal){
	$DSP = '';	
	foreach( $cmd['op']  as $option ){
		$DSP .= '<input type="checkbox" ';
		$DSP .= empty( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= !empty( $option['.attributes']['checked'] ) && 'true' == $option['.attributes']['checked'] && is_null($dfVal) ? 'checked="true" ' : '';
		$DSP .= count($dfVal) > 0  && in_array( (empty( $option['.attributes']['value'] ) ? '' : $option['.attributes']['value'] ) , $dfVal) ? 'checked="true" ' : '';
		$DSP .= '>' ;
		$DSP .= $option['.value'];
	}
	return $DSP;
}
function radioTreat( $cmd, $dfVal){
	$DSP = '';
	foreach( $cmd['op']  as $option ){
		$DSP .= '<input type="radio" name="'. htmlspecialchars($cmd['.attributes']['name']) .'" ';
		$DSP .= empty( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= !empty( $option['.attributes']['checked'] ) && 'true' == $option['.attributes']['checked'] && is_null($dfVal) ? 'checked="true" ' : '';
		$DSP .= !is_null($dfVal) && (empty( $option['.attributes']['value'] ) ? '' : $option['.attributes']['value']== $dfVal ) ? 'checked="true" ' : '';
		$DSP .= '>' ;
		$DSP .= $option['.value'];
	}
	return $DSP;
}
?>
