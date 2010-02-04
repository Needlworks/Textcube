<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
define('__TEXTCUBE_IPHONE__', true);
require ROOT . '/library/preprocessor.php';
requireView('iphoneView');
if(empty($suri['id'])) {
	printIphoneHtmlHeader();
?>	
	<ul id="home" title="<?php echo htmlspecialchars(UTF8::lessenAsEm($blog['title'],30));?>" selected="true">
	<?php
		$blogAuthor = User::getBlogOwnerName($blogid);
		$blogLogo = !empty($blog['logo']) ? printIphoneImageResizer($blogid, $blog['logo'], 80) : "{$service['path']}/resources/style/iphone/image/textcube_logo.png";
		$itemsView = '<li class="blog_info">'.CRLF;
		$itemsView .= '	<div class="logo"><img src="' . $blogLogo . '" /></div>'.CRLF;
		$itemsView .= '	<div class="blog_container">'.CRLF;
		$itemsView .= '		<span class="title">' . htmlspecialchars($blog['title']). '</span>'.CRLF;
		$itemsView .= '		<span class="author">by ' . $blogAuthor . '</span>'.CRLF;
		$itemsView .= '		<span class="description">' . htmlspecialchars($blog['description']) . '</span>'.CRLF;
		$itemsView .= '	</div>'.CRLF;
		$itemsView .= '</li>'.CRLF;
		print $itemsView;
	?>
		<li><a href="<?php echo $blogURL;?>/entry" class="link"><?php echo _text('글목록');?></a></li>
		<li><a href="#categories" class="link"><?php echo _text('분류');?></a></li>
		<li><a href="#archives" class="link"><?php echo _text('보관목록');?></a></li>
		<li><a href="#tags" class="link"><?php echo _text('태그');?></a></li>
		<li><a href="<?php echo $blogURL;?>/comment" class="link"><?php echo _text('최근 댓글');?></a></li>
		<li><a href="<?php echo $blogURL;?>/trackback" class="link"><?php echo _text('최근 트랙백');?></a></li>
		<li><a href="<?php echo $blogURL;?>/guestbook" class="link"><?php echo _text('방명록');?></a></li>
		<li><a href="<?php echo $blogURL;?>/link" class="link"><?php echo _text('링크');?></a></li>
	<?php
		if (doesHaveOwnership()) {
	?>
		<li><a href="<?php echo $defaultURL;?>/owner/center/dashboard" onclick="window.location.href='<?php echo $defaultURL;?>/owner/center/dashboard'" class="link dashboard"><?php echo _text('관리 패널');?></a></li>
		<li><a href="<?php echo $blogURL;?>/logout" class="link logout"><?php echo _text('로그아웃');?></a></li>
	<?php
		}else{
	?>
		<li><a href="<?php echo $blogURL;?>/login" class="link"><?php echo _text('로그인');?></a></li>
	<?php
		}
	?>
		<li><a href="#textcube" class="link"><span class="colorText"><span class="c1">T</span><span class="c2">e</span><span class="c3">x</span><span class="c4">t</span><span class="c5">c</span><span class="c6">u</span><span class="c7">b</span><span class="c8">e</span></span></a></li>
	</ul>

	<ul id="categories" title="Categories" selected="false">
	<?php
		$totalPosts = getEntriesTotalCount($blogid);
		$categories = getCategories($blogid);
		print printIphoneCategoriesView($totalPosts, $categories, true);	
	?>
	</ul>

	<ul id="archives" title="Archives" selected="false">
	<?php
		$archives = printIphoneArchives($blogid);
		print printIphoneArchivesView($archives);	
	?>
	</ul>

	<ul id="tags" title="Tags" selected="false">
		<li class="group"><span class="left">Random Tags (100)</span><span class="right">&nbsp;</span></li>
		<li class="panel">
		<div class="content padding5">
			<ul class="tag_list">
				<?php
					$tags = printIphoneTags($blogid, 'random', 100);
					print printIphoneTagsView($tags);	
				?>	
			</ul>
		</div>
		</li>
	</ul>

    <form id="searchForm" method="GET" class="dialog snug editorBar" action="<?php echo $blogURL;?>/search">
        <fieldset>
            <h1><?php echo _text('글 검색');?></h1>
            <a class="button leftButton" type="cancel" onclick="searchAction(false);"><?php echo _text('취소');?></a>
            <a class="button blueButton" type="submit"><?php echo _text('검색');?></a>
            
            <div class="searchIcon"></div>
			<img id="clearButton" class="clearButton" src="<?php echo $service['path'];?>/resources/image/spacer.gif" onclick="cancelAction(this);" />
			<input id="qString" type="text" name="search" autocomplete="off" unedited="true" class="search" onkeyup="searchKeywordCheck(this);" onkeydown="searchKeywordCheck(this);" />
		</fieldset>
    </form>

	<div id="textcube" title="TEXTCUBE" selected="false">
		<div class="textcubeLogo">&nbsp;</div>
		<div class="textcubeVersion">
			Brand yourself! : <?php echo TEXTCUBE_NAME;?> <?php echo TEXTCUBE_VERSION;?>
		</div>
		<div class="textcubeDescription">
			<ul>
				<li class="group">Textcube</li>
				<li>
					<?php echo _t('텍스트큐브(Textcube) 는 웹에서 자신의 생각이나 일상을 기록하고 표현하기 위한 도구입니다.').' '._t('텍스트큐브는 개인 사용자부터 서비스 구축까지 넓은 폭으로 사용할 수 있으며, 플러그인과 테마 시스템, 다국어 지원을 통하여 무한한 확장성을 제공합니다.');?><br/><br/>
					<?php echo _t('2007년 4월 태터앤프렌즈(TNF)는 태터 네트워크 재단(TNF, Tatter Network Foundation) 계획과 함께 적극적 참여 집단인 니들웍스(Needlworks) 를 발표하였습니다. 또한 태터툴즈를 기반으로 하는 오픈소스 블로그 소프트웨어인 S2 개발 계획도 발표하였습니다.').' '._t('2007년 4월 23일 TNF의 박용주님에 의하여 S2는 텍스트큐브로 명명 되었으며, 이후 개발 기간을 거쳐 2007년 8월 16일 TNF에 의하여 텍스트큐브의 첫 정식 버전인 텍스트큐브 1.5가 발표되었습니다.');?><br/><br/>
				</li>
			</ul>
		</div>
	</div>
<?php
	printIphoneHtmlFooter();
}
?>
