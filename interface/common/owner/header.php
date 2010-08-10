<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/***** Automatic menu location routine. *****/
$blogMenu = array();
$urlFragments = preg_split('/\//',ltrim($suri['directive'],'/'));
if(isset($urlFragments[1])) $blogMenu['topMenu'] = $urlFragments[1];
if(isset($urlFragments[2])) $blogMenu['contentMenu'] = $urlFragments[2];
else $blogMenu['contentMenu'] = $urlFragments[1];
if(isset($urlFragments[3])) $blogMenu['contentMenu'] .= $urlFragments[3];
// If admin.panel plugin, set the menu location again.
if(isset($urlFragments[2])&&strncmp($urlFragments[2],'adminMenu',9) == 0) {
	if($service['fancyURL'] < 2) {
		$plugin = isset($_GET['/owner/plugin/adminMenu?name']) ? $_GET['/owner/plugin/adminMenu?name'] : '';
	} else {
		$plugin = isset($_GET['name']) ? $_GET['name'] : '';
	}
	$pluginDir = strtok($plugin,'/');
	$blogMenu['topMenu'] = $adminMenuMappings[$plugin]['topMenu'];
}

if($urlFragments[0] == 'control' && Acl::check('group.creators')) {
	$blogTopMenuItem = array(
		array('menu'=>'control','title'=>_t('서비스관리'),'link'=>'/control/blog')
		);
	$blogMenu['topMenu'] = 'control';
} else if(Acl::check('group.administrators')) {
	$blogTopMenuItem = array(
		array('menu'=>'center','title'=>_t('센터'),'link'=>'/owner/center/dashboard'),
		array('menu'=>'entry','title'=>_t('글'),'link'=>'/owner/entry'),
		array('menu'=>'communication','title'=>_t('소통'),'link'=>'/owner/communication/comment'),
		array('menu'=>'network','title'=>_t('네트워크'),'link'=>'/owner/network/link'),
		array('menu'=>'skin','title'=>_t('꾸미기'),'link'=>'/owner/skin'),
		array('menu'=>'plugin','title'=>_t('플러그인'),'link'=>'/owner/plugin'),	
		array('menu'=>'setting','title'=>_t('설정'),'link'=>'/owner/setting/blog')
		);
} else {
	$blogTopMenuItem = array(
		array('menu'=>'center','title'=>_t('센터'),'link'=>'/owner/center/dashboard'),
		array('menu'=>'entry','title'=>_t('글'),'link'=>'/owner/entry'),
		array('menu'=>'communication','title'=>_t('소통'),'link'=>'/owner/communication/comment'),
		array('menu'=>'network','title'=>_t('네트워크'),'link'=>'/owner/network/link'),		
		array('menu'=>'setting','title'=>_t('설정'),'link'=>'/owner/setting/account')
		);
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
	case 'communication':
		$blogMenu['title'] = _t('소통');
		$blogMenu['loadCSS'] = array('communication');
		$blogMenu['loadCSSIE6'] = array('communication');
		$blogMenu['loadCSSIE7'] = array('communication');
		break;
	case 'network':
		$blogMenu['title'] = _t('네트워크');
		$blogMenu['loadCSS'] = array('network');
		$blogMenu['loadCSSIE6'] = array('network');
		$blogMenu['loadCSSIE7'] = array('network');
		break;
	case 'skin':
		$blogMenu['title'] = _t('꾸미기');
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
		$blogMenu['title'] = _t('설정');
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
		$blogMenu['title'] = _t('서비스');
		$blogMenu['loadCSS'] = array('control');
		break;
}
// exception for reader CSS. RSS reader will keep as an independent module.
if(defined('__TEXTCUBE_READER_SUBMENU__') && $blogMenu['contentMenu'] == 'reader') {
	$blogMenu['topMenu'] = 'network';
	$blogMenu['title'] = _t('네트워크');
	$blogMenu['loadCSS'] = array('reader');
	$blogMenu['loadCSSIE6'] = array('reader');
	$blogMenu['loadCSSIE7'] = array('reader');
}
// mapping data management to setting
if(isset($blogMenu['topMenu']) && $blogMenu['topMenu']=='data') $blogMenu['topMenu'] = 'setting';
$pluginListForCSS = array();
if ($blogMenu['topMenu'] == 'center' && $blogMenu['contentMenu'] == 'dashboard') {
	if (isset($centerMappings)) {
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

/***** Submenu generation *****/
if(isset($blogMenu['topMenu'])) {
	if(Acl::check('group.administrators')) {
		$blogContentMenuItem['center'] = array(
			array('menu'=>'dashboard','title'=>_t('조각보'),'link'=>'/owner/center/dashboard'),
		);
	} else{
		$blogContentMenuItem['center'] = array(
			array('menu'=>'dashboard','title'=>_t('조각보'),'link'=>'/owner/center/dashboard')
		);
	}
	if(Acl::check('group.editors')) {
		$blogContentMenuItem['entry'] = array(
			array('menu'=>'entry','title'=>_t('글 목록'),'link'=>'/owner/entry'),
			array('menu'=>'line','title'=>_t('라인 관리'),'link'=>'/owner/entry/line'),
			array('menu'=>'divider','title'=> '-','link'=>'/'),
			array('menu'=>'post','title'=>_t('글 쓰기'),'link'=>'/owner/entry/post'),
			array('menu'=>'notice','title'=>_t('공지 쓰기'),'link'=>'/owner/entry/post?category=-2'),
			array('menu'=>'keylog','title'=>_t('키워드 만들기'),'link'=>'/owner/entry/post?category=-1'),
			array('menu'=>'template','title'=>_t('서식 만들기'),'link'=>'/owner/entry/post?category=-4'),
			array('menu'=>'divider','title'=> '-','link'=>'/'),
			array('menu'=>'category','title'=>_t('분류 관리'),'link'=>'/owner/entry/category'),
			array('menu'=>'tag','title'=>_t('태그 관리'),'link'=>'/owner/entry/tag')
		);
	} else {
		$blogContentMenuItem['entry'] = array(
			array('menu'=>'entry','title'=>_t('글 목록'),'link'=>'/owner/entry'),
			array('menu'=>'divider','title'=> '-','link'=>'/'),
			array('menu'=>'post','title'=>_t('글쓰기'),'link'=>'/owner/entry/post'),
			array('menu'=>'notice','title'=>_t('공지 쓰기'),'link'=>'/owner/entry/post?category=-2'),
			array('menu'=>'keylog','title'=>_t('키워드 만들기'),'link'=>'/owner/entry/post?category=-1'),
			array('menu'=>'template','title'=>_t('서식 만들기'),'link'=>'/owner/entry/post?category=-4')
		);
	}
	if(Acl::check('group.administrators')) {
		$blogContentMenuItem['communication'] = array(
			array('menu'=>'comment','title'=>_t('댓글'),'link'=>'/owner/communication/comment?status=comment'),
			array('menu'=>'guestbook','title'=>_t('방명록'),'link'=>'/owner/communication/comment?status=guestbook'),
			array('menu'=>'notify','title'=>_t('댓글 알리미'),'link'=>'/owner/communication/notify'),
			array('menu'=>'trackbackreceived','title'=>_t('걸린 글'),'link'=>'/owner/communication/trackback?status=received'),
			array('menu'=>'trackbacksent','title'=>_t('건 글'),'link'=>'/owner/communication/trackback?status=sent'),
			array('menu'=>'divider','title'=> '-','link'=>'/'),
			array('menu'=>'openid','title'=>_t('오픈아이디 기록'),'link'=>'/owner/communication/openid'),
			array('menu'=>'divider','title'=> '-','link'=>'/'),
			array('menu'=>'trash','title'=>_t('휴지통'),'link'=>'/owner/communication/trash/comment'),
			array('menu'=>'filter','title'=>_t('스팸 필터'),'link'=>'/owner/communication/filter')
		);
	} else {
		$blogContentMenuItem['communication'] = array(
			array('menu'=>'comment','title'=>_t('소통 기록'),'link'=>'/owner/communication/comment'),
			array('menu'=>'divider','title'=> '-','link'=>'/'),
			array('menu'=>'trash','title'=>_t('휴지통'),'link'=>'/owner/communication/trash/comment')
		);
	}
	if(Acl::check('group.administrators')) {
		$blogContentMenuItem['network'] = array(
			array('menu'=>'teamblog','title'=>_t('필진 목록'),'link'=>'/owner/network/teamblog'),			
			array('menu'=>'link','title'=>_t('링크'),'link'=>'/owner/network/link')
		);
		if($service['reader'] == true) array_push($blogContentMenuItem['network'],array('menu'=>'reader','title'=>_t('바깥 글 읽기'),'link'=>'/owner/network/reader'));
	} else {
		$blogContentMenuItem['network'] = array();
		if($service['reader'] == true) array_push($blogContentMenuItem['network'],array('menu'=>'reader','title'=>_t('바깥 글 읽기'),'link'=>'/owner/network/reader'));
	}	
	if(Acl::check('group.administrators')) {
		$blogContentMenuItem['skin'] = array(
			array('menu'=>'skin','title'=>_t('스킨 선택'),'link'=>'/owner/skin'),
			array('menu'=>'adminSkin','title'=>_t('관리 패널 스킨 선택'),'link'=>'/owner/skin/adminSkin'),
			array('menu'=>'divider','title'=> '-','link'=>'/'),
			array('menu'=>'edit','title'=>_t('스킨 편집'),'link'=>'/owner/skin/edit'),
			array('menu'=>'setting','title'=>_t('스킨 상세 설정'),'link'=>'/owner/skin/setting'),
			array('menu'=>'divider','title'=> '-','link'=>'/'),
			array('menu'=>'sidebar','title'=>_t('사이드바 위젯'),'link'=>'/owner/skin/sidebar'),
			array('menu'=>'coverpage','title'=>_t('표지 위젯'),'link'=>'/owner/skin/coverpage')
		);
	}
	if(Acl::check('group.administrators')) {
		$blogContentMenuItem['plugin'] = array(
			array('menu'=>'plugin','title'=>_t('플러그인 목록'),'link'=>'/owner/plugin')
		);
		if(Acl::check('group.creators')) array_push($blogContentMenuItem['plugin'], array('menu'=>'tableSetting','title'=>_t('플러그인 데이터 관리'),'link'=>'/owner/plugin/tableSetting'));
	}
	if(Acl::check('group.administrators')) {
		$blogContentMenuItem['setting'] = array(
			array('menu'=>'blog','title'=>_t('블로그'),'link'=>'/owner/setting/blog'),
			array('menu'=>'entry','title'=>_t('글 작성'),'link'=>'/owner/setting/entry'),
			array('menu'=>'account','title'=>_t('개인 정보'),'link'=>'/owner/setting/account'),
			array('menu'=>'data','title'=>_t('데이터 관리'),'link'=>'/owner/data')
		);
	} else {
		$blogContentMenuItem['setting'] = array(
			array('menu'=>'account','title'=>_t('개인 정보'),'link'=>'/owner/setting/account')
		);
	}
	if(Acl::check('group.creators')) {
		$blogContentMenuItem['control'] = array(
			array('menu'=>'blog','title'=>_t('블로그'),'link'=>'/control/blog'),
			array('menu'=>'user','title'=>_t('사용자'),'link'=>'/control/user'),
			array('menu'=>'server','title'=>_t('서버'),'link'=>'/control/server'),
			array('menu'=>'system','title'=>_t('시스템 정보'),'link'=>'/control/system')
		);
	}
}

if( empty($blogContentMenuItem) ) {
	echo _t('접근권한이 없습니다');
	exit;
}

foreach($adminMenuMappings as $path => $pluginAdminMenuitem) {
	if(isset($blogContentMenuItem[$pluginAdminMenuitem['topMenu']])) { 
		if(count($blogContentMenuItem[$pluginAdminMenuitem['topMenu']]) < $pluginAdminMenuitem['contentMenuOrder'] 
		  || $pluginAdminMenuitem['contentMenuOrder'] < 1)
			$pluginAdminMenuitem['contentMenuOrder'] = count($blogContentMenuItem[$pluginAdminMenuitem['topMenu']]);
		array_splice($blogContentMenuItem[$pluginAdminMenuitem['topMenu']], $pluginAdminMenuitem['contentMenuOrder'], 0, 
			array(array('menu'=>'adminMenu?name='.$path,
			'title'=>$pluginAdminMenuitem['title'],
			'link'=>'/owner/plugin/adminMenu?name='.$path))
		);
	}
}

/** Adds 'about' panel at the last part of center panel. **/
$blogContentMenuItem['center'] = array_merge($blogContentMenuItem['center'] , array(array('menu'=>'about','title'=>_t('텍스트큐브는'),'link'=>'/owner/center/about')));

/***** Start header output *****/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo (isset($blog['language']) ? $blog['language'] : "ko");?>">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo htmlspecialchars($blog['title']);?> &gt; <?php echo $blogMenu['title'];?></title>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/basic.css" />
<?php
	$browser = Utils_Browser::getInstance();
	if($browser->isMobile()) {
?>
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
<?php
	}
	if($context->getProperty('blog.useBlogIconAsIphoneShortcut') == true && file_exists(ROOT."/attach/".$context->getProperty('blog.id')."/index.gif")) {
?>
	<link rel="apple-touch-icon" href="<?php echo $context->getProperty('uri.default')."/index.gif";?>" />
<?php
	}
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
if (array_key_exists('loadCSSIE6', $blogMenu)) {
	foreach($blogMenu['loadCSSIE6'] as $loadCSS) {
?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/<?php echo $loadCSS;?>.ie.css" />
<?php
	}
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
if (array_key_exists('loadCSSIE7', $blogMenu)) {
	foreach($blogMenu['loadCSSIE7'] as $loadCSS) {
?>
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$adminSkinSetting['skin'];?>/<?php echo $loadCSS;?>.ie7.css" />
<?php
	}
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
if (in_array($blogMenu['contentMenu'],array('post','edit'))) {
	if(file_exists(ROOT.$adminSkinSetting['editorTemplate'])) {
?>
			var editorCSS = "<?php echo $adminSkinSetting['editorTemplate'];?>";
<?php
	} else {
?>
			var editorCSS = "/resources/style/default-wysiwyg.css";
<?php
	}
}
include ROOT . '/resources/locale/messages.php';
?>
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/byTextcube.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/jquery/jquery-<?php echo JQUERY_VERSION;?>.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/EAF4.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/common2.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/owner.js"></script>
<?php
if(!in_array($blogMenu['contentMenu'],array('post','edit'))) {
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/mootools10.js"></script>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/moodalbox/moodalbox.js"></script>
	<link rel="stylesheet" href="<?php echo $service['path'];?>/resources/style/helper/moodalbox.css" type="text/css" media="screen" />
<?php
}
?>
<?php
if($service['interface'] == 'simple') {
	if(!in_array($blogMenu['contentMenu'],array('post','edit'))) {
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/mootools.js"></script>
<?php
	}
}
if( isset($service['admin_script']) ) {
	if( is_array($service['admin_script']) ) {
		foreach( $service['admin_script'] as $src ) {
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/<?php echo $src;?>"></script>
<?php
		}
	} else {
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/<?php echo $service['admin_script'];?>"></script>
<?php
	}
}
if($blogMenu['topMenu']=='entry' && in_array($blogMenu['contentMenu'],array('post','edit','keylog','template','notice'))) {
?>
	<script type="text/javascript" src="<?php echo $service['path'];?>/resources/script/editor3.js"></script>
<?php
}
echo fireEvent('ShowAdminHeader', '');

/** Get Help URL **/
$submenuURL = null;
if(strstr($blogMenu['contentMenu'], 'adminMenu?name=') !== false) { // Plugin.
	$submenuURL = $pluginMenuValue[0];
} else {
	$submenuURL = $blogMenu['contentMenu'];
}
$helpURL = $blogMenu['topMenu'].(isset($blogMenu['contentMenu']) ? '/'.$submenuURL : '');

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
$writer = POD::queryCell("SELECT name FROM {$database['prefix']}Users WHERE userid = ".getUserId());
?>
						<li id="description-blogger"><span class="text"><?php echo _f('환영합니다. <em>%1</em>님.', htmlspecialchars($writer));?></span></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="main-action-box">
					<ul id="main-action">
<?php if(Acl::check('group.creators')) { ?>
						<li id="action-move-to-control-panel"><a href="<?php echo $blogURL.'/control';?>" title="<?php echo _t('관리 패널로 이동합니다.');?>"><span class="text"><?php echo _t('텍스트큐브 관리');?></span></a></li>
<?php } ?>
						<li id="action-helper"><a href="<?php echo getHelpURL($helpURL);?>" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미');?></span></a></li>
						<li id="action-move-to-blog"><a href="<?php echo $blogURL;?>/" title="<?php echo _t('블로그 메인으로 이동합니다.');?>"><span class="text"><?php echo _t('블로그로 이동');?></span></a></li>
						<li id="action-logout"><a href="<?php echo $blogURL;?>/logout" title="<?php echo _t('로그아웃하고 블로그 메인으로 이동합니다.');?>"><span class="text"><?php echo _t('로그아웃');?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />

				<div id="main-blog-box">
					<div id="main-blog">
<?php
						if ('single' != $service['type'] ) {
?>
						<label for="blog-list"><?php echo _t('현재 블로그');?></label>
<?php echo User::changeBlog();
						}
?>
					</div>
				</div>

				<hr class="hidden" />
				
				<h2><?php echo _t('메인메뉴');?></h2>
				
				<div id="main-menu-box">
					<ul id="main-menu">
						<li id="menu-textcube"><a href="<?php echo $blogURL.'/owner/center/dashboard';?>" title="<?php echo _t('센터로 이동합니다.');?>"><span class="text"><?php echo _t('텍스트큐브');?></span></a></li>
<?php //echo User::changeBlog();?>						
<?php
foreach($blogTopMenuItem as $menuItem) {
?>
						<li id="menu-<?php echo $menuItem['menu'];?>"<?php echo $menuItem['menu']==$blogMenu['topMenu'] ? ' class="selected"' : '';?>>
							<a href="<?php echo $blogURL.$menuItem['link'];?>" class="menu-name"><span><?php echo $menuItem['title'];?></span><!--[if gte IE 7]><!--></a><!--<![endif]-->
							<!--[if lte IE 6]><table><tr><td><![endif]-->							
							<ul id="submenu-<?php echo $menuItem['menu'];?>" class="sub-menu">
<?php
	$firstChildClass = ' firstChild';
	if(isset($_POST['category']) && isset($_GET['category'])) {
		$_POST['category'] == $_GET['category'];
	}
/*	if (isset($_POST['category'])) $currentCategory = $_POST['category'];
	else if (isset($_GET['category'])) $currentCategory = $_GET['category'];
	else $currentCategory = null;*/
	if($blogMenu['contentMenu'] == 'post' && isset($_GET['category'])) {
		switch($_GET['category']) {
			case -1:
				$blogMenu['contentMenu'] = 'keylog';
				break;
			case -2:
				$blogMenu['contentMenu'] = 'notice';
				break;
			case -4:
				$blogMenu['contentMenu'] = 'template';
				break;
			default:
		}
	}
	$currentCategory = null;
	if(isset($_POST['status'])) {
		if(($blogMenu['contentMenu'] == 'comment') && ($_POST['status'] == 'guestbook'))
			$blogMenu['contentMenu'] = 'guestbook';
		else if($blogMenu['contentMenu'] == 'trackback') 
			$blogMenu['contentMenu'] = $blogMenu['contentMenu'].$_POST['status'];
	} else if(in_array($blogMenu['contentMenu'],array('trashcomment','trashtrackback'))) {
		$blogMenu['contentMenu'] = 'trash';
	} else if(in_array($blogMenu['contentMenu'],array('linkadd','linkedit','linkcategoryEdit','xfn'))) {
		$blogMenu['contentMenu'] = 'link';
	}
//	else if(in_array($blogMenu['contentMenu'],array('coverpage','sidebar')))
//		$blogMenu['contentMenu'] = 'widget';
	foreach($blogContentMenuItem[$menuItem['menu']] as &$contentMenuItem) {
		$PostIdStr = null;
		if(strstr($contentMenuItem['menu'], 'adminMenu?name=') !== false) {
			$pluginMenuValue = explode('/',substr($contentMenuItem['menu'], 15));
			$PostIdStr = $pluginMenuValue[0];
		} else {
			$PostIdStr = $contentMenuItem['menu'];
		}
		if($contentMenuItem['menu'] == 'divider') {
?>
								<li class="divider"><?php
		} else {
?>
								<li id="sub-menu-<?php echo $PostIdStr;?>"<?php echo 
	((( $menuItem['menu'] == $blogMenu['topMenu'] && $blogMenu['contentMenu'] == $contentMenuItem['menu'])|| 
	(isset($_GET['name']) && ('adminMenu?name='.$_GET['name'] == $contentMenuItem['menu'])) ||
	($contentMenuItem['menu'] == 'add' && strpos($blogMenu['contentMenu'],'add') !== false) ||
	($contentMenuItem['menu'] == 'blog' && strpos($blogMenu['contentMenu'],'blog') !== false && strpos($blogMenu['contentMenu'],'teamblog') === false) ||
	($contentMenuItem['menu'] == 'user' && strpos($blogMenu['contentMenu'],'user') !== false) ||
	($blogMenu['contentMenu'] == 'edit' && $contentMenuItem['menu'] == 'post')) ? 
		" class=\"selected{$firstChildClass}\"" : ($firstChildClass ? " class=\"$firstChildClass\"" : ''));?>><?php
		}
		if($contentMenuItem['menu'] != 'divider') {
?><a href="<?php 
						echo $blogURL.
							$contentMenuItem['link'].
							($contentMenuItem['menu'] == 'post' && isset($currentCategory) ? '?category='.$currentCategory : '');
						?>"><span class="text"><?php echo $contentMenuItem['title'];?></span></a><?php
		} else {
?><span class="divider"><?php echo $contentMenuItem['title'];?></span><?php
		}
?></li>
<?php
		$firstChildClass = null;
	}
?>
							</ul>
							<!--[if lte IE 6]></td></tr></table></a><![endif]-->
						</li>
<?php
}
?>
					</ul>
				</div>
			</div>
			
			<hr class="hidden" />
<?php
/********** Submenu part. ***********/

if(!defined('__TEXTCUBE_READER_SUBMENU__')) {
?>
			<div id="layout-body">
<?php
}
?>
				<h2><?php echo isset($blogMenu['title']) ? _f('서브메뉴 : %1', $blogMenu['title']) : _t('서브메뉴');?></h2>

				<div id="sub-menu-box">
					<ul id="sub-menu">
<?php
	$firstChildClass = ' firstChild';
	$submenuURL = null;

	foreach($blogContentMenuItem[$blogMenu['topMenu']] as &$contentMenuItem) {
		$PostIdStr = null;
		if(strstr($contentMenuItem['menu'], 'adminMenu?name=') !== false) {
			$pluginMenuValue = explode('/',substr($contentMenuItem['menu'], 15));
			$PostIdStr = $pluginMenuValue[0];
			if(($blogMenu['contentMenu'] == $contentMenuItem['menu'] || (isset($_GET['name']) && ('adminMenu?name='.$_GET['name'] == $contentMenuItem['menu'])) || ($contentMenuItem['menu'] == 'trash' && strpos($blogMenu['contentMenu'],'trash') !== false))) {
				$submenuURL = $pluginMenuValue[0];
			}
		} else {
			$PostIdStr = $contentMenuItem['menu'];
			if(($blogMenu['contentMenu'] == $contentMenuItem['menu'] 
				|| (isset($_GET['name']) && ('adminMenu?name='.$_GET['name'] == $contentMenuItem['menu'])) 
				|| (in_array($contentMenuItem['menu'],array('blog','user')) && strpos($blogMenu['contentMenu'],'detail') !== false)
				)) {
				$submenuURL = $blogMenu['contentMenu'];
			}
		}
		if($contentMenuItem['menu'] == 'divider') {
?>
						<li class="divider"><span class="divider"><?php echo $contentMenuItem['title'];?></span><?php
		} else {
?>
						<li id="sub-menu-<?php echo $PostIdStr;?>"<?php echo 
						(($blogMenu['contentMenu'] == $contentMenuItem['menu'] || 
							(isset($_GET['name']) && ('adminMenu?name='.$_GET['name'] == $contentMenuItem['menu'])) ||
							($contentMenuItem['menu'] == 'add' && strpos($blogMenu['contentMenu'],'add') !== false) ||
							($contentMenuItem['menu'] == 'blog' && strpos($blogMenu['contentMenu'],'blog') !== false && strpos($blogMenu['contentMenu'],'teamblog') === false) ||
							($contentMenuItem['menu'] == 'user' && strpos($blogMenu['contentMenu'],'user') !== false) ||
							($blogMenu['contentMenu'] == 'edit' && $contentMenuItem['menu'] == 'post')) ? " class=\"selected{$firstChildClass}\"" : ($firstChildClass ? " class=\"$firstChildClass\"" : ''));?>><?php
			if($contentMenuItem['menu'] == 'divider') {?><span class="divider"><?php echo $contentMenuItem['title'];?></span><?php
				} else {?><a href="<?php 
						echo $blogURL.
							$contentMenuItem['link'].
							($contentMenuItem['menu'] == 'post' && isset($currentCategory) ? '?category='.$currentCategory : '');
						?>"><span class="text"><?php echo $contentMenuItem['title'];?></span></a><?php
				}
		}?></li>
<?php
		$firstChildClass = null;
	}
	
	$helpURL = $blogMenu['topMenu'].(isset($blogMenu['contentMenu']) ? '/'.$submenuURL : '');
?>
					</ul>
					<div id="custom-sub-menu">
					</div>
				</div>
<?php
if(!defined('__TEXTCUBE_READER_SUBMENU__')) {
?>
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
<?php
}
?>
