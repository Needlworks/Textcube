<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];
if (!empty($_POST['mode']) && $_POST['mode'] == 'fb') {
	$IV = array(
		'GET' => array(
			'page' => array('int', 1, 'default' => 1),
			'category' => array('int', 0, 'mandatory'=>false)
		),
		'POST' => array(
			'mode' => array(array('fb')),
			'partial' => array('bool','default' => false),
			's_home_title' => array('string', 'default'=>''),
			's_name' => array('string' , 'default'=>''),
			's_no' => array('int'),
			'url' => array('string', 'default'=>''),
			's_url' => array('string', 'default'=>''),
			's_post_title' => array('string', 'default'=>''),
			'r1_no' => array('int'),
			'r1_name' => array('string', 'default'=>''),
			'r1_rno' => array('int'),
			'r1_homepage' => array('string', 'default'=>''),
			'r1_regdate' => array('timestamp'),
			'r1_body' => array('string'),
			'r1_url' => array('string', 'default'=>''),
			'r2_no' => array('int'),
			'r2_name' => array('string', 'default'=>''),
			'r2_rno' => array('int'),
			'r2_homepage' => array('string', 'default'=>''),
			'r2_regdate' => array('timestamp'),
			'r2_body' => array('string'),
			'r2_url' => array('string', 'default'=>'')
		)
	);
} else {
	$IV = array(
		'GET' => array(
			'page' => array('int', 1, 'default' => 1),
			'category' => array('int', 0, 'mandatory'=>false)
		)
	);
}

require ROOT . '/library/preprocessor.php';

// Redirect for ipod touch / iPhone
if(Setting::getBlogSettingGlobal('useiPhoneUI',true) && (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'],'iPod') || strpos($_SERVER['HTTP_USER_AGENT'],'iPhone')))){
	if(isset($suri['id'])) {
		$slogan = getSloganById($blogid, $suri['id']);
		if(!empty($slogan)) {
			header("Location: $blogURL/i/entry/".$slogan); exit;
		}
	} else {
		header("Location: $blogURL/i"); exit;
	}
}
publishEntries();

if (!empty($_POST['mode']) && $_POST['mode'] == 'fb') { // Treat comment notifier.
	$result = receiveNotifiedComment($_POST);
	if ($result > 0)
		echo '<?xml version="1.0" encoding="utf-8"?><response><error>1</error><message>error('.$result.')</message></response>';
	else
		echo '<?xml version="1.0" encoding="utf-8"?><response><error>0</error></response>';
} else {
	notifyComment();
}

fireEvent('OBStart');

if(empty($suri['id'])) {  // Without id.
	$skin = new Skin($skinSetting['skin']);
	$frontpage = Setting::getBlogSettingGlobal('frontpage','entry');
	if (empty($suri['value']) && $suri["directive"] == "/" && ($frontpage != 'entry')) {
		if($frontpage == 'cover' && isset($skin->cover)	&& count($coverpageMappings) > 0) {		
			define('__TEXTCUBE_COVER__',true);
			require ROOT . '/interface/common/blog/begin.php';
			dress('article_rep', '', $view);
			dress('paging', '', $view);
			require ROOT . '/interface/common/blog/cover.php';
		} else if ($frontpage == 'line' && isset($skin->line)) {
			define('__TEXTCUBE_LINE__',true);
			$lineobj = Model_Line::getInstance();
			$lineobj->reset();
			$lineobj->setFilter(array('category','equals','public',true));
			$lineobj->setLimit(20);
			$lineobj->setOrder('created','desc');
			$lines = $lineobj->get();
			require ROOT . '/interface/common/blog/begin.php';
			require ROOT . '/interface/common/blog/line.php';
		}
	} else {
		list($entries, $paging) = getEntriesWithPaging($blogid, $suri['page'], $blog['entriesOnPage']);
		require ROOT . '/interface/common/blog/begin.php';
		require ROOT . '/interface/common/blog/entries.php';
	}
	
	require ROOT . '/interface/common/blog/end.php';
} else {  // With id.
	if(isset($_GET['category'])) { // category exists
		if(Validator::isInteger($_GET['category'], 0)) {
			list($entries, $paging) = getEntryWithPaging($blogid, $suri['id'],false,$_GET['category']);
		}
	} else { // Just normal entry view
		list($entries, $paging) = getEntryWithPaging($blogid, $suri['id']);
	}
	
	if (isset($_POST['partial'])) { // Partial output.
		header('Content-Type: text/plain; charset=utf-8');
		$skin = new Skin($skinSetting['skin']);
		$view = '[##_article_rep_##]';
		require ROOT . '/interface/common/blog/entries.php';
		$view = removeAllTags($view);
		if ($view != '[##_article_rep_##]')
			print $view;
	} else {
		require ROOT . '/interface/common/blog/begin.php';
		if (empty($entries)) {
			header('HTTP/1.1 404 Not Found');
			if (empty($skin->pageError)) { 
				dress('article_rep', '<div class="TCwarning">' . _text('존재하지 않는 페이지입니다.') . '</div>', $view);
			} else{
				dress('article_rep', NULL, $view); 
				dress('page_error', $skin->pageError, $view);
			}
			unset($paging);
		} else {
			require ROOT . '/interface/common/blog/entries.php';
		}
		require ROOT . '/interface/common/blog/end.php';
	}
}
fireEvent('OBEnd');
?>