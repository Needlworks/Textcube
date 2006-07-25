<?php

function activatePlugin($name) {
	global $database, $owner, $activePlugins;
	if (in_array($name, $activePlugins))
		return true;
	if (!ereg('^[[:alnum:] _-]+$', $name))
		return false;
	if (!is_dir(ROOT . "/plugins/$name"))
		return false;
	if (!file_exists(ROOT . "/plugins/$name/index.xml") || !file_exists(ROOT . "/plugins/$name/index.php"))
		return false;
	$name = mysql_escape_string($name);
	mysql_query("INSERT INTO {$database['prefix']}Plugins VALUES ($owner, '$name', null)");
	return (mysql_affected_rows() == 1);
}

function deactivatePlugin($name) {
	global $database, $owner, $activePlugins;
	if (!in_array($name, $activePlugins))
		return;
	$name = mysql_escape_string($name);
	mysql_query("DELETE FROM {$database['prefix']}Plugins WHERE owner = $owner AND name = '$name'");
}

function getCurrentSetting( $name){
/*
	ToDo : 여기서 설정값 형식을 조립해줄것인가 결정
*/
/*	global $database , $owner, $activePlugins;
	if( !in_array( $name , $activePlugins))
		return false;
	$name = mysql_escape_string( $name ) ;
	$result = mysql_query("SELECT settings FROM {$database['prefix']}Plugins WHERE owner = $owner AND name = '$name'");
	if( false === $result ) 
		return false;
	$out = mysql_fetch_array($result); 
	return $out['settings'];
*/
}
function updatePluginConfig( $name , $setVal){
/*
	여기는 걍 설정값( 선조립되어 있음) 을 넣는 일만 함.....
*/
	global $database, $owner, $activePlugins;
	if (!in_array($name, $activePlugins))
		return false;
	$name = mysql_escape_string( $name ) ;
	$setVal = mysql_escape_string( $setVal ) ;
	mysql_query(
	"UPDATE {$database['prefix']}Plugins 
	SET settings = '$setVal' 
	WHERE owner = $owner 
	AND name = '$name'"
	);
	if( mysql_affected_rows() == 1 )
		return true;
	return (mysql_error() == '');
}
?>
