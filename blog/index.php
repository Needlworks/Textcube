<?php
define('ROOT', '..');
if (isset($_POST['page']))
	$_GET['page'] = $_POST['page'];
if (!empty($_POST['mode']) && $_POST['mode'] == 'fb') {
	$IV = array(
		'GET' => array(
			'page' => array('int', 1, 'default' => 1)
		),
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
	$IV = array(
		'GET' => array(
			'page' => array('int', 1, 'default' => 1)
		)
	);
}
require ROOT . '/lib/include.php';
if (!empty($_POST['mode']) && $_POST['mode'] == 'fb') {
	$result = receiveNotifiedComment($_POST);
	if ($result > 0)
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><response><error>1</error><message>error($result)</message></response>";
	else
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?><response><error>0</error></response>";
	exit;
} else {
	$IV = array(    'POST' => array() );
	if(!Validator::validate($IV))
		respondNotFoundPage();
	notifyComment();
}
publishEntries();
list($entries, $paging) = getEntriesWithPaging($owner, $suri['page'], $blog['entriesOnPage']);
require ROOT . '/lib/piece/blog/begin.php';
require ROOT . '/lib/piece/blog/entries.php';
require ROOT . '/lib/piece/blog/end.php';
?>
