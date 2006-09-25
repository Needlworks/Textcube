<?php
function getSidebarModuleOrderData($sidebarCount) {
	if (!is_null($tempValue = getUserSetting("sidebarOrder", NULL))) {
		$emptyArray = unserialize($tempValue);
	} else {
		$emptyArray = array();
	}
	
	if ($emptyArray === false) return null;
	return $emptyArray;
}

function addSidebarModuleOrderData($dataArray, $sidebarNumber, $modulePos, $newModuleData) {
	if (empty($dataArray[$sidebarNumber]))
		$dataArray[$sidebarNumber] = array();
	array_push($dataArray[$sidebarNumber], $newModuleData);
	return $dataArray;
}

function deleteSidebarModuleOrderData($dataArray, $sidebarNumber, $modulePos) {
	if (!isset($dataArray[$sidebarNumber]))
		$dataArray[$sidebarNumber] = array();
	
	array_pick($dataArray[$sidebarNumber], $modulePos);
	sort($dataArray[$sidebarNumber]);
	
	return $dataArray;
}

function &array_pick(&$array, $keys)
{
   if (! is_array($array)) {
	   trigger_error('First parameter must be an array', E_USER_ERROR);
	   return false;
   }

   if (! (is_array($keys) || is_scalar($keys))) {
	   trigger_error('Second parameter must be an array of keys or a scalar key', E_USER_ERROR);
	   return false;
   }

   if (is_array($keys)) {
	   // nothing to do
   } else if (is_scalar($keys)) {
	   $keys = array ($keys);
   }

   $resultArray = array ();
   foreach ($keys as $key) {
	   if (is_scalar($key)) {
		   if (array_key_exists($key, $array)) {
			   $resultArray[$key] = $array[$key];
			   unset($array[$key]);
		   }
	   } else {
		   trigger_error('Supplied key is not scalar', E_USER_ERROR);
		   return false;
	   }
   }

   return $resultArray;
}

function array_insert($src, $dest, $pos) {
	if (!is_array($src) || !is_array($dest) || $pos <= 0) return FALSE;
	return array_merge(array_slice($dest, 0, $pos), $src, array_slice($dest, $pos));
}
?>