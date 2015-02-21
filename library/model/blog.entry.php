<?php
/// Copyright (c) 2004-2015, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getEntriesTotalCount($blogid) {
	$pool = DBModel::getInstance();
	$pool->reset('Entries');
	if(!doesHaveOwnership()) {
		$exList = getCategoryVisibilityList($blogid, 'private');
		$pool = DBModel::getInstance();
		$pool->reset('Entries');
		$pool->setQualifier('visibility','b',0);
		if (!empty($exList)) {
			$pool->setQualifier('category','hasnoneof',$exList);
		}
	}
	if(doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array('userid','eq',getUserId()),'OR',array('visibility','b',0));
	}
	$pool->setQualifier('blogid','eq',$blogid);
	$pool->setQualifier('draft','eq',0);
	$pool->setQualifier('category','beq',0);
	$count = $pool->getCount('id');
	return ($count ? $count : 0);
}

function getNoticesTotalCount($blogid) {
	return getSpecialEntriesTotalCount($blogid, -2);
}

function getPagesTotalCount($blogid) {
	return getSpecialEntriesTotalCount($blogid, -3);
}

function getSpecialEntriesTotalCount($blogid, $categoryId) {
	$pool = DBModel::getInstance();
	$pool->reset('Entries');
	if (doesHaveOwnership()) $pool->setQualifier('visibility','b',0);
	if(doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array('userid','eq',getUserId()),'OR',array('visibility','b',0));
	}
	$pool->setQualifier('blogid','eq',$blogid);
	$pool->setQualifier('draft','eq',0);
	$pool->setQualifier('category','eq',$categoryId);
	return $pool->getCount('*');
}

function getEntries($blogid, $attributes = '*', $condition = false, $order = array('published','DESC')) {
	$pool = DBModel::getInstance();
	$pool->reset("Entries");

	if (!empty($condition))
		$pool->setQualifierSet($condition);

	if (!doesHaveOwnership()) {
		$pool->setQualifier("visibility",">",0);
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("userid","eq",getUserId()),"OR",array("visibility",">",0));
	}
	$pool->setOrder($order[0],$order[1]);
	return $pool->getAll($attributes);
}


function getTemplates($blogid, $attributes = '*', $condition = false, $order = array('published','DESC')) {
	$pool = DBModel::getInstance();
	$pool->reset("Entries");

	if (!empty($condition))
		$pool->setQualifierSet($condition);
	$pool->setQualifier("blogid","eq",$blogid);
	$pool->setQualifier("category","eq",-4);
	$pool->setOrder($order[0],$order[1]);
	return $pool->getAll($attributes);
}

function getEntry($blogid, $id, $draft = false) {
	$pool = DBModel::getInstance();
	requireModel('blog.attachment');
	if($id == 0) {
		if (!doesHaveOwnership())
			return null;
		deleteAttachments($blogid, 0);
		return array('id'    => 0,
				'userid'     => 0,
				'draft'      => 0,
				'visibility' => 0,
				'starred'    => 1,
				'category'   => 0,
				'location'   => '',
				'latitude'   => null,
				'longitude'  => null,
				'title'      => '',
				'content'    => '',
				'contentformatter' => getDefaultFormatter(),
				'contenteditor'    => getDefaultEditor(),
				'acceptcomment'    => 1,
				'accepttrackback'  => 1,
				'published'  => time(),
				'slogan'     => '');
	}
	if ($draft) {
		$pool->init("Entries");
		$pool->setQualifier("blogid","eq",$blogid);
		$pool->setQualifier("id","eq",$id);
		$pool->setQualifier("draft","eq",1);
		$entry = $pool->getRow();
		if (!$entry)
			return null;
		if ($entry['published'] == 1)
			$entry['republish'] = true;
		else if ($entry['published'] != 0)
			$entry['appointed'] = $entry['published'];
		if ($id != 0) {
			$pool->init("Entries");
			$pool->setQualifier("blogid","eq",$blogid);
			$pool->setQualifier("id","eq",$id);
			$pool->setQualifier("draft","eq",0);
			$entry['published'] = $pool->getCell("published");
		}
		return $entry;
	} else {
		$pool->init("Entries");
		$pool->setQualifier("blogid","eq",$blogid);
		$pool->setQualifier("id","eq",$id);
		$pool->setQualifier("draft","eq",0);
		if (!doesHaveOwnership()) {
			$pool->setQualifier("visibility",">",0);
		}
		$entry = $pool->getRow();
		if (!$entry)
			return null;
		if ($entry['visibility'] < 0)
			$entry['appointed'] = $entry['published'];
		return $entry;
	}
}

function getUserIdOfEntry($blogid, $id, $draft = false) {
	$pool = DBModel::getInstance();
	$pool->reset('Entries');
	$pool->setQualifier('blogid','eq',$blogid);
	$pool->setQualifier('id','eq',$id);
	$result = $pool->getCell('userid');
	if(!empty($result)) return $result;
	else return null;
}

function getEntryAttributes($blogid, $id, $attributeNames) {
	if (stristr($attributeNames, "from") != false) // security check!
		return null;
	$pool = DBModel::getInstance();
	$pool->reset('Entries');
	$pool->setQualifier('blogid','eq',$blogid);
	$pool->setQualifier('id','eq',$id);
	$pool->setQualifier('draft','eq',0);
	if(!doesHaveOwnership()) $pool->setQualifier('visibility','b',0);
	return $pool->getRow($attributeNames);
}

function getEntryListWithPagingByCategory($blogid, $category, $page, $count) {
	if ($category === null)
		return array();
	if (!doesHaveOwnership() && getCategoryVisibility($blogid, $category) < 2 && $category != 0)
		return array();
	$ctx = Model_Context::getInstance();
	$pool = DBModel::getInstance();

	if ($category > 0) {
		$pool->init("Categories");
		$pool->setQualifier("blogid","eq",$blogid);
		$pool->setQualifier("parent","eq",$category);
		$categories = $pool->getColumn("id");
		array_push($categories, $category);
		if(!doesHaveOwnership())
			$categories = array_diff($categories, getCategoryVisibilityList($blogid, 'private'));
		$pool->init("Entries");
		$pool->setAlias("Entries","e");
		$pool->setQualifier("e.category","hasoneof",$categories);
		if (!doesHaveOwnership()) {
			$pool->setQualifier("e.visibility",">",0);
		}
	} else {
		$pool->init("Entries");
		$pool->setAlias("Entries","e");
		$pool->setQualifier("e.category",">=",0);
		if (!doesHaveOwnership()) {
			$pool->setQualifier("e.visibility",">",0);
			$pool = getPrivateCategoryExclusionQualifier($pool,$blogid);
		}
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("e.userid","eq",getUserId()),"OR",array("e.visibility",">",0));
	}
	$pool->setProjection("e.blogid","e.userid","e.id","e.title","e.comments","e.slogan","e.published");
	$pool->setOrder("e.published","desc");

	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".((!$ctx->getProperty('blog.useSloganOnCategory',true) && $ctx->getProperty('suri.id',null) != null) ? $ctx->getProperty('suri.id') : $ctx->getProperty('suri.value')));
}

function getEntryListWithPagingByAuthor($blogid, $author, $page, $count) {
	$ctx = Model_Context::getInstance();
	if ($author === null)
		return array();
	$userid = User::getUserIdByName($author);
	if(empty($userid)) return array();
	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.userid","eq",$userid);
	$pool->setQualifier("e.draft","eq",0);
	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
		$pool = getPrivateCategoryExclusionQualifier($pool,$blogid);
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("e.userid","eq",getUserId()),"OR",array("e.visibility",">",0));
	}
	$pool->setOrder("e.published","DESC");
	$pool->setProjection("e.blogid","e.userid","e.id","e.title","e.comments","e.slogan","e.published");
	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'));
}

function getEntryListWithPagingByTag($blogid, $tag, $page, $count) {
	$ctx = Model_Context::getInstance();

	if ($tag === null)
		return array(array(), array('url'=>'','prefix'=>'','postfix'=>''));

	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setAlias("TagRelations","t");
	$pool->join("TagRelation","left",array(
		array("e.id","eq","t.entry"),
		array("e.blogid","eq","t.blogid")
	));
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.category",">=",0);
	$pool->setQualifier("e.draft","eq",0);
	$pool->setQualifier("t.tag","eq",$tag,true);

	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
		$pool = getPrivateCategoryExclusionQualifier($pool,$blogid);
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("e.userid","eq",getUserId()),"OR",array("e.visibility",">",0));
	}

	$pool->setOrder("e.published","DESC");
	$pool->setProjection("e.blogid","e.userid","e.id","e.title","e.comments","e.slogan","e.published");

	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".((!Setting::getBlogSettingGlobal('useSloganOnTag',true) && ($ctx->getProperty('suri.id') != null)) ? $ctx->getProperty('suri.id') : $ctx->getProperty('suri.value')));
}

function getEntryListWithPagingByPeriod($blogid, $period, $page, $count) {
	$ctx = Model_Context::getInstance();

	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.category",">=",0);
	$pool->setQualifier("e.draft","eq",0);
	$pool->setQualifier("e.published",">=",getTimeFromPeriod($period));
	$pool->setQualifier("e.published","<",getTimeFromPeriod(addPeriod($period)));
	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
		$pool = getPrivateCategoryExclusionQualifier($pool,$blogid);
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("e.userid","eq",getUserId()),"OR",array("e.visibility",">",0));
	}

	$pool->setOrder("e.published","DESC");
	$pool->setProjection("e.blogid","e.userid","e.id","e.title","e.comments","e.slogan","e.published");

	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'));
}

function getEntryListWithPagingBySearch($blogid, $search, $page, $count) {
	$ctx = Model_Context::getInstance();
	$search = escapeSearchString($search);
	if (strlen($search) == 0) {
		return Paging::fetch(null, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'));
	}
	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.category",">=",0);
	$pool->setQualifier("e.draft","eq",0);
	$pool->setQualifier("e.published",">=",getTimeFromPeriod($period));
	$pool->setQualifier("e.published","<",getTimeFromPeriod(addPeriod($period)));
	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",1);
		$pool = getPrivateCategoryExclusionQualifier($pool,$blogid);
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("e.userid","eq",getUserId()),"OR",array("e.visibility",">",0));
	}

	$pool->setQualifierSet(array("e.title","like",$search,true),"OR",array("e.content","like",$search,true));
	$pool->setOrder("e.published","DESC");
	$pool->setProjection("e.blogid","e.userid","e.id","e.title","e.comments","e.slogan","e.published");
	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'));
}

function getEntriesWithPaging($blogid, $page, $count) {
	$pool = DBModel::getInstance();
	$pool->reset("Entries");
	$pool->setAlias("Entries","e");
	$pool->extend("Categories","LEFT", array(array('e.blogid','eq','c.blogid'),array('e.category','eq','c.id')));
	$pool->setAlias("Categories","c");

	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
		$pool->setQualifierSet(array('c.visibility','>',1),'OR',array('e.category','eq',0));
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array('e.userid','eq',getUserId()),'OR',array('e.visibility','eq',0));
	}
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.draft","eq",0);
	$pool->setQualifier("e.category",">=",0);
	$pool->setOrder("e.published","DESC");
	$pool->setProjection("e.*","c.label AS categoryLabel");
	return Paging::fetch($pool, $page, $count);
}

function getEntriesWithPagingByCategory($blogid, $category, $page, $count, $countItem) {
	$ctx = Model_Context::getInstance();
	if ($category === null)
		return Paging::fetch(null, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'));
	$pool = DBModel::getInstance();
	if ($category > 0) {
		$categories = getChildCategoryId($blogid, $category);
		array_push($categories, $category);
	}
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setAlias("Categories","c");
	$pool->join("Categories","left",array(
		array("e.category","eq","c.id"),
		array("e.blogid","eq","c.blogid")
	));
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.draft","eq",0);
	if ($category > 0) {
		$pool->setQualifier("e.category","hasoneof",$categories);
		if(!doesHaveOwnership()) {
			$pool->setQualifier("e.visibility",">",0);
		}
	} else {
		$pool->setQualifier("e.category","beq",0);
		if(!doesHaveOwnership()) {
			$pool->setQualifier("e.visibility",">",0);
			$pool->setQualifierSet(array("c.visibility","bigger",1),"OR",array("e.category","eq",0));
		}
	}
	$pool->setOrder("e.published","DESC");
	$pool->setProjection("e.*","c.label AS categoryLabel");
	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".((!$ctx->getProperty('blog.useSloganOnCategory',true) && $ctx->getProperty('suri.id',null)!= null) ? $ctx->getProperty('suri.id') : $ctx->getProperty('suri.value')),"?page=",$countItem);
}

function getEntriesWithPagingByTag($blogid, $tag, $page, $count, $countItem = null) {
	$ctx = Model_Context::getInstance();

	if ($tag === null)
		return Paging::fetch(null, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'));
	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setAlias("Categories","c");
	$pool->setAlias("TagRelations","t");
	$pool->join("Categories","left",array(
		array("e.blogid","eq","c.blogid"),
		array("e.category","eq","c.id")
	));
	$pool->join("TagRelations","left",array(
		array("e.id","eq","t.entry"),
		array("e.blogid","eq","t.blogid")
	));
	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility","bigger",0);
		$pool = getPrivateCategoryExclusionQualifier($pool,$blogid);
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("e.userid","eq",getUserId()),"OR",array("e.visibility","bigger",0));
	}
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.draft","eq",0);
	$pool->setQualifier("e.category",">=",0);
	$pool->setQualifier("t.tag","eq",$tag,true);
	$pool->setProjection("e.*", "c.label AS categoryLabel");
	$pool->setOrder("e.published","DESC");

	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".((!Setting::getBlogSettingGlobal('useSloganOnTag',true) && ($ctx->getProperty('suri.id')!= null)) ? $ctx->getProperty('suri.id') : $ctx->getProperty('suri.value')),"?page=", $countItem);
}

function getEntriesWithPagingByNotice($blogid, $page, $count, $countItem = null) {
	$ctx = Model_Context::getInstance();

	$pool = DBModel::getInstance();
	$pool->init("Entries");
	if (!doesHaveOwnership()) {
		$pool->setQualifier("visibility",">",1);
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("userid","eq",getUserId()),"OR",array("visibility","bigger",0));
	}
	$pool->setQualifier("blogid","eq",$blogid);
	$pool->setQualifier("category","eq",-2);
	$pool->setProjection("*");
	$pool->setOrder("published","DESC");

	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'),"?page=", $countItem);
}

function getEntriesWithPagingByPage($blogid, $page, $count, $countItem = null) {
	$ctx = Model_Context::getInstance();

	$pool = DBModel::getInstance();
	$pool->init("Entries");
	if (!doesHaveOwnership()) {
		$pool->setQualifier("visibility",">",0);
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("userid","eq",getUserId()),"OR",array("visibility","bigger",0));
	}
	$pool->setQualifier("blogid","eq",$blogid);
	$pool->setQualifier("category","eq",-3);
	$pool->setProjection("*");
	$pool->setOrder("published","DESC");

	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'),"?page=", $countItem);
}

function getEntriesWithPagingByPeriod($blogid, $period, $page, $count, $countItem = null) {
	$ctx = Model_Context::getInstance();

	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setAlias("Categories","c");
	$pool->join("Categories","left",array(
		array("e.blogid","eq","c.blogid"),
		array("e.category","eq","c.id")
	));
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.category",">=",0);
	$pool->setQualifier("e.draft","eq",0);
	$pool->setQualifier("e.published",">=",getTimeFromPeriod($period));
	$pool->setQualifier("e.published","<",getTimeFromPeriod(addPeriod($period)));
	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
		$pool->setQualifierSet(array("c.visibility",">",1),"OR",array("e.category","eq",0));
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("e.userid","eq",getUserId()),"OR",array("e.visibility",">",0));
	}

	$pool->setOrder("e.published","DESC");
	$pool->setProjection("e.*","c.label AS categoryLabel");

	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'), $countItem);
}

function getEntriesWithPagingBySearch($blogid, $search, $page, $count, $countItem) {
	$ctx = Model_Context::getInstance();

	$search = escapeSearchString($search);

	if(strlen($search) == 0)
		return Paging::fetch(null, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'));

	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setAlias("Categories","c");
	$pool->join("Categories","left",array(
		array("e.blogid","eq","c.blogid"),
		array("e.category","eq","c.id")
	));
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.category",">=",0);
	$pool->setQualifier("e.draft","eq",0);

	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
		$pool->setQualifierSet(array("c.visibility",">",1),"OR",array("e.category","eq",0));
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("e.userid","eq",getUserId()),"OR",array("e.visibility",">",0));
	}
	$pool->setQualifierSet(
		array("e.title","like",$search,true),
		"OR",
		array("e.content","like",$search,true)
	);
	$pool->setOrder("e.published","DESC");
	$pool->setProjection("e.*","c.label AS categoryLabel");
	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'),"?page=", $countItem);
}

function getEntriesWithPagingByAuthor($blogid, $author, $page, $count, $countItem = null) {
	$ctx = Model_Context::getInstance();

	$userid = User::getUserIdByName($author);

	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setAlias("Categories","c");
	$pool->join("Categories","left",array(
		array("e.blogid","eq","c.blogid"),
		array("e.category","eq","c.id")
	));
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.userid","eq",$userid);
	$pool->setQualifier("e.category",">=",0);
	$pool->setQualifier("e.draft","eq",0);
	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
		$pool->setQualifierSet(array("c.visibility",">",1),"OR",array("e.category","eq",0));
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array("e.userid","eq",getUserId()),"OR",array("e.visibility",">",0));
	}

	$pool->setOrder("e.published","DESC");
	$pool->setProjection("e.*","c.label AS categoryLabel");
	return Paging::fetch($pool, $page, $count, $ctx->getProperty('uri.folder')."/".$ctx->getProperty('suri.value'),"?page=", $countItem);
}

function getEntriesWithPagingForOwner($blogid, $category, $search, $page, $count, $visibility = null, $starred = null, $draft = null, $tag = null) {
	$pool = DBModel::getInstance();
	if ($category > 0) {
		$categories = getChildCategoryId($blogid, $category);
		array_push($categories, $category);
	}
	$pool->reset("Entries");
	$pool->setAlias("Entries","e");
	$pool->extend("Categories","LEFT", array(array('e.blogid','eq','c.blogid'),array('e.category','>','c.id')));
	$pool->setAlias("Categories","c");
	$pool->extend("Entries d","LEFT", array(array('e.blogid','eq','d.blogid'),array('e.id','eq','d.id'),array("d.draft","eq",1)));

	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
		$pool->setQualifierSet(array('c.visibility','>',1),'OR',array('e.category','eq',0));
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array('e.userid','eq',getUserId()),'OR',array('e.visibility','eq',0));
	}
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.draft","eq",0);
	$pool->setProjection("e.*","c.label AS categoryLabel", "d.id AS draft");
	$pool->setOrder("e.published","DESC");

	if( ! Acl::check("group.editors", "entry.list") ) {
		$pool->setQualifier("e.userid","eq",getUserId());
	}
	if ($category > 0) {
		$pool->setQualifier("e.category","hasoneof",$categories);
	} else if ($category == -3) {
		$pool->setQualifier("e.category","eq",0);
	} else if ($category == -5) {
		$pool->setQualifier("e.category",">=",-3);
	} else if ($category == 0) {
		$pool->setQualifier("e.category",">=",0);
	} else {
		$pool->setQualifier("e.category","eq",$category);
	}
	if(isset($visibility)) {
		if(Validator::isInteger($visibility,0,3)) {
			$pool->setQualifier("e.visibility","eq",$visibility);
		}
	}
	if(isset($starred)) {
		if(Validator::isInteger($starred,0,3)) {
			$pool->setQualifier("e.starred","eq",$starred);
		}
	}
	if (!empty($search)) {
		$search = escapeSearchString($search);
		$pool->setQualifierSet(array("e.title","like",$search,true),"OR",array("e.content","like",$search,true));
	}
	if (!empty($tag)) {
		$pool->join("TagRelations","left",array(
			array("e.id","eq","t.entry"),
			array("e.blogid","eq","t.blogid")
		));
		$pool->setAlias("TagRelations","t");
		$pool->setQualifier("t.tag","eq",$tag,true);
	}
	return Paging::fetch($pool, $page, $count);
}

function getEntryWithPaging($blogid, $id, $isSpecialEntry = false, $categoryId = false) {
	$ctx = Model_Context::getInstance();

	requireModel('blog.category');
	$entries = array();
	$paging = Paging::init($ctx->getProperty('uri.folder'), '/');

	if($categoryId !== false) {
		if($categoryId != 0) {	// Not a 'total' category.
			$childCategories = getChildCategoryId($blogid, $categoryId);
		}
	}

	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setAlias("Categories","c");
	$pool->join("Categories","left",array(
		array("e.blogid","eq","c.blogid"),
		array("e.category","eq","c.id")
	));
	if(!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
	}
	if (!($isSpecialEntry || doesHaveOwnership())) {
		$pool = getPrivateCategoryExclusionQualifier($pool,$blogid);
	}
	if (doesHaveOwnership() && !Acl::check('group.editors')) {
		$pool->setQualifierSet(array('e.userid','eq',getUserId()),'OR',array('e.visibility','>',0));
	}
	if ($isSpecialEntry) {
		if($isSpecialEntry == 'page') {
			$pool->setQualifier("e.category","=",-3);
		} else {
			$pool->setQualifier("e.category","=",-2);
		}
	} else {
		$pool->setQualifier("e.category",">=",0);
	}

	if(!empty($childCategories)) {
		$pool->setQualifier("e.category","hasoneof",$childCategories);
	} else {
		$pool->setQualifier("e.category","eq",$categoryId);
	}
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.id","eq",$id);
	$pool->setQualifier("e.draft","eq",0);

	$currentEntry = $pool->getRow("e.*, c.label AS categoryLabel");

	$pool->unsetQualifier("e.id");
	$pool->setOrder("e.published","DESC");
	$result = $pool->getColumn("e.id");

	if (!$result || !$currentEntry)
		return array($entries, $paging);
	if($categoryId !== false) {
		$paging['pages'] = $categoryId == 0 ? getEntriesTotalCount($blogid):getEntriesCountByCategory($blogid, $categoryId);
		$paging['postfix'] = '?category='.$categoryId;
	} else {
		$paging['pages'] = $isSpecialEntry ? ($isSpecialEntry == 'page' ? getPagesTotalCount($blogid) : getNoticesTotalCount($blogid)) : getEntriesTotalCount($blogid);
	}

	for ($i = 1; $entryId = array_shift($result); $i++) {
		if ($entryId != $id) {
			if (array_push($paging['before'], $entryId) > 4) {
				if ($i == 5)
					$paging['first'] = array_shift($paging['before']);
				else
					array_shift($paging['before']);
			}
			continue;
		}
		$paging['page'] = $i;
		array_push($entries, $currentEntry);
		$paging['after'] = array();
		for ($i++; (count($paging['after']) < 4) && ($entryId = array_shift($result)); $i++)
			array_push($paging['after'], $entryId);
		if ($i < $paging['pages']) {
			while ($entryId = array_shift($result))
				$paging['last'] = $entryId;
		}
		if (count($paging['before']) > 0)
			$paging['prev'] = $paging['before'][count($paging['before']) - 1];
		if (isset($paging['after'][0]))
			$paging['next'] = $paging['after'][0];
		return array($entries, $paging);
	}
	$paging['page'] = $paging['pages'] + 1;
	return array($entries, $paging);
}

function getEntryWithPagingBySlogan($blogid, $slogan, $isSpecialEntry = false, $categoryId = false) {
	requireModel('blog.category');
	$entries = array();
	$ctx = Model_Context::getInstance();
	$paging = $isSpecialEntry ? ( $isSpecialEntry == 'page' ? Paging::init($ctx->getProperty('uri.blog')."/page", '/'): Paging::init($ctx->getProperty('uri.blog')."/notice", '/')) : Paging::init($ctx->getProperty('uri.blog')."/entry", '/');
	$visibility = doesHaveOwnership() ? '' : 'AND e.visibility > 0';
	$visibility .= ($isSpecialEntry || doesHaveOwnership()) ? '' : getPrivateCategoryExclusionQuery($blogid);
	$visibility .= (doesHaveOwnership() && !Acl::check('group.editors')) ? ' AND (e.userid = '.getUserId().' OR e.visibility > 0)' : '';
	$category = $isSpecialEntry ? ( $isSpecialEntry == 'page' ? 'e.category = -3' : 'e.category = -2' ) : 'e.category >= 0';
	if($categoryId !== false) {
		if(!$categoryId == 0) {	// Not a 'total' category.
			$childCategories = getChildCategoryId($blogid, $categoryId);
			if(!empty($childCategories)) {
				$category = 'e.category IN ('.$categoryId.','.implode(",",$childCategories).')';
			} else {
				$category = 'e.category = '.$categoryId;
			}
		}
	}
	$currentEntry = POD::queryRow("SELECT e.*, c.label AS categoryLabel
		FROM ".$ctx->getProperty('database.prefix')."Entries e
		LEFT JOIN ".$ctx->getProperty('database.prefix')."Categories c ON e.blogid = c.blogid AND e.category = c.id
		WHERE e.blogid = $blogid
			AND e.slogan = '".POD::escapeString($slogan)."'
			AND e.draft = 0 $visibility AND $category");

	$result = POD::queryAll("SELECT e.id, e.slogan
		FROM ".$ctx->getProperty('database.prefix')."Entries e
		LEFT JOIN ".$ctx->getProperty('database.prefix')."Categories c ON e.blogid = c.blogid AND e.category = c.id
		WHERE e.blogid = $blogid
			AND e.draft = 0 $visibility AND $category
		ORDER BY e.published DESC");
	if (!$result || !$currentEntry)
		return array($entries, $paging);

	if($categoryId !== false) {
		$paging['pages'] = $categoryId == 0 ? getEntriesTotalCount($blogid):getEntriesCountByCategory($blogid, $categoryId);
		$paging['postfix'] = '?category='.$categoryId;
	} else {
		$paging['pages'] = $isSpecialEntry ? ($isSpecialEntry == 'page' ? getPagesTotalCount($blogid) : getNoticesTotalCount($blogid)) : getEntriesTotalCount($blogid);
	}

	for ($i = 1; $entry = array_shift($result); $i++) {
		if ($entry['slogan'] != $slogan) {
			if (array_push($paging['before'], $entry['slogan']) > 4) {
				if ($i == 5)
					$paging['first'] = array_shift($paging['before']);
				else
					array_shift($paging['before']);
			}
			continue;
		}
		$paging['page'] = $i;
		array_push($entries, $currentEntry);
		$paging['after'] = array();
		for ($i++; (count($paging['after']) < 4) && ($entry = array_shift($result)); $i++)
			array_push($paging['after'], $entry['slogan']);
		if ($i < $paging['pages']) {
			while ($entry = array_shift($result))
				$paging['last'] = $entry['slogan'];
		}
		if (count($paging['before']) > 0)
			$paging['prev'] = $paging['before'][count($paging['before']) - 1];
		if (isset($paging['after'][0]))
			$paging['next'] = $paging['after'][0];
		return array($entries, $paging);
	}
	$paging['page'] = $paging['pages'] + 1;
	return array($entries, $paging);
}

function getSlogan($slogan) {
	$slogan = preg_replace('/-+/', ' ', $slogan);
	$slogan = preg_replace('@[!-/:-\@\[-\^`{-~]+@', '', $slogan);
	$slogan = preg_replace('/\s+/', '-', $slogan);
	$slogan = trim($slogan, '-');
	return strlen($slogan) > 0 ? $slogan : 'XFile';
}

function getRecentEntries($blogid) {
	$ctx = Model_Context::getInstance();
	$pool = DBModel::getInstance();
	$pool->init("Entries");
	$pool->setAlias("Entries","e");
	$pool->setQualifier("e.blogid","eq",$blogid);
	$pool->setQualifier("e.draft","eq",0);
	$pool->setQualifier("e.category","beq",0);
	if (!doesHaveOwnership()) {
		$pool->setQualifier("e.visibility",">",0);
		$pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
	}
	$pool->setOrder("e.published","DESC");
	$pool->setLimit($ctx->getProperty('skin.entriesOnRecent'));
	$result = $pool->getAll("e.id, e.userid, e.title, e.slogan, e.comments, e.published");
 	if($result) {
 		return $result;
 	} else {
 		return array();
	}
}

function addEntry($blogid, $entry, $userid = null) {
	global $gCacheStorage;
	$ctx = Model_Context::getInstance();

	requireModel("blog.attachment");
	requireModel("blog.feed");
	requireModel("blog.category");
	requireModel("blog.tag");
	requireModel("blog.locative");

	if(empty($userid)) $entry['userid'] = getUserId();
	else $entry['userid'] = $userid;
	$entry['title'] = Utils_Unicode::lessenAsEncoding(trim($entry['title']), 255);
	$entry['location'] = Utils_Unicode::lessenAsEncoding(trim($entry['location']), 255);
	$entry['slogan'] = array_key_exists('slogan', $entry) ? trim($entry['slogan']) : '';

	if((empty($entry['slogan']))||($entry['category'] == -1)) {
		$slogan = $slogan0 = getSlogan($entry['title']);
	} else {
		$slogan = $slogan0 = getSlogan($entry['slogan']);
	}

	$slogan = POD::escapeString(Utils_Unicode::lessenAsEncoding($slogan, 255));
	$title = POD::escapeString($entry['title']);

	if($entry['category'] == -1) {
		if($entry['visibility'] == 1 || $entry['visibility'] == 3)
			return false;
		if(POD::queryCell("SELECT count(*) FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND draft = 0 AND title = '$title' AND category = -1") > 0)
			return false;
	}

	if ($entry['category'] < 0) {
		if ($entry['visibility'] == 1) $entry['visibility'] = 0;
		if ($entry['visibility'] == 3) $entry['visibility'] = 2;
	}
	if ($entry['category'] == -4) {
		$entry['visibility'] = 0;
	}

	$result = POD::queryCount("SELECT slogan FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
	for ($i = 1; $result > 0; $i++) {
		if ($i > 1000)
			return false;
		$slogan = POD::escapeString(Utils_Unicode::lessenAsEncoding($slogan0, 245) . '-' . $i);
		$result = POD::queryCount("SELECT slogan FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
	}
	$userid = $entry['userid'];
	$content = POD::escapeString($entry['content']);
	$contentformatter = POD::escapeString($entry['contentformatter']);
	$contenteditor = POD::escapeString($entry['contenteditor']);
	$password = POD::escapeString(generatePassword());
	$location = POD::escapeString($entry['location']);
	$latitude = isset($entry['latitude']) && !is_null($entry['latitude']) ? $entry['latitude'] : 'NULL';
	$longitude = isset($entry['longitude']) && !is_null($entry['longitude']) ? $entry['longitude'] : 'NULL';
	if (!isset($entry['firstEntry']) && isset($entry['published']) && is_numeric($entry['published']) && ($entry['published'] >= 2)) {
		$published = $entry['published'];
		$entry['visibility'] = 0 - $entry['visibility'];
		if($entry['visibility'] < 0) {
			$closestReservedTime = Setting::getBlogSettingGlobal('closestReservedPostTime',INT_MAX);
			if($published < $closestReservedTime) {
				Setting::setBlogSetting('closestReservedPostTime',$published,true);
			}
		}
	} else {
		$published = 'UNIX_TIMESTAMP()';
	}

	$currentMaxId = POD::queryCell("SELECT MAX(id) FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND draft = 0");
	if(!empty($currentMaxId) && $currentMaxId > 0) {
		$id = $currentMaxId + 1;
	} else {
		$id = 1;
	}
	$result = POD::query("INSERT INTO ".$ctx->getProperty('database.prefix')."Entries
			(blogid, userid, id, draft, visibility, starred, category, title, slogan, content, contentformatter,
			 contenteditor, location, latitude, longitude, password, acceptcomment, accepttrackback, published, created, modified,
			 comments, trackbacks, pingbacks)
			VALUES (
			$blogid,
			$userid,
			$id,
			0,
			{$entry['visibility']},
			{$entry['starred']},
			{$entry['category']},
			'$title',
			'$slogan',
			'$content',
			'$contentformatter',
			'$contenteditor',
			'$location',
			$latitude,
			$longitude,
			'$password',
			{$entry['acceptcomment']},
			{$entry['accepttrackback']},
			$published,
			UNIX_TIMESTAMP(),
			UNIX_TIMESTAMP(),
			0,
			0,
			0)");
	if (!$result)
		return false;
	POD::query("UPDATE ".$ctx->getProperty('database.prefix')."Attachments SET parent = $id WHERE blogid = $blogid AND parent = 0");
	POD::query("DELETE FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $id AND draft = 1");
	updateCategoryByEntryId($blogid, $id, 'add');
	CacheControl::flushEntry($id);
	clearFeed();

	if ($entry['visibility'] == 3)
		syndicateEntry($id, 'create');
	if ($entry['visibility'] >= 2) {
		CacheControl::flushAuthor($userid);
		CacheControl::flushDBCache('entry');
		$gCacheStorage->purge();
	}

	if (!empty($entry['tag'])) {
		$tags = getTagsWithEntryString($entry['tag']);
		Tag::addTagsWithEntryId($blogid, $id, $tags);
	}
	return $id;
}

function updateEntry($blogid, $entry, $updateDraft = 0) {
	global $gCacheStorage;
	$ctx = Model_Context::getInstance();

	requireModel('blog.tag');
	requireModel('blog.locative');
	requireModel('blog.attachment');
	requireModel('blog.category');
	requireModel('blog.feed');
	requireComponent('Textcube.Data.Tag');

	if($entry['id'] == 0) return false;

	$oldEntry = POD::queryRow("SELECT *
		FROM ".$ctx->getProperty('database.prefix')."Entries
		WHERE blogid = $blogid
		AND id = {$entry['id']}
		AND draft = 0");
	if(empty($oldEntry)) return false;

	if(empty($entry['userid'])) $entry['userid'] = getUserId();
	$entry['title'] = Utils_Unicode::lessenAsEncoding(trim($entry['title']));
	$entry['location'] = Utils_Unicode::lessenAsEncoding(trim($entry['location']));
	$entry['slogan'] = array_key_exists('slogan', $entry) ? trim($entry['slogan']) : '';
	if(empty($entry['slogan'])) {
		$slogan = $slogan0 = getSlogan($entry['title']);
	} else {
		$slogan = $slogan0 = getSlogan($entry['slogan']);
	}
	$slogan = POD::escapeString(Utils_Unicode::lessenAsEncoding($slogan, 255));
	$title = POD::escapeString($entry['title']);

	if($entry['category'] == -1) {
		if($entry['visibility'] == 1 || $entry['visibility'] == 3)
			return false;
		if(POD::queryCell("SELECT count(*)
			FROM ".$ctx->getProperty('database.prefix')."Entries
			WHERE blogid = $blogid
				AND id <> {$entry['id']}
				AND draft = 0
				AND title = '$title'
				AND category = -1") > 0)
			return false;
	}

	if ($entry['category'] < 0) {
		if ($entry['visibility'] == 1) $entry['visibility'] = 0;
		if ($entry['visibility'] == 3) $entry['visibility'] = 2;
	}
	if ($entry['category'] == -4) {
		$entry['visibility'] = 0;
	}

	$result = POD::queryCount("SELECT slogan
		FROM ".$ctx->getProperty('database.prefix')."Entries
		WHERE blogid = $blogid
		AND slogan = '$slogan'
		AND id = {$entry['id']}
		AND draft = 0
		LIMIT 1");
	if ($result == 0) { // if changed
		$result = POD::queryCount("SELECT slogan FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
		for ($i = 1; $result > 0; $i++) {
			if ($i > 1000)
				return false;
			$slogan = POD::escapeString(Utils_Unicode::lessenAsEncoding($slogan0, 245) . '-' . $i);
			$result = POD::queryCount("SELECT slogan FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
		}
	}
	$tags = getTagsWithEntryString($entry['tag']);
	Tag::modifyTagsWithEntryId($blogid, $entry['id'], $tags);

	$location = POD::escapeString($entry['location']);
	$latitude = isset($entry['latitude']) && !is_null($entry['latitude']) ? $entry['latitude'] : 'NULL';
	$longitude = isset($entry['longitude']) && !is_null($entry['longitude']) ? $entry['longitude'] : 'NULL';
	$content = POD::escapeString($entry['content']);
	$contentformatter = POD::escapeString($entry['contentformatter']);
	$contenteditor = POD::escapeString($entry['contenteditor']);
	switch ($entry['published']) {
		case 0:
			$published = 'published';
			break;
		case 1:
			$published = 'UNIX_TIMESTAMP()';
			break;
		default:
			$published = $entry['published'];
			$entry['visibility'] = 0 - $entry['visibility'];
			if($entry['visibility'] < 0) {
				$closestReservedTime = Setting::getBlogSettingGlobal('closestReservedPostTime',9999999999);
				if($published < $closestReservedTime) {
					Setting::setBlogSetting('closestReservedPostTime',$published,true);
				}
			}
			break;
	}

	$result = POD::query("UPDATE ".$ctx->getProperty('database.prefix')."Entries
			SET
				userid             = {$entry['userid']},
				visibility         = {$entry['visibility']},
				starred            = {$entry['starred']},
				category           = {$entry['category']},
				draft              = 0,
				location           = '$location',
				latitude           = $latitude,
				longitude          = $longitude,
				title              = '$title',
				content            = '$content',
				contentformatter   = '$contentformatter',
				contenteditor      = '$contenteditor',
				slogan             = '$slogan',
				acceptcomment      = {$entry['acceptcomment']},
				accepttrackback    = {$entry['accepttrackback']},
				published          = $published,
				modified           = UNIX_TIMESTAMP()
			WHERE blogid = $blogid AND id = {$entry['id']} AND draft = $updateDraft");
	if ($result)
		@POD::query("DELETE FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = {$entry['id']} AND draft = 1");

	updateCategoryByEntryId($blogid, $entry['id'], 'update',
		array('category'=>array($oldEntry['category'],$entry['category']),
			'visibility'=>array($oldEntry['visibility'],$entry['visibility'])
		));

	CacheControl::flushEntry($entry['id']);
	$gCacheStorage->purge();
	if ($entry['visibility'] == 3)
		syndicateEntry($entry['id'], 'modify');
	POD::query("UPDATE ".$ctx->getProperty('database.prefix')."Attachments SET parent = {$entry['id']} WHERE blogid = $blogid AND parent = 0");
	if ($entry['visibility'] >= 2)
		clearFeed();
	return $result ? $entry['id'] : false;
}

function saveDraftEntry($blogid, $entry) {
	$ctx = Model_Context::getInstance();

	requireModel('blog.tag');
	requireModel('blog.locative');
	requireModel('blog.attachment');
	requireModel('blog.category');
	requireModel('blog.feed');
	requireComponent('Textcube.Data.Tag');

	if($entry['id'] == 0) return -11;

	$draftCount = POD::queryCell("SELECT count(*) FROM ".$ctx->getProperty('database.prefix')."Entries
		WHERE blogid = $blogid
			AND id = ".$entry['id']."
			AND draft = 1");

	if($draftCount > 0) { // draft가 없으면 insert를, 있으면 update를.
		$doUpdate = true;
	} else {
		$doUpdate = false;
	}
	// 원 글을 읽어서 몇가지 정보를 보존한다. 원래 글이 없는 경우 draft는 저장될 수 없다.
	$origEntry = POD::queryRow("SELECT created, comments, trackbacks, pingbacks, password
		FROM ".$ctx->getProperty('database.prefix')."Entries
		WHERE blogid = $blogid
			AND id = ".$entry['id']."
			AND draft = 0");
	if(empty($origEntry)) return -12;

	$created = $origEntry['created'];
	$comments = $origEntry['comments'];
	$trackbacks = $origEntry['trackbacks'];
	$pingbacks = $origEntry['pingbacks'];
	$password = $origEntry['password'];

	if(empty($entry['userid'])) $entry['userid'] = getUserId();
	$entry['title'] = Utils_Unicode::lessenAsEncoding(trim($entry['title']));
	$entry['location'] = Utils_Unicode::lessenAsEncoding(trim($entry['location']));
	$entry['slogan'] = array_key_exists('slogan', $entry) ? trim($entry['slogan']) : '';
	if(empty($entry['slogan'])) {
		$slogan = $slogan0 = getSlogan($entry['title']);
	} else {
		$slogan = $slogan0 = getSlogan($entry['slogan']);
	}
	$slogan = POD::escapeString(Utils_Unicode::lessenAsEncoding($slogan, 255));
	$title = POD::escapeString($entry['title']);

	if($entry['category'] == -1) {
		if($entry['visibility'] == 1 || $entry['visibility'] == 3)
			return false;
		if(POD::queryCell("SELECT count(*)
			FROM ".$ctx->getProperty('database.prefix')."Entries
			WHERE blogid = $blogid
				AND id <> {$entry['id']}
				AND draft = 0
				AND title = '$title'
				AND category = -1") > 0)
			return -13;
	}

	if ($entry['category'] < 0) {
		if ($entry['visibility'] == 1) $entry['visibility'] = 0;
		if ($entry['visibility'] == 3) $entry['visibility'] = 2;
	}
	if ($entry['category'] == -4) {
		$entry['visibility'] = 0;
	}

	$result = POD::queryCount("SELECT slogan
		FROM ".$ctx->getProperty('database.prefix')."Entries
		WHERE blogid = $blogid
		AND slogan = '$slogan'
		AND id = {$entry['id']}
		AND draft = 0 LIMIT 1");
	if ($result == 0) { // if changed
		$result = POD::queryExistence("SELECT slogan FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
		for ($i = 1; $result != false; $i++) {
			if ($i > 1000)
				return false;
			$slogan = POD::escapeString(Utils_Unicode::lessenAsEncoding($slogan0, 245) . '-' . $i);
			$result = POD::queryExistence("SELECT slogan FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND slogan = '$slogan' AND draft = 0 LIMIT 1");
		}
	}
	$tags = getTagsWithEntryString($entry['tag']);
	Tag::modifyTagsWithEntryId($blogid, $entry['id'], $tags);

	$location = POD::escapeString($entry['location']);
	$latitude = isset($entry['latitude']) && !is_null($entry['latitude']) ? $entry['latitude'] : 'NULL';
	$longitude = isset($entry['longitude']) && !is_null($entry['longitude']) ? $entry['longitude'] : 'NULL';
	$content = POD::escapeString($entry['content']);
	$contentformatter = POD::escapeString($entry['contentformatter']);
	$contenteditor = POD::escapeString($entry['contenteditor']);
	switch ($entry['published']) {
		case 0:
			$published = 'published';
			break;
		case 1:
			$published = 'UNIX_TIMESTAMP()';
			break;
		default:
			$published = $entry['published'];
			$entry['visibility'] = 0 - $entry['visibility'];
			break;
	}

	if($doUpdate) {
		$result = POD::query("UPDATE ".$ctx->getProperty('database.prefix')."Entries
			SET
				userid             = {$entry['userid']},
				visibility         = {$entry['visibility']},
				starred            = {$entry['starred']},
				category           = {$entry['category']},
				draft              = 1,
				location           = '$location',
				latitude           = $latitude,
				longitude          = $longitude,
				title              = '$title',
				content            = '$content',
				contentformatter   = '$contentformatter',
				contenteditor      = '$contenteditor',
				slogan             = '$slogan',
				acceptcomment      = {$entry['acceptcomment']},
				accepttrackback    = {$entry['accepttrackback']},
				published          = $published,
				modified           = UNIX_TIMESTAMP()
			WHERE blogid = $blogid AND id = {$entry['id']} AND draft = 1");
	} else {
		$result = POD::query("INSERT INTO ".$ctx->getProperty('database.prefix')."Entries
			(blogid, userid, id, draft, visibility, starred, category, title, slogan, content, contentformatter,
			 contenteditor, location, password, acceptcomment, accepttrackback, published, created, modified,
			 comments, trackbacks, pingbacks)
			VALUES (
			$blogid,
			{$entry['userid']},
			{$entry['id']},
			1,
			{$entry['visibility']},
			{$entry['starred']},
			{$entry['category']},
			'$title',
			'$slogan',
			'$content',
			'$contentformatter',
			'$contenteditor',
			'$location',
			'$password',
			{$entry['acceptcomment']},
			{$entry['accepttrackback']},
			$published,
			$created,
			UNIX_TIMESTAMP(),
			$comments,
			$trackbacks,
			$pingbacks)");
	}
	return $result ? $entry['id'] : false;
}

function updateRemoteResponsesOfEntry($blogid, $id) {
	$ctx = Model_Context::getInstance();

	$trackbacks = POD::queryCell("SELECT COUNT(*) FROM ".$ctx->getProperty('database.prefix')."RemoteResponses WHERE blogid = $blogid AND entry = $id AND isfiltered = 0 AND responsetype = 'trackback'");
	$pingbacks  = POD::queryCell("SELECT COUNT(*) FROM ".$ctx->getProperty('database.prefix')."RemoteResponses WHERE blogid = $blogid AND entry = $id AND isfiltered = 0 AND responsetype = 'pingback'");
	if ($trackbacks === null || $pingbacks === null)
		return false;
	return POD::execute("UPDATE ".$ctx->getProperty('database.prefix')."Entries SET trackbacks = $trackbacks, pingbacks = $pingbacks WHERE blogid = $blogid AND id = $id");
}

function deleteEntry($blogid, $id) {
	global $gCacheStorage;
	$ctx = Model_Context::getInstance();

	requireModel("blog.feed");
	requireModel("blog.category");
	requireModel("blog.attachment");
	requireModel("blog.tag");
	requireComponent("Textcube.Data.Tag");

	$target = getEntry($blogid, $id);
	if (is_null($target)) return false;
	if (POD::queryCell("SELECT visibility FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $id") == 3)
		syndicateEntry($id, 'delete');
	CacheControl::flushEntry($id);
	CacheControl::flushDBCache('entry');
	CacheControl::flushDBCache('comment');
	CacheControl::flushDBCache('trackback');
	$gCacheStorage->purge();
	$result = POD::queryCount("DELETE FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $id");
	if ($result > 0) {
		$result = POD::query("DELETE FROM ".$ctx->getProperty('database.prefix')."Comments WHERE blogid = $blogid AND entry = $id");
		$result = POD::query("DELETE FROM ".$ctx->getProperty('database.prefix')."RemoteResponses WHERE blogid = $blogid AND entry = $id");
		$result = POD::query("DELETE FROM ".$ctx->getProperty('database.prefix')."RemoteResponseLogs WHERE blogid = $blogid AND entry = $id");
		updateCategoryByEntryId($blogid, $id, 'delete', array('entry' => $target));
		deleteAttachments($blogid, $id);

		Tag::deleteTagsWithEntryId($blogid, $id);
		clearFeed();
		fireEvent('DeletePost', $id, null);
		return true;
	}
	return false;
}

function changeCategoryOfEntries($blogid, $entries, $category) {
	$ctx = Model_Context::getInstance();

	requireModel("blog.category");
	requireModel("blog.feed");

	$targets = array_unique(preg_split('/,/', $entries, -1, PREG_SPLIT_NO_EMPTY));
	$effectedCategories = array();
	if ( count($targets)<1 || !is_numeric($category) )
		return false;

	if ($category == -1) { // Check Keyword duplication
		foreach($targets as $entryId) {
			$title = POD::queryCell("SELECT title FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $entryId AND draft = 0");
			if (is_null($title)) return false;
			if (POD::queryExistence("SELECT id FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id <> $entryId AND draft = 0 AND title = '$title' AND category = -1") == true) return false;
		}
	} else {
		$parent = getParentCategoryId($blogid, $categoryId);
		array_push($effectedCategories, $parent);
	}

	foreach($targets as $entryId) {
		list($effectedCategoryId, $oldVisibility) = POD::queryRow("SELECT category, visibility FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $entryId AND draft = 0");
		$visibility = 	$oldVisibility;
		if ($category < 0) {
			if ($visibility == 1) $visibility = 0;
			if ($visibility == 3) $visibility = 2;
		}

		if (($oldVisibility == 3) && ($visibility != 3))
			syndicateEntry($entryId, 'delete');

		POD::execute("UPDATE ".$ctx->getProperty('database.prefix')."Entries SET category = $category , visibility = $visibility WHERE blogid = $blogid AND id = $entryId");

		if (!in_array($effectedCategoryId, $effectedCategories)) {
			array_push($effectedCategories, $effectedCategoryId);
			$parent = getParentCategoryId($blogid, $effectedCategoryId);
			if(!is_null($parent)) array_push($effectedCategories, $parent);
		}
	}
	$effected = false;
	foreach($effectedCategories as $effectedCategory) {
		updateEntriesOfCategory($blogid, $effectedCategory);
		$effected = true;
	}

	if(updateEntriesOfCategory($blogid, $category)) {
		if ($effected) {
			clearFeed();
			CacheControl::flushDBCache('comment');
			CacheControl::flushDBCache('trackback');
		}
		return true;
	}
	return false;
}

function changeAuthorOfEntries($blogid, $entries, $userid) {
	$ctx = Model_Context::getInstance();

	requireModel("blog.feed");

	$targets = array_unique(preg_split('/,/', $entries, -1, PREG_SPLIT_NO_EMPTY));
	foreach($targets as $entryId) {
		POD::execute("UPDATE ".$ctx->getProperty('database.prefix')."Entries SET userid = $userid WHERE blogid = $blogid AND id = $entryId");
	}
	clearFeed();
	CacheControl::flushAuthor();
	return true;
}

function setEntryVisibility($id, $visibility) {
	$ctx = Model_Context::getInstance();

	requireModel("blog.feed");
	requireModel("blog.category");
	$blogid = getBlogId();
	if (($visibility < 0) || ($visibility > 3))
		return false;
	list($oldVisibility, $category) = POD::queryRow("SELECT visibility, category FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $id AND draft = 0");

	if ($category < 0) {
		if ($visibility == 1) $visibility = 0;
		if ($visibility == 3) $visibility = 2;
	}

	if ($oldVisibility === null)
		return false;
	if ($visibility == $oldVisibility)
		return true;

	if ($oldVisibility == 3)
		syndicateEntry($id, 'delete');
	else if ($visibility == 3) {
		if (!syndicateEntry($id, 'create')) {
			POD::query("UPDATE ".$ctx->getProperty('database.prefix')."Entries
				SET visibility = $oldVisibility,
					modified = UNIX_TIMESTAMP()
				WHERE blogid = $blogid AND id = $id");
			return false;
		}
	}

	$result = POD::queryCount("UPDATE ".$ctx->getProperty('database.prefix')."Entries
		SET visibility = $visibility,
			modified = UNIX_TIMESTAMP()
		WHERE blogid = $blogid AND id = $id");
	if (!$result)		// Error.
		return false;
	if ($result == 0)	// Not changed.
		return true;

	if ($category >= 0) {
		if ((($oldVisibility >= 2) && ($visibility < 2)) || (($oldVisibility < 2) && ($visibility >= 2)))
			clearFeed();
		if ((($oldVisibility == 3) && ($visibility <= 2)) || (($oldVisibility <= 2) && ($visibility == 3)))
			clearFeed();
		if ($category > 0)
			updateCategoryByEntryId($blogid, $id, 'update',$parameters = array('visibility' => array($oldVisibility, $visibility)));
//			updateEntriesOfCategory($blogid, $category);
	}
	CacheControl::flushEntry($id);
	CacheControl::flushDBCache('entry');
	CacheControl::flushDBCache('comment');
	CacheControl::flushDBCache('trackback');
	fireEvent('ChangeVisibility', $visibility, $id);
	return true;
}

function protectEntry($id, $password) {
	$ctx = Model_Context::getInstance();

	$password = POD::escapeString($password);
	$result = POD::queryCount("UPDATE ".$ctx->getProperty('database.prefix')."Entries SET password = '$password', modified = UNIX_TIMESTAMP() WHERE blogid = ".getBlogId()." AND id = $id AND visibility = 1");
	if($result > 0) {
		CacheControl::flushEntry($id);
		CacheControl::flushDBCache('entry');
		CacheControl::flushDBCache('comment');
		CacheControl::flushDBCache('trackback');
		return true;
	} else return false;
}

function syndicateEntry($id, $mode) {
	$context = Model_Context::getInstance();
	$pool = DBModel::getInstance();

	$pool->reset('XMLRPCPingSettings');
	$pool->setQualifier('blogid','equals',$context->getProperty('blog.id'));
	$sites = $pool->getAll('url,pingtype');

	$entry = getEntry($context->getProperty('blog.id'), $id);
	if (is_null($entry)) return false;

	if(!empty($sites)) {
		foreach ($sites as $site) {
			$rpc = new XMLRPC();
			$rpc->url = $site['url'];
			$result[$site['url']] = $rpc->call($context->getProperty('blog.title'), $context->getProperty('uri.default'));
		}
	}
	if($mode == 'create') {
		fireEvent('CreatePostSyndicate', $id, $entry);
	} else if($mode == 'modify') {
		fireEvent('ModifyPostSyndicate', $id, $entry);
	} else if($mode == 'delete') {
		fireEvent('DeletePostSyndicate', $id, $entry);
	}
	return true;
}

function publishEntries() {
	$ctx = Model_Context::getInstance();

	$blogid = getBlogId();
	$closestReservedTime = Setting::getBlogSettingGlobal('closestReservedPostTime',INT_MAX);
	if($closestReservedTime < Timestamp::getUNIXtime()) {
		$entries = POD::queryAll("SELECT id, visibility, category
			FROM ".$ctx->getProperty('database.prefix')."Entries
			WHERE blogid = $blogid AND draft = 0 AND visibility < 0 AND published < UNIX_TIMESTAMP()");
		if (count($entries) == 0)
			return;
		foreach ($entries as $entry) {
			$result = POD::query("UPDATE ".$ctx->getProperty('database.prefix')."Entries
				SET visibility = 0
				WHERE blogid = $blogid AND id = {$entry['id']} AND draft = 0");
			if ($entry['visibility'] == -3) {
				if ($result && setEntryVisibility($entry['id'], 2)) {
					$updatedEntry = getEntry($blogid, $entry['id']);
					if (!is_null($updatedEntry)) {
						fireEvent('UpdatePost', $entry['id'], $updatedEntry);
						setEntryVisibility($entry['id'], 3);
					}
				}
			} else {
				if ($result) {
					setEntryVisibility($entry['id'], abs($entry['visibility']));
					$updatedEntry = getEntry($blogid, $entry['id']);
					if (!is_null($updatedEntry)) {
						fireEvent('UpdatePost', $entry['id'], $updatedEntry);
					}
				}
			}
		}
		$newClosestTime = POD::queryCell("SELECT min(published)
			FROM ".$ctx->getProperty('database.prefix')."Entries
			WHERE blogid = $blogid AND draft = 0 AND visibility < 0 AND published > UNIX_TIMESTAMP()");
		if(!empty($newClosestTime)) Setting::setBlogSettingGlobal('closestReservedPostTime',$newClosestTime);
		else Setting::setBlogSettingGlobal('closestReservedPostTime',INT_MAX);
	}
}

function getTagsWithEntryString($entryTag) {
	$ctx = Model_Context::getInstance();

	$tags = explode(',', $entryTag);

	$ret = array();

	foreach ($tags as $tag) {
		$tag = Utils_Unicode::lessenAsEncoding($tag, 255, '');
		$tag = str_replace('&quot;', '"', $tag);
		$tag = str_replace('&#39;', '\'', $tag);
		$tag = preg_replace('/ +/', ' ', $tag);
		$tag = preg_replace('/[\x00-\x1f]|[\x7f]/', '', $tag);
		$tag = preg_replace('/^(-|\s)+/', '', $tag);
		$tag = preg_replace('/(-|\s)+$/', '', $tag);
		$tag = trim($tag);

		array_push($ret, $tag);
	}

	return $ret;
}

function getEntryVisibilityName($visibility) {
	switch (abs($visibility)) {
		case 0:
			return _text('비공개');
		case 1:
			return _text('보호');
		case 2:
			return _text('공개');
		case 3:default:
			return _text('발행');
	}
}

function getSloganById($blogid, $id) {
	$ctx = Model_Context::getInstance();
	$result = POD::queryCell("SELECT slogan FROM ".$ctx->getProperty('database.prefix')."Entries WHERE blogid = $blogid AND id = $id");
	if (is_null($result))
		return false;
	else
		return $result;
}

function getEntryIdBySlogan($blogid, $slogan) {
	$ctx = Model_Context::getInstance();
	$pool = DBModel::getInstance();
	$pool->reset("Entries");
	$pool->setQualifier("blogid","eq",$blogid);
	$pool->setQualifier("slogan","eq",$slogan,true);
	$result = $pool->getCell("id");
	if(!$result) return false;
	else return $result;
}

function setEntryStar($entryId, $mark) {
	$ctx = Model_Context::getInstance();
	$pool = DBModel::getInstance();
	$pool->reset("Entries");
	$pool->setAttribute("starred","eq",$mark);
	$pool->setQualifier("blogid","eq",$blogid);
	$pool->setQualifier("id","eq",$entryId);
	$result = $pool->update();
	if(!$result) return false;
	else return true;
}

function getEntriesByTagId($blogid, $tagId) {
	$ctx = Model_Context::getInstance();

	return POD::queryAll('SELECT e.blogid, e.userid, e.id, e.title, e.comments, e.slogan, e.published FROM '.$ctx->getProperty('database.prefix').'Entries e LEFT JOIN '.$ctx->getProperty('database.prefix').'TagRelations t ON e.id = t.entry AND e.blogid = t.blogid WHERE e.blogid = '.$blogid.' AND t.tag = '.$tagId);
}
?>
