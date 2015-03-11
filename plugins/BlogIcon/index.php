<?php
function BlogIcon_main($target, $mother) {  
    $context = Model_Context::getInstance();
    $data = $context->getProperty('plugin.config');

	if (!is_null($data))	$ico_size = $data['ico_size'];
	if (!isset($ico_size) || is_null($ico_size))	$ico_size = 16;
	
	if ($mother['secret'] == 1) {
		if (empty($mother['homepage'])) {
			$imageStr = "<img src=\"".$context->getProperty('plugin.uri')."/images/secret.png\" alt=\"\" width=\"{$ico_size}\" height=\"{$ico_size}\" />";
		} else {
			$slash = ($mother['homepage']{strlen($mother['homepage']) - 1} == '/' ? '' : '/');
			$imageStr = "<img src=\"{$mother['homepage']}{$slash}index.gif\" alt=\"\" width=\"{$ico_size}\" height=\"{$ico_size}\" onerror=\"this.src = '".$context->getProperty('plugin.uri')."/images/secret.png'\" />";
		}
	} else {
		if (empty($mother['homepage'])) {
			$imageStr = "<img src=\"".$context->getProperty('plugin.uri')."/images/default.png\" alt=\"\" width=\"{$ico_size}\" height=\"{$ico_size}\" />";
		} else {
			$slash = ($mother['homepage']{strlen($mother['homepage']) - 1} == '/' ? '' : '/');
			$imageStr = "<img src=\"{$mother['homepage']}{$slash}index.gif\" alt=\"\" width=\"{$ico_size}\" height=\"{$ico_size}\" onerror=\"this.src = '".$context->getProperty('plugin.uri')."/images/default.png'\" />";
		}
	}
	
	return "{$imageStr} {$target}";
}

function BlogIcon_ConfigOut_ko($plugin) {
    $context = Model_Context::getInstance();

	$manifest = NULL;
	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="">'.CRLF;
	$manifest .= '	<window width="500" height="310" />'.CRLF;
	$manifest .= '		<fieldset legend="원하시는 블로그 아이콘 크기를 선택해주세요.">'.CRLF;
	$manifest .= '		<field title="블로그 아이콘을 " name="ico_size" type="select">'.CRLF;
	$manifest .= '			<op value="16" checked="checked">16x16 크기로 출력</op>'.CRLF;
	$manifest .= '			<op value="32">32x32 크기로 출력</op>'.CRLF;
	$manifest .= '			<op value="48">48x48 크기로 출력</op>'.CRLF;
	$manifest .= '			<caption>'.CRLF;
	$manifest .= '				<![CDATA['.CRLF;
	$manifest .= '				단위는 px, 기본값은 16x16 입니다.<br />'.CRLF;
	$manifest .= '				환경설정에서 블로그 아이콘을 업로드 해야 아이콘이 출력됩니다.'.CRLF;
	$manifest .= '				<p>'.CRLF;
	$manifest .= '					<img src="' . $context->getProperty('service.path') . '/image/icon_blogIcon_default.png" alt="16x16 예제" width="16" height="16" /> (16x16),'.CRLF;
	$manifest .= '					<img src="' . $context->getProperty('service.path') . '/image/icon_blogIcon_default.png" alt="32x32 예제" width="32" height="32" /> (32x32),'.CRLF;
	$manifest .= '					<img src="' . $context->getProperty('service.path') . '/image/icon_blogIcon_default.png" alt="48x48 예제" width="48" height="48" /> (48x48)'.CRLF;
	$manifest .= '				</p>'.CRLF;
	$manifest .= '				]]>'.CRLF;
	$manifest .= '			</caption>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>';
	
	return $manifest;
}

function BlogIcon_ConfigOut_en($plugin) {
	$manifest = NULL;
	
	$manifest .= '<?xml version="1.0" encoding="utf-8"?>'.CRLF;
	$manifest .= '<config dataValHandler="">'.CRLF;
	$manifest .= '	<window width="500" height="310" />'.CRLF;
	$manifest .= '		<fieldset legend="Select a size of blog icons for displaying.">'.CRLF;
	$manifest .= '		<field title="Size : " name="ico_size" type="select">'.CRLF;
	$manifest .= '			<op value="16" checked="checked">16x16</op>'.CRLF;
	$manifest .= '			<op value="32">32x32</op>'.CRLF;
	$manifest .= '			<op value="48">48x48</op>'.CRLF;
	$manifest .= '			<caption>'.CRLF;
	$manifest .= '				<![CDATA['.CRLF;
	$manifest .= '				A defualt value is 16x16 (pixel by pixel).<br />'.CRLF;
	$manifest .= '				<p>'.CRLF;
	$manifest .= '					<img src="' . $context->getProperty('service.path') . '/image/icon_blogIcon_default.png" alt="16x16 Example" width="16" height="16" /> (16x16),'.CRLF;
	$manifest .= '					<img src="' . $context->getProperty('service.path') . '/image/icon_blogIcon_default.png" alt="32x32 Example" width="32" height="32" /> (32x32),'.CRLF;
	$manifest .= '					<img src="' . $context->getProperty('service.path') . '/image/icon_blogIcon_default.png" alt="48x48 Example" width="48" height="48" /> (48x48)'.CRLF;
	$manifest .= '				</p>'.CRLF;
	$manifest .= '				]]>'.CRLF;
	$manifest .= '			</caption>'.CRLF;
	$manifest .= '		</field>'.CRLF;
	$manifest .= '	</fieldset>'.CRLF;
	$manifest .= '</config>';
	
	return $manifest;
}

function BlogIcon_DataSet($legacy){
    $context = Model_Context::getInstance();
    $config = $context->getProperty('plugin.config');
    return true;
}
?>
