<?php
/// Copyright (c) 2004-2014, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

define('__TEXTCUBE_CHECKUP_FILE__', __TEXTCUBE_CACHE_DIR__ . '/CHECKUP');

function getBlogVersion() {
	if (defined('__TEXTCUBE_GAE__')) {
		$version = Setting::getServiceSetting('blogVersion',null,true);
		if (is_null($version)) {
			$version = '0';
		}
		return $version;
	}
	if (!file_exists(__TEXTCUBE_CHECKUP_FILE__)) {
		return '0.0';
	}
	return trim(file_get_contents(__TEXTCUBE_CHECKUP_FILE__));
}

function setBlogVersion() {
	$version = TEXTCUBE_VERSION_ID;
	if (defined('__TEXTCUBE_GAE__')) {
		Setting::setServiceSetting('blogVersion',$version,true);
		return;
	}
	$fp = fopen(__TEXTCUBE_CHECKUP_FILE__, 'w');
	if ($fp !== FALSE) {
		fwrite($fp, $version);
		fclose($fp);
		@chmod(__TEXTCUBE_CHECKUP_FILE__, 0666);
	}	
}

function isNeededCheckupBlogVersion() {
	$current_version = getBlogVersion();
	return ($current_version != TEXTCUBE_VERSION_ID);
}

function printScriptCheckTextcubeVersion() {
	$context = Model_Context::getInstance();
	if (isNeededCheckupBlogVersion()) {
		$message = _t('텍스트큐브 시스템 점검이 필요합니다. 지금 점검하시겠습니까?');
		if (getBlogVersion() == '0') {
			$message = _t('버전업 체크를 위한 파일을 생성합니다. 지금 생성하시겠습니까?');
		}
?>
		window.addEventListener("load", checkTextcubeVersion, false);
		function checkTextcubeVersion() {
			if (confirm("<?php echo $message;?>"))
				window.location.href = "<?php echo $context->getProperty('uri.blog');?>/checkup";
		}
<?php
	}
}
?>
