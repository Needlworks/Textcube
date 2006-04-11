<?
$logoPath = $service['path'] . '/image/owner/controlPanelLogo.gif';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=htmlspecialchars($blog['title'])?> &gt; <?=_t('통계보기')?></title>
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
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="height:45px">
  <tr>
    <td width="170" align="right" valign="bottom" style="padding-right:25px;"><img src="<?=$logoPath?>" alt="" vspace="3" class="pointerCursor" onclick="window.location.href = '<?=$blogURL?>/owner/entry'" /></td>
    <td valign="bottom">
      <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td style="background-image:url('<?=$service['path']?>/image/owner/menuP2.gif')">
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td style="width:7px; padding-right:10px"><img src="<?=$service['path']?>/image/owner/menuP1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?=$blogURL?>/owner/entry'"><?=_t('글관리')?></td>
<!--
                <td><img src="<?=$service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?=$blogURL?>/owner/keyword'"><?=_t('키워드관리')?></td>
-->
                <td><img src="<?=$service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?=$blogURL?>/owner/notice'"><?=_t('공지관리')?></td>				
                <td><img src="<?=$service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?=$blogURL?>/owner/link'"><?=_t('링크관리')?></td>
                <td><img src="<?=$service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?=$blogURL?>/owner/skin'"><?=_t('스킨관리')?></td>
                <td><img src="<?=$service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td>
                  <table cellspacing="0" class="menuItem1" onclick="window.location.href = '<?=$blogURL?>/owner/statistics/visitor'">
                    <tr>
                      <td><img src="<?=$service['path']?>/image/owner/menuP3.gif" alt="" /></td>
                      <td class="menuItem2" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'"><?=_t('통계보기')?></td>
                      <td><img src="<?=$service['path']?>/image/owner/menuP5.gif" alt="" /></td>
                    </tr>
                  </table>
                </td>
                <td><img src="<?=$service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?=$blogURL?>/owner/setting/blog'"><?=_t('환경설정')?></td>
                <td><img src="<?=$service['path']?>/image/owner/menuLine1.gif" alt="" /></td>
                <td class="menuItem3" nowrap="nowrap" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'" onclick="window.location.href = '<?=$blogURL?>/owner/reader'"><?=_t('리더')?></td>
              </tr>
            </table>
          </td>
          <td align="right" style="background-image:url('<?=$service['path']?>/image/owner/menuP2.gif')">
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td width="150" align="center" style="background-image:url('<?=$service['path']?>/image/owner/menuBg.gif'); padding-top:8px"><img class="pointerCursor" onclick="window.location.href = '<?=$blogURL?>/'" src="<?=$service['path']?>/image/owner/blog.gif" alt="" /><img src="<?=$service['path']?>/image/owner/menuLine2.gif" alt="" /><img class="pointerCursor" src="<?=$service['path']?>/image/owner/logout.gif" onclick="window.location.href = '<?=$blogURL?>/logout'" alt="" /> </td>
                <td width="10"></td>
                <td width="6"><img src="<?=$service['path']?>/image/owner/menuBg2.gif" alt="" /></td>
              </tr>
            </table>
          </td>
          <td width="10"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<table cellspacing="0" width="100%" style="height:450px;background-image:url('<?=$service['path']?>/image/owner/bg.gif'); background-repeat:repeat-x">
	<tr>
		<td valign="top" style="padding:10px 25px 30px 20px">
