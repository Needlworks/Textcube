<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
function getMetapageModuleOrderData() {
	if (!is_null($tempValue = getBlogSetting("metapageOrder", NULL))) {
		$emptyArray = unserialize($tempValue);
	} else {
		$emptyArray = false;
	}
	
	if ($emptyArray === false) return null;
	return $emptyArray;
}

function addMetapageModuleOrderData($dataArray, $metapageNumber, $modulePos, $newModuleData) {
	global $skin, $metapageMappings;
	
	if (!isset($dataArray[$metapageNumber]) || empty($dataArray[$metapageNumber]))
		$dataArray[$metapageNumber] = array();
	
	if ($modulePos < 0) {
		$modulePos = count($dataArray[$metapageNumber]);
	} else if ($modulePos > count($dataArray[$metapageNumber])) {
		$modulePos = count($dataArray[$metapageNumber]);
	}
	
	if ($newModuleData[0] == 3) {
			$plugin = $newModuleData[1];
			$handler = $newModuleData[2];
		
		$matched = false;
		
		foreach($metapageMappings as $item) {
			if (($item['plugin'] == $newModuleData[1]) && ($item['handler'] == $newModuleData[2])) {
				array_splice($dataArray[$metapageNumber], $modulePos, 0, 
					array(array('type' => 3, 'id' => array('plugin' => $newModuleData[1], 'handler' => $newModuleData[2]),
					'parameters' => '')));
				$matched = true;
			}
		}
		
		if ($matched == false) return null;
	}
	
	return $dataArray;
}

function deleteMetapageModuleOrderData($dataArray, $metapageNumber, $modulePos) {
	if (!isset($dataArray[$metapageNumber]))
		$dataArray[$metapageNumber] = array();
	
	array_splice($dataArray[$metapageNumber], $modulePos, 1);
	
	return $dataArray;
}

?>
