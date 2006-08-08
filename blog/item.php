<?php
define('ROOT', '..');
if (!empty($_POST['mode']) && $_POST['mode'] == 'fb') {
	$IV = array(
		'POST' => array(
			'mode' => array(array('fb')),
			's_home_title' => array('string'),
			's_home' => array('string'),
			's_no' => array('id'),
			'url' => array('string'),
			's_url' => array('string'),
			's_post_title' => array('string'),
			'r1_no' => array('id'),
			'r1_name' => array('string'),
			'r1_rno' => array('string'),
			'r1_homepage' => array('string'),
			'r1_regdate' => array('timestamp'),
			'r1_body' => array('string'),
			'r1_url' => array('string'),
			'r2_no' => array('id'),
			'r2_name' => array('string'),
			'r2_rno' => array('id'),
			'r2_homepage' => array('string'),
			'r2_regdate' => array('timestamp'),
			'r2_body' => array('string'),
			'r2_url' => array('string')
		)
	);
} else {
	$IV = array();
}
require ROOT . '/lib/include.php';
if (false) {
	fetchConfigVal();
}
publishEntries();
if (!empty($_POST['mode']) && $_POST['mode'] == 'fb') {
	$result = receiveNotifiedComment($_POST);
	if ($result > 0)
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><response><error>1</error><message>error($result)</message></response>";
	else
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><response><error>0</error></response>";
} else {
	notifyComment();
}
list($entries, $paging) = getEntryWithPaging($owner, $suri['id']);
if (isset($_POST['partial'])) {
	header('Content-Type: text/plain; charset=utf-8');
	$skin = new Skin($skinSetting['skin']);
	$view = '[##_article_rep_##]';
	require ROOT . '/lib/piece/blog/entries.php';
	$view = removeAllTags($view);
	if ($view != '[##_article_rep_##]')
		print $view;
} else {
	require ROOT . '/lib/piece/blog/begin.php';
	if (empty($entries)) {
		header('HTTP/1.1 404 Not Found');
		if (empty($skin->pageError)) {
			dress('article_rep', '<div style="text-align:center;font-size:14px;font-weight:bold;padding-top:50px;margin:50px 0;color:#333;background:url(' . $service['path'] . '/image/warning.gif) no-repeat top center;">' . _text('존재하지 않는 페이지입니다.') . '</div>', $view);
		} else {
			dress('article_rep', NULL, $view);
			dress('page_error', $skin->pageError, $view);
		}
	} else {
		require ROOT . '/lib/piece/blog/entries.php';
	}
	require ROOT . '/lib/piece/blog/end.php';
}
?>
