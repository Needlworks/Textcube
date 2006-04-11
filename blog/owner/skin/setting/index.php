<?
define('ROOT', '../../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header3.php';
require ROOT . '/lib/piece/owner/contentMenu32.php';
$categories = getCategories($owner);
$selected = 0;
?>
<script type="text/javascript">
	//<![CDATA[
		function getValueById(id) {
			return document.getElementById(id).value;
		}
		
		
		function setSkin() {
				if(document.getElementById('showListOnCategoryTitles').checked) 
					showListOnCategory =1;
				else 
					showListOnCategory = 0;
				
				if(document.getElementById('showListOnArchiveTitles').checked) 
					showListOnArchive =1;
				else 
					showListOnArchive =0;
				
				if(document.getElementById('expandComment').checked) 
					expandComment =1;
				else 
					expandComment = 0;
				
				if(document.getElementById('expandTrackback').checked) 
					expandTrackback =1;
				else 
					expandTrackback =0;
				
				var tagboxAlign = 1;
				if (document.getElementById('tagboxAlignUsed')	.checked) {
					tagboxAlign = 1;
				} else if(document.getElementById('tagboxAlignName').checked) {
					tagboxAlign = 2;
				} else {
					tagboxAlign = 3;
				}
				
				param  = '';
				param += 'entriesOnPage='+getValueById('entriesOnPage') +'&';
				param += 'entriesOnRecent='+getValueById('entriesOnRecent') +'&';
				param += 'commentsOnRecent='+getValueById('commentsOnRecent') +'&';
				param += 'commentsOnGuestbook='+getValueById('commentsOnGuestbook') +'&';
				param += 'tagboxAlign='+tagboxAlign +'&';
				param += 'tagsOnTagbox='+getValueById('tagsOnTagbox') +'&';
				param += 'trackbacksOnRecent='+getValueById('trackbacksOnRecent') +'&';
				param += 'showListOnCategory='+showListOnCategory +'&';
				param += 'showListOnArchive='+showListOnArchive +'&';
				param += 'expandComment='+expandComment +'&';				
				param += 'expandTrackback='+expandTrackback +'&';
				param += 'recentNoticeLength='+getValueById('recentNoticeLength') +'&';
				param += 'recentEntryLength='+getValueById('recentEntryLength') +'&';
				param += 'recentCommentLength='+getValueById('recentCommentLength') +'&';
				param += 'recentTrackbackLength='+getValueById('recentTrackbackLength') +'&';				
				param += 'linkLength='+getValueById('linkLength') +'&';
				var request = new HTTPRequest("POST", '<?=$blogURL?>/owner/skin/setting/skin/');
				request.onSuccess = function() {
					PM.showMessage("<?=_t('저장되었습니다')?>", "center", "bottom");
				}
				request.send(param);
		}
		
		function changeTreeStyle() {
			
				var param = '';
				param += 'name='+document.getElementById('tree').value+'&';
				param += 'url=<?=$service['path']?>/image/tree/'+document.getElementById('tree').value+'&';
				param += 'showValue='+(document.getElementById('showValue').checked ? 1:0)+'&';
				param += 'itemColor='+document.getElementById('colorOnTree').value+'&';
				param += 'itemBgColor='+document.getElementById('bgColorOnTree').value+'&';
				param += 'activeItemColor='+document.getElementById('activeColorOnTree').value+'&';
				param += 'activeItemBgColor='+document.getElementById('activeBgColorOnTree').value+'&';
				param += 'labelLength='+document.getElementById('labelLengthOnTree').value+'&';
				
				document.getElementById('treePreview').src="<?=$blogURL?>/owner/skin/setting/tree/preview/?"+param;
			
		}
		
		
	//]]>
</script>

<table cellspacing="0" style="width:100%; ">
    <tr>
        <td style="width:18px;"><img alt="" src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18"/></td>
        <td style="padding:3px 0px 0px 4px;"><?=_t('스킨에 맞춘 내용 출력을 설정합니다')?></td>
    </tr>
</table>
<form id="skinSetting" name="skinSetting" enctype="application/x-www-form-urlencoded">
    <table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
        <tr>
            <td style="background-color:#EBF2F8; padding:10px 5px 10px 5px;"><div style="padding-left:20px">
                    <table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="entriesOnPage" id="entriesOnPage">
<?
for ($i = 1; $i < 30; $i++) {
	if ($i == $blog['entriesOnPage'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
<?
}
?>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('블로그 글을 한 페이지 당 %1개 보여줍니다', $arg)?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="entriesOnRecent" id="entriesOnRecent">
                                    <?
for ($i = 1; $i < 30; $i++) {
	if ($i == $skinSetting['entriesOnRecent'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
                                    <?
}
?>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('최신 글을 %1개 보여줍니다', $arg)?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="commentsOnRecent" id="commentsOnRecent">
                                    <?
for ($i = 1; $i < 30; $i++) {
	if ($i == $skinSetting['commentsOnRecent'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
                                    <?
}
?>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('최신 댓글을 %1개 보여줍니다', $arg)?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="trackbacksOnRecent" id="trackbacksOnRecent">
                                    <?
for ($i = 1; $i < 30; $i++) {
	if ($i == $skinSetting['trackbacksOnRecent'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
                                    <?
}
?>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('최신 트랙백을 %1개 보여줍니다', $arg)?></td>
                        </tr>
                    </table>
                </div>
                <table style="width:100%; margin:7px 0px 5px 0px;">
                    <tr>
                        <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle2.gif')"><img alt=""  src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;" /></td>
                    </tr>
                </table>
                <div style="padding-left:20px">
                    <table>
                        <tr>
                            <td>- <?=_t('카테고리 클릭 시')?></td>
                            <td><table>
                                    <tr>
                                        <td><input name="showListOnCategory" id="showListOnCategoryTitles" type="radio" value="titles" <?=$skinSetting['showListOnCategory'] ? 'checked = "checked"' : ''?> />
                                        </td>
                                        <td><label for="showListOnCategoryTitles"><?=_t('글 목록을 표시합니다')?></label></td>
                                    </tr>
                                    <tr>
                                        <td><input name="showListOnCategory" id="showListOnCategoryContents" type="radio" value="contents" <?=$skinSetting['showListOnCategory'] ? '' : 'checked = "checked"'?> />
                                        </td>
                                        <td><label for="showListOnCategoryContents"><?=_t('글 내용을 표시합니다')?></label></td>
                                    </tr>
                                </table></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td>- <?=_t('아카이브 클릭 시')?></td>
                            <td><table>
                                    <tr>
                                        <td><input name="showListOnArchive" id="showListOnArchiveTitles" type="radio" value="titles" <?=$skinSetting['showListOnArchive'] ? 'checked = "checked"' : ''?>/>
                                        </td>
                                        <td><label for="showListOnArchiveTitles"><?=_t('글 목록을 표시합니다')?></label></td>
                                    </tr>
                                    <tr>
                                        <td><input name="showListOnArchive" id="showListOnArchiveContents" type="radio" value="contents" <?=$skinSetting['showListOnArchive'] ? '' : 'checked = "checked"'?>/></td>
                                        <td><label for="showListOnArchiveContents"><?=_t('글 내용을 표시합니다')?></label></td>
                                    </tr>
                                </table></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td>- <?=_t('글을 표시할 때')?></td>
                            <td><table>
                                    <tr>
                                        <td><input name="expandComment" type="checkbox" id="expandComment" <?=$skinSetting['expandComment'] ? 'checked = "checked"' : ''?>/></td>
                                        <td><label for="expandComment"><?=_t('코멘트를 기본으로 펼칩니다')?></label></td>
                                    </tr>
                                    <tr>
                                        <td><input name="expandTrackback" type="checkbox" id="expandTrackback" <?=$skinSetting['expandTrackback'] ? 'checked = "checked"' : ''?> /></td>
                                        <td><label for="expandTrackback"><?=_t('트랙백을 기본으로 펼칩니다')?></label></td>
                                    </tr>
                                </table></td>
                        </tr>
                    </table>
                </div>
                <table style="width:100%; margin:7px 0px 5px 0px;">
                    <tr>
                        <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle2.gif')"><img alt=""  src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;" /></td>
                    </tr>
                </table>
                <div style="padding-left:20px">
                    <table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="recentNoticeLength" id="recentNoticeLength">
                                   <?
for ($i = 3; $i <= 40; $i++) {
	if ($i == $skinSetting['recentNoticeLength'])
		$checked = ' selected="selecte"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
                                    <?
}
?>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('최신 공지를 %1 글자로 표시합니다', $arg)?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="recentEntryLength" id="recentEntryLength">
                                   <?
for ($i = 3; $i <= 40; $i++) {
	if ($i == $skinSetting['recentEntryLength'])
		$checked = ' selected="selecte"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
                                    <?
}
?>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('최신 글을 %1 글자로 표시합니다', $arg)?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="recentCommentLength" id="recentCommentLength">
                                   <?
for ($i = 3; $i <= 40; $i++) {
	if ($i == $skinSetting['recentCommentLength'])
		$checked = ' selected="selecte"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
                                    <?
}
?>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('최신 댓글을 %1 글자로 표시합니다', $arg)?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="recentTrackbackLength" id="recentTrackbackLength">
                                   <?
for ($i = 3; $i <= 40; $i++) {
	if ($i == $skinSetting['recentTrackbackLength'])
		$checked = ' selected="selecte"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
                                    <?
}
?>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('최신 트랙백을 %1 글자로 표시합니다', $arg)?></td>
                        </tr>
                    </table>
                    <table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="linkLength" id="linkLength">
                                   <?
for ($i = 3; $i <= 40; $i++) {
	if ($i == $skinSetting['linkLength'])
		$checked = ' selected="selecte"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
                                    <?
}
?>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('링크를 %1 글자로 표시합니다', $arg)?></td>
                        </tr>
                    </table>
                </div>
				<table style="width:100%; margin:7px 0px 5px 0px;">
                    <tr>
                        <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle2.gif')"><img alt=""  src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;" /></td>
                    </tr>
                </table>
				<div style="padding-left:20px">
					<table>
                        <tr>
                            <td>- <?=_t('태그의 정렬방법을')?></td>
                            <td><table>
                                    <tr>
                                        <td><input name="tagboxAlign" id="tagboxAlignUsed" type="radio" value="1" 	<?=($skinSetting['tagboxAlign'] == 1 ? 'checked = "checked"' : '')?>/>
                                        </td>
                                        <td><label for="tagboxAlignUsed"><?=_t('인기도순으로 표시합니다')?></label></td>
                                    </tr>
                                    <tr>
                                        <td><input name="tagboxAlign" id="tagboxAlignName" type="radio" value="2" 	<?=($skinSetting['tagboxAlign'] == 2 ? 'checked = "checked"' : '')?>/></td>
                                        <td><label for="tagboxAlignName"><?=_t('이름순으로 표시합니다')?></label></td>
                                    </tr>
									<tr>
                                        <td><input name="tagboxAlign" id="tagboxAlignRadom" type="radio" value="3" <?=($skinSetting['tagboxAlign'] == 3 ? 'checked = "checked"' : '')?>/></td>
                                        <td><label for="tagboxAlignRadom"><?=_t('임의순으로 표시합니다')?></label></td>
                                    </tr>
                                </table></td>
                        </tr>
                    </table>
					<table>
                        <tr>
<?
ob_start();
?>
                            </td>
                            <td style="padding-left:3px"><select name="tagsOnTagbox" id="tagsOnTagbox">
<?
for ($i = 10; $i <= 200; $i += 10) {
	if ($i == $skinSetting['tagsOnTagbox'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>
                                    <option value="<?=$i?>" <?=$checked?>>
                                    <?=$i?>
                                    </option>
                                    <?
}
?>
  									<option value="-1" <?=$skinSetting['tagsOnTagbox'] == - 1 ? 'selected = "selected"' : ''?>><?=_t('전체')?></option>
                                </select>
                            </td>
                            <td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('태그박스에 태그를 %1개 표시합니다', $arg)?></td>
                        </tr>
                    </table>
				</div>
			    <div align="center"><br />
		        </div>
			    <div align="center"></div>
			    <table style="width:100%; margin:7px 0px 5px 0px;">
                    <tr>
                        <td style="background-image:url('<?=$service['path']?>/image/owner/dotHorizontalStyle2.gif')"><img alt=""  src="<?=$service['path']?>/image/owner/spacer.gif" style="width:1px; height:1px;" /></td>
                    </tr>
                </table>
				<div style="padding-left:20px">
					<table>
                        <tr>
<?
ob_start();
?>
                            </td>
							<td style="padding-left:3px"><select name="commentsOnGuestbook" id="commentsOnGuestbook">
<?
for ($i = 1; $i < 30; $i++) {
	if ($i == $skinSetting['commentsOnGuestbook'])
		$checked = ' selected="selected"';
	else
		$checked = '';
?>						
								<option value="<?=$i?>" <?=$checked?>>
								<?=$i?>
								</option>
<?
}
?>
							</select></td>
							<td>
<?
$arg = ob_get_contents();
ob_end_clean();
?>
                            <td>- <?=_f('방명록 한 페이지 당 %1개 글을 표시합니다', $arg)?></td>
                        </tr>
                    </table>
				</div>
			    <div align="center"><br />
		        </div>
			    <div align="center"></div>
			    <table align="center">
                    <tr>
                        <td><table class="buttonTop" cellspacing="0" onclick="setSkin(); return false;">
                                <tr>
                                    <td><img alt=""  width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                                    <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('저장하기')?></td>
                                    <td><img alt=""  width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                                </tr>
                        </table></td>
                    </tr>
                </table>			    </td>
        </tr>
    </table>
</form>
<div align="center" style="margin-top:15px;"></div>
<table cellspacing="0" style="width:100%; height:28px;">
    <tr>
        <td style="width:18px;"><img alt=""  src="<?=$service['path']?>/image/owner/sectionDescriptionIcon.gif" width="18" height="18"/></td>
        <td style="padding:3px 0px 0px 4px;"><?=_t('스킨에 맞춘 트리의 출력을 설정합니다')?></td>
    </tr>
</table>
<table cellspacing="0" style="width:100%; border-style:solid; border-width:2px 0px 2px 0px; border-color:#00A6ED">
    <tr>
        <td style="background-color:#EBF2F8; padding:10px;">
		<table  width="100%">				
                <tr>
                    <td valign="top" style="border:solid 2px #EEEEEE; background-color:#FFFFFF; padding:10px" width="250">
						<iframe id="treePreview" src="<?=$blogURL?>/owner/skin/setting/tree/preview" width="300" height="300" frameborder="0" style="overflow:visible"></iframe>
                    </td>
                    <td width="40"></td>
                    <form method="post" id="setSkinForm" action="<?=$blogURL?>/owner/skin/setting/tree" enctype="application/x-www-form-urlencoded">
                        <td width="500" align="left" valign="top">
							<table cellspacing="0" style="margin-top:10px;">
                                <tr>
                                    <td class="entryEditTableLeftCell"><?=_t('트리선택')?> |</td>
                                    <td>
										<select name="tree" id="tree" onchange="changeTreeStyle()">
                                            <?
$skinPath = ROOT . '/image/tree';
if ($dh = opendir($skinPath)) {
	while (($file = readdir($dh)) !== false) {
		if ($file == '.' || $file == '..')
			continue;
		if ((!file_exists($skinPath . '/' . $file . '/tab_top.gif')))
			continue;
		if ($skinSetting['tree'] == $file)
			echo "<option value=\"$file\" selected=\"selected\">$file</option>";
		else
			echo "<option value=\"$file\">$file</option>";
	}
	closedir($dh);
}
?>
                                        </select>
                                    </td>
                                </tr>
                            </table>
							<table cellspacing="0" style="margin-top:10px;">
                                <tr>
                                    <td class="entryEditTableLeftCell"><?=_t('선택된 폰트색')?></td>
                                    <td><input name="activeColorOnTree" id="activeColorOnTree" type="text" class="text1" value="<?=$skinSetting['activeColorOnTree']?>" size="7" maxlength="6" onchange="changeTreeStyle()"/></td>
                                </tr>
                            </table>
                            <table cellspacing="0">
                                <tr>
                                    <td class="entryEditTableLeftCell"><?=_t('선택된 배경색')?> |</td>
                                    <td><input name="activeBgColorOnTree" id="activeBgColorOnTree" type="text" class="text1" value="<?=$skinSetting['activeBgColorOnTree']?>" size="7" maxlength="6" onchange="changeTreeStyle()"/></td>
                                </tr>
                            </table>
                            <table cellspacing="0" style="margin-top:10px;">
                                <tr>
                                    <td class="entryEditTableLeftCell"><?=_t('선택되지 않은 폰트색')?> |</td>
                                    <td><input name="colorOnTree" id="colorOnTree" type="text" class="text1" value="<?=$skinSetting['colorOnTree']?>" size="7" maxlength="6" onchange="changeTreeStyle()"/></td>
                                </tr>
                            </table>
                            <table cellspacing="0">
                                <tr>
                                    <td class="entryEditTableLeftCell"><?=_t('선택되지 않은 배경색')?></td>
                                    <td><input name="bgColorOnTree" id="bgColorOnTree" type="text" class="text1" value="<?=$skinSetting['bgColorOnTree']?>" size="7" maxlength="6" onchange="changeTreeStyle()"/></td>
                                </tr>
                            </table>
                            <table cellspacing="0" style="margin-top:10px;" width="100%">
                                <tr>
                                    <td>
										<table width="100%">
                                            <tr>
                                                <td><?=_f('레이블을 %1 글자로 표시합니다', '<input name="labelLengthOnTree" id="labelLengthOnTree" type="text" class="text1" value="' . $skinSetting['labelLengthOnTree'] . '" size="3" maxlength="6" onchange="changeTreeStyle()"/>')?></td>
                                            </tr>
                                        </table></td>
                                </tr>
                            </table>
							<table cellspacing="0" style="margin-top:10px;" width="100%">
                                <tr>
                                    <td>
										<table width="100%">
                                            <tr>
                                                <td> 
													<input type="checkbox" name="showValueOnTree" id="showValue" onclick="changeTreeStyle()" <?=$skinSetting['showValueOnTree'] ? 'checked' : ''?>/>
													<label for="showValue"><?=_t('카테고리의 글 수를 표시합니다')?></label>
                                                </td>
                                            </tr>
                                        </table></td>
                                </tr>
                            </table>
						</td>
					</form>
		    </tr>
			</table>
<div align="center" style="margin-top:15px;">
    <table>
        <tr>
            <td><table class="buttonTop" cellspacing="0" onclick="document.getElementById('setSkinForm').submit()">
                    <tr>
                        <td><img alt=""  width="4" height="24" src="<?=$service['path']?>/image/owner/buttonLeft.gif"/></td>
                        <td class="buttonTop" style="work-break:keep-all;background-image:url('<?=$service['path']?>/image/owner/buttonCenter.gif');"><?=_t('저장하기')?></td>
                        <td><img alt=""  width="5" height="24" src="<?=$service['path']?>/image/owner/buttonRight.gif"/></td>
                    </tr>
                </table>
			</td>
        </tr>
    </table>
</div>
</td>
