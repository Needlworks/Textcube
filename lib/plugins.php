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
$typeSchema = null;

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
				$title = htmlspecialchars($xmls->getValue('/plugin/title[lang()]'));
				foreach ($xmls->selectNodes('/plugin/binding/sidebar') as $sidebar) {
					if (!empty($sidebar['.attributes']['handler'])) {
						array_push($sidebarMappings, array('plugin' => $plugin, 'class' => $sidebar['.attributes']['class'], 'title' => $sidebar['.attributes']['title'], 'display' => $title, 'handler' => $sidebar['.attributes']['handler']));
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
			$plugin = mysql_real_escape_string($plugin);
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
				$configVal = getCurrentSetting($mapping['plugin']);
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

function cutSidebars(& $sval, & $obj) {
	global $service, $sidebarMappings, $pluginURL, $configMappings, $configVal;
	
	$replacer = '';
	
	foreach ($sidebarMappings as $mapping) {
		include_once (ROOT . "/plugins/{$mapping['plugin']}/index.php");
		$content_temp = $obj->sidebarItem;

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
							if( !empty( $configMappings[$mapping['plugin']]['config'] ) ) 				
								$configVal = getCurrentSetting($mapping['plugin']);
							else
								$configVal ='';
							$pluginURL = "{$service['path']}/plugins/{$mapping['plugin']}";
							$target = call_user_func($mapping['handler'], $target, $content);
						}
						dress('sidebar_contents', $target, $content_temp);
						break;
				}
			}
		}
		$obj->sidebarElement[$mapping['plugin']] = array($mapping['display'], $content_temp);
		$replacer .= "[##_sidebar_module_{$obj->inlineSidebarCount}_##]";
		$obj->inlineSidebarCount++;
	}
	
	dress('sidebar_rep_element', $replacer, $obj->sidebar);
}

// 내장형 사이드바 모듈 속성 배열.
function getBasicSidebarList() {
	$innerSidebarModules = array();
	
	$innerSidebarModules['%Category%'] = array(
						"title" => _t('스킨 내장형 분류'),
						"description" => _t('스킨에 내장되어 있는 분류 사이드바 모듈입니다.')
						);
	$innerSidebarModules['%CategoryList%'] = array(
						"title" => _t('스킨 내장형 분류(XHTML)'),
						"description" => _t('스킨에 내장되어 있는 XHTML형 분류 사이드바 모듈입니다.')
						);
	$innerSidebarModules['%Calendar%'] = array(
						"title" => _t('스킨 내장형 달력'),
						"description" => _t('스킨에 내장되어 있는 달력 사이드바 모듈입니다.')
						);
	$innerSidebarModules['%TagList%'] = array(
						"title" => _t('스킨 내장형 태그 목록'),
						"description" => _t('스킨에 내장되어 있는 태그 목록 사이드바 모듈입니다.')
						);
	$innerSidebarModules['%RecentPosts%'] = array(
						"title" => _t('스킨 내장형 최근 글 목록'),
						"description" => _t('스킨에 내장되어 있는 최근 등록글 목록 사이드바 모듈입니다.')
						);
	$innerSidebarModules['%RecentTrackback%'] = array(
						"title" => _t('스킨 내장형 최근 글걸기 목록'),
						"description" => _t('스킨에 내장되어 있는 최근 글걸기 목록 사이드바 모듈입니다.')
						);
	$innerSidebarModules['%RecentArchive%'] = array(
						"title" => _t('스킨 내장형 최근 저장소 목록'),
						"description" => _t('스킨에 내장되어 있는 최근 저장소 목록 사이드바 모듈입니다.')
						);
	$innerSidebarModules['%Link%'] = array(
						"title" => _t('스킨 내장형 링크 목록'),
						"description" => _t('스킨에 내장되어 있는 링크 사이드바 모듈입니다.')
						);
	$innerSidebarModules['%Counter%'] = array(
						"title" => _t('스킨 내장형 카운터'),
						"description" => _t('스킨에 내장되어 있는 카운터 사이드바 모듈입니다.')
						);
	
	return $innerSidebarModules;
}

function handleSidebars(& $sval, & $obj) {
	cutSidebars($sval, $obj);
	$orderKeys = getSidebarModuleOrder($obj);
	for ($i=0; $i<count($orderKeys); $i++) {
		dress("sidebar_module_{$i}", $obj->sidebarElement[$orderKeys[$i]][1], $obj->sidebar);
	}
	dress("sidebar", $obj->sidebar, $sval);
}

function getSidebarModuleOrder(& $obj) {
	global $skinSetting;
	
	if (!is_object($obj))
		$obj = new Skin($skinSetting['skin']);
	
	$orderKeys = getUserSetting('sidebarOrder');
	if (is_null($orderKeys)) {
		$sidebarOrder = array_keys($obj->sidebarElement);
		setUserSetting("sidebarOrder", implode("|", $sidebarOrder));
		return $sidebarOrder;
	} else {
		return explode("|", $orderKeys);
	}
}

function handleDataSet( $plugin , $DATA ){
	global $configMappings, $activePlugins;
	$xmls = new XMLStruct();
	if( ! $xmls->open($DATA) ) {
		unset($xmls);	
		return array('error' => '3' ,'customError' => '' ) ;
	}unset($xmls);	
	if( ! in_array($plugin, $activePlugins) ) 
		return array('error' => '9' , 'customError'=> _t($plugin.'사용중인 플러그인만 설정을 변경할 수 있습니다.')) ;
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



function handleConfig( $plugin){
	global $service , $typeSchema;
	
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
	$defaultCss = (true === file_exists( ROOT. "/plugins/$plugin/configStyle.css" ) ) ? $service['path']. "/plugins/$plugin/configStyle.css" : $service['path']. '/style/configStyle.css';
	if ($manifest && $xmls->open($manifest)) {
		//설정 핸들러가 존재시 바꿈
		$config = $xmls->selectNode('/plugin/binding/config');
		unset( $xmls );
		if( !empty($config['.attributes']['manifestHandler']) ){
			$handler = $config['.attributes']['manifestHandler'] ;
			$oldconfig = $config;
			include_once (ROOT . "/plugins/$plugin/index.php");
			$manifest = call_user_func( $handler , $plugin );
			$newXmls = new XMLStruct();
			if($newXmls->open( $manifest) ){	 
				unset( $config );
				$config = $newXmls->selectNode('/config');
			}
			unset( $newXmls);
		}
		if( is_null( $config['fieldset'] ) ) 
			return array( 'code' => _t('설정 값이 없습니다.') , 'script' => '[]' , 'css' => $defaultCss ) ;  	
		foreach ($config['fieldset'] as $fieldset) {
			$legend = !empty($fieldset['.attributes']['legend']) ? htmlspecialchars($fieldset['.attributes']['legend']) :'';
			$CDSPval .= CRLF.TAB."<fieldset>".CRLF.TAB.TAB."<legend>$legend</legend>".CRLF;
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
	return array( 'code' => $CDSPval , 'script' => $clientData , 'css' => $defaultCss ) ;
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
