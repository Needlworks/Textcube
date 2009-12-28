<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
function getCoverpageModuleOrderData() {
	if (!is_null($tempValue = Setting::getBlogSettingGlobal("coverpageOrder", NULL))) {
		$emptyArray = unserialize($tempValue);
	} else {
		$emptyArray = false;
	}
	
	if ($emptyArray === false) return null;
	return $emptyArray;
}

function addCoverpageModuleOrderData($dataArray, $coverpageNumber, $modulePos, $newModuleData) {
	global $skin, $coverpageMappings;
	
	if (!isset($dataArray[$coverpageNumber]) || empty($dataArray[$coverpageNumber]))
		$dataArray[$coverpageNumber] = array();
	
	if ($modulePos < 0) {
		$modulePos = count($dataArray[$coverpageNumber]);
	} else if ($modulePos > count($dataArray[$coverpageNumber])) {
		$modulePos = count($dataArray[$coverpageNumber]);
	}
	
	if ($newModuleData[0] == 3) {
			$plugin = $newModuleData[1];
			$handler = $newModuleData[2];
		
		$matched = false;
		
		foreach($coverpageMappings as $item) {
			if (($item['plugin'] == $newModuleData[1]) && ($item['handler'] == $newModuleData[2])) {
				array_splice($dataArray[$coverpageNumber], $modulePos, 0, 
					array(array('type' => 3, 'id' => array('plugin' => $newModuleData[1], 'handler' => $newModuleData[2]),
					'parameters' => '')));
				$matched = true;
			}
		}
		
		if ($matched == false) return null;
	}
	
	return $dataArray;
}

function deleteCoverpageModuleOrderData($dataArray, $coverpageNumber, $modulePos) {
	if (!isset($dataArray[$coverpageNumber]))
		$dataArray[$coverpageNumber] = array();
	
	array_splice($dataArray[$coverpageNumber], $modulePos, 1);
	
	return $dataArray;
}

?>
