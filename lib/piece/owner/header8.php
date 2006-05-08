<?php 
$logoPath = $service['path'] . '/image/owner/controlPanelLogo.gif';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo htmlspecialchars($blog['title'])?> &gt; <?php echo _t('글관리')?></title>
<script type="text/javascript">
var servicePath = "<?php echo $service['path']?>"; var blogURL = "<?php echo $blogURL?>";
</script>
<script type="text/javascript" src="<?php echo $service['path']?>/script/byTattertools.js" ></script>
<script type="text/javascript" src="<?php echo $service['path']?>/script/EAF.js"></script>
<script type="text/javascript" src="<?php echo $service['path']?>/script/common.js" ></script>
<script type="text/javascript" src="<?php echo $service['path']?>/script/gallery.js" ></script>
<script type="text/javascript" src="<?php echo $service['path']?>/script/owner.js" ></script>

<link rel="stylesheet" type="text/css" href="<?php echo $service['path']?>/style/owner.css" />
</head>
<body<?php echo (empty($htmlBodyEvents) ? '' : $htmlBodyEvents)?>>
<table cellspacing="0" width="100%" style="height:450px;background-image:url('<?php echo $service['path']?>/image/owner/bg.gif'); background-repeat:repeat-x">
	<tr>
		<td valign="top" style="padding:10px 25px 30px 20px">
