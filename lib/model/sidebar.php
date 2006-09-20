<?php
function getSidebarModuleOrderData($num=0) {
	$temp = array(
					0 => array(
								array("id" => 0),
								array("id" => 2),
								array("id" => "SB_Category_New", "parameters" => NULL)
					  	 )
				 );
	return $temp[$num];
}
?>