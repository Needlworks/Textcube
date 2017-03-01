<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/control/header.php';

$context = Model_Context::getInstance();
// htacccess modification
$htaccessContent = '';
if (file_exists(ROOT . "/.htaccess")) {
	$htaccessContent = @file_get_contents(ROOT . "/.htaccess");
}

// Encodings
$encodingList = array('UTF-8','EUC-KR','SHIFT_JIS','EUC-JP','BIG5','EUC-CN','EUC-TW','GBK');

// Languages
$locale = Locales::getInstance();
$locale->setDirectory(ROOT.'/resources/locale/description');
$supportedLanguages = $locale->getSupportedLocales();

// Skins
$skinList = array($service['skin'] => null);
if (is_dir(ROOT.'/skin/') && $handler = opendir(ROOT.'/skin/')) {
	while (($file = readdir($handler)) !== false) {
		if (!is_dir(__TEXTCUBE_SKIN_DIR__) || in_array($file, array('.', '..', 'customize'))) {
			continue;
		}
		if (!file_exists(__TEXTCUBE_SKIN_DIR__.'/'.$file.'/index.xml') || !file_exists(__TEXTCUBE_SKIN_DIR__.'/'.$file.'/skin.html')) {
			continue;
		}
		$skinList[$file] = null;
	}
}
closedir($handler);

?>
						<script type="text/javascript">
						//<![CDATA[
							function getEncodedValueById(id) {
								val = document.getElementById(id).value;
								return encodeURIComponent(val);
							}
							function setConfigFile() {
								if(!confirm('<?php echo _f('적용하시면 기존의 %1 파일이 변경됩니다. 이 변경사항은 되돌릴 수 없습니다. 그래도 적용하시겠습니까?','config.php');?>')) return;
								if(document.getElementById('usePageCache').checked) usePageCache = 1;
								else usePageCache = 0;
								if(document.getElementById('useCodeCache').checked) useCodeCache = 1;
								else useCodeCache = 0;
								if(document.getElementById('useSkinCache').checked) useSkinCache = 1;
								else useSkinCache = 0;
								if(document.getElementById('useMemcached').checked) useMemcached = 1;
								else useMemcached = 0;
								if(document.getElementById('useSSL').checked) useSSL = 1;
								else useSSL = 0;
								if(document.getElementById('useExternalResource').checked) useExternalResource = 1;
								else useExternalResource = 0;
								if(document.getElementById('useReader').checked) useReader = 1;
								else useReader = 0;
								if(document.getElementById('useNumericRSS').checked) useNumericRSS = 1;
								else useNumericRSS = 0;
								if(document.getElementById('useEncodedURL').checked) useEncodedURL = 1;
								else useEncodedURL = 0;
								if(document.getElementById('allowBlogVisibilitySetting').checked) allowBlogVisibilitySetting = 1;
								else allowBlogVisibilitySetting = 0;
								if(document.getElementById('requireLogin').checked) requireLogin = 1;
								else requireLogin = 0;
								if(document.getElementById('flashClipboardPoter').checked) flashClipboardPoter = 1;
								else flashClipboardPoter = 0;
								if(document.getElementById('flashUploader').checked) flashUploader = 1;
								else flashUploader = 0;
								if(document.getElementById('useDebugMode').checked) useDebugMode = 1;
								else useDebugMode = 0;
								if(document.getElementById('useSessionDebugMode').checked) useSessionDebugMode = 1;
								else useSessionDebugMode = 0;
								if(document.getElementById('useRewriteDebugMode').checked) useRewriteDebugMode = 1;
								else useRewriteDebugMode = 0;
								param = '';
								param += 'usePageCache='+usePageCache+'&';
								param += 'useCodeCache='+useCodeCache+'&';
								param += 'useSkinCache='+useSkinCache +'&';
								param += 'useMemcached='+useMemcached +'&';
								param += 'useSSL='+useSSL +'&';
								param += 'useExternalResource='+useExternalResource +'&';
								param += 'useReader='+useReader +'&';
								param += 'useNumericRSS='+useNumericRSS +'&';
								param += 'useEncodedURL='+useEncodedURL +'&';
								param += 'allowBlogVisibility='+allowBlogVisibilitySetting +'&';
								param += 'requireLogin='+requireLogin +'&';
								param += 'flashClipboardPoter='+flashClipboardPoter +'&';
								param += 'flashUploader='+flashUploader +'&';
								param += 'useDebugMode='+useDebugMode +'&';
								param += 'useSessionDebugMode='+useSessionDebugMode +'&';
								param += 'useRewriteDebugMode='+useRewriteDebugMode +'&';
								param += 'timeout='+getEncodedValueById('timeout') +'&';
								param += 'skin='+getEncodedValueById('skin') +'&';
								param += 'faviconDailyTraffic='+getEncodedValueById('faviconDailyTraffic') +'&';
								param += 'language='+getEncodedValueById('language') +'&';
								param += 'timezone='+getEncodedValueById('timezone') +'&';
								param += 'encoding='+getEncodedValueById('encoding') +'&';
								param += 'serviceurl='+getEncodedValueById('serviceurl') + '&';
								param += 'cookieprefix='+getEncodedValueById('cookieprefix') + '&';
								param += 'externalResourceURL='+getEncodedValueById('externalResourceURL');
								var request = new HTTPRequest("POST", '<?php echo $blogURL;?>/control/server/config/');
								request.onSuccess = function() {
									PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
								}
								request.onError = function() {
									PM.showErrorMessage("<?php echo _t('저장하지 못했습니다');?>", "center", "bottom");
									alert(this.getText("/response/msg"));
								}
								request.send(param);
							}
							function setSmtp() {
								var useCustomSMTP = document.getElementById('useCustomSMTP').checked?1:0;
								var smtpHost = document.getElementById('smtpHost').value;
								var smtpPort = document.getElementById('smtpPort').value;

								var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/control/server/mailhost/");
								request.onVerify = function() {
									return this.getText("/response/error") == 0;
								}
								request.onSuccess = function() {
									PM.showMessage("<?php echo _t('저장했습니다');?>", "center", "bottom");
								}
								request.onError = function() {
										alert('<?php echo _t('저장하지 못했습니다');?>');
								}
								request.send("&useCustomSMTP="+useCustomSMTP+"&smtpHost="+encodeURIComponent(smtpHost)+"&smtpPort="+smtpPort);
							}
<?php
if(!defined('__TEXTCUBE_NO_FANCY_URL__')) {
?>
							function setRewrite() {
								if(!confirm('<?php echo _f('적용하시면 기존의 %1 파일이 변경됩니다. 이 변경사항은 되돌릴 수 없습니다. 그래도 적용하시겠습니까?','.htaccess');?>')) return;
								var htaccess = document.getElementById('rewrite');

								var request = new HTTPRequest("POST", "<?php echo $blogURL;?>/control/server/rewrite/");
								request.onSuccess = function() {
									PM.showMessage("<?php echo _t('저장되었습니다');?>", "center", "bottom");
								}
								request.onError = function() {
									if (this.getText("/response/msg"))
										alert(this.getText("/response/msg"));
									else
										alert('<?php echo _t('실패했습니다.');?>');
								}
								request.send('body='+encodeURIComponent(htaccess.value));
							}
<?php
}
?>
							function setDefault() {
								if(!confirm('<?php echo _f('선택하신 옵션은 %1 파일을 처음 설치할 때의 값으로 되돌립니다.','.htaccess').' '._f('적용하시면 기존의 %1 파일이 변경됩니다. 이 변경사항은 되돌릴 수 없습니다. 그래도 적용하시겠습니까?','.htaccess');?>')) return;
								var htaccess = document.getElementById('rewrite');
								htaccess.value = '<?php echo getDefaultHtaccess(true);?>';
								return true;
							}
						//]]>
						</script>

						<div id="part-control-config" class="part">
							<h2 class="caption"><span class="main-text"><?php	echo _t('서비스 기능을 조정합니다');?></span></h2>
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('텍스트큐브의 기능을 조정합니다.').' '._t('이 명령은 config.php 파일을 직접 수정합니다. 또한 서비스 전체에 동시에 영향을 줍니다.').' '._t('문제가 생길 경우에는 설치된 디렉토리의 config.php를 직접 수정하셔서 복원할 수 있습니다.').' '._t('변경시 텍스트큐브의 동작에 큰 영향을 줄 수 있으므로 주의하시기 바랍니다.');?></p>
<?php
if (!is_writable(ROOT . "/config.php")) {
?>
								<p class="waring"><?php echo _f('파일 쓰기 권한이 없습니다. 웹서버가 %1 파일의 쓰기 권한을 가지고 있는지 확인하세요.','config.php').' '._t('아래의 설정을 저장하셔도 반영되지 않습니다.');?></p>
<?php
}
?>
							</div>

							<div class="data-inbox">
								<form id="configSetting" class="section" method="post" action="<?php echo $blogURL;?>/control/server/config" enctype="application/x-www-form-urlencoded">
									<fieldset id="cache-container" class="container">
										<legend><?php echo _t('기본 설정');?></legend>

										<dl id="timeout-line" class="line">
											<dt><span class="label"><?php echo _t('세션 타임아웃');?></span></dt>
											<dd>
												<input id="timeout" type="text" class="input-text" name="timeout" size="5" value="<?php echo $service['timeout'];?>" />
												<label for="timeout"><?php echo _t('세션 타임 아웃까지의 시간을 설정합니다. 단위는 초입니다.');?></label>
											</dd>
										</dl>
										<dl id="skin-line" class="line">
											<dt><span class="label"><?php echo _t('기본 스킨');?></span></dt>
											<dd>
												<select id="skin" name="skin">
<?php
foreach ($skinList as $skin => $value) {
?>
													<option value="<?php echo $skin; ?>"<?php echo ($skin == $service['skin'] ? ' selected="selected"' : ''); ?>><?php echo $skin; ?></option>
<?php
}
?>												</select>
												<label for="skin"><?php echo _t('블로그의 기본 스킨을 정합니다. 반드시 존재하는 스킨의 디렉토리 이름이어야 합니다.');?></label>
											</dd>
										</dl>
										<dl id="favicon-line" class="line">
											<dt><span class="label"><?php echo _t('파비콘 트래픽');?></span></dt>
											<dd>
												<input id="faviconDailyTraffic" type="text" class="input-text" name="faviconDailyTraffic" size="5" value="<?php echo $service['favicon_daily_traffic'];?>" />
												<label for="faviconDailyTraffic"><?php echo _t('블로그 파비콘이 하루에 소모하는 트래픽을 제한합니다. 단위는 Megabyte 입니다.');?></label>
											</dd>
										</dl>
										<dl id="language-line" class="line">
											<dt><span class="label"><?php echo _t('언어');?></span></dt>
											<dd>
												<select id="language" name="language">
<?php
foreach ($supportedLanguages as $locale => $language) {
?>
													<option value="<?php echo $locale; ?>"<?php echo ($locale == $service['language'] ? ' selected="selected"' : ''); ?>><?php echo $language; ?></option>
<?php
}
?>												</select>
												<label for="language"><?php echo _t('이 서비스의 기본 언어를 설정합니다.');?></label>
											</dd>
										</dl>
										<dl id="timezone-line" class="line">
											<dt><span class="label"><?php echo _t('시간대');?></span></dt>
											<dd>
												<select id="timezone" name="timezone">
<?php
$tz = new Timezone;
foreach ($tz->getList() as $timezone) {
?>
													<option value="<?php echo $timezone;?>"<?php echo ($timezone == $service['timezone'] ? ' selected="selected"' : '');?>><?php echo _t($timezone);?></option>
<?php
}
?>
												</select>
												<label for="timezone"><?php echo _t('이 서비스의 기본 시간대를 설정합니다.');?></label>
											</dd>
										</dl>
										<dl id="encoding-line" class="line">
											<dt><span class="label"><?php echo _t('인코딩');?></span></dt>
											<dd>
												<select id="encoding" name="encoding">
<?php
foreach($encodingList as $enc) {
?>
													<option value="<?php echo $enc;?>"<?php if($enc==$service['encoding']) echo ' selected="selected"';?>><?php echo  htmlspecialchars($enc);?></option>

<?php
}
//											<input id="encoding" type="text" class="input-text" name="encoding" size="13" value="<?php echo $service['encoding'];?>" />
?>
												</select>

<label for="encoding"><?php echo _t('이 서비스의 기본 인코딩을 설정합니다.');?></label>
											</dd>
										</dl>
										<dl id="serviceurl-line" class="line">
											<dt><span class="label"><?php echo _t('서비스 리소스 경로');?></span></dt>
											<dd>
												<input id="serviceurl" type="text" class="input-text" name="serviceurl" size="45" value="<?php echo $serviceURL;?>" />
												<label for="serviceurl"><?php echo _t('이 서비스가 참조할 경로를 강제로 지정합니다.').'<br />'._t('정적인 파일들 (script, attach, image, style 하위 디렉토리)을 별도의 경로로 관리할 수 있습니다. 다른 웹 프로그램을 같은 도메인에서 운영할 때 동작이 방해받는 경우, 또는 서버에 걸리는 부하를 분산하고 싶은 경우 지정하면 됩니다.');?></label>
											</dd>
										</dl>
										<dl id="externalResource-line" class="line">
											<dt><span class="label"><?php echo _t('외부 리소스 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useExternalResource" class="checkbox" name="useExternalResource"<?php echo $service['externalresources'] ? ' checked="checked"' : '';?> /><label for="useExternalResource"><?php echo _t('텍스트큐브의 자바 스크립트 컴포넌트등을 외부에서 불러옵니다.').' '._f('서버의 트래픽을 줄이기 위하여 동작시 필요한 일부 리소스를 공개 텍스트큐브 리소스 저장소(%1)에서 읽어오거나 지정한 서버에서 읽어옵니다.',TEXTCUBE_RESOURCE_URL);?></label>
											</dd>
										</dl>
										<dl id="externalresourceeurl-line" class="line">
											<dt><span class="label"><?php echo _t('외부 리소스 저장소 경로');?></span></dt>
											<dd>
												<input id="externalResourceURL" type="text" class="input-text" name="externalResourceURL" size="45" value="<?php echo (isset($service['resourceURL']) ? $service['resourceURL'] : '');?>" />
												<label for="externalResourceURL"><?php echo _t('외부 리소스를 사용할 경우 리소스 저장소를 임의로 지정할 수 있습니다.').'<br />'._f('이 값을 지정하지 않고 외부 리소스 사용을 선택할 경우 텍스트큐브 리소스 저장소(%1)를 기본값으로 사용합니다.',TEXTCUBE_RESOURCE_URL);?></label>
											</dd>
										</dl>
										<dl id="cookieprefix-line" class="line">
											<dt><span class="label"><?php echo _t('COOKIE 접두어');?></span></dt>
											<dd>
												<input id="cookieprefix" type="text" class="input-text" name="cookieprefix" size="45" value="<?php echo ($context->getProperty('service.cookie_prefix','') == 'Textcube'.str_replace('.','',TEXTCUBE_VERSION_ID)) ? '' : $context->getProperty('service.cookie_prefix','');?>" />
												<label for="cookieprefix"><?php echo _t('이 서비스에서 사용할 웹 브라우저 쿠키 이름 앞에 붙을 접두어를 지정합니다.').'<br />'._t('지정하지 않을 경우 웹 브라우저 쿠키 접두어는 Textcube[버전번호] 로 자동으로 지정합니다.');?></label>
											</dd>
										</dl>
									</fieldset>
									<fieldset id="cache-container" class="container">
										<legend><?php echo _t('캐시 사용 조절');?></legend>

										<dl id="cache-line" class="line">
											<dt><span class="label"><?php echo _t('페이지 캐시 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="usePageCache" class="checkbox" name="usePageCache"<?php echo $service['pagecache'] ? ' checked="checked"' : '';?> /><label for="usePageCache"><?php echo _t('텍스트큐브의 전반적인 캐시 기능을 사용합니다. 페이지 캐시, 스킨캐시, 실시간 쿼리 캐시 및 정책을 모두 포함됩니다.');?></label>
											</dd>
										</dl>
										<dl id="codecache-line" class="line">
											<dt><span class="label"><?php echo _t('코드 캐시 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useCodeCache" class="checkbox" name="useCodeCache"<?php echo $service['codecache'] ? ' checked="checked"' : '';?> /><label for="useCodeCache"><?php echo _t('텍스트큐브 코드를 캐시하는 기능을 사용합니다.').' '._t('작업에 따라 각각 하나의 파일로 최적화된 프로그램 코드 캐시를 만들어 메인 코드 대신 사용합니다.').' '._t('페이지 호출 응답 속도가 빨라집니다.');?></label>
											</dd>
										</dl>
										<dl id="skin-line" class="line">
											<dt><span class="label"><?php echo _t('스킨 캐시 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useSkinCache" class="checkbox" name="useSkinCache"<?php echo $service['skincache'] ? ' checked="checked"' : '';?> /><label for="useSkinCache"><?php echo _t('스킨 캐시를 사용합니다.').' '._t('스킨 캐시를 사용하지 않도록 설정하면 페이지 캐시를 사용하도록 설정해도 스킨 캐시를 사용하지않습니다.').' '._t('스킨 파일을 직접 수정한 후 바로 변경된 결과를 보아야 하는 경우 스킨 캐시를 끄고 작업하시기 바랍니다.');?></label>
											</dd>
										</dl>
										<dl id="memcached-line" class="line">
											<dt><span class="label"><?php echo _t('Memcached 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useMemcached" class="checkbox" name="useMemcached"<?php echo (isset($service['memcached']) && $service['memcached']) ? ' checked="checked"' : '';?> /><label for="useMemcached"><?php echo _t('Memcached 모듈을 사용합니다.').' '._t('블로그의 속도 향상을 위하여 Memcached를 사용합니다. 이 기능을 사용하기 위해서는 서버에 Memcached가 설치되어 있고, PHP가 Memcached를 사용할 수 있도록 설정되어 있어야 합니다.');?></label>
											</dd>
										</dl>
										<dl id="ssl-line" class="line">
											<dt><span class="label"><?php echo _t('SSL 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useSSL" class="checkbox" name="useSSL"<?php echo (isset($service['useSSL']) && $service['useSSL']) ? ' checked="checked"' : '';?> /><label for="useSSL"><?php echo _t('SSL을 사용합니다.').' '._t('모든 http://링크가 https링크로 변환됩니다.');?></label>
											</dd>
										</dl>
										<dl id="reader-line" class="line">
											<dt><span class="label"><?php echo _t('RSS 이웃글 보기 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useReader" class="checkbox" name="useReader"<?php echo $service['reader'] ? ' checked="checked"' : '';?> /><label for="useReader"><?php echo _t('바깥글 보기 (RSS 리더)를 사용합니다. 끌 경우 리더가 RSS를 읽어오는 로드가 줄어들기 때문에 서버 로드가 줄어듭니다.');?></label>
											</dd>
										</dl>
									</fieldset>

									<fieldset id="option-container" class="container">
										<dl id="numeric-rss-line" class="line">
											<dt><span class="label"><?php echo _t('RSS 주소를 숫자로 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useNumericRSS" class="checkbox" name="useNumericRSS"<?php echo $service['useNumericURLonRSS'] ? ' checked="checked"' : '';?> /><label for="useNumericRSS"><?php echo _t('RSS 주소를 fancyURL을 사용하지 않고 강제로 숫자로 설정합니다.');?></label>
											</dd>
										</dl>
										<dl id="encoded-url-line" class="line">
											<dt><span class="label"><?php echo _t('인코딩된 문자 주소 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useEncodedURL" class="checkbox" name="useEncodedURL"<?php echo $service['useEncodedURL'] ? ' checked="checked"' : '';?> /><label for="useEncodedURL"><?php echo _t('영어 이외의 주소 출력시 RFC1738 규격에 맞추어 주소를 인코딩한 채로 출력합니다.');?></label>
											</dd>
										</dl>
										<dl id="blog-visibility-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 공개 설정 변경 허용');?></span></dt>
											<dd>
												<input type="checkbox" id="allowBlogVisibilitySetting" class="checkbox" name="allowBlogVisibilitySetting"<?php echo $service['allowBlogVisibilitySetting'] ? ' checked="checked"' : '';?> /><label for="allowBlogVisibilitySetting"><?php echo _t('다중 블로그 모드에서 블로그 운영자가 블로그를 비공개로 설정하는 것을 허용합니다.');?></label>
											</dd>
										</dl>
										<dl id="blog-service-visibility-line" class="line">
											<dt><span class="label"><?php echo _t('블로그 서비스 공개 정도');?></span></dt>
											<dd>
												<input type="checkbox" id="requireLogin" class="checkbox" name="requireLogin"<?php echo $service['requirelogin'] ? ' checked="checked"' : '';?> /><label for="requireLogin"><?php echo _t('다중 블로그 모드에서 블로그 회원만 다른 블로그를 볼 수 있도록 합니다. 비공개 블로그 커뮤니티에 유용합니다.');?></label>
											</dd>
										</dl>
										<dl id="flash-clipboard--line" class="line">
											<dt><span class="label"><?php echo _t('플래시 트랙백 주소 복사 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="flashClipboardPoter" class="checkbox" name="flashClipboardPoter"<?php echo $service['flashclipboardpoter'] ? ' checked="checked"' : '';?> /><label for="flashClipboardPoter"><?php echo _t('크로스 브라우징을 지원하는 트랙백 복사를 위한 플래시를 사용합니다.');?></label>
											</dd>
										</dl>
										<dl id="flash-uploader--line" class="line">
											<dt><span class="label"><?php echo _t('플래시 업로더 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="flashUploader" class="checkbox" name="flashUploader"<?php echo $service['flashuploader']  ? ' checked="checked"' : '';?> /><label for="flashUploader"><?php echo _t('에디터에서 다중 파일 업로드를 지원하는 플래시 업로더를 사용합니다.');?></label>
											</dd>
										</dl>
									</fieldset>

									<fieldset id="debug-container" class="container">
										<legend><?php echo _t('디버그 모드 조절');?></legend>

										<dl id="debugmode-line" class="line">
											<dt><span class="label"><?php echo _t('디버그 모드 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useDebugMode" class="checkbox" name="useDebugMode"<?php echo $service['debugmode'] ? ' checked="checked"' : '';?> /><label for="useDebugMode"><?php echo _t('디버그 모드를 사용합니다. 텍스트큐브의 모든 쿼리 실행 결과 및 코드의 에러가 발생하는 부분을 추적할 수 있습니다.');?></label>
											</dd>
										</dl>
										<dl id="debug-session-dump-line" class="line">
											<dt><span class="label"><?php echo _t('세션 디버그 모드 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useSessionDebugMode" class="checkbox" name="useSessionDebugMode"<?php echo $service['debug_session_dump']  ? ' checked="checked"' : '';?> /><label for="useSessionDebugMode"><?php echo _t('세션 디버그 모드를 디버그 모드에 추가합니다. 현재 사용중인 세션에 대한 정보가 디버그 모드 끝에 출력됩니다.');?></label>
											</dd>
										</dl>
										<dl id="debug-rewrite-module-line" class="line">
											<dt><span class="label"><?php echo _t('rewrite 모듈 디버그 모드 사용');?></span></dt>
											<dd>
												<input type="checkbox" id="useRewriteDebugMode" class="checkbox" name="useRewriteDebugMode"<?php echo $service['debug_rewrite_module'] ? ' checked="checked"' : '';?> /><label for="useRewriteDebugMode"><?php echo _t('rewrite 모듈 디버그 모드를 디버그 모드에 추가합니다. 접근시 적용된 주소를 해석한 정보가 디버그 모드 끝에 출력됩니다.');?></label>
											</dd>
										</dl>
									</fieldset>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setConfigFile(); return false;" />
									</div>
								</form>
							</div>
						</div>

						<div id="part-control-mailhost" class="part">
							<h2 class="caption"><span class="main-text"><?php	echo _t('메일 보낼 서버를 지정합니다');?></span></h2>

							<div class="data-inbox">
								<form class="section" method="post" action="<?php echo $blogURL;?>/control/server/mailhost">
									<dl>
										<dt class="title"><span class="label"><?php	echo _t('메일 서버 설정');?></span></dt>
										<dd>

											<div class="line">
												<input id="useCustomSMTP" type="checkbox" class="checkbox" name="useCustomSMTP" value="1" <?php if( getServiceSetting( 'useCustomSMTP', 0 ) ) { echo "checked='checked'"; } ?> />
												<label for="useCustomSMTP"><?php echo _t('메일서버 지정'); ?></label>
											</div>
											<div class="line">
												<label for="smtpHost"><?php echo _t('메일서버 IP 주소:포트'); ?></label>
												<input id="smtpHost" type="text" class="input-text" name="smtpHost" value="<?php echo getServiceSetting( 'smtpHost', '127.0.0.1' ); ?>" /> :
												<input id="smtpPort" type="text" class="input-text" name="smtpPort" value="<?php echo getServiceSetting( 'smtpPort', 25 );?>" />
											</div>
										</dd>
									</dl>
									<div class="button-box">
										<input type="submit" class="save-button input-button" value="<?php	echo _t('저장하기');?>" onclick="setSmtp(); return false;" />
									</div>
								</form>
							</div>
						</div>

						<hr class="hidden" />
<?php
if(!defined('__TEXTCUBE_NO_FANCY_URL__')) {
?>
						<div id="part-control-rewrite" class="part">
							<h2 class="caption"><span class="main-text"><?php	echo _t('rewrite 규칙을 편집합니다');?></span></h2>
							<div class="main-explain-box">
								<p class="explain"><?php echo _t('rewrite 모듈의 동작을 조정하는 .htaccess 파일을 변경합니다.').' '._t('변경시 텍스트큐브의 동작에 큰 영향을 줄 수 있으므로 주의하시기 바랍니다.');?></p>
<?php
if (!is_writable(ROOT . "/.htaccess")) {
?>
								<p class="waring"><?php echo _f('파일 쓰기 권한이 없습니다. 웹서버가 %1 파일의 쓰기 권한을 가지고 있는지 확인하세요.','.htaccess').' '._t('아래의 설정을 저장하셔도 반영되지 않습니다.');?></p>
<?php
}
?>
							</div>
							<div class="data-inbox">
								<form id="rewriteSectionForm" class="section" method="post" action="<?php echo $blogURL;?>/control/server/rewrite/">

									<div id="rewrite-container">
										<textarea id="rewrite" name="htaccess" cols="100" rows="20" onkeyup="htaccessSaved=false"><?php echo htmlspecialchars($htaccessContent);?></textarea>
									</div>
									<div class="button-box">
										<input type="button" class="default-button input-button" value="<?php echo _t('기본값');?>" onclick="setDefault();return false;" />
										<input type="reset" class="reset-button input-button" value="<?php echo _t('되돌리기');?>" />
										<input type="submit" class="save-button input-button" value="<?php echo _t('저장하기');?>" onclick="setRewrite(); return false;" />
									</div>
								</form>
							</div>
						</div>
						<hr class="hidden" />
<?php
}
if (isset($_GET['message'])) {
	$msg = escapeJSInCData($_GET['message']);
?>
	<script type="text/javascript">
		//<![CDATA[
			window.onload = function() { PM.showMessage("<?php echo $msg;?>", "center", "bottom"); }
		//]]>
	</script>
<?php
}

require ROOT . '/interface/common/control/footer.php';
?>
