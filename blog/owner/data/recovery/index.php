<?
define('ROOT', '../../../..');
require ROOT . '/lib/include.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=_t('데이터를 점검합니다')?>...</title>
<script type="text/javascript">
//<![CDATA[
//]]>
</script>
<style type="text/css" media="screen">
	body{
		font:12px/1.5 Verdana, Gulim;
		color:#333;
	}
	h3 {
		color:#0099FF;
		padding-bottom:5px;
	}
</style>
</head>
<body>
<h3><?=_t('데이터를 점검합니다')?>...</h3>
<p>
<ul>
<?
$changed = false;
echo '<li>', _t('글의 댓글 정보를 다시 계산해서 저장합니다'), ': ';
requireComponent('Tattertools.Data.Post');
if (Post::updateComments())
	echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
else
	echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
echo '<li>', _t('분류의 글 정보를 다시 계산해서 저장합니다'), ': ';
if (updateEntriesOfCategory($owner))
	echo '<span style="color:#33CC33;">', _t('성공'), '</span></li>';
else
	echo '<span style="color:#FF0066;">', _t('실패'), '</span></li>';
?>
</ul>
<?=($changed ? _t('완료되었습니다.') : _t('확인되었습니다.'))?>
</p>
</body>
</html>
