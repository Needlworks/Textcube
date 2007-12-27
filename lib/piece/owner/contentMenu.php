<?php
/// Copyright (c) 2004-2007, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

if(isset($blogMenu['topMenu'])) {
	switch($blogMenu['topMenu']) {
		case 'center':
			if(Acl::check('group.administrators')) {
				$blogContentMenuItem = array(
					array('menu'=>'dashboard','title'=>_t('조각보'),'link'=>'/owner/center/dashboard'),
					array('menu'=>'coverpage','title'=>_t('색동'),'link'=>'/owner/center/coverpage'),
				);
			}
			else{
				$blogContentMenuItem = array(
					array('menu'=>'dashboard','title'=>_t('조각보'),'link'=>'/owner/center/dashboard'),
				);
			}
		break;
		case 'entry':
			if(Acl::check('group.editors')) {
				$blogContentMenuItem = array(
					array('menu'=>'post','title'=>_t('글쓰기'),'link'=>'/owner/entry/post'),
					array('menu'=>'entry','title'=>_t('글 목록'),'link'=>'/owner/entry'),
					array('menu'=>'comment','title'=>_t('댓글'),'link'=>'/owner/entry/comment'),
					array('menu'=>'notify','title'=>_t('댓글 알리미'),'link'=>'/owner/entry/notify'),
					array('menu'=>'trackback','title'=>_t('걸린글'),'link'=>'/owner/entry/trackback'),
					array('menu'=>'category','title'=>_t('분류 관리'),'link'=>'/owner/entry/category'),
					array('menu'=>'trash','title'=>_t('휴지통'),'link'=>'/owner/entry/trash')
				);
			} else {
				$blogContentMenuItem = array(
					array('menu'=>'post','title'=>_t('글쓰기'),'link'=>'/owner/entry/post'),
					array('menu'=>'entry','title'=>_t('글 목록'),'link'=>'/owner/entry'),
				);
			}
		break;
		case 'link':
		if(Acl::check('group.administrators')) {
			$blogContentMenuItem = array(
				array('menu'=>'add','title'=>_t('링크 추가'),'link'=>'/owner/link/add'),
				array('menu'=>'link','title'=>_t('링크 목록'),'link'=>'/owner/link'),
				array('menu'=>'xfn','title'=>_t('친구 링크 관리'),'link'=>'/owner/link/xfn')
			);
		}
		break;
		case 'skin':
		if(Acl::check('group.administrators')) {
			$blogContentMenuItem = array(
				array('menu'=>'skin','title'=>_t('스킨 선택'),'link'=>'/owner/skin'),
				array('menu'=>'edit','title'=>_t('스킨 편집'),'link'=>'/owner/skin/edit'),
				array('menu'=>'setting','title'=>_t('출력 설정'),'link'=>'/owner/skin/setting'),
				array('menu'=>'sidebar','title'=>_t('사이드바'),'link'=>'/owner/skin/sidebar')
			);
		}
		break;
		case 'plugin':
		if(Acl::check('group.administrators')) {
			$blogContentMenuItem = array(
				array('menu'=>'plugin','title'=>_t('플러그인 목록'),'link'=>'/owner/plugin'),
				array('menu'=>'tableSetting','title'=>_t('플러그인 데이터 관리'),'link'=>'/owner/plugin/tableSetting')
			);
		}
		break;
		case 'setting':
		case 'data':
		if(Acl::check('group.administrators')) {
			$blogContentMenuItem = array(
				array('menu'=>'blog','title'=>_t('블로그'),'link'=>'/owner/setting/blog'),
				array('menu'=>'entry','title'=>_t('글 작성'),'link'=>'/owner/setting/entry'),
				array('menu'=>'account','title'=>_t('계정 정보'),'link'=>'/owner/setting/account'),
				array('menu'=>'teamblog','title'=>_t('팀블로그'),'link'=>'/owner/setting/teamblog'),
				array('menu'=>'filter','title'=>_t('스팸 필터'),'link'=>'/owner/setting/filter'),
				array('menu'=>'data','title'=>_t('데이터 관리'),'link'=>'/owner/data')
			);
		} else if(Acl::check('group.editors')) {
			$blogContentMenuItem = array(
				array('menu'=>'blog','title'=>_t('블로그'),'link'=>'/owner/setting/blog'),
				array('menu'=>'entry','title'=>_t('글 작성'),'link'=>'/owner/setting/entry'),
				array('menu'=>'account','title'=>_t('계정 정보'),'link'=>'/owner/setting/account'),
				array('menu'=>'teamblog','title'=>_t('팀블로그'),'link'=>'/owner/setting/teamblog'),
				array('menu'=>'filter','title'=>_t('스팸 필터'),'link'=>'/owner/setting/filter'),
			);
		} else {
			$blogContentMenuItem = array(
				array('menu'=>'account','title'=>_t('계정 정보'),'link'=>'/owner/setting/account'),
			);
		}
		break;
		case 'reader':
		break;
		case 'control':
		if(Acl::check('group.creators')) {
			$blogContentMenuItem = array(
				array('menu'=>'blog','title'=>_t('블로그'),'link'=>'/owner/control/blog'),
				array('menu'=>'user','title'=>_t('사용자'),'link'=>'/owner/control/user'),
				array('menu'=>'etc','title'=>_t('기타 설정'),'link'=>'/owner/control/etc')
			);
		}
		break;
	}

	if( empty($blogContentMenuItem) ) {
		echo _t('접근권한이 없습니다');
		exit;
	}

	foreach($adminMenuMappings as $path => $pluginAdminMenuitem) {
		if($pluginAdminMenuitem['topMenu'] == $blogMenu['topMenu']) {
			if(count($blogContentMenuItem) < $pluginAdminMenuitem['contentMenuOrder'] 
			  || $pluginAdminMenuitem['contentMenuOrder'] < 1)
				$pluginAdminMenuitem['contentMenuOrder'] = count($blogContentMenuItem);
			array_splice($blogContentMenuItem, $pluginAdminMenuitem['contentMenuOrder'], 0, 
				array(array('menu'=>'adminMenu?name='.$path,
				'title'=>$pluginAdminMenuitem['title'],
				'link'=>'/owner/plugin/adminMenu?name='.$path))
			);
		}
	}

	if($blogMenu['topMenu'] == 'center') {  // exception handling for 'about.' about menu will hold the end of center.
		$blogContentMenuItem = array_merge($blogContentMenuItem, array(array('menu'=>'about','title'=>_t('텍스트큐브는'),'link'=>'/owner/center/about')));
	}
}
?>
			<div id="layout-body">
				<h2><?php echo isset($blogMenu['title']) ? _f('서브메뉴 : %1', $blogMenu['title']) : _t('서브메뉴');?></h2>

<?php
if(isset($blogContentMenuItem)) {
?>	
				<div id="sub-menu-box">
					<ul id="sub-menu">
<?php
	$firstChildClass = ' firstChild';
	$submenuURL = null;
	if (isset($_POST['category'])) $currentCategory = $_POST['category'];
	else if (isset($_GET['category'])) $currentCategory = $_GET['category'];
	else $currentCategory = null;
	foreach($blogContentMenuItem as $contentMenuItem) { 
		$PostIdStr = null;
		
		if(strstr($contentMenuItem['menu'], 'adminMenu?name=') !== false) {
			$pluginMenuValue = explode('/',substr($contentMenuItem['menu'], 15));
			$PostIdStr = $pluginMenuValue[0];
			if(($blogMenu['contentMenu'] == $contentMenuItem['menu'] || (isset($_GET['name']) && ('adminMenu?name='.$_GET['name'] == $contentMenuItem['menu'])) || ($contentMenuItem['menu'] == 'trash' && strpos($blogMenu['contentMenu'],'trash') !== false))) {
				$submenuURL = $pluginMenuValue[0];
			}
		} else {
			$PostIdStr = $contentMenuItem['menu'];
			if(($blogMenu['contentMenu'] == $contentMenuItem['menu'] || (isset($_GET['name']) && ('adminMenu?name='.$_GET['name'] == $contentMenuItem['menu'])) || ($contentMenuItem['menu'] == 'trash' && strpos($blogMenu['contentMenu'],'trash') !== false))) {
				$submenuURL = $blogMenu['contentMenu'];
			}
		}
?>
						<li id="sub-menu-<?php echo $PostIdStr;?>"<?php echo 
						(($blogMenu['contentMenu'] == $contentMenuItem['menu'] || 
							(isset($_GET['name']) && ('adminMenu?name='.$_GET['name'] == $contentMenuItem['menu'])) ||
							($contentMenuItem['menu'] == 'trash' && strpos($blogMenu['contentMenu'],'trash') !== false) ||
							($blogMenu['contentMenu'] == 'edit' && $contentMenuItem['menu'] == 'post')) ? " class=\"selected{$firstChildClass}\"" : '');?>><a href="<?php 
						echo $blogURL.
							$contentMenuItem['link'].
							($service['useRewriteEngine'] ? '' : '/index.php').
							($contentMenuItem['menu'] == 'post' && isset($currentCategory) ? '?category='.$currentCategory : '');
						?>"><span class="text"><?php echo $contentMenuItem['title'];?></span></a></li>
<?php
		$firstChildClass = null;
	}
	
	$helpURL = $blogMenu['topMenu'].(isset($blogMenu['contentMenu']) ? '/'.$submenuURL : '');
?>
						<li id="sub-menu-helper"><a href="<?php echo getHelpURL($helpURL);?>" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미');?></span></a></li>
					</ul>
				</div>
<?php
}
?>
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
