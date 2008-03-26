<?php 
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

$fxList = array();
function setDetailPanel($id,$type = 'section') {
	global $fxList;
//	if(in_array($fxList,$id)) return '';
	array_push($fxList, $id);
	switch($type) {
	case 'button':
		$hrefVal = TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dl class="line">'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dt class="hidden">'._t('패널 보기 설정').'</dt>'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dd><span class="input-button"><a id="toggle'.$id.'" href="#">'._t('변경 가능한 모든 값 보기').'</a></span></dd>'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'</dl>'.CRLF;
		break;
	case 'section': default:
		$hrefVal = TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dl class="panel-setting">'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dt>'._t('패널 보기 설정').'</dt>'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dd><a id="toggle'.$id.'" href="#">'._t('변경 가능한 모든  값 보기').'</a></dd>'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'</dl>'.CRLF;
		break;
	}
	return $hrefVal;
}

function activateDetailPanelJS() {
	global $fxList, $service;
	$jsVal = '';
	if(!empty($fxList)) {
		foreach($fxList as $fxItem) {
			$jsVal .= "var ".$fxItem." = new Fx.Slide('".$fxItem."');".CRLF
				."$('toggle".$fxItem."').addEvent('click', function(e){".CRLF
				."e = new Event(e);".CRLF
				.$fxItem.".toggle();".CRLF
				."e.stop();".CRLF
				."});".CRLF
				.$fxItem.".hide();".CRLF;
		}
	}
	return $jsVal;
}
?>
