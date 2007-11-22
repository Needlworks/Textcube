<?php
// Automatic menu location routine.
$blogMenu = array();
$urlFragments = preg_split('/\//',ltrim($suri['directive'],'/'));
if(isset($urlFragments[1])) $blogMenu['topMenu'] = $urlFragments[1];
if(isset($urlFragments[2])) $blogMenu['contentMenu'] = $urlFragments[2];
else $blogMenu['contentMenu'] = $urlFragments[1];
if(isset($urlFragments[3])) $blogMenu['contentMenu'] .= $urlFragments[3];

// If admin.panel plugin, set the menu location again.
if(isset($urlFragments[2])&&strncmp($urlFragments[2],'adminMenu',9) == 0) {
	$plugin = isset($_GET['name']) ? $_GET['name'] : '';
	$pluginDir = strtok($plugin,'/');
	$blogMenu['topMenu'] = $adminMenuMappings[$plugin]['topMenu'];
}

if(Acl::check('group.administrators')) {
	$blogTopMenuItem = array(
		array('menu'=>'center','title'=>_t('센터'),'link'=>'/owner/center/dashboard'),
		array('menu'=>'entry','title'=>_t('글'),'link'=>'/owner/entry'),
		array('menu'=>'link','title'=>_t('링크'),'link'=>'/owner/link'),
		array('menu'=>'skin','title'=>_t('스킨'),'link'=>'/owner/skin'),
		array('menu'=>'plugin','title'=>_t('플러그인'),'link'=>'/owner/plugin'),	
		array('menu'=>'setting','title'=>_t('환경설정'),'link'=>'/owner/setting/blog'),
		array('menu'=>'reader','title'=>_t('리더'),'link'=>'/owner/reader'),
		);
} else {
	$blogTopMenuItem = array(
		array('menu'=>'center','title'=>_t('센터'),'link'=>'/owner/center/dashboard'),
		array('menu'=>'entry','title'=>_t('글'),'link'=>'/owner/entry'),
		array('menu'=>'setting','title'=>_t('환경설정'),'link'=>'/owner/setting/account'),
		array('menu'=>'reader','title'=>_t('리더'),'link'=>'/owner/reader')
		);
}
if(Acl::check('group.inviters')) {  //TODO : Super Adminisrator에 맞는 권한 필요 현재는 inviters
	array_push($blogTopMenuItem, array('menu'=>'control','title'=>_t('제어판'),'link'=>'/owner/control/blog'));
}
switch($blogMenu['topMenu']) {
	case 'center':
		$blogMenu['title'] = _t('센터');
		$blogMenu['loadCSS'] = array('center');
		$blogMenu['loadCSSIE6'] = array('center');
		$blogMenu['loadCSSIE7'] = array('center');
		break;
	case 'entry':
		$blogMenu['title'] = _t('글');
		if ($blogMenu['contentMenu'] == 'post' || $blogMenu['contentMenu'] == 'edit') {
			$blogMenu['loadCSS'] = array('post','editor');
			$blogMenu['loadCSSIE6'] = array('post','editor');
			$blogMenu['loadCSSIE7'] = array('post','editor');
		} else {
			$blogMenu['loadCSS'] = array('post');
			$blogMenu['loadCSSIE6'] = array('post');
			$blogMenu['loadCSSIE7'] = array('post');
		}
		
		break;
	case 'link':
		$blogMenu['title'] = _t('링크');
		$blogMenu['loadCSS'] = array('link');
		$blogMenu['loadCSSIE6'] = array('link');
		$blogMenu['loadCSSIE7'] = array('link');
		break;
	case 'skin':
		$blogMenu['title'] = _t('스킨');
		$blogMenu['loadCSS'] = array('skin');
		$blogMenu['loadCSSIE6'] = array('skin');
		$blogMenu['loadCSSIE7'] = array('skin');
		break;
	case 'plugin':
		$blogMenu['title'] = _t('플러그인');
		$blogMenu['loadCSS'] = array('plugin');
		$blogMenu['loadCSSIE6'] = array('plugin');
		$blogMenu['loadCSSIE7'] = array('plugin');
		break;
	case 'setting':
	case 'data':
		$blogMenu['title'] = _t('환경설정');
		$blogMenu['loadCSS'] = array('setting');
		$blogMenu['loadCSSIE6'] = array('setting');
		$blogMenu['loadCSSIE7'] = array('setting');
		break;
	case 'reader':
		$blogMenu['title'] = _t('리더');
		$blogMenu['loadCSS'] = array('reader');
		$blogMenu['loadCSSIE6'] = array('reader');
		$blogMenu['loadCSSIE7'] = array('reader');
		break;
	case 'control':
		$blogMenu['title'] = _t('제어판');
		$blogMenu['loadCSS'] = array('control');
		break;
}

// mapping data management to setting
if(isset($blogMenu['topMenu']) && $blogMenu['topMenu']=='data') $blogMenu['topMenu'] = 'setting';
$pluginListForCSS = array();
if ($blogMenu['topMenu'] == 'center' && $blogMenu['contentMenu'] == 'dashboard') {
	if (isset($eventMappings['AddPostEditorToolbox'])) {
		foreach ($centerMappings as $tempPlugin) {
			array_push($pluginListForCSS, $tempPlugin['plugin']);
		}
	}
} else if ($blogMenu['topMenu'] == 'entry' && ($blogMenu['contentMenu'] == 'post' || $blogMenu['contentMenu'] == 'edit')) {
	if (isset($eventMappings['AddPostEditorToolbox'])) {
		foreach ($eventMappings['AddPostEditorToolbox'] as $tempPlugin) {
			array_push($pluginListForCSS, $tempPlugin['plugin']);
		}
	}
} else if (isset($pluginDir)) {
	array_push($pluginListForCSS, $pluginDir);
}
unset($tempPlugin);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo (isset($blog['language']) ? $blog['language'] : "ko");?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo htmlspecialchars($blog['title']);?> &gt; <?php echo $blogMenu['title'];?></title>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/basic.css" />
<?php
// common CSS.
foreach($blogMenu['loadCSS'] as $loadCSS) {
?>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/<?php echo $loadCSS;?>.css" />
<?php
}

foreach ($pluginListForCSS as $tempPluginDir) {
	if (isset($tempPluginDir) && file_exists(ROOT . "/plugins/$tempPluginDir/plugin-main.css")) {
?>
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'];?>/plugins/<?php echo $tempPluginDir;?>/plugin-main.css" />
<?php
	}
}
?>
	<!--[if lte IE 6]>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/basic.ie.css" />
<?php
// CSS for Internet Explorer 6
foreach($blogMenu['loadCSSIE6'] as $loadCSS) {
?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/<?php echo $loadCSS;?>.ie.css" />
<?php
}

foreach ($pluginListForCSS as $tempPluginDir) {
	if (isset($tempPluginDir) && file_exists(ROOT . "/plugins/$tempPluginDir/plugin-main.ie.css")) {
?>
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'];?>/plugins/<?php echo $tempPluginDir;?>/plugin-main.ie.css" />
<?php
	}
}
?>	
	<![endif]-->
	<!--[if IE 7]>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/basic.ie7.css" />
<?php
// CSS for Internet Explorer 7
foreach($blogMenu['loadCSSIE7'] as $loadCSS) {
?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/<?php echo $loadCSS;?>.ie7.css" />
<?php
}

foreach ($pluginListForCSS as $tempPluginDir) {
	if (isset($tempPluginDir) && file_exists(ROOT . "/plugins/$tempPluginDir/plugin-main.ie7.css")) {
?>
	<link rel="stylesheet" type="text/css" href="<?php echo $service['path'];?>/plugins/<?php echo $tempPluginDir;?>/plugin-main.ie7.css" />
<?php
	}
}

unset($pluginListForCSS);
unset($tempPluginDir);
?>
	<![endif]-->
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?php echo $service['path'];?>";
			var blogURL = "<?php echo $blogURL;?>";
			var adminSkin = "<?php echo $adminSkinSetting['skin'];?>";
<?php
if (file_exists(ROOT.$adminSkinSetting['editorTemplate'])) {
?>
			var editorCSS = "<?php echo $adminSkinSetting['editorTemplate'];?>";
<?php
} else {
?>
			var editorCSS = "/style/default-wysiwyg.css";
<?php
}

include ROOT . '/language/messages.php';
?>
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/byTextcube.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/EAF4.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/common2.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/gallery.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/owner.js"></script>
<?php
if( isset($service['admin_script']) ) {
	if( is_array($service['admin_script']) ) {
		foreach( $service['admin_script'] as $src ) {
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/<?php echo $src;?>"></script>
<?php
		}
	} else {
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/<?php echo $service['admin_script'];?>"></script>
<?php
	}
}
if($blogMenu['topMenu']=='entry') {
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/script/editor3.js"></script>
<?php
}
echo fireEvent('ShowAdminHeader', '');
?>
</head>
<body id="body-<?php echo $blogMenu['topMenu'];?>">
	<div id="temp-wrap">
		<div id="all-wrap">
			<div id="layout-header">
				<h1><?php echo _t('텍스트큐브 관리 페이지');?></h1>
				
				<div id="main-description-box">
					<ul id="main-description">
<?php
$writer = DBQuery::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = ".getUserId());
requireComponent('Textcube.Core');
?>
						<li id="description-blogger"><span class="text"><?php echo _f('환영합니다. <em>%1</em>님.', htmlspecialchars($writer));?></span></li>
						<li id="description-teamblog"><label for="teamblog"><?php echo _t('참여중인 블로그');?></label>
<?php echo User::changeBlog();?>
						</li>
						<li id="description-blog"><a href="<?php echo $blogURL;?>/" title="<?php echo _t('블로그 메인으로 이동합니다.');?>"><span class="text"><?php echo _t('블로그로 이동');?></span></a></li>
						<li id="description-logout"><a href="<?php echo $blogURL;?>/logout" title="<?php echo _t('로그아웃하고 블로그 메인으로 이동합니다.');?>"><span class="text"><?php echo _t('로그아웃');?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<h2><?php echo _t('메인메뉴');?></h2>
				
				<div id="main-menu-box">
					<ul id="main-menu">
						<li id="menu-textcube"><a href="<?php echo TEXTCUBE_HOMEPAGE;?>" onclick="window.open(this.href); return false;" title="<?php echo _t('텍스트큐브 홈페이지로 이동합니다.');?>"><span class="text"><?php echo _t('텍스트큐브 홈페이지');?></span></a></li>
						
<?php
foreach($blogTopMenuItem as $menuItem) {
?>
						<li id="menu-<?php echo $menuItem['menu'];?>"<?php echo $menuItem['menu']==$blogMenu['topMenu'] ? ' class="selected"' : '';?>><a href="<?php echo $blogURL.$menuItem['link'].($service['useRewriteEngine'] ? '' : '/index.php');?>"><span><?php echo $menuItem['title'];?></span></a></li>
<?php
}
?>
					</ul>
				</div>
			</div>
			
			<hr class="hidden" />
