<?
define('ROOT', '../../../../..');
$IV = array(	'GET' => array(	'Name' => array('string')	));
if( !empty( $_POST ) ){
	$IV['POST'] = array();
	foreach( $_POST as $key => $val)
		$IV['POST'][$key] = array( 'any', 'mandatory' => false);
}
require ROOT . '/lib/includeForOwner.php';
$pluginName = $_GET['Name'];
$postCheck = null;
if( !empty( $_POST) ) $postCheck = handleDataSet(	$pluginName, $_POST );

$typeSchema = array(
	'text' 
,	'textarea'
,	'select'
,	'checkbox'
,	'radio'
);
$result =  handleConfig($pluginName,  $postCheck);
if( is_null($result) )	respondNotFoundPage();
?><?= $result ?>

<?
function handleConfig( $plugin , $postCheck){
	global $service;
	$manifest = @file_get_contents(ROOT . "/plugins/$plugin/index.xml");
	$xmls = new XMLStruct();	
	$CDSPval = '';
	$i=0;
	if ($manifest && $xmls->open($manifest)) {
		foreach ($xmls->selectNodes('/plugin/binding/config/fieldset') as $fieldset) {
			$CDSPval .= "<div id='fieldset$i' class='groupstyle'>";
			if (!empty($fieldset['.attributes']['legend'])) 
				$CDSPval .= "<span id='fieldsetLegend$i'>{$fieldset['.attributes']['legend']}</span><br />\n";
			if( !empty( $fieldset['field'] ) ){
				$CDSPval .= '<table class="fieldset"  >';
				foreach( $fieldset['field'] as $field )
					$CDSPval .=  TreatType( $field , null ) ;
				$CDSPval .= '</table >';
			}
			$CDSPval .= "</div>\n";
			$i++;
		}
	}else	$CDSPval = _t('죄송합니다. 설정값을 찾을 수 없습니다.'); 	
return
"
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
	<link rel=\"stylesheet\" type=\"text/css\" href=\"{$service['path']}/style/configStyle.css\" />
	<script type=\"text/javascript\" src=\"{$service['path']}/script/pluginconfig.js\"> </script>
	<title>$plugin config</title>
</head>
<body>
<h3>$plugin CONFIG</h3>
<div id='config_data'>
$CDSPval
</div>
<div width='100%' align='center'>
	<input type='button' value='"._t('설정')."' onclick='saveConfig();'>
</div>
</body>
</html>
";	
}

function TreatType(  $cmd , $dfVal ){
	global $typeSchema;
	if( empty($cmd['.attributes']['type']) || !in_array($cmd['.attributes']['type'] , $typeSchema  ) ) return '';
	if( empty($cmd['.attributes']['title']) || empty($cmd['.attributes']['name'])) return '';
	return	'<tr><td class="title">'.htmlspecialchars($cmd['.attributes']['title']).'</td><td>'. call_user_func($cmd['.attributes']['type'].'Treat' , $cmd, $dfVal) .'</td></tr>';
}
function textTreat( $cmd , $dfVal ){
	$DSP = '<input type="text" name="'.htmlspecialchars($cmd['.attributes']['name']).'" ';
	$DSP .= empty( $cmd['.attributes']['size'] ) ? '' : 'size="'. $cmd['.attributes']['size'] . '"' ;
//디폴트 발류	$DSP .= empty( $dfVal[ $cmd
	$DSP .= empty( $cmd['.attributes']['value'] ) ? '' : 'value="'. htmlspecialchars($cmd['.attributes']['value'] ). '"' ;
	$DSP .= " /> </br>\n" ;
	return $DSP;
}
function textareaTreat( $cmd, $dfVal){
	$DSP = '<textarea name="'.htmlspecialchars($cmd['.attributes']['name']).'" ';
	$DSP .= empty( $cmd['.attributes']['rows'] ) ? '' : 'rows="'. $cmd['.attributes']['rows'] . '"' ;
	$DSP .= empty( $cmd['.attributes']['cols'] ) ? '' : 'cols="'. $cmd['.attributes']['cols'] . '"' ;
	$DSP .= '>';
//디폴트 발류	$DSP .= empty( $dfVal[ $cmd
	$DSP .= empty( $cmd['.value'] ) ? '' : htmlspecialchars($cmd['.value']);
	$DSP .= "</textarea> </br>\n" ;
	return $DSP;
}
function selectTreat( $cmd, $dfVal ){
	$DSP = '<select name="'.htmlspecialchars($cmd['.attributes']['name']).'" >';
	foreach( $cmd['op']  as $option ){
		$DSP .= '<option ';
		$DSP .= empty( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= !empty( $option['.attributes']['checked'] ) && 'true' == $option['.attributes']['checked'] && is_null($dfVal) ? 'selected="true" ' : '';
		$DSP .= !is_null($dfVal) && (empty( $option['.attributes']['value'] ) ? '' : $option['.attributes']['value']== $dfVal ) ? 'selected="true" ' : '';
		$DSP .= '>';
		$DSP .= $option['.value'];
		$DSP .= "</option>\n";
	}
	$DSP .= "</select> </br>\n" ;
	return $DSP;
}
function checkboxTreat( $cmd, $dfVal){
	$checked_arr = explode( "," , $dfVal );
	$DSP = '<div name="'.htmlspecialchars($cmd['.attributes']['name']).'" >';
	$i = 0 ;
	foreach( $cmd['op']  as $option ){
		$DSP .= '<input type="checkbox" ';
		$DSP .= empty( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= !empty( $option['.attributes']['checked'] ) && 'true' == $option['.attributes']['checked'] && is_null($dfVal) ? 'checked="true" ' : '';
		$DSP .= count($checked_arr) > 0  && in_array( (empty( $option['.attributes']['value'] ) ? '' : $option['.attributes']['value'] ) , $checked_arr ) ? 'checked="true" ' : '';
		$DSP .= '> ' ;
		$DSP .= $option['.value'];
		$DSP .= ( $i % 3 == 2 )? '<br />' : '' ;		
		$i++;
	}
	$DSP .= "</div> </br>\n" ;
	return $DSP;
}
function radioTreat( $cmd, $dfVal){
	$checked_arr = explode( "," , $dfVal );
	$DSP = '<div >';
	$i = 0;
	foreach( $cmd['op']  as $option ){
		$DSP .= '<input type="radio" name="'. htmlspecialchars($cmd['.attributes']['name']) .'" ';
		$DSP .= empty( $option['.attributes']['value'] ) ? '' : 'value="'.htmlspecialchars($option['.attributes']['value']).'" ';
		$DSP .= !empty( $option['.attributes']['checked'] ) && 'true' == $option['.attributes']['checked'] && is_null($dfVal) ? 'checked="true" ' : '';
		$DSP .= !is_null($dfVal) && (empty( $option['.attributes']['value'] ) ? '' : $option['.attributes']['value']== $dfVal ) ? 'selected="true" ' : '';
		$DSP .= '> ' ;
		$DSP .= $option['.value'];
		$DSP .= ( $i % 3 ==2 )? '<br />' : '' ;
		$i++;
	}
	$DSP .= "</div> </br>\n" ;
	return $DSP;
}
?>