<?
define('ROOT', '..');
require ROOT . '/lib/include.php';
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