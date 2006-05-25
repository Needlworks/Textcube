<?
define('ROOT', '../../..');
require ROOT . '/lib/includeForOwner.php';
require ROOT . '/lib/piece/owner/header3.php';
require ROOT . '/lib/piece/owner/contentMenu30.php';
$skins = array();
$dirHandler = dir(ROOT . "/skin");
while ($file = $dirHandler->read()) {
	if ($file == '.' || $file == '..')
		continue;
	if (!file_exists(ROOT . "/skin/$file/skin.html"))
		continue;
	$preview = "";
	if (file_exists(ROOT . "/skin/$file/preview.jpg"))
		$preview = "{$service['path']}/skin/$file/preview.jpg";
	if (file_exists(ROOT . "/skin/$file/preview.gif"))
		$preview = "{$service['path']}/skin/$file/preview.gif";
	array_push($skins, array('name' => $file, 'path' => ROOT . "/skin/$file/", 'preview' => $preview));
}

function writeValue($value, $label) {
?>
												<tr>
													<td class="name"><?=$label?></td>
													<td class="explain"><?=nl2br(addLinkSense($value, ' onclick="window.open(this.href); return false;"'))?></td>
												</tr>
<?
}
?>
								<script type="text/javascript">
									//<![CDATA[
										var isSkinModified = <?=($skinSetting['skin'] == "customize/$owner") ? 'true' : 'false'?>;

										function selectSkin(name) {
											if(isSkinModified) {
												if(!confirm("<?=_t('수정된 스킨을 사용중입니다. 새로운 스킨을 선택하면 수정된 스킨의 내용은 모두 지워집니다.\n스킨을 적용하시겠습니까?')?>"))
													return;
											}
											try {
												var request = new HTTPRequest("POST", "<?=$blogURL?>/owner/skin/change/");
												request.onSuccess = function() {
													isSkinModified = false;
													PM.showMessage("<?=_t('성공적으로 변경했습니다.')?>", "center", "bottom");
													document.getElementById('currentPreview').innerHTML = document.getElementById('preview_'+name).innerHTML;
													document.getElementById('currentInfo').innerHTML = document.getElementById('info_'+name).innerHTML;
													window.location.href = "#currentSkinAnchor";
													//eleganceScroll('currentSkin',8);
													/*
													document.getElementById('currentSkinPreview').innerHTML = document.getElementById('preivew_'+name).innerHTML
													document.getElementById('currentSkinInfo').innerHTML = document.getElementById('info_'+name).innerHTML
													*/
												}
												request.onError = function() {
													alert(result['msg']);
												}
												request.send("skinName=" + encodeURIComponent(name));
											} catch(e) {
												alert(e.message);
											}
										}
									//]]>
								</script>

								<div id="part-skin-current" class="part">
									<h2 class="caption"><span class="main-text"><?=_t('현재 사용하고 있는 스킨')?></span></h2>

									<div class="data-inbox">
										<div id="currentSkin" class="section" style="display:none;">
											<a name="currentSkinAnchor"></a>
											<div id="currentPreview" class="preview"></div>
											<div class="information">
												<span id="currentInfo"></span>
												<span class="hidden">|</span>
												<a class="edit-button button" href="#void" onclick="window.location='<?=$blogURL?>/owner/skin/edit'"><span><?=_t('편집하기')?></span></a>
											</div>
											<div class="clear"></div>
										</div>
										<div class="clear"></div>
									</div>
								</div>

								<div id="currentSkinLoading" style="display: none;">
									<?=_t('로딩 중...')?>
								</div>
								
								<hr class="hidden" />
								
								<div id="part-skin-list" class="part">
									<h2 class="caption"><span class="main-text"><?=_t('스킨을 변경하시려면 아래 목록에서 마음에 드는 스킨을 골라 사용하기 버튼을 클릭해 주세요')?></span></h2>

									<div class="data-inbox">
<?
$count = 0;
for ($i = 0; $i < count($skins); $i++) {
	$skin = $skins[$i];
?>
										<div class="section">
											<div id="preview_<?=$skin['name']?>" class="preview">
<?
	if ($skin['preview'] == '') {
?>
												<img src="<?=$service['path']?>/style/default/image/noPreview.gif" alt="<?=_t('스킨 미리보기')?>" />
<?
	} else {
?>
												<img src="<?=$skin['preview']?>" alt="<?=_t('스킨 미리보기')?>" />
<?
	}
?>
											</div>
											<div id="info_<?=$skin['name']?>" class="information">
												<table cellspacing="0" cellpadding="0">
<?
	if (file_exists(ROOT . "/skin/{$skin['name']}/index.xml")) {
		$xml = file_get_contents(ROOT . "/skin/{$skin['name']}/index.xml");
		$xmls = new XMLStruct();
		$xmls->open($xml, $service['encoding']);
		writeValue('<b>' . $xmls->getValue('/skin/information/name') . '</b> <span class="version">ver.' . $xmls->getValue('/skin/information/version') . '</span>', _t('제목'));
		writeValue($xmls->getValue('/skin/information/license'), _t('저작권'));
		writeValue($xmls->getValue('/skin/author/name'), _t('만든이'));
		writeValue($xmls->getValue('/skin/author/homepage'), _t('홈페이지'));
		writeValue($xmls->getValue('/skin/author/email'), _t('E-mail'));
		writeValue($xmls->getValue('/skin/information/description'), _t('설명'));
	} else {
		writeValue($skin['name'], _t('제목'));
	}
?>
												</table>
												<a class="preview-button button" href="#void" onclick="window.open('<?=$blogURL?>/owner/skin/preview/?skin=<?=$skin['name']?>')"><span><?=_t('미리보기')?></span></a>
												<span class="hidden">|</span>
												<a class="apply-button button" href="#void" onclick="selectSkin('<?=$skin['name']?>');"><span><?=_t('적용')?></span></a>
											</div>
											<div class="clear"></div>
										</div>
<?
}
?>
									</div>
									<div class="clear"></div>
								</div>

								<script type="text/javascript">
									//<![CDATA[
										try {
											document.getElementById('currentPreview').innerHTML = document.getElementById('preview_<?=$skinSetting['skin']?>').innerHTML;
											document.getElementById('currentInfo').innerHTML = document.getElementById('info_<?=$skinSetting['skin']?>').innerHTML;
											document.getElementById('currentSkin').style.display = "block";
											document.getElementById('currentSkinLoading').style.display = "none";
										} catch(e) {
											document.getElementById('currentPreview').innerHTML ='<img src="<?=$service['path']?>/style/default/image/noPreview.gif" alt="<?=_t('스킨 미리보기')?>" />';
											document.getElementById('currentInfo').innerHTML = "<?=_t('수정된 스킨입니다.')?>";
											document.getElementById('currentSkin').style.display = "block";
											document.getElementById('currentSkinLoading').style.display = "none";
										}
									//]]>
								</script>
<?
require ROOT . '/lib/piece/owner/footer1.php';
?>