<?php
$activePlugins = array();
$eventMappings = array();
$tagMappings = array();
$sidebarMappings = array();
$centerMappings = array();
$storageMappings = array();
$storageKeymappings = array();
$adminMenuMappings = array();
$adminHandlerMappings = array();

$configMappings = array();
$baseConfigPost = $service['path'].'/owner/setting/plugins/currentSetting';
$configPost  = '';
$configVal = '';
$typeSchema = null;

if (!empty($owner)) {
	$activePlugins = fetchQueryColumn("SELECT name FROM {$database['prefix']}Plugins WHERE owner = $owner");
	$xmls = new XMLStruct();
	foreach ($activePlugins as $plugin) {
		$manifest = @file_get_contents(ROOT . "/plugins/$plugin/index.xml");
		if ($manifest && $xmls->open($manifest)) {
			$version = '';
			if ($xmls->doesExist('/plugin/version')) {
				$version = $xmls->getValue('/plugin/version');
			}
			if ($xmls->doesExist('/plugin/storage')) {
				foreach ($xmls->selectNodes('/plugin/storage/table') as $table) {
					$storageMappings = array();
					$storageKeymappings = array();					 
					if(empty($table['name'][0]['.value'])) continue;
					$tableName = htmlspecialchars($table['name'][0]['.value']);
					if (!empty($table['fields'][0]['field'])) {
						foreach($table['fields'][0]['field'] as $field) 
						{
							if (!isset($field['name']))
								continue; // Error? maybe loading fail, so skipping is needed.
							$fieldName = $field['name'][0]['.value'];
							
							if (!isset($field['attribute']))
								continue; // Error? maybe loading fail, so skipping is needed.
							$fieldAttribute = $field['attribute'][0]['.value'];
							
							$fieldLength = isset($field['length']) ? $field['length'][0]['.value'] : -1;
							$fieldIsNull = isset($field['isnull']) ? $field['isnull'][0]['.value'] : 1;
							$fieldDefault = isset($field['default']) ? $field['default'][0]['.value'] : null;
							
							array_push($storageMappings, array('name' => $fieldName, 'attribute' => $fieldAttribute, 'length' => $fieldLength, 'isnull' => $fieldIsNull, 'default' => $fieldDefault));
						}
					}
					if (!empty($table['key'][0]['.value'])) {
						foreach($table['key'] as $key) {
							array_push($storageKeymappings, $key['.value']);
						}
					}
					treatPluginTable($plugin, $tableName,$storageMappings,$storageKeymappings, $version);
					unset($tableName);
					unset($storageMappings);
					unset($storageKeymappings);
				}
			}
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
				$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
				foreach ($xmls->selectNodes('/plugin/binding/sidebar') as $sidebar) {
					if (!empty($sidebar['.attributes']['handler'])) {
						// parameter parsing
						$parameters = array();
						if (isset($sidebar['params']) && isset($sidebar['params'][0]) && isset($sidebar['params'][0]['param'])) {
							foreach($sidebar['params'][0]['param'] as $param) {
								$parameter = array('name' => $param['name'][0]['.value'], 'type' => $param['type'][0]['.value'], 'title' => XMLStruct::getValueByLocale($param['title']));
								array_push($parameters, $parameter);				
							}
						}
						array_push($sidebarMappings, array('plugin' => $plugin, 'title' => $sidebar['.attributes']['title'], 'display' => $title, 'handler' => $sidebar['.attributes']['handler'], 'parameters' => $parameters));
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
			if ($xmls->doesExist('/plugin/binding/adminMenu')){
				$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
				
				if ($xmls->doesExist('/plugin/binding/adminMenu/viewMethods')){
					foreach($xmls->selectNodes('/plugin/binding/adminMenu/viewMethods/method') as $adminViewMenu) {
						$menutitle = htmlspecialchars(XMLStruct::getValueByLocale($adminViewMenu['title']));
						if (empty($menutitle)) continue;
						$menuposition = empty($adminViewMenu['position'][0]['.value'])?'menu-plugin':$adminViewMenu['position'][0]['.value'];
						$menuhelpurl = empty($adminViewMenu['helpurl'][0]['.value'])?'':$adminViewMenu['helpurl'][0]['.value'];
						
						if (!isset($adminViewMenu['handler'][0]['.value'])) continue;
						$viewhandler = htmlspecialchars($adminViewMenu['handler'][0]['.value']);	
						if (empty($viewhandler)) continue;
						$params = array();
						if (isset($adminViewMenu['params'][0]['param'])) {
							foreach($adminViewMenu['params'][0]['param'] as $methodParam) {
									if (!isset($methodParam['name'][0]['.value']) || !isset($methodParam['type'][0]['.value'])) continue;
									array_push($params,array(
											'name' => $methodParam['name'][0]['.value'],
											'type' => $methodParam['type'][0]['.value'],
											'mandatory' => @$methodParam['mandatory'][0]['.value'],
											'default' => @$methodParam['default'][0]['.value']
											));
							}
						}
								
						$adminMenuMappings[$plugin . '/' . $viewhandler] 
							= array('plugin' => $plugin, 'title' => $menutitle,'position' => $menuposition , 'handler' => $viewhandler, 'params' => $params , 'helpurl'=>$menuhelpurl);
					}
				}
				
				unset($menutitle);
				unset($viewhandler);
				unset($adminViewMenu);
				unset($params);
				
				if ($xmls->doesExist('/plugin/binding/adminMenu/methods')){
					foreach($xmls->selectNodes('/plugin/binding/adminMenu/methods/method') as $adminMethods) {
						$method = array();
						$method['plugin'] = $plugin;
						if (!isset($adminMethods['handler'][0]['.value'])) continue;
						$method['handler'] = $adminMethods['handler'][0]['.value'];
						$method['params'] = array();
							if (isset($adminMethods['params'][0]['param'])) {
							foreach($adminMethods['params'][0]['param'] as $methodParam) {
								if (!isset($methodParam['name'][0]['.value']) || !isset($methodParam['type'][0]['.value'])) continue;
								array_push($method['params'],array(
									'name' => $methodParam['name'][0]['.value'],
									'type' => $methodParam['type'][0]['.value'],
									'mandatory' => @$methodParam['mandatory'][0]['.value'],
									'default' => @$methodParam['default'][0]['.value']
								));
							}
						}
						$adminHandlerMappings[$plugin . '/' . $method['handler']] = $method;
					}
				}
				
				unset($method);
				unset($methodParam);
				unset($adminMethods);
				
			}
		} else {
			$plugin = mysql_real_escape_string($plugin);
			mysql_query("DELETE FROM {$database['prefix']}Plugins WHERE owner = $owner AND name = '$plugin'");
		}
	}
	unset($xmls);
	unset($plugin);
	
}

function fireEvent($event, $target = null, $mother = null, $condition = true) {
	global $service, $eventMappings, $pluginURL, $pluginPath, $configMappings, $configVal;
	if (!$condition)
		return $target;
	if (!isset($eventMappings[$event]))
		return $target;
	foreach ($eventMappings[$event] as $mapping) {
		include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
		if (function_exists($mapping['listener'])) {
			if( !empty( $configMappings[$mapping['plugin']]['config'] ) ) 				
				$configVal = getCurrentSetting($mapping['plugin']);
			else
				$configVal =null;
			$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
			$pluginPath = ROOT . "/plugins/{$mapping['plugin']}";
			$target = call_user_func($mapping['listener'], $target, $mother);
		}
	}
	return $target;
}

function handleTags( & $content) {
	global $service, $tagMappings, $pluginURL, $pluginPath, $configMappings, $configVal;
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
					$pluginPath = ROOT . "/plugins/{$mapping['plugin']}";
					$target = call_user_func($mapping['handler'], $target);
				}
			}
			dress($tag, $target, $content);
		}
	}
}

function handleCenters($mapping) {
	global $service, $pluginURL, $pluginPath, $configMappings, $configVal;
	$target = '';

	include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
	if (function_exists($mapping['handler'])) {
		if( !empty( $configMappings[$mapping['plugin']]['config'] ) ) 				
			$configVal = getCurrentSetting($mapping['plugin']);
		else
			$configVal ='';
		$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
		$pluginPath = ROOT . "/plugins/{$mapping['plugin']}";
		$target = call_user_func($mapping['handler'], $target);
	}

	return $target;
}

// 저장된 사이드바 정렬 순서 정보를 가져온다.
function handleSidebars(& $sval, & $obj) {
	global $service, $pluginURL, $pluginPath, $configVal, $configMappings;
	$newSidebarAllOrders = array(); 
	// [sidebar id][element id](type, id, parameters)
	// type : 1=skin text, 2=default handler, 3=plug-in
	// id : type1=sidebar i, type2=handler id, type3=plug-in handler name
	// parameters : type1=sidebar j, blah blah~
	
	$sidebarCount = count($obj->sidebarBasicModules);
	$sidebarAllOrders = getSidebarModuleOrderData($sidebarCount);
	
	for ($i=0; $i<$sidebarCount; $i++) {
		$str = "";
		if ((!is_null($sidebarAllOrders)) && ((array_key_exists($i, $sidebarAllOrders)))) {
			$currentSidebarOrder = $sidebarAllOrders[$i];
			for ($j=0; $j<count($currentSidebarOrder); $j++) {
				if ($currentSidebarOrder[$j]['type'] == 1) { // skin text
					$skini = $currentSidebarOrder[$j]['id'];
					$skinj = $currentSidebarOrder[$j]['parameters'];
					if (isset($obj->sidebarBasicModules[$skini]) && isset($obj->sidebarBasicModules[$skini][$skinj])) {
						$str .= $obj->sidebarBasicModules[$skini][$skinj]['body'];
					}
				} else if ($currentSidebarOrder[$j]['type'] == 2) { // default handler
					// TODO : implement
				} else if ($currentSidebarOrder[$j]['type'] == 3) { // plugin
					$plugin = $currentSidebarOrder[$j]['id']['plugin'];
					$handler = $currentSidebarOrder[$j]['id']['handler'];
					include_once (ROOT . "/plugins/{$plugin}/index.php");
					if (function_exists($handler)) {
						$str .= "[##_temp_sidebar_element_{$i}_{$j}_##]";
						$parameters = $currentSidebarOrder[$j]['parameters'];
						$pluginURL = "{$service['path']}/plugins/{$plugin}";
						$pluginPath = ROOT . "/plugins/{$plugin}";
						if( !empty( $configMappings[$plugin]['config'] ) ) 				
							$configVal = getCurrentSetting($plugin);
						else
							$configVal ='';
						
						if (function_exists($handler)) {
							$obj->sidebarStorage["temp_sidebar_element_{$i}_{$j}"] = call_user_func($handler, $parameters);
						} else {
							$obj->sidebarStorage["temp_sidebar_element_{$i}_{$j}"] = "";
						}
					}
				} else {
					// WHAT?
				}
			}
		} else {
			$newSidebarAllOrders[$i] = array();
			
			for ($j=0; $j<count($obj->sidebarBasicModules[$i]); $j++) {
				$str .= $obj->sidebarBasicModules[$i][$j]['body'];
				array_push($newSidebarAllOrders[$i], array('type' => '1', 'id' => "$i", 'parameters' => "$j"));
			}
		}
		
		dress("sidebar_{$i}", $str, $sval);
	}
	
	if (count($newSidebarAllOrders) > 0) {
		setUserSetting("sidebarOrder", serialize($newSidebarAllOrders));
	}
}

function handleDataSet( $plugin , $DATA ){
	global $configMappings, $activePlugins, $service, $pluginURL, $pluginPath, $configMapping, $configVal;
	$xmls = new XMLStruct();
	if( ! $xmls->open($DATA) ) {
		unset($xmls);	
		return array('error' => '3' ,'customError' => '' ) ;
	}unset($xmls);	
	if( ! in_array($plugin, $activePlugins) ) 
		return array('error' => '9' , 'customError'=> _t($plugin.'사용중인 플러그인만 설정을 변경할 수 있습니다.')) ;
	$reSetting = true;
	if( !empty( $configMappings[$plugin]['dataValHandler'] ) ){
		$pluginURL = "{$service['path']}/plugins/{$plugin}";
		$pluginPath = ROOT . "/plugins/{$plugin}";
		include_once (ROOT . "/plugins/{$plugin}/index.php");
		if( function_exists( $configMappings[$plugin]['dataValHandler'] ) ) {
			if( !empty( $configMappings[$plugin]['config'] ) ) 				
				$configVal = getCurrentSetting($plugin);
			else
				$configVal ='';
			$reSetting = call_user_func( $configMappings[$plugin]['dataValHandler'] , $DATA);
		}
		if( true !== $reSetting )	
			return array( 'error' => '9', 'customError' => $reSetting)	;
	}
	$result = updatePluginConfig($plugin, $DATA);
	return array('error' => $result , 'customError'=> '' ) ;
}

function fetchConfigVal( $DATA ){
	$xmls = new XMLStruct();
	$outVal = array();
	if( ! $xmls->open($DATA) ) {
		unset($xmls);	
		return null;
	}
	if( is_null(  $xmls->selectNodes('/config/field') )){
	 	unset($xmls);	
		return null;
	}
	foreach ($xmls->selectNodes('/config/field') as $field) {
		if( empty( $field['.attributes']['name'] )  || empty( $field['.attributes']['type'] ) ){
		 	unset($xmls);	
			return null;
		}
		$outVal[$field['.attributes']['name']] = $field['.value'] ;
	}
	unset($xmls);	
	return ( $outVal);
}



function handleConfig($plugin){
	global $service , $typeSchema, $pluginURL, $pluginPath, $configMappings, $configVal, $adminSkinSetting;
	
	$typeSchema = array(
		'text' 
	,	'textarea'
	,	'select'
	,	'checkbox'
	,	'radio'
	);
	$manifest = @file_get_contents(ROOT . "/plugins/$plugin/index.xml");
	$xmls = new XMLStruct();	
	$CDSPval = '';
	$i=0;
	$dfVal =  fetchConfigVal(getCurrentSetting($plugin));
	$name = '';
	$clientData ='[';
	
	if ($manifest && $xmls->open($manifest)) {
		//설정 핸들러가 존재시 바꿈
		$config = $xmls->selectNode('/plugin/binding/config');
		unset( $xmls );
		if( !empty($config['.attributes']['manifestHandler']) ){
			$handler = $config['.attributes']['manifestHandler'] ;
			$oldconfig = $config;
			include_once (ROOT . "/plugins/$plugin/index.php");
			if (function_exists($handler)) {
				if( !empty( $configMappings[$plugin]['config'] ) ) 				
					$configVal = getCurrentSetting($plugin);
				else
					$configVal ='';
				$manifest = call_user_func( $handler , $plugin );
			}
			$newXmls = new XMLStruct();
			if($newXmls->open( $manifest) ){	 
				unset( $config );
				$config = $newXmls->selectNode('/config');
			}
			unset( $newXmls);
		}
		if( is_null( $config['fieldset'] ) ) 
			return array( 'code' => _t('설정 값이 없습니다.') , 'script' => '[]' ) ;  	
		foreach ($config['fieldset'] as $fieldset) {
			$legend = !empty($fieldset['.attributes']['legend']) ? htmlspecialchars($fieldset['.attributes']['legend']) :'';
			$CDSPval .= CRLF.TAB."<fieldset>".CRLF.TAB.TAB."<legend><span class=\"text\">$legend</span></legend>".CRLF;
			if( !empty( $fieldset['field'] ) ){
				foreach( $fieldset['field'] as $field ){
					if( empty( $field['.attributes']['name'] ) ) continue;
					$name = $field['.attributes']['name'] ;
					$clientData .= getFieldName($field , $name) ;
					$CDSPval .=  TreatType( $field , $dfVal ,  $name) ;	
				}
			}
			$CDSPval .= TAB."</fieldset>".CRLF;
		}
	}else	$CDSPval = _t('설정 값이 없습니다.'); 	
	$clientData .= ']';
	return array( 'code' => $CDSPval , 'script' => $clientData ) ;
}

function getFieldName( $field , $name ){
	if( 'checkbox' != $field['.attributes' ]['type'] ) return '"' . $name . '",';
	$tname ='';
	foreach( $field['op'] as $op ){
		if( !empty( $op['.attributes']['name'] ) )
			$tname .= '"' . $op['.attributes']['name'] . '",';
	}
	return $tname;
}

function TreatType(  $cmd , $dfVal , $name ){
	global $typeSchema;
	if( empty($cmd['.attributes']['type']) || !in_array($cmd['.attributes']['type'] , $typeSchema  ) ) return '';
	if( empty($cmd['.attributes']['title']) || empty($cmd['.attributes']['name'])) return '';
	$titleFw = empty($cmd['.attributes']['titledirection']) ? true : ($cmd['.attributes']['titledirection'] == 'bk' ? false : true);
	$fieldTitle = TAB.TAB.TAB.'<label class="fieldtitle">'.htmlspecialchars($cmd['.attributes']['title']) . '</label>';
	$fieldControl = TAB.TAB.TAB.'<span class="fieldcontrol">' .CRLF.  call_user_func($cmd['.attributes']['type'].'Treat' , $cmd, $dfVal, $name) .TAB.TAB.TAB.'</span>';
	$caption = empty($cmd['caption'][0]) ? '':TAB.TAB.TAB.'<div class="fieldcaption">'. $cmd['caption'][0]['.value']  . '</div>'.CRLF;
	if( $titleFw) 
		return	TAB.TAB.'<div class="field" id="div_'.htmlspecialchars($cmd['.attributes']['name']).'">'.CRLF.$fieldTitle.CRLF.$fieldControl.CRLF.$caption.TAB.TAB."</div>\n";
	else
		return	TAB.TAB.'<div class="field" id="div_'.htmlspecialchars($cmd['.attributes']['name']).'">'.CRLF.$fieldControl.CRLF.$fieldTitle.CRLF.$caption.TAB.TAB."</div>\n";
}

function textTreat( $cmd , $dfVal , $name ){
	$dfVal = ( !is_null( $dfVal[$name]  ) ) ? $dfVal[$name]  :  (empty($cmd['.attributes']['value'] )?null:$cmd['.attributes']['value'] );
	$DSP = TAB.TAB.TAB.TAB.'<input type="text" class="textcontrol" ';
	$DSP .= ' id="'.$name.'" ';
	$DSP .= empty( $cmd['.attributes']['size'] ) ? '' : 'size="'. $cmd['.attributes']['size'] . '"' ;
	$DSP .= is_null( $dfVal  ) ? '' : 'value="'. htmlspecialchars($dfVal). '"' ;
	$DSP .= ' />'.CRLF ;
	return $DSP;
}
function textareaTreat( $cmd, $dfVal , $name){
	$dfVal = ( !is_null( $dfVal[$name]  ) ) ? $dfVal[$name] :  (empty($cmd['.value'] )?null:$cmd['.value'] );
	$DSP = TAB.TAB.TAB.TAB.'<textarea class="textareacontrol"';
	$DSP .= ' id="'.$name.'" ';
	$DSP .= empty( $cmd['.attributes']['rows'] ) ? 'rows="2"' : 'rows="'. $cmd['.attributes']['rows'] . '"' ;
	$DSP .= empty( $cmd['.attributes']['cols'] ) ? 'cols="23" ' : 'cols="'. $cmd['.attributes']['cols'] . '"' ;
	$DSP .= '>';
	$DSP .= is_null( $dfVal  )  ? '' : htmlspecialchars($dfVal);
	$DSP .= '</textarea>'.CRLF ;
	return $DSP;
}
function selectTreat( $cmd, $dfVal , $name){
	$DSP = TAB.TAB.TAB.TAB.'<select id="'.$name.'" class="selectcontrol">'.CRLF;	
    $df = empty($dfVal[$name] ) ? NULL: $dfVal[$name];
	foreach( $cmd['op']  as $option ){
		$ov = empty($option['.attributes']['value']) ? NULL :$option['.attributes']['value']; 
		$oc = empty($option['.attributes']['checked']) ? NULL:$option['.attributes']['checked'];
		
		$DSP .= TAB.TAB.TAB.TAB.TAB.'<option ';
		$DSP .= !is_string( $ov ) ? '' : 'value="'.htmlspecialchars($ov).'" ';
		$DSP .= is_string( $oc ) && 'checked' == $oc && is_null($dfVal) ? 'selected="selected" ':'';
		$DSP .= is_string($df) && (!is_string( $ov ) ? false : $ov== $df ) ? 'selected="selected" ' : '';
		$DSP .= '>';
		$DSP .= $option['.value'];
		$DSP .= '</option>'.CRLF;
	}
	$DSP .= TAB.TAB.TAB.TAB.'</select>'.CRLF ;
	return $DSP;
}
function checkboxTreat( $cmd, $dfVal, $name){
	$DSP = '';	
	foreach( $cmd['op']  as $option ){
		if( empty($option['.attributes']['name']) || !is_string( $option['.attributes']['name'] ) ) continue;
		$df = empty( $dfVal[$option['.attributes']['name']] ) ? NULL : $dfVal[$option['.attributes']['name']];
		$oc = empty( $option['.attributes']['checked'] ) ? NULL : $option['.attributes']['checked'];
		$checked = !is_string( $df ) ? 
				( is_string( $oc ) && 'checked' == $oc && is_null( $dfVal ) ? 'checked="checked" ' : ''   ) :
				( '' != $df ? 'checked="checked" ' : '');
		$DSP .= TAB.TAB.TAB.TAB.'<input type="checkbox" class="checkboxcontrol"';
		$DSP .= ' id="'.$option['.attributes']['name'].'" ';
		$DSP .= !is_string( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= $checked;
		$DSP .= ' />' ;
		$DSP .= "<label class='checkboxlabel' >{$option['.value']}</label>".CRLF;
	}
	return $DSP;
}
function radioTreat( $cmd, $dfVal, $name){
	$DSP = '';
	$df = empty( $dfVal[$name]) ? NULL :$dfVal[$name];
	foreach( $cmd['op']  as $option ){
		$DSP .= TAB.TAB.TAB.TAB.'<input type="radio"  class="radiocontrol" ';
		$DSP .= ' name="'.$name.'" ';
		$oc = empty( $option['.attributes']['checked'] ) ? NULL: $option['.attributes']['checked'];
		$DSP .= !is_string( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= is_string( $oc ) && 'checked' == $oc && is_null($dfVal) ? 'checked="checked" ' : '';
		$DSP .= is_string($df) && (!is_string( $option['.attributes']['value'] ) ? false : $option['.attributes']['value']== $df ) ? 'checked="checked" ' : '';
		$DSP .= ' />' ;
		$DSP .= "<label class='radiolabel' >{$option['.value']}</label >".CRLF;
	}
	return $DSP;
}
?>
