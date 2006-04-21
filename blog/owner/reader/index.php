<?
define('ROOT', '../../..');
define('__TATTERTOOLS_READER__', true);
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header6.php';
$setting = getReaderSetting($owner);
?>
<script type="text/javascript">
	//<![CDATA[
	var s_unknownError = "<?=_t('알 수 없는 에러입니다')?>";
	var s_notFoundPrevPost = "<?=_t('이전 포스트가 없습니다')?>";
	var s_notFoundNextPost = "<?=_t('다음 포스트가 없습니다')?>";
	var s_groupAdded = "<?=_t('그룹이 추가됐습니다')?>";
	var s_enterFeedName = "<?=_t('피드 이름을 입력하세요')?>";
	var s_groupExists = "<?=_t('이미 존재하는 그룹 이름입니다')?>";
	var s_addingGroup = "<?=_t('그룹을 추가하고 있습니다')?>";
	var s_groupModified = "<?=_t('그룹이 수정됐습니다')?>";
	var s_enterGroupName = "<?=_t('그룹 이름을 입력하세요')?>";
	var s_editingGroup = "<?=_t('그룹을 수정하고 있습니다')?>";
	var s_confirmDelete = "<?=_t('삭제하시겠습니까?')?>";
	var s_groupRemoved = "<?=_t('그룹이 삭제됐습니다')?>";
	var s_groupNotFound = "<?=_t('없는 그룹입니다')?>";
	var s_removingGroup = "<?=_t('그룹을 삭제하고 있습니다')?>";
	var s_feedAdded = "<?=_t('피드가 추가됐습니다')?>";
	var s_feedExists = "<?=_t('이미 존재하는 피드 주소입니다')?>";
	var s_conNotConnect = "<?=_t('입력하신 URL에 접속할 수 없습니다')?>";
	var s_feedBroken = "<?=_t('올바른 피드가 아닙니다')?>";
	var s_requestFeed = "<?=_t('피드를 가져오고 있습니다')?>";
	var s_feedModified = "<?=_t('피드가 수정됐습니다')?>";
	var s_editingFeed = "<?=_t('피드를 수정하고 있습니다')?>";
	var s_feedRemoved = "<?=_t('피드가 삭제됐습니다')?>";
	var s_removingFeed = "<?=_t('피드를 삭제하고 있습니다')?>";
	var s_saved = "<?=_t('저장되었습니다')?>";
	var s_markedAsUnread = "<?=_t('읽지 않은 상태로 변경됐습니다')?>";
	var s_loadingList = "<?=_t('포스트 목록을 불러오고 있습니다')?>";
	var s_opmlImportComplete = "<?=_t('OPML 파일을 가져왔습니다')?>";
	var s_opmlUploadComplete = "<?=_t('개의 피드를 가져왔습니다\n피드를 업데이트 해주세요')?>";
	var s_xmlBroken = "<?=_t('올바른 XML 파일이 아닙니다')?>";
	var s_opmlBroken = "<?=_t('올바른 OPML 파일이 아닙니다')?>";
	var s_loadingOPML = "<?=_t('OPML 파일을 가져오고 있습니다')?>";
	//]]>
</script>
<script type="text/javascript" src="<?=$service['path']?>/script/reader.js"></script>
<script type="text/javascript">
	//<![CDATA[
	var Reader = new TTReader();
	Reader.isPannelCollapsed = <?=getPersonalization($owner, 'readerPannelVisibility') == 1 ? 'false' : 'true'?>;
	STD.addEventListener(document);
	document.addEventListener("mouseup", Reader.finishResizing, false);
	STD.addEventListener(window);
	window.addEventListener("scroll", function() { Reader.setListPosition(); }, false);
	<?
if ($setting['loadImage'] == 2) {
?>
	Reader.optionForceLoadImage = true;
	<?
}
if ($setting['newWindow'] == 2) {
?>
	Reader.optionForceNewWindow = true;
	<?
}
?>
	//]]>
</script>
<iframe id="hiddenFrame" name="hiddenFrame" src="about:blank" style="display: none"></iframe>
<table cellspacing="0" style="width:100%">
<tr>
  <td style="width:7px; height:7px"><img width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeLeftTop.gif" alt="" /></td>
  <td width="100%" bgcolor="#FFFFFF"><img width="1" height="1" src="<?=$service['path']?>/image/owner/spacer.gif" alt="" /></td>
  <td style="width:7px; height:7px"><img width="7" height="7" src="<?=$service['path']?>/image/owner/roundEdgeRightTop.gif" alt="" /></td>
</tr>
</table>
<table cellspacing="0" style="width:100%; background-color:#FFFFFF">
<tr>
<td valign="top" style="height:50px; padding:5px 15px 15px 15px">
<form method="post" action="<?=$blogURL?>/owner/reader/opml/import/file/" target="hiddenFrame" enctype="multipart/form-data">
<table width="100%" border="0" cellspacing="0" cellpadding="8">
  <tr>
    <td>
	<table width="100%" height="30" border="0" cellpadding="0" cellspacing="0" background="<?=$service['path']?>/image/owner/reader/menuBg.gif">
      <tr>
        <td><table height="30" border="0" cellpadding="0" cellspacing="0" background="<?=$service['path']?>/image/owner/reader/menuBg.gif" id="menu">
          <tr>
            <td width="80" align="center" id="cursor1" style="background:url('<?=$service['path']?>/image/owner/reader/menuIndicate.gif') no-repeat bottom center;"><a href="<?=$blogURL . '/owner/reader'?>"><span style="color:#2e67b4; font-size:14px;"><?=_t('전체보기')?></span></a></td>
            <td width="3" valign="top"><img src="<?=$service['path']?>/image/owner/reader/menuVline.gif" /></td>
            <td width="150" align="center" id="cursor2" style="background:url('<?=$service['path']?>/image/spacer.gif') no-repeat bottom center;"><a href="#" onclick="Reader.showStarredOnly(); return false"><img id="starredOnlyIndicator" src="<?=$service['path']?>/image/owner/reader/iconStarOff.gif" alt="Starred Only" style="vertical-align: 0px" /> <?=_t('스크랩된 글 보기')?></a> </td>
            <td width="3" valign="top"><img src="<?=$service['path']?>/image/owner/reader/menuVline.gif" /></td>
            <td width="220" align="center" valign="top" id="cursor3" style="background:url('<?=$service['path']?>/image/spacer.gif') no-repeat bottom center;">
			  <input type="text" id="keyword" style="border:1px #2e67b4 solid; height: 14px" onkeydown="if(event.keyCode==13) Reader.showSearch()"/>
              <input type="button" value="<?=_t('검색')?>" style="	border:1px #30609b solid;background:#6494c1;color:#fff;	font-size:11px;font-weight:bold; padding-top:2px; height: 18px" onclick="Reader.showSearch()"/></td>
          </tr>
        </table>
		</td>
        <td align="right">
		<table border="0" cellspacing="0" cellpadding="0" id="utilmenu">
          <tr>
            <td><img src="<?=$service['path']?>/image/owner/reader/iconCollect.gif" width="17" height="17" hspace="3" align="absmiddle" /><a href="#" onclick="Reader.updateAllFeeds(); return false"><?=_t('모든 피드 업데이트')?></a> <span id="progress"></span> </td>
            <td> <img src="<?=$service['path']?>/image/owner/reader/iconSetup.gif" width="17" height="17" hspace="3" align="absmiddle" /> <a href="#" onclick="Reader.toggleConfigure(); return false"><?=_t('설정')?></a> </td>
          </tr>
        </table></td>
      </tr>
    </table>
	<div id="pannel" style="display: <?=getPersonalization($owner, 'readerPannelVisibility') == 1 ? 'block' : 'none'?>">
	<div id="groupsAndFeeds">
      <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#92b2d9">
        <tr>
          <td width="256" height="100%" valign="top" style="padding: 0px 6px">
		  <div id="groupBox" style="height: <?=getPersonalization($owner, 'readerPannelHeight')?>px">
		  <?
printFeedGroups($owner);
?>
          </div>
		  </td>
          <td valign="top" height="100%" style="padding: 0px 6px 0px 0px">
		  <div id="feedBox" style="height: <?=getPersonalization($owner, 'readerPannelHeight')?>px">
		  <?
printFeeds($owner);
?>
          </div>
		  </td>
        </tr>
      </table>
	  </div>
	<div id="configure" style="background-color: #92b2d9; display: none; border: 6px solid #92b2d9; border-width: 0px 6px">
	<table align=center width="100%" border="0" cellpadding="10" cellspacing="0" bgcolor="#ffffff" style="table-layout: fixed">
		<tr>
			<td valign="top" style="padding: 10px">
				<span style="font-size:14px; font-weight:bold"><img src="<?=$service['path']?>/image/owner/reader/iconSetupitem.gif" width="11" height="12" hspace="5" /><?=_t('리더 환경을 설정합니다')?></span>
				<table align="center" width="100%" border="0" cellpadding="8" cellspacing="0" bgcolor="#e3effe">
					<tr>
						<td style="padding: 10px">
							<table width="100%" border="0" cellpadding="2" cellspacing="0">
								<?
if (getUserId() == 1) {
?>
								<tr>
									<td align="right" style="color:#333;"><?=_t('업데이트 주기')?> | </td>
									<td>
										<select name="updateCycle">
										<option value="0"<?=$setting['updateCycle'] == 0 ? ' selected="selected"' : ''?>><?=_t('수집하지 않음')?></option>
										<option value="60"<?=$setting['updateCycle'] == 60 ? ' selected="selected"' : ''?>>1<?=_t('시간')?></option>
										<option value="120"<?=$setting['updateCycle'] == 120 ? ' selected="selected"' : ''?>>2<?=_t('시간')?></option>
										<option value="240"<?=$setting['updateCycle'] == 240 ? ' selected="selected"' : ''?>>4<?=_t('시간')?></option>
										<option value="480"<?=$setting['updateCycle'] == 480 ? ' selected="selected"' : ''?>>8<?=_t('시간')?></option>
										<option value="960"<?=$setting['updateCycle'] == 960 ? ' selected="selected"' : ''?>>16<?=_t('시간')?></option>
										</select>
									</td>
								</tr> 
								<tr height="1">
									<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline02.gif"></td>
								</tr>
								<tr>
									<td align="right" style="color:#333;"><?=_t('수집한 글의 보존기간')?> | </td>
									<td>
										<select name="feedLife">
										<option value="10"<?=$setting['feedLife'] == 10 ? ' selected="selected"' : ''?>>10<?=_t('일')?></option>
										<option value="20"<?=$setting['feedLife'] == 20 ? ' selected="selected"' : ''?>>20<?=_t('일')?></option>
										<option value="30"<?=$setting['feedLife'] == 30 ? ' selected="selected"' : ''?>>30<?=_t('일')?></option>
										<option value="45"<?=$setting['feedLife'] == 45 ? ' selected="selected"' : ''?>>45<?=_t('일')?></option>
										<option value="60"<?=$setting['feedLife'] == 60 ? ' selected="selected"' : ''?>>60<?=_t('일')?></option>
										<option value="90"<?=$setting['feedLife'] == 90 ? ' selected="selected"' : ''?>>90<?=_t('일')?></option>
										<option value="0"<?=$setting['feedLife'] == 0 ? ' selected="selected"' : ''?>><?=_t('계속보관')?></option>
										</select>
									</td>
								</tr>
								<tr height="1">
									<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline02.gif"></td>
								</tr>
								<?
}
?>
								<tr>
									<td align="right" style="color:#333;"><?=_t('링크가 차단된 이미지')?> | </td>
									<td>
										<input name="loadImage" id="loadImage1" type="radio" value="1" <?=$setting['loadImage'] == 1 ? ' checked="checked"' : ''?>/>
										<label for="loadImage1"><?=_t('그대로 두기')?></label>
										<input name="loadImage" id="loadImage2" type="radio" value="2" <?=$setting['loadImage'] == 2 ? ' checked="checked"' : ''?>/>
										<label for="loadImage2"><?=_t('강제로 읽어오기')?></label>
									</td>
								</tr>
								<tr height="1">
									<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline02.gif"></td>
								</tr>					  
								<tr>
									<td align="right" style="color:#333;"><?=_t('자바스크립트 허용')?> | </td>
									<td>
										<input name="allowScript" id="allowScript1" type="radio" value="1" <?=$setting['allowScript'] == 1 ? ' checked="checked"' : ''?>/>
										<label for="allowScript1"><?=_t('허용')?></label>
										<input name="allowScript" id="allowScript2" type="radio" value="2" <?=$setting['allowScript'] == 2 ? ' checked="checked"' : ''?>/>
										<label for="allowScript2"><?=_t('거부')?></label>
									</td>
								</tr>
								<tr height="1">
									<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline02.gif"></td>
								</tr>					  
								<tr>
									<td align="right" style="color:#333;"><?=_t('링크')?> | </td>
									<td>
										<input name="newWindow" id="newWindow1" type="radio" value="1" <?=$setting['newWindow'] == 1 ? ' checked="checked"' : ''?>/>
										<label for="newWindow1"><?=_t('기본값')?></label>
										<input name="newWindow" id="newWindow2" type="radio" value="2" <?=$setting['newWindow'] == 2 ? ' checked="checked"' : ''?>/>
										<label for="newWindow2"><?=_t('새창으로')?></label>
									</td>
								</tr>
								<tr height="1">
									<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline02.gif"></td>
								</tr>					  
								<tr>
									<td colspan="2" align="center" height="30">
										<div style="padding: 10px;">
										<input type="button" value="<?=_t('저장하기')?>" style="border:1px #5788C4 solid;background:#8DB0DC;padding-top:2px;color:#fff; margin-top:5px;" onclick="Reader.saveSetting()"/>
										</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
			<td valign="top" style="padding: 10px">
				<span style="font-size:14px; font-weight:bold"><img src="<?=$service['path']?>/image/owner/reader/iconSetupitem.gif" width="11" height="12" hspace="5" /><?=_t('OPML 관리')?> </span>
				<table align="center" width="100%" border="0" cellpadding="8" cellspacing="0" bgcolor="#e3effe">
					<tr>
						<td style="padding: 10px">
							<table width="100%" border="0" cellpadding="2" cellspacing="0" bgcolor="#e3effe">
								<tr>
									<td width="25%" align="right" style="color:#333;"><?=_t('가져오기')?> | </td>
									<td>
										<input type="radio" name="opmlMethod" id="opmlMethod1" value="1" checked="checked" onclick="document.getElementById('opmlUpload').style.display='block';document.getElementById('opmlRequest').style.display='none';"/>
										<label for="opmlMethod1"><?=_t('파일 업로드')?></label>
										<input type="radio" name="opmlMethod" id="opmlMethod2" value="2" onclick="document.getElementById('opmlUpload').style.display='none';document.getElementById('opmlRequest').style.display='block';"/>
										<label for="opmlMethod2"><?=_t('URL 입력')?></label>
									</td>
								</tr>
								<tr height="1">
									<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline02.gif"></td>
								</tr>
								<tr id="opmlUpload" height="30">
									<td width="25%" align="right" style="color:#333;"><?=_t('OPML 업로드')?> | </td>
									<td>
										<input type="file" id="opmlUploadValue" name="opmlFile" class="text2" style="width: 90%"/>
									</td>
								</tr>
								<tr id="opmlRequest" style="display: none" height="30">
									<td width="25%" align="right" style="color:#333;"><?=_t('URL로 읽어오기')?> | </td>
									<td>
										<input type="text" id="opmlRequestValue" class="text2" style="width: 90%"/>
									</td>
								</tr>
								<tr height="1">
									<td colspan="2" background="<?=$service['path']?>/image/owner/reader/dotline02.gif"></td>
								</tr>					  
								<tr>
									<td colspan="2" align="center" height="30">
										<div style="padding: 19px 10px 10px;">
										<input type="button" value="<?=_t('가져오기')?>" style="border:1px #5788C4 solid;background:#8DB0DC;padding-top:2px;color:#fff; margin-top:5px;" onclick="if(document.forms[0].opmlMethod[0].checked) Reader.importOPMLUpload(); else Reader.importOPMLURL();"/>
										<input type="button" value="<?=_t('내보내기')?>" style="border:1px #5788C4 solid;background:#8DB0DC;padding-top:2px;color:#fff; margin-top:5px;" onclick="Reader.exportOPML()"/>
										</div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	</div>
	</div>	  
      <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:6px; table-layout: fixed">
        <tr>
          <td align="center" bgcolor="#7097c6" style="cursor: s-resize" onmousedown="Reader.startResizing(event)"><img id="toggleButton" src="<?=$service['path']?>/image/owner/reader/bar<?=getPersonalization($owner, 'readerPannelVisibility') == 1 ? 'Hide' : 'Show'?>.gif" width="69" height="8" style="cursor: pointer" onclick="Reader.togglePannel(event)"/></td>
        </tr>
      </table>
      <table id="scrollPoint" width="100%" border="0" cellpadding="0" cellspacing="0" style="background: #B4D1F1 url('<?=$service['path']?>/image/owner/reader/contentsBg.gif') repeat-x">
        <tr>
          <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="10"><img src="<?=$service['path']?>/image/owner/reader/boxRound01.gif" /></td>
                <td valign="bottom">
					<table width="255" border="0" cellspacing="0" cellpadding="0">
						<tr>
							<td>
								<a href="<?=$blogURL?>/owner/reader"><span style="font-size:14px; font-weight:bold; color:#333;"><?=_t('전체 목록')?></span></a> <span style="font-size:10px; font-family:Tahoma; color:#333">(<span id="entriesShown">0</span> / <span id="entriesTotal">0</span>) </span>
							</td>
							<td width="70" align="right">
								<a id="iconMoreEntries" href="#" onclick="Reader.listScroll(1); return false" style="color: #fff"><img src="<?=$service['path']?>/image/owner/reader/iconMoreRead.gif" alt="Load more entries"/></a>
							</td>
						</tr>
					</table>
				</td>
              </tr>
          </table></td>
          <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td valign="bottom" style="padding-left:10px;"><span id="blogTitle" style="font-size:13px; font-weight:bold; color:#333;"></span> </td>
                <td width="120" align="right" valign="bottom"><a href="javascript:Reader.prevEntry()" style="color: #000"><img class="pointerCursor" src="<?=$service['path']?>/image/owner/reader/pagePrev.gif" width="12" height="12"/><?=_t('이전')?></a> <img src="<?=$service['path']?>/image/owner/reader/pageSp.gif" width="5" height="15" hspace="2" /> <a href="javascript:Reader.nextEntry()" style="color: #000"><?=_t('다음')?><img class="pointerCursor" src="<?=$service['path']?>/image/owner/reader/pageNext.gif" width="12" height="12"/></a></td>
                <td width="10" align="right"><img src="<?=$service['path']?>/image/owner/reader/boxRound02.gif" /></td>
              </tr>
          </table></td>
        </tr>
        <tr>
          <td width="261" height="500" valign="top" bgcolor="#B4D1F1" style="padding-left:6px; padding-right:6px;">
		  <div id="floatingList">
		  <table width="100%" height="450" border="0" cellpadding="5" cellspacing="0">
              <tr>
                <td bgcolor="#84A9D1" style="padding: 4px">
				<div id="listup" onscroll="Reader.listScroll(0)">
				<?
printFeedEntries($owner);
?>
                </div>
                    <table width="100%" border="0" cellspacing="0" cellpadding="3">
                      <tr>
                        <td style="padding: 5px"><a href="#" onclick="Reader.showUnreadOnly(); return false"><span style="color:#fff; font-size:11px"><?=_t('읽은글 감추기')?></span></a> - <span style="color:#036; font-size:11px;cursor: pointer" onclick="document.getElementById('shortcuts').style.display = document.getElementById('shortcuts').style.display=='none' ? 'block' : 'none'"><?=_t('단축키 보기')?></span></td>
                      </tr>
                  </table>
                    <table id="shortcuts" width="100%" border="0" cellpadding="3" cellspacing="0" bgcolor="#6593C4" style="display: none">
                      <tr>
                        <td style="color:#fff;"><strong>A, H</strong> - <?=_t('이전 글')?>,  <strong>S, L</strong> - <?=_t('다음 글')?>,  <strong>D</strong> - <?=_t('새창으로')?>, <br />
                          <strong>F</strong> - <?=_t('안읽은 글만 보기')?>,  <strong>G</strong> - <?=_t('스크랩한 글만 보기')?><br />
                          <strong>Q</strong> - <?=_t('블로그 화면으로')?>,  <strong>W</strong> - <?=_t('현재글 스크랩')?><br />
                          <strong>R</strong> - <?=_t('리더 첫화면으로')?>,<strong> T</strong> - <?=_t('글 수집하기')?><br />
                          <strong>J</strong> - <?=_t('위로 스크롤')?>,<strong> K</strong> - <?=_t('아래로 스크롤')?></td>
                      </tr>
                    </table>
			  </td>
              </tr>
          </table>
		  </div>
		  </td>
          <td valign="top" bgcolor="#B4D1F1" style="padding-right:6px; padding-bottom:10px;"><table width="100%" border="0" cellpadding="0" cellspacing="1" bgcolor="#6D9CD6">
             <tr>
             <td id="entry" bgcolor="#ffffff" style="padding: 10px">
			<?
printFeedEntry($owner);
?>
			</td>
        </tr>
      </table>
	  </td>
  </tr>
</table>
<?
if (isset($_GET['forceRefresh'])) {
?>
<script type="text/javascript">
Reader.updateAllFeeds();
</script>
<?
}
require ROOT . '/lib/piece/owner/footer.php';
?>