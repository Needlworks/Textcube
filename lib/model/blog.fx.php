<?php 
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$fxList = array();
function setDetailPanel($id) {
	global $fxList;
//	if(in_array($fxList,$id)) return '';
	array_push($fxList, $id);
	$hrefVal = TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dl class="panel-setting">'.CRLF
		.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dt>'._t('패널 보기 설정').'</dt>'.CRLF
		.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dd><a id="toggle'.$id.'" href="#">'._t('패널 보기').'</a></dd>'.CRLF
		.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'</dl>'.CRLF;
	return $hrefVal;
}

function activateDetailPanelJS() {
	global $fxList;
	$jsVal = '';
	foreach($fxList as $fxItem) {
		$jsVal .= "var ".$fxItem." = new Fx.Slide('".$fxItem."');".CRLF
			."$('toggle".$fxItem."').addEvent('click', function(e){".CRLF
			."e = new Event(e);".CRLF
			.$fxItem.".toggle();".CRLF
			."e.stop();".CRLF
			."});".CRLF;
	}
	return $jsVal;
}
?>
