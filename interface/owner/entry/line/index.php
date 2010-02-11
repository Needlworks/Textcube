<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'GET' => array(
		'category' => array('string', 'mandatory' => false),
		'page' => array('int', 1, 'default' => 1),
		'search' => array('string', 'mandatory' => false)
	),
	'POST' => array(
		'category' => array('string', 'mandatory' => false),
		'perPage' => array('int', 1, 'mandatory' => false),
		'search' => array('string', 'mandatory' => false)
	)
);

require ROOT . '/library/preprocessor.php';
require ROOT . '/interface/common/owner/header.php';

if(isset($_GET['category'])) $_POST['category'] = $_GET['category'];

if(isset($_POST['search'])) $searchKeyword = $_POST['search'];
else $searchKeyword = '';

$tabsClass = array();
if (isset($_GET['category'])) {
	if($_GET['category']=='public') {
		$tabsClass['public'] = true;
	} else if($_GET['category']=='private') {
		$tabsClass['private'] = true;
	} else {
		$tabsClass['all'] = true;
	}
} else {
	$tabsClass['all'] = true;
	unset($_GET['category']);
}

$conditions = array();
$conditions['blogid'] = getBlogId();
$conditions['page'] = $_GET['page'];
if(isset($_GET['category'])) $conditions['category'] = $_GET['category'];
if(isset($_POST['search'])) $conditions['keyword'] = $_POST['search'];
$conditions['linesforpage'] = 15;

$d = _t('삭제');

$conditions['template'] = <<<EOS
			<dl id="line_[##_id_##]" class="line">
				<dt class="date">[##_date_##]</dt>
				<dd class="content">[##_content_##]</dd>
				<dd class="permalink"><a href="[##_permalink_##]" class="permalink">at [##_root_##]</a></dd>
				<dd class="delete input-button" onclick="deleteLine('[##_id_##]');return false;"><span class="text">{$d}</span></dd>
			</dl>
EOS;
$conditions['dress'] = array('id'=>'id','date'=>'created','content'=>'content','permalink'=>'permalink','root'=>'root');
$line = Model_Line::getInstance();
$view = $line->getFormattedList($conditions);
$m = _t('더 보기');
$nextPage = $conditions['page'] + 1;
$button['template'] = <<< EOS
				<input type="submit" class="more-button input-button" value="{$m}" onclick="getMoreContent({$nextPage},{$conditions['linesforpage']},'bottom');return false;" />
EOS
?>
						<script type="text/javascript">
							//<![CDATA[
							function deleteLine(id) {
								var request = new HTTPRequest("POST","<?php echo $blogURL;?>/owner/entry/line/delete/");
								request.onSuccess = function () {
									PM.removeRequest(this);
									deleteBlock = document.getElementById("line_"+id);
									deleteBlock.parentNode.removeChild(deleteBlock);
									PM.showMessage("<?php echo _t('라인을 삭제하였습니다.');?>", "center", "bottom");
								}
								request.onError = function () {
									PM.removeRequest(this);
									alert("<?php echo _t('라인을 삭제할 수 없었습니다.');?>");
								}
								PM.addRequest(request, "<?php echo _t('라인을 삭제하고 있습니다.');?>");
								request.send("id="+id);
							}
							
							function getMoreContent(page,lines,mode) {
								var request = new HTTPRequest("POST","<?php echo $blogURL;?>/owner/entry/line/more/");
								request.onSuccess = function () {
									PM.removeRequest(this);
									contentView = this.getText("/response/contentView");
									buttonView = this.getText("/response/buttonView");
									if(page == 1 && lines == 1) buttonView = "";
									updateList(contentView, buttonView, mode);
									PM.showMessage("<?php echo _t('새 라인을 불러왔습니다.');?>", "center", "bottom");
								}
								request.onError = function () {
									PM.removeRequest(this);
									alert("<?php echo _t('새 라인을 불러올 수 없었습니다.');?>");
								}
								PM.addRequest(request, "<?php echo _t('라인을 불러오고 있습니다.');?>");
								request.send("page="+page
									+"&lines="+lines<?php
									if(isset($conditions['category'])) echo '+"&category="+'.$conditions['category'];
									if(isset($conditions['keyword'])) echo '+"&keyword='.htmlspecialchars($searchKeyword).'"';?>);
							}
							
							function updateList(contentView, buttonView, position) {
								Ocontent = document.getElementById("line-content");
								Pcontent = document.getElementById("line-more-page");
								if(position == "top") {
									Ocontent.innerHTML = contentView+Ocontent.innerHTML;
								} else {
									Ocontent.innerHTML = Ocontent.innerHTML+contentView;
								}
								if(buttonView != "") Pcontent.innerHTML = buttonView;
								return true;							
							}
							
							function writeLine() {
								contentForm = document.getElementById("line-write");
								content = contentForm.value;
								
								var request = new HTTPRequest("POST","<?php echo $blogURL;?>/line/");
								request.onSuccess = function () {
									PM.removeRequest(this);
									PM.showMessage("<?php echo _t('새 라인을 저장했습니다.');?>", "center", "bottom");
									getMoreContent(1,1,"top");
									Icontent = document.getElementById("line-write");
									Icontent.value = '<?php echo _t('내용을 입력하세요');?>';
								}
								request.onError = function () {
									PM.removeRequest(this);
									alert("<?php echo _t('라인을 저장할 수 없었습니다.');?>");
								}
								PM.addRequest(request, "<?php echo _t('라인을 저장하고 있습니다.');?>");
								request.send("content="+content+"&mode=ajax");				
							}
							//]]>
						</script>

						<div id="part-post-line" class="part">
							<h2 class="caption"><span class="main-text"><?php echo _t('라인을 관리합니다');?></span></h2>

							<form id="line-menuform" class="line-box" method="post" action="<?php echo $blogURL;?>/owner/entry/line">
								<ul id="line-tabs-box" class="tabs-box">
									<li class="line-public<?php echo isset($tabsClass['all']) ? ' selected' : NULL;?>""><a href="<?php echo $blogURL;?>/owner/entry/line"><?php echo _t('전체');?></a></li>
									<li class="line-public<?php echo isset($tabsClass['public']) ? ' selected' : NULL;?>""><a href="<?php echo $blogURL;?>/owner/entry/line?category=public"><?php echo _t('공개');?></a></li>
									<li class="line-private<?php echo isset($tabsClass['private']) ? ' selected' : NULL;?>""><a href="<?php echo $blogURL;?>/owner/entry/line?category=private"><?php echo _t('비공개');?></a></li>
								</ul>
							</form>

							<hr class="hidden" />
							
							<div id="line-content-box">
								<form id="line-write-form" method="post" action="<?php echo $blogURL;?>/line">
									<input type="text" id="line-write" value="<?php echo _t('내용을 입력하세요');?>" onkeypress="if (event.keyCode == 13) { return false; }" onclick="if(this.value=='<?php echo _t('내용을 입력하세요');?>') { this.value = ''}" />
									<input type="submit" class="input-button" value="<?php echo _t('라인 쓰기');?>" onclick="writeLine();return false;" />

								</form>
								<div id="line-content">
<?php echo $view;?>
								</div>
								<div id="line-more-page">
<?php echo $button['template'];?>
								</div>
							</div>
							
							<hr class="hidden" />
							
							<form id="search-form" class="data-subbox" method="post" action="<?php echo $blogURL;?>/owner/entry/line">
								<h2><?php echo _t('검색');?></h2>
								
								<div class="section">
									<label for="search"><?php echo _t('제목');?>, <?php echo _t('내용');?></label>
									<input type="text" id="search" class="input-text" name="search" value="<?php echo htmlspecialchars($searchKeyword);?>" onkeydown="if (event.keyCode == '13') { document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit(); }" />
									<input type="hidden" name="withSearch" value="" />
									<input type="submit" class="search-button input-button" value="<?php echo _t('검색');?>" onclick="document.getElementById('search-form').withSearch.value = 'on'; document.getElementById('search-form').submit();" />
								</div>
							</form>
						</div>
<?php
require ROOT . '/interface/common/owner/footer.php';
?>
