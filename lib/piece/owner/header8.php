<?
$logoPath = $service['path'] . '/image/owner/controlPanelLogo.gif';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=htmlspecialchars($blog['title'])?> &gt; <?=_t('글관리')?></title>
<script type="text/javascript">
var servicePath = "<?=$service['path']?>"; var blogURL = "<?=$blogURL?>";
</script>
<script type="text/javascript" src="<?=$service['path']?>/script/byTattertools.js" ></script>
<script type="text/javascript" src="<?=$service['path']?>/script/EAF.js"></script>
<script type="text/javascript" src="<?=$service['path']?>/script/common.js" ></script>
<script type="text/javascript" src="<?=$service['path']?>/script/gallery.js" ></script>
<script type="text/javascript" src="<?=$service['path']?>/script/owner.js" ></script>

<link rel="stylesheet" type="text/css" href="<?=$service['path']?>/style/owner.css" />
</head>
<body<?=(empty($htmlBodyEvents) ? '' : $htmlBodyEvents)?>>
<table cellspacing="0" width="100%" style="height:450px;background-image:url('<?=$service['path']?>/image/owner/bg.gif'); background-repeat:repeat-x">
	<tr>
		<td valign="top" style="padding:10px 25px 30px 20px">
