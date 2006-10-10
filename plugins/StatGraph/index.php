<?
/* Statistics Graph for Tattertools 1.1
   ----------------------------------
   Version 1.0

   Creator          : gendoh (http://process.kaist.ac.kr/~gendoh)
   Maintainer       : gendoh

   Created at       : 2006.10.2
   Last modified at : 2006.10.10

 This plugin adds Statistics Graph on sidebar panel.

 if installed at other directory, edit a location of count
 global $pluginURL;
*/

function DisplayStatisticsGraph($target) 
{ 
	global $pluginURL, $owner; 
	$target = '<div><img src="' . $pluginURL .  '/count/count.php?owner=' . $owner . '"></div>';
	return $target;
}
?>
