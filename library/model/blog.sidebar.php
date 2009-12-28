<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getSidebarModuleOrderData($sidebarCount) {
	if (!is_null($tempValue = Setting::getBlogSettingGlobal("sidebarOrder", NULL))) {
		$emptyArray = unserialize($tempValue);
	} else {
		$emptyArray = false;
	}
	
	if ($emptyArray === false) return null;
	return $emptyArray;
}

function addSidebarModuleOrderData($dataArray, $sidebarNumber, $modulePos, $newModuleData) {
	global $skin, $sidebarMappings, $gCacheStorage;
	
	if (!isset($dataArray[$sidebarNumber]) || empty($dataArray[$sidebarNumber]))
		$dataArray[$sidebarNumber] = array();
	
	if ($modulePos < 0) {
		$modulePos = count($dataArray[$sidebarNumber]);
	} else if ($modulePos > count($dataArray[$sidebarNumber])) {
		$modulePos = count($dataArray[$sidebarNumber]);
	}
	
	if ($newModuleData[0] == 1) {
		if (isset($skin->sidebarBasicModules[$newModuleData[1]]) && isset($skin->sidebarBasicModules[$newModuleData[1]][$newModuleData[2]])) 
		{
			array_splice($dataArray[$sidebarNumber], $modulePos, 0, 
				array(array('type' => 1, 'id' => $newModuleData[1], 'parameters' => $newModuleData[2])));
		} else {
			return null;
		}
	} else  if ($newModuleData[0] == 2) {
		return null;
	} else if ($newModuleData[0] == 3) {
			$plugin = $newModuleData[1];
			$handler = $newModuleData[2];
		
		$matched = false;
		
		foreach($sidebarMappings as $item) {
			if (($item['plugin'] == $newModuleData[1]) && ($item['handler'] == $newModuleData[2])) {
				array_splice($dataArray[$sidebarNumber], $modulePos, 0, 
					array(array('type' => 3, 'id' => array('plugin' => $newModuleData[1], 'handler' => $newModuleData[2]),
					'parameters' => '')));
				$matched = true;
			}
		}
		
		if ($matched == false) return null;
	}
	CacheControl::flushSkin();
	$gCacheStorage->purge();
	return $dataArray;
}

function deleteSidebarModuleOrderData($dataArray, $sidebarNumber, $modulePos) {
	global $gCacheStorage;
	if (!isset($dataArray[$sidebarNumber]))
		$dataArray[$sidebarNumber] = array();
	
	array_splice($dataArray[$sidebarNumber], $modulePos, 1);
	
	CacheControl::flushSkin();	
	$gCacheStorage->purge();
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
