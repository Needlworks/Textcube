<?
function TrackbackTracker_AddingTrackback($target, $mother) {
	if (!$url = parse_url($mother['url']))
		return false;
	if (empty($url['host']))
		return false;
	$exceptions = array(
		'.blogspot.com',
		'.egloos.com',
		'.ohmyblog.com',
		'.onblog.com',
		'.typepad.com',
		'blog.daum.net',
		'blog.empas.com',
		'blog.kr.yahoo.com',
		'blog.naver.com',
		'blog.paran.com',
		'blog.yes24.com',
	);
	if (preg_match('/(' . str_replace(',', '|', preg_quote(implode(',', $exceptions))) . ')$/', $url['host']))
		return true;
	if ($_SERVER['REMOTE_ADDR'] != gethostbyname($url['host']))
		return false;
	return true;
}
?>