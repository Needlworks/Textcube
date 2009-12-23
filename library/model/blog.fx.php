<?php 
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

$fxList = array();
function setDetailPanel($id,$type = 'section',$string = '') {
	global $fxList, $service;
//	if(in_array($fxList,$id)) return '';
	array_push($fxList, $id);
	$hrefVal = '';
	switch($type) {
	case 'button':
		if($service['interface'] == 'simple') {
			$hrefVal = TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dl class="line">'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dt class="hidden">'._t('패널 보기 설정').'</dt>'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dd><span class="input-button"><a id="toggle'.$id.'" href="#">'._t('자세한 설정 보기').'</a></span></dd>'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'</dl>'.CRLF;
		} 
		break;
	case 'link': 
		if($service['interface'] == 'simple') {
			$hrefVal = '<a id="toggle'.$id.'" href="#">'.$string.'</a>';
		} else {
			$hrefVal = $string;
		}
		break;
	case 'sectionButton': default:
		if($service['interface'] == 'simple') {
			$hrefVal = TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dl class="panel-setting">'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dt>'._t('패널 보기 설정').'</dt>'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'<dd><a id="toggle'.$id.'" href="#">'._t('패널 열기').'</a></dd>'.CRLF
			.TAB.TAB.TAB.TAB.TAB.TAB.TAB.'</dl>'.CRLF;
		}
		break;
	}
	return $hrefVal;
}

function activateDetailPanelJS() {
	global $fxList, $service;
	$jsVal = '';
	if(!empty($fxList) && ($service['interface'] == 'simple')) {
		foreach($fxList as $fxItem) {
			$jsVal .= "var ".$fxItem." = new Fx.Slide('".$fxItem."');".CRLF
				."$('toggle".$fxItem."').addEvent('click', function(e){".CRLF
				."e = new Event(e);".CRLF
				.$fxItem.".slideIn();".CRLF
				."e.stop();".CRLF
				."});".CRLF
				.$fxItem.".hide();".CRLF;
		}
	}
	return $jsVal;
}
?>
