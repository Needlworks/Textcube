<?php 
$logoPath = $service['path'] . '/image/owner/controlPanelLogo.gif';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo htmlspecialchars($blog['title'])?> &gt; <?php echo _t('링크관리')?></title>
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
<body>

<table width="100%" border="0" cellpadding="0" cellspacing="0" style="height:45px">
  <tr>
    <td width="170" align="right" valign="bottom" style="padding-right:25px;"><img src="<?php echo $logoPath?>" alt="" vspace="3" class="pointerCursor" onclick="window.location.href = '<?php echo $blogURL?>/owner/entry'" /></td>
    <td valign="bottom">
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td style="background-image:url('<?php echo $service['path']?>/image/owner/menuP2.gif')">
            <table cellspacing="0">
              <tr>
                <td style="width:7px; padding-right:10px"><img src="<?php echo $service['path']?>/image/owner/menuP1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?php echo $blogURL?>/owner/entry'"><?php echo _t('글관리')?></td>
<!--
                <td><img src="<?php echo $service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?php echo $blogURL?>/owner/keyword'"><?php echo _t('키워드관리')?></td>
-->
                <td><img src="<?php echo $service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?php echo $blogURL?>/owner/notice'"><?php echo _t('공지관리')?></td>				
                <td><img src="<?php echo $service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td>
                  <table cellspacing="0" class="menuItem1" onclick="window.location.href = '<?php echo $blogURL?>/owner/link'">
                    <tr>
                      <td><img src="<?php echo $service['path']?>/image/owner/menuP3.gif" alt="" /></td>
                      <td class="menuItem2" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'"><?php echo _t('링크관리')?></td>
                      <td><img src="<?php echo $service['path']?>/image/owner/menuP5.gif" alt="" /></td>
                    </tr>
                  </table>
                </td>
                <td><img src="<?php echo $service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?php echo $blogURL?>/owner/skin'"><?php echo _t('스킨관리')?></td>
                <td><img src="<?php echo $service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?php echo $blogURL?>/owner/statistics/visitor'"><?php echo _t('통계보기')?></td>
                <td><img src="<?php echo $service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?php echo $blogURL?>/owner/setting/blog'"><?php echo _t('환경설정')?></td>
                <td><img src="<?php echo $service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?php echo $blogURL?>/owner/reader'"><?php echo _t('리더')?></td>
              </tr>
            </table>
          </td>
          <td align="right" style="background-image:url('<?php echo $service['path']?>/image/owner/menuP2.gif')">
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td width="150" align="center" style="background-image:url('<?php echo $service['path']?>/image/owner/menuBg.gif'); padding-top:8px"><img class="pointerCursor" onclick="window.location.href = '<?php echo $blogURL?>/'" src="<?php echo $service['path']?>/image/owner/blog.gif" alt="" /><img src="<?php echo $service['path']?>/image/owner/menuLine2.gif" alt="" /><img class="pointerCursor" src="<?php echo $service['path']?>/image/owner/logout.gif" onclick="window.location.href = '<?php echo $blogURL?>/logout'" alt="" /> </td>
                <td width="10"></td>
                <td width="6"><img src="<?php echo $service['path']?>/image/owner/menuBg2.gif" alt="" /></td>
              </tr>
            </table>
          </td>
          <td width="10"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table cellspacing="0" width="100%" style="height:450px;background-image:url('<?php echo $service['path']?>/image/owner/bg.gif'); background-repeat:repeat-x">
	<tr>
		<td valign="top" style="padding:10px 25px 30px 20px">
