<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
$IV = array(
	'POST' => array(
		'tagId' => array ('int','min'=>1)	
	)
);

require ROOT . '/library/preprocessor.php';
requireStrictRoute();
requireModel('blog.entry');

/// Loads entry list.
$listWithPaging = getEntryListWithPagingByTag(getBlogId(), $_POST['tagId'], 1, 1);
$list = array('title' => $suri['value'], 'items' => $listWithPaging[0], 'count' => $listWithPaging[1]['total']);

$tagName = getTagById(getBlogId(),$_POST['tagId']);
$numberOfPosts = $list['count'];
$entryList = '<a href="'.$blogURL.'/owner/entry/?tagId='.$_POST['tagId'].'">'._f('"%1" 태그를 갖는 모든 글의 목록을 봅니다',$tagName).'</a>';
$result = array('error'=>0,
	'tagName'=> $tagName,
	'numberOfPosts' => $numberOfPosts,
	'entryList' => $entryList
	);
Respond::PrintResult($result);
?>