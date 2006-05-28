<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ko">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo htmlspecialchars($blog['title'])?> &gt; <?php echo _t('스킨관리')?></title>
	<script type="text/javascript">
		//<![CDATA[
			var servicePath = "<?=$service['path']?>";
			var blogURL = "<?=$blogURL?>";
			var adminSkin = "<?=$service['adminSkin']?>";
		//]]>
	</script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/byTattertools.js"></script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/EAF.js"></script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/common.js"></script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/gallery.js"></script>
	<script type="text/javascript" src="<?php echo $service['path']?>/script/owner.js"></script>
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$service['adminSkin']?>/basic.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$service['adminSkin']?>/skin.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$service['adminSkin']?>/basic.opera.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$service['adminSkin']?>/skin.opera.css" />
	<!--[if lte IE 6]><link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$service['adminSkin']?>/basic.ie.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $service['path'].$service['adminSkin']?>/skin.ie.css" /><![endif]-->
</head>
<body id="body-skin">
	<div id="temp-wrap">
		<div id="all-wrap">
			<div id="layout-header">
				<h1><span>태터툴즈 관리 페이지</span></h1>
				
				<hr class="hidden" />
				
				<h2><span>메인메뉴</span></h2>
				
				<div id="main-menu-outbox">
					<ul id="main-menu">
						<li class="tattertools"><a href="<?php echo TATTERTOOLS_HOMEPAGE?>" title="<?php echo _t('태터툴즈 홈페이지로 이동합니다.')?>"><span>태터툴즈 홈페이지로 이동</span></a></li>
						<li class="post"><a href="<?php echo $blogURL?>/owner/entry"><span><?php echo _t('글관리')?></span></a></li>
						<!--li class="keyword"><a href="<?php echo $blogURL?>/owner/keyword"><span><?php echo _t('키워드관리')?></span></a></li-->
						<li class="notice"><a href="<?php echo $blogURL?>/owner/notice"><span><?php echo _t('공지관리')?></span></a></li>				
						<li class="link"><a href="<?php echo $blogURL?>/owner/link"><span><?php echo _t('링크관리')?></span></a></li>
						<li class="skin selected"><a href="<?php echo $blogURL?>/owner/skin"><span><?php echo _t('스킨관리')?></span></a></li>
						<li class="trash"><a href="<?php echo $blogURL?>/owner/trash/trackback"><span><?php echo _t('휴지통')?></span></a></li>
						<li class="statistics"><a href="<?php echo $blogURL?>/owner/statistics/visitor"><span><?php echo _t('통계보기')?></span></a></li>
						<li class="setting"><a href="<?php echo $blogURL?>/owner/setting/blog"><span><?php echo _t('환경설정')?></span></a></li>
						<li class="reader"><a href="<?php echo $blogURL?>/owner/reader"><span><?php echo _t('리더')?></span></a></li>
						<li class="blog"><a href="<?php echo $blogURL?>/" title="<?php echo _t('블로그 메인으로 이동합니다.')?>"><span><?php echo _t('블로그 메인으로 이동')?></span></a></li>
						<li class="logout"><a href="<?php echo $blogURL?>/logout" title="<?php echo _t('로그아웃하고 블로그 메인으로 이동합니다.')?>"><span><?php echo _t('로그아웃')?></span></a></li>
					</ul>
				</div>
			</div>
			
			<hr class="hidden" />

