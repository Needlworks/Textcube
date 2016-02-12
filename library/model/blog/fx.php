<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function setDetailPanel($id, $type = 'section', $string = '') {
    $context = Model_Context::getInstance();
//	if(in_array($fxList,$id)) return '';
    $fxList = $context->getProperty('blog.fxList',array());
    array_push($fxList, $id);

    $hrefVal = '';
    switch ($type) {
        case 'button':
            if ($context->getProperty('service.interface') == 'simple') {
                $hrefVal = TAB . TAB . TAB . TAB . TAB . TAB . TAB . '<dl class="line">' . CRLF
                    . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '<dt class="hidden">' . _t('패널 보기 설정') . '</dt>' . CRLF
                    . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '<dd><span class="input-button"><a id="toggle' . $id . '" href="#">' . _t('자세한 설정 보기') . '</a></span></dd>' . CRLF
                    . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '</dl>' . CRLF;
            }
            break;
        case 'link':
            if ($context->getProperty('service.interface') == 'simple') {
                $hrefVal = '<a id="toggle' . $id . '" href="#">' . $string . '</a>';
            } else {
                $hrefVal = $string;
            }
            break;
        case 'sectionButton':
        default:
            if ($context->getProperty('service.interface') == 'simple') {
                $hrefVal = TAB . TAB . TAB . TAB . TAB . TAB . TAB . '<dl class="panel-setting">' . CRLF
                    . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '<dt>' . _t('패널 보기 설정') . '</dt>' . CRLF
                    . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '<dd><a id="toggle' . $id . '" href="#">' . _t('패널 열기') . '</a></dd>' . CRLF
                    . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '</dl>' . CRLF;
            }
            break;
    }
    $context->setProperty('blog.fxList', $fxList);
    return $hrefVal;
}

function activateDetailPanelJS() {
    $context = Model_Context::getInstance();
    $fxList = $context->getProperty('blog.fxList');
    $jsVal = '';
    if (!empty($fxList) && ($context->getProperty('service.interface') == 'simple')) {
        $jsVal = "jQuery(document).ready(function(jQuery) {" . CRLF;
        foreach ($fxList as $fxItem) {
            $jsVal .= TAB . TAB . TAB . TAB . TAB . TAB . TAB . 'jQuery("#toggle' . $fxItem . '").click( function() {' . CRLF
                . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . 'if (jQuery("#' . $fxItem . '").is(":hidden")) {' . CRLF
                . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '	jQuery("#' . $fxItem . '").slideDown(150);' . CRLF
                . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '} else {' . CRLF
                . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '	jQuery("#' . $fxItem . '").slideUp(150);' . CRLF
                . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '}' . CRLF
                . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . 'return false;' . CRLF
                . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . '});' . CRLF
                . TAB . TAB . TAB . TAB . TAB . TAB . TAB . TAB . 'jQuery("#' . $fxItem . '").css("display","none");' . CRLF;
        }
        $jsVal .= TAB . TAB . TAB . TAB . TAB . TAB . TAB . "});" . CRLF;
    }
    return $jsVal;
}

?>
