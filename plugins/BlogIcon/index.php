<?php
function BlogIcon_main($target, $mother) {  
	global $configVal;
	$data = fetchConfigVal( $configVal);

	if (!is_null($data))	$ico_size = $data['ico_size'];
	if (!isset($ico_size) || is_null($ico_size))	$ico_size = 16;
  
	if (empty($mother['homepage']))
		return $target;
	$slash = ($mother['homepage']{strlen($mother['homepage']) - 1} == '/' ? '' : '/');
	return "<img src=\"{$mother['homepage']}{$slash}index.gif\" width=\"{$ico_size}\" height=\"{$ico_size}\" onerror=\"this.parentNode.removeChild(this)\" /> $target";
}
?>
