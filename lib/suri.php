<?
$url = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['SCRIPT_NAME'];
$suri = array('url' => $url, 'value' => '');
$owner = null;
$depth = substr_count($service['path'], '/');
if ($depth > 0) {
	if (ereg("^((/+[^/]+){{$depth}})(.*)$", $url, $matches))
		$url = $matches[3];
	else
		respondNotFoundPage();
}
if ($service['type'] == 'single') {
	$owner = 1;
} else {
	if ($service['type'] == 'domain') {
		if ($_SERVER['HTTP_HOST'] == $service['domain']) {
			$owner = 1;
		} else {
			$domain = explode('.', $_SERVER['HTTP_HOST'], 2);
			if ($domain[1] == $service['domain']) {
				$owner = getOwner($domain[0]);
				if ($owner === null)
					$owner = getOwnerBySecondaryDomain($_SERVER['HTTP_HOST']);
			} else {
				$owner = getOwnerBySecondaryDomain($_SERVER['HTTP_HOST']);
			}
		}
	} else {
		if ($url == '/') {
			$owner = 1;
		} else if (ereg('^/+([^/]+)(.*)$', $url, $matches)) {
			$owner = getOwner($matches[1]);
			$url = $matches[2];
		} else {
			respondNotFoundPage();
		}
	}
	if ($owner === null)
		respondNotFoundPage();
}
$blog = getBlogSetting($owner);
$skinSetting = getSkinSetting($owner);
$depth = substr_count(ROOT, '/');
if ($depth > 0) {
	if (ereg("^((/+[^/]+){{$depth}})/*(.*)$", $url, $matches)) {
		$suri['directive'] = $matches[1];
		if ($matches[3] !== false)
			$suri['value'] = $matches[3];
	} else
		respondNotFoundPage();
} else {
	$suri['directive'] = '/';
	$suri['value'] = ltrim($url, '/');
}
if (is_numeric($suri['value']))
	$suri['id'] = $suri['value'];
$suri['page'] = empty($_POST['page']) ? (empty($_GET['page']) ? true : $_GET['page']) : $_POST['page'];
$hostURL = "http://{$_SERVER['HTTP_HOST']}" . (isset($service['port']) ? ":{$service['port']}" : '');
$blogURL = $service['type'] == 'path' ? "{$service['path']}/{$blog['name']}" : $service['path'];
$folderURL = rtrim($blogURL . $suri['directive'], '/');
if (defined('__TATTERTOOLS_MOBILE__')) {
	$blogURL .= '/m';
}
unset($url, $domain);

// 어드민 스킨 및 에디터 템플릿 설정.
$adminSkinSetting = array();
$tempAdminSkin = getUserSetting("adminSkin");
if (empty($tempAdminSkin))
	$adminSkinSetting['skin'] = "/style/admin/default";
else
	$adminSkinSetting['skin'] = "/style/admin/$tempAdminSkin";
$tempTemplate = getUserSetting("visualEditorTemplate");
if (empty($tempTemplate))
	$adminSkinSetting['editorTemplate'] = "/style/default-wysiwyg.css";
else
	$adminSkinSetting['editorTemplate'] = "/skin/$tempTemplate/wysiwyg.css";
unset($tempTemplate);
unset($tempAdminSkin);

if (!file_exists(ROOT . '/config.php')) {
	header("Location: $blogURL/setup.php");
	exit;
}

function respondNotFoundPage() {
	header('HTTP/1.1 404 Not Found');
	header("Connection: close");
	exit;
}

function respondForbiddenPage() {
	header('HTTP/1.1 403 Forbidden');
	header("Connection: close");
	exit;
}
?>
