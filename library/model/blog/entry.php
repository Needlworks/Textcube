<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getEntriesTotalCount($blogid) {
    $pool = DBModel::getInstance();
    $pool->reset('Entries');
    if (!doesHaveOwnership()) {
        $exList = getCategoryVisibilityList($blogid, 'private');
        $pool = DBModel::getInstance();
        $pool->reset('Entries');
        $pool->setQualifier('visibility', 'b', 0);
        if (!empty($exList)) {
            $pool->setQualifier('category', 'hasnoneof', $exList);
        }
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array('userid', 'eq', getUserId()), 'OR', array('visibility', 'b', 0));
    }
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('draft', 'eq', 0);
    $pool->setQualifier('category', 'beq', 0);
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
    if (doesHaveOwnership()) {
        $pool->setQualifier('visibility', 'b', 0);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array('userid', 'eq', getUserId()), 'OR', array('visibility', 'b', 0));
    }
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('draft', 'eq', 0);
    $pool->setQualifier('category', 'eq', $categoryId);
    return $pool->getCount('*');
}

function getEntries($blogid, $attributes = '*', $condition = false, $order = array('published', 'DESC')) {
    $pool = DBModel::getInstance();
    $pool->reset("Entries");

    if (!empty($condition)) {
        $pool->setQualifierSet($condition);
    }

    if (!doesHaveOwnership()) {
        $pool->setQualifier("visibility", ">", 0);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("userid", "eq", getUserId()), "OR", array("visibility", ">", 0));
    }
    $pool->setOrder($order[0], $order[1]);
    return $pool->getAll($attributes);
}


function getTemplates($blogid, $attributes = '*', $condition = false, $order = array('published', 'DESC')) {
    $pool = DBModel::getInstance();
    $pool->reset("Entries");

    if (!empty($condition)) {
        $pool->setQualifierSet($condition);
    }
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("category", "eq", -4);
    $pool->setOrder($order[0], $order[1]);
    return $pool->getAll($attributes);
}

function getEntry($blogid, $id, $draft = false) {
    $pool = DBModel::getInstance();
    importlib('model.blog.attachment');
    if ($id == 0) {
        if (!doesHaveOwnership()) {
            return null;
        }
        deleteAttachments($blogid, 0);
        return array('id' => 0,
            'userid' => 0,
            'draft' => 0,
            'visibility' => 0,
            'starred' => 1,
            'category' => 0,
            'location' => '',
            'latitude' => null,
            'longitude' => null,
            'title' => '',
            'content' => '',
            'contentformatter' => getDefaultFormatter(),
            'contenteditor' => getDefaultEditor(),
            'acceptcomment' => 1,
            'accepttrackback' => 1,
            'published' => time(),
            'slogan' => '');
    }
    if ($draft) {
        $pool->init("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("id", "eq", $id);
        $pool->setQualifier("draft", "eq", 1);
        $entry = $pool->getRow();
        if (!$entry) {
            return null;
        }
        if ($entry['published'] == 1) {
            $entry['republish'] = true;
        } else {
            if ($entry['published'] != 0) {
                $entry['appointed'] = $entry['published'];
            }
        }
        if ($id != 0) {
            $pool->init("Entries");
            $pool->setQualifier("blogid", "eq", $blogid);
            $pool->setQualifier("id", "eq", $id);
            $pool->setQualifier("draft", "eq", 0);
            $entry['published'] = $pool->getCell("published");
        }
        return $entry;
    } else {
        $pool->init("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("id", "eq", $id);
        $pool->setQualifier("draft", "eq", 0);
        if (!doesHaveOwnership()) {
            $pool->setQualifier("visibility", ">", 0);
        }
        $entry = $pool->getRow();
        if (!$entry) {
            return null;
        }
        if ($entry['visibility'] < 0) {
            $entry['appointed'] = $entry['published'];
        }
        return $entry;
    }
}

function getUserIdOfEntry($blogid, $id, $draft = false) {
    $pool = DBModel::getInstance();
    $pool->reset('Entries');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $result = $pool->getCell('userid');
    if (!empty($result)) {
        return $result;
    } else {
        return null;
    }
}

function getEntryAttributes($blogid, $id, $attributeNames) {
    if (stristr($attributeNames, "from") != false) // security check!
    {
        return null;
    }
    $pool = DBModel::getInstance();
    $pool->reset('Entries');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $pool->setQualifier('draft', 'eq', 0);
    if (!doesHaveOwnership()) {
        $pool->setQualifier('visibility', 'b', 0);
    }
    return $pool->getRow($attributeNames);
}

function getEntryListWithPagingByCategory($blogid, $category, $page, $count) {
    if ($category === null) {
        return array();
    }
    if (!doesHaveOwnership() && getCategoryVisibility($blogid, $category) < 2 && $category != 0) {
        return array();
    }
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();

    if ($category > 0) {
        $pool->init("Categories");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("parent", "eq", $category);
        $categories = $pool->getColumn("id");
        array_push($categories, $category);
        if (!doesHaveOwnership()) {
            $categories = array_diff($categories, getCategoryVisibilityList($blogid, 'private'));
        }
        $pool->init("Entries");
        $pool->setAlias("Entries", "e");
        $pool->setQualifier("e.category", "hasoneof", $categories);
        if (!doesHaveOwnership()) {
            $pool->setQualifier("e.visibility", ">", 0);
        }
    } else {
        $pool->init("Entries");
        $pool->setAlias("Entries", "e");
        $pool->setQualifier("e.category", ">=", 0);
        if (!doesHaveOwnership()) {
            $pool->setQualifier("e.visibility", ">", 0);
            $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
        }
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("e.userid", "eq", getUserId()), "OR", array("e.visibility", ">", 0));
    }
    $pool->setProjection("e.blogid", "e.userid", "e.id", "e.title", "e.comments", "e.slogan", "e.published");
    $pool->setOrder("e.published", "desc");

    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . ((!$context->getProperty('blog.useSloganOnCategory', true) && $context->getProperty('suri.id', null) != null) ? $context->getProperty('suri.id') : $context->getProperty('suri.value')));
}

function getEntryListWithPagingByAuthor($blogid, $author, $page, $count) {
    $context = Model_Context::getInstance();
    if ($author === null) {
        return array();
    }
    $userid = User::getUserIdByName($author);
    if (empty($userid)) {
        return array();
    }
    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.userid", "eq", $userid);
    $pool->setQualifier("e.draft", "eq", 0);
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("e.userid", "eq", getUserId()), "OR", array("e.visibility", ">", 0));
    }
    $pool->setOrder("e.published", "DESC");
    $pool->setProjection("e.blogid", "e.userid", "e.id", "e.title", "e.comments", "e.slogan", "e.published");
    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'));
}

function getEntryListWithPagingByTag($blogid, $tag, $page, $count) {
    $context = Model_Context::getInstance();

    if ($tag === null) {
        return array(array(), array('url' => '', 'prefix' => '', 'postfix' => ''));
    }

    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("TagRelations", "t");
    $pool->join("TagRelations", "left", array(
        array("e.id", "eq", "t.entry"),
        array("e.blogid", "eq", "t.blogid")
    ));
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.category", ">=", 0);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("t.tag", "eq", $tag, true);

    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("e.userid", "eq", getUserId()), "OR", array("e.visibility", ">", 0));
    }

    $pool->setOrder("e.published", "DESC");
    $pool->setProjection("e.blogid", "e.userid", "e.id", "e.title", "e.comments", "e.slogan", "e.published");

    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . ((!Setting::getBlogSettingGlobal('useSloganOnTag', true) && ($context->getProperty('suri.id') != null)) ? $context->getProperty('suri.id') : $context->getProperty('suri.value')));
}

function getEntryListWithPagingByPeriod($blogid, $period, $page, $count) {
    $context = Model_Context::getInstance();

    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.category", ">=", 0);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("e.published", ">=", getTimeFromPeriod($period));
    $pool->setQualifier("e.published", "<", getTimeFromPeriod(addPeriod($period)));
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("e.userid", "eq", getUserId()), "OR", array("e.visibility", ">", 0));
    }

    $pool->setOrder("e.published", "DESC");
    $pool->setProjection("e.blogid", "e.userid", "e.id", "e.title", "e.comments", "e.slogan", "e.published");

    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'));
}

function getEntryListWithPagingBySearch($blogid, $search, $page, $count) {
    $context = Model_Context::getInstance();
    $search = escapeSearchString($search);
    if (strlen($search) == 0) {
        return Paging::fetch(null, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'));
    }
    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.category", ">=", 0);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("e.published", ">=", getTimeFromPeriod($period));
    $pool->setQualifier("e.published", "<", getTimeFromPeriod(addPeriod($period)));
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 1);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("e.userid", "eq", getUserId()), "OR", array("e.visibility", ">", 0));
    }

    $pool->setQualifierSet(array("e.title", "like", $search, true), "OR", array("e.content", "like", $search, true));
    $pool->setOrder("e.published", "DESC");
    $pool->setProjection("e.blogid", "e.userid", "e.id", "e.title", "e.comments", "e.slogan", "e.published");
    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'));
}

function getEntriesWithPaging($blogid, $page, $count) {
    $pool = DBModel::getInstance();
    $pool->reset("Entries");
    $pool->setAlias("Entries", "e");
    $pool->extend("Categories", "LEFT", array(array('e.blogid', 'eq', 'c.blogid'), array('e.category', 'eq', 'c.id')));
    $pool->setAlias("Categories", "c");

    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool->setQualifierSet(array('c.visibility', '>', 1), 'OR', array('e.category', 'eq', 0));
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array('e.userid', 'eq', getUserId()), 'OR', array('e.visibility', 'eq', 0));
    }
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("e.category", ">=", 0);
    $pool->setOrder("e.published", "DESC");
    $pool->setProjection("e.*", "c.label AS categoryLabel");
    return Paging::fetch($pool, $page, $count);
}

function getEntriesWithPagingByCategory($blogid, $category, $page, $count, $countItem) {
    $context = Model_Context::getInstance();
    if ($category === null) {
        return Paging::fetch(null, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'));
    }
    $pool = DBModel::getInstance();
    if ($category > 0) {
		$categories = getChildCategoryId($blogid, $category);
        array_push($categories, $category);
    }
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->join("Categories", "left", array(
        array("e.category", "eq", "c.id"),
        array("e.blogid", "eq", "c.blogid")
    ));
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.draft", "eq", 0);
    if ($category > 0) {
        $pool->setQualifier("e.category", "hasoneof", $categories);
        if (!doesHaveOwnership()) {
            $pool->setQualifier("e.visibility", ">", 0);
        }
    } else {
        $pool->setQualifier("e.category", "beq", 0);
        if (!doesHaveOwnership()) {
            $pool->setQualifier("e.visibility", ">", 0);
            $pool->setQualifierSet(array("c.visibility", "bigger", 1), "OR", array("e.category", "eq", 0));
        }
    }
    $pool->setOrder("e.published", "DESC");
    $pool->setProjection("e.*", "c.label AS categoryLabel");
    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . ((!$context->getProperty('blog.useSloganOnCategory', true) && $context->getProperty('suri.id', null) != null) ? $context->getProperty('suri.id') : $context->getProperty('suri.value')), "?page=", $countItem);
}

function getEntriesWithPagingByTag($blogid, $tag, $page, $count, $countItem = null) {
    $context = Model_Context::getInstance();

    if ($tag === null) {
        return Paging::fetch(null, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'));
    }
    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->setAlias("TagRelations", "t");
    $pool->join("Categories", "left", array(
        array("e.blogid", "eq", "c.blogid"),
        array("e.category", "eq", "c.id")
    ));
    $pool->join("TagRelations", "left", array(
        array("e.id", "eq", "t.entry"),
        array("e.blogid", "eq", "t.blogid")
    ));
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", "bigger", 0);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("e.userid", "eq", getUserId()), "OR", array("e.visibility", "bigger", 0));
    }
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("e.category", ">=", 0);
    $pool->setQualifier("t.tag", "eq", $tag, true);
    $pool->setProjection("e.*", "c.label AS categoryLabel");
    $pool->setOrder("e.published", "DESC");

    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . ((!Setting::getBlogSettingGlobal('useSloganOnTag', true) && ($context->getProperty('suri.id') != null)) ? $context->getProperty('suri.id') : $context->getProperty('suri.value')), "?page=", $countItem);
}

function getEntriesWithPagingByNotice($blogid, $page, $count, $countItem = null) {
    $context = Model_Context::getInstance();

    $pool = DBModel::getInstance();
    $pool->init("Entries");
    if (!doesHaveOwnership()) {
        $pool->setQualifier("visibility", ">", 1);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("userid", "eq", getUserId()), "OR", array("visibility", "bigger", 0));
    }
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("category", "eq", -2);
    $pool->setProjection("*");
    $pool->setOrder("published", "DESC");

    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'), "?page=", $countItem);
}

function getEntriesWithPagingByPage($blogid, $page, $count, $countItem = null) {
    $context = Model_Context::getInstance();

    $pool = DBModel::getInstance();
    $pool->init("Entries");
    if (!doesHaveOwnership()) {
        $pool->setQualifier("visibility", ">", 0);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("userid", "eq", getUserId()), "OR", array("visibility", "bigger", 0));
    }
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("category", "eq", -3);
    $pool->setProjection("*");
    $pool->setOrder("published", "DESC");

    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'), "?page=", $countItem);
}

function getEntriesWithPagingByPeriod($blogid, $period, $page, $count, $countItem = null) {
    $context = Model_Context::getInstance();

    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->join("Categories", "left", array(
        array("e.blogid", "eq", "c.blogid"),
        array("e.category", "eq", "c.id")
    ));
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.category", ">=", 0);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("e.published", ">=", getTimeFromPeriod($period));
    $pool->setQualifier("e.published", "<", getTimeFromPeriod(addPeriod($period)));
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool->setQualifierSet(array("c.visibility", ">", 1), "OR", array("e.category", "eq", 0));
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("e.userid", "eq", getUserId()), "OR", array("e.visibility", ">", 0));
    }

    $pool->setOrder("e.published", "DESC");
    $pool->setProjection("e.*", "c.label AS categoryLabel");

    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'), $countItem);
}

function getEntriesWithPagingBySearch($blogid, $search, $page, $count, $countItem) {
    $context = Model_Context::getInstance();

    $search = escapeSearchString($search);

    if (strlen($search) == 0) {
        return Paging::fetch(null, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'));
    }

    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->join("Categories", "left", array(
        array("e.blogid", "eq", "c.blogid"),
        array("e.category", "eq", "c.id")
    ));
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.category", ">=", 0);
    $pool->setQualifier("e.draft", "eq", 0);

    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool->setQualifierSet(array("c.visibility", ">", 1), "OR", array("e.category", "eq", 0));
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("e.userid", "eq", getUserId()), "OR", array("e.visibility", ">", 0));
    }
    $pool->setQualifierSet(
        array("e.title", "like", $search, true),
        "OR",
        array("e.content", "like", $search, true)
    );
    $pool->setOrder("e.published", "DESC");
    $pool->setProjection("e.*", "c.label AS categoryLabel");
    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'), "?page=", $countItem);
}

function getEntriesWithPagingByAuthor($blogid, $author, $page, $count, $countItem = null) {
    $context = Model_Context::getInstance();

    $userid = User::getUserIdByName($author);

    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->join("Categories", "left", array(
        array("e.blogid", "eq", "c.blogid"),
        array("e.category", "eq", "c.id")
    ));
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.userid", "eq", $userid);
    $pool->setQualifier("e.category", ">=", 0);
    $pool->setQualifier("e.draft", "eq", 0);
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool->setQualifierSet(array("c.visibility", ">", 1), "OR", array("e.category", "eq", 0));
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array("e.userid", "eq", getUserId()), "OR", array("e.visibility", ">", 0));
    }

    $pool->setOrder("e.published", "DESC");
    $pool->setProjection("e.*", "c.label AS categoryLabel");
    return Paging::fetch($pool, $page, $count, $context->getProperty('uri.folder') . "/" . $context->getProperty('suri.value'), "?page=", $countItem);
}

function getEntriesWithPagingForOwner($blogid, $category, $search, $page, $count, $visibility = null, $starred = null, $draft = null, $tag = null) {
    $pool = DBModel::getInstance();
    if ($category > 0) {
        $categories = getChildCategoryId($blogid, $category);
        array_push($categories, $category);
    }
    $pool->reset("Entries");
    $pool->setAlias("Entries", "e");
    $pool->extend("Categories", "LEFT", array(array('e.blogid', 'eq', 'c.blogid'), array('e.category', '=', 'c.id')));
    $pool->setAlias("Categories", "c");
    $pool->extend("Entries d", "LEFT", array(array('e.blogid', 'eq', 'd.blogid'), array('e.id', 'eq', 'd.id'), array("d.draft", "eq", 1)));

	if( ! Acl::check("group.editors", "entry.list") ) {
		$pool->setQualifier("e.userid","eq",getUserId());
	}
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setProjection("e.*", "c.label AS categoryLabel", "d.id AS draft");
    $pool->setOrder("e.published", "DESC");

    if (!Acl::check("group.editors", "entry.list")) {
        $pool->setQualifier("e.userid", "eq", getUserId());
    }
    if ($category > 0) {
        $pool->setQualifier("e.category", "hasoneof", $categories);
    } else if ($category == -3) {
        $pool->setQualifier("e.category", "eq", 0);
    } else if ($category == -5) {
        $pool->setQualifier("e.category", ">=", -3);
    } else if ($category == 0) {
        $pool->setQualifier("e.category", ">=", 0);
    } else {
        $pool->setQualifier("e.category", "eq", $category);
	}

    if (isset($visibility)) {
        if (Validator::isInteger($visibility, 0, 3)) {
            $pool->setQualifier("e.visibility", "eq", $visibility);
        }
    }
    if (isset($starred)) {
        if (Validator::isInteger($starred, 0, 3)) {
            $pool->setQualifier("e.starred", "eq", $starred);
        }
    }
    if (!empty($search)) {
        $search = escapeSearchString($search);
        $pool->setQualifierSet(array("e.title", "like", $search, true), "OR", array("e.content", "like", $search, true));
    }
    if (!empty($tag)) {
        $pool->join("TagRelations", "left", array(
            array("e.id", "eq", "t.entry"),
            array("e.blogid", "eq", "t.blogid")
        ));
        $pool->setAlias("TagRelations", "t");
        $pool->setQualifier("t.tag", "eq", $tag, true);
    }
    return Paging::fetch($pool, $page, $count);
}

function getEntryWithPaging($blogid, $id, $isSpecialEntry = false, $categoryId = false) {
    $context = Model_Context::getInstance();

    importlib('model.blog.category');
    $entries = array();
    $paging = Paging::init($context->getProperty('uri.folder'), '/');

    if ($categoryId !== false) {
        if ($categoryId != 0) {    // Not a 'total' category.
            $childCategories = getChildCategoryId($blogid, $categoryId);
        }
    }

    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->join("Categories", "left", array(
        array("e.blogid", "eq", "c.blogid"),
        array("e.category", "eq", "c.id")
    ));
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
    }
    if (!($isSpecialEntry || doesHaveOwnership())) {
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array('e.userid', 'eq', getUserId()), 'OR', array('e.visibility', '>', 0));
    }
    if ($isSpecialEntry) {
        if ($isSpecialEntry == 'page') {
            $pool->setQualifier("e.category", "=", -3);
        } else {
            $pool->setQualifier("e.category", "=", -2);
        }
    } else {
        $pool->setQualifier("e.category", ">=", 0);
    }
	if ($categoryId !== false) {
	    if (!empty($childCategories)) {
    	    $pool->setQualifier("e.category", "hasoneof", $childCategories);
	    } else {
    	    $pool->setQualifier("e.category", "eq", $categoryId);
		}
	}
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.id", "eq", $id);
    $pool->setQualifier("e.draft", "eq", 0);

    $currentEntry = $pool->getRow("e.*, c.label AS categoryLabel");

    $pool->unsetQualifier("e.id");
    $pool->setOrder("e.published", "DESC");
    $result = $pool->getColumn("e.id");

    if (!$result || !$currentEntry) {
        return array($entries, $paging);
    }
    if ($categoryId !== false) {
        $paging['pages'] = $categoryId == 0 ? getEntriesTotalCount($blogid) : getEntriesCountByCategory($blogid, $categoryId);
        $paging['postfix'] = '?category=' . $categoryId;
    } else {
        $paging['pages'] = $isSpecialEntry ? ($isSpecialEntry == 'page' ? getPagesTotalCount($blogid) : getNoticesTotalCount($blogid)) : getEntriesTotalCount($blogid);
    }

    for ($i = 1; $entryId = array_shift($result); $i++) {
        if ($entryId != $id) {
            if (array_push($paging['before'], $entryId) > 4) {
                if ($i == 5) {
                    $paging['first'] = array_shift($paging['before']);
                } else {
                    array_shift($paging['before']);
                }
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
        if (count($paging['before']) > 0) {
            $paging['prev'] = $paging['before'][count($paging['before']) - 1];
        }
        if (isset($paging['after'][0])) {
            $paging['next'] = $paging['after'][0];
        }
        return array($entries, $paging);
    }
    $paging['page'] = $paging['pages'] + 1;
    return array($entries, $paging);
}

function getEntryWithPagingBySlogan($blogid, $slogan, $isSpecialEntry = false, $categoryId = false) {
    importlib('model.blog.category');
    $context = Model_Context::getInstance();


    importlib('model.blog.category');
    $entries = array();
    $paging = $isSpecialEntry ? ($isSpecialEntry == 'page' ? Paging::init($context->getProperty('uri.blog') . "/page", '/') : Paging::init($context->getProperty('uri.blog') . "/notice", '/')) : Paging::init($context->getProperty('uri.blog') . "/entry", '/');

    if ($categoryId !== false) {
        if ($categoryId != 0) {    // Not a 'total' category.
            $childCategories = getChildCategoryId($blogid, $categoryId);
        }
    }

    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("Categories", "c");
    $pool->join("Categories", "left", array(
        array("e.blogid", "eq", "c.blogid"),
        array("e.category", "eq", "c.id")
    ));
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
    }
    if (!($isSpecialEntry || doesHaveOwnership())) {
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    if (doesHaveOwnership() && !Acl::check('group.editors')) {
        $pool->setQualifierSet(array('e.userid', 'eq', getUserId()), 'OR', array('e.visibility', '>', 0));
    }
    if ($isSpecialEntry) {
        if ($isSpecialEntry == 'page') {
            $pool->setQualifier("e.category", "=", -3);
        } else {
            $pool->setQualifier("e.category", "=", -2);
        }
    } else {
        $pool->setQualifier("e.category", ">=", 0);
    }
    if ($categoryId !== false) {
        if (!empty($childCategories)) {
            $pool->setQualifier("e.category", "hasoneof", $childCategories);
        } else {
            $pool->setQualifier("e.category", "eq", $categoryId);
        }
    }
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.slogan", "eq", $slogan, true);
    $pool->setQualifier("e.draft", "eq", 0);

    $currentEntry = $pool->getRow("e.*, c.label AS categoryLabel");

    $pool->unsetQualifier("e.id");
    $pool->setOrder("e.published", "DESC");
    $result = $pool->getAll("e.id, e.slogan");

    if (!$result || !$currentEntry) {
        return array($entries, $paging);
    }

    if ($categoryId !== false) {
        $paging['pages'] = $categoryId == 0 ? getEntriesTotalCount($blogid) : getEntriesCountByCategory($blogid, $categoryId);
        $paging['postfix'] = '?category=' . $categoryId;
    } else {
        $paging['pages'] = $isSpecialEntry ? ($isSpecialEntry == 'page' ? getPagesTotalCount($blogid) : getNoticesTotalCount($blogid)) : getEntriesTotalCount($blogid);
    }

    for ($i = 1; $entry = array_shift($result); $i++) {
        if ($entry['slogan'] != $slogan) {
            if (array_push($paging['before'], $entry['slogan']) > 4) {
                if ($i == 5) {
                    $paging['first'] = array_shift($paging['before']);
                } else {
                    array_shift($paging['before']);
                }
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
        if (count($paging['before']) > 0) {
            $paging['prev'] = $paging['before'][count($paging['before']) - 1];
        }
        if (isset($paging['after'][0])) {
            $paging['next'] = $paging['after'][0];
        }
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
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("e.draft", "eq", 0);
    $pool->setQualifier("e.category", "beq", 0);
    if (!doesHaveOwnership()) {
        $pool->setQualifier("e.visibility", ">", 0);
        $pool = getPrivateCategoryExclusionQualifier($pool, $blogid);
    }
    $pool->setOrder("e.published", "DESC");
    $pool->setLimit($context->getProperty('skin.entriesOnRecent'));
    $result = $pool->getAll("e.id, e.userid, e.title, e.slogan, e.comments, e.published");
    if ($result) {
        return $result;
    } else {
        return array();
    }
}

function getUniqueSlogan($blogid, $slogan, $id = null, $keepOriginal = false) {
    $slogan0 = $slogan;
	$pool = DBModel::getInstance();
	$pool->reset("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("slogan", "eq", $slogan, true);
    $pool->setQualifier("draft", "eq", 0);
    if (!is_null($id)) {
        $pool->setQualifier("id", "eq", $id);
    }
    $result = $pool->doesExist("slogan");
    if ($keepOriginal == true && $result) {
        return $slogan;
    }
    for ($i = 1; $result > 0; $i++) {
        if ($i > 1000) {
            return false;
        }
        $slogan = Utils_Unicode::lessenAsEncoding($slogan0, 245) . '-' . $i;
        $pool->reset("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("slogan", "eq", $slogan, true);
        $pool->setQualifier("draft", "eq", 0);
        $result = $pool->doesExist("slogan");
    }
    return $slogan;
}

function addEntry($blogid, $entry, $userid = null) {
    $gCacheStorage = globalCacheStorage::getInstance();
    $pool = DBModel::getInstance();
    importlib("model.blog.attachment");
    importlib("model.blog.feed");
    importlib("model.blog.category");
    importlib("model.blog.tag");
    importlib("model.blog.locative");

    if (empty($userid)) {
        $entry['userid'] = getUserId();
    } else {
        $entry['userid'] = $userid;
    }
    $entry['title'] = Utils_Unicode::lessenAsEncoding(trim($entry['title']), 255);
    $entry['location'] = Utils_Unicode::lessenAsEncoding(trim($entry['location']), 255);
    $entry['slogan'] = array_key_exists('slogan', $entry) ? trim($entry['slogan']) : '';

    if ((empty($entry['slogan'])) || ($entry['category'] == -1)) {
        $slogan = $slogan0 = getSlogan($entry['title']);
    } else {
        $slogan = $slogan0 = getSlogan($entry['slogan']);
    }

    $slogan = Utils_Unicode::lessenAsEncoding($slogan, 255);
    $title = $entry['title'];

    if ($entry['category'] == -1) {
        if ($entry['visibility'] == 1 || $entry['visibility'] == 3) {
            return false;
        }
        $pool->reset("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("draft", "eq", 0);
        $pool->setQualifier("title", "eq", $title, true);
        $pool->setQualifier("category", "eq", -1);
        if ($pool->getCount() > 0) {
            return false;
        }
    }

    if ($entry['category'] < 0) {
        if ($entry['visibility'] == 1) {
            $entry['visibility'] = 0;
        }
        if ($entry['visibility'] == 3) {
            $entry['visibility'] = 2;
        }
    }
    if ($entry['category'] == -4) {
        $entry['visibility'] = 0;
    }

    $slogan = getUniqueSlogan($blogid, $slogan);
    $userid = $entry['userid'];
    $latitude = isset($entry['latitude']) && !is_null($entry['latitude']) ? $entry['latitude'] : null;
    $longitude = isset($entry['longitude']) && !is_null($entry['longitude']) ? $entry['longitude'] : null;
    if (!isset($entry['firstEntry']) && isset($entry['published']) && is_numeric($entry['published']) && ($entry['published'] >= 2)) {
        $published = $entry['published'];
        $entry['visibility'] = 0 - $entry['visibility'];
        if ($entry['visibility'] < 0) {
            $closestReservedTime = Setting::getBlogSettingGlobal('closestReservedPostTime', INT_MAX);
            if ($published < $closestReservedTime) {
                Setting::setBlogSetting('closestReservedPostTime', $published, true);
            }
        }
    } else {
        $published = Timestamp::getUNIXtime();
    }
    $pool->reset("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("draft", "eq", 0);
    $currentMaxId = $pool->getCell("MAX(id)");
    if (!empty($currentMaxId) && $currentMaxId > 0) {
        $id = $currentMaxId + 1;
    } else {
        $id = 1;
    }

    $pool->reset("Entries");
    $pool->setAttribute("blogid", $blogid);
    $pool->setAttribute("userid", $userid);
    $pool->setAttribute("id", $id);
    $pool->setAttribute("draft", 0);
    $pool->setAttribute("visibility", $entry['visibility']);
    $pool->setAttribute("starred", $entry['starred']);
    $pool->setAttribute("category", $entry['category']);
    $pool->setAttribute("title", $title, true);
    $pool->setAttribute("slogan", $slogan, true);
    $pool->setAttribute("content", $entry['content'], true);
    $pool->setAttribute("contentformatter", $entry['contentformatter'], true);
    $pool->setAttribute("contenteditor", $entry['contenteditor'], true);
    $pool->setAttribute("location", $entry['location'], true);
    $pool->setAttribute("latitude", $latitude);
    $pool->setAttribute("longitude", $longitude);
    $pool->setAttribute("password", generatePassword(), true);
    $pool->setAttribute("acceptcomment", $entry['acceptcomment']);
    $pool->setAttribute("accepttrackback", $entry['accepttrackback']);
    $pool->setAttribute("published", $published);
    $pool->setAttribute("created", Timestamp::getUNIXtime());
    $pool->setAttribute("modified", Timestamp::getUNIXtime());
    $pool->setAttribute("comments", 0);
    $pool->setAttribute("trackbacks", 0);
    $pool->setAttribute("pingbacks", 0);
    $result = $pool->insert();
    if (!$result) {
        return false;
    }

    $pool->reset("Attachments");
    $pool->setAttribute("parent", $id);
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("parent", "eq", 0);
    $pool->update();

    $pool->reset("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $pool->setQualifier("draft", "eq", 1);
    $pool->delete();
    updateCategoryByEntryId($blogid, $id, 'add');
    CacheControl::flushEntry($id);
    clearFeed();

    if ($entry['visibility'] == 3) {
        syndicateEntry($id, 'create');
    }
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
    $gCacheStorage = globalCacheStorage::getInstance();
    $pool = DBModel::getInstance();

    importlib('model.blog.tag');
    importlib('model.blog.locative');
    importlib('model.blog.attachment');
    importlib('model.blog.category');
    importlib('model.blog.feed');

    if ($entry['id'] == 0) {
        return false;
    }
    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $entry['id']);
    $pool->setQualifier("draft", "eq", 0);
    $oldEntry = $pool->getRow();
    if (empty($oldEntry)) {
        return false;
    }

    if (empty($entry['userid'])) {
        $entry['userid'] = getUserId();
    }
    $entry['title'] = Utils_Unicode::lessenAsEncoding(trim($entry['title']));
    $entry['location'] = Utils_Unicode::lessenAsEncoding(trim($entry['location']));
    $entry['slogan'] = array_key_exists('slogan', $entry) ? trim($entry['slogan']) : '';
    if (empty($entry['slogan'])) {
        $slogan = $slogan0 = getSlogan($entry['title']);
    } else {
        $slogan = $slogan0 = getSlogan($entry['slogan']);
    }
    $slogan = Utils_Unicode::lessenAsEncoding($slogan, 255);

    if ($entry['category'] == -1) {
        if ($entry['visibility'] == 1 || $entry['visibility'] == 3) {
            return false;
        }
        $pool->init("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("id", "neq", $entry['id']);
        $pool->setQualifier("draft", "eq", 0);
        $pool->setQualifier("title", "eq", $entry['title'], true);
        $pool->setQualifier("category", "eq", -1);
        if ($pool->getCount() > 0) {
            return false;
        }
    }

    if ($entry['category'] < 0) {
        if ($entry['visibility'] == 1) {
            $entry['visibility'] = 0;
        }
        if ($entry['visibility'] == 3) {
            $entry['visibility'] = 2;
        }
    }
    if ($entry['category'] == -4) {
        $entry['visibility'] = 0;
    }

    $slogan = getUniqueSlogan($blogid, $slogan, $entry['id'], true);

    $tags = getTagsWithEntryString($entry['tag']);
    Tag::modifyTagsWithEntryId($blogid, $entry['id'], $tags);

    $latitude = isset($entry['latitude']) && !is_null($entry['latitude']) ? $entry['latitude'] : null;
    $longitude = isset($entry['longitude']) && !is_null($entry['longitude']) ? $entry['longitude'] : null;
    switch ($entry['published']) {
        case 0:
            $published = $oldEntry['published'];
            break;
        case 1:
            $published = Timestamp::getUNIXtime();
            break;
        default:
            $published = $entry['published'];
            $entry['visibility'] = 0 - $entry['visibility'];
            if ($entry['visibility'] < 0) {
                $closestReservedTime = Setting::getBlogSettingGlobal('closestReservedPostTime', 9999999999);
                if ($published < $closestReservedTime) {
                    Setting::setBlogSetting('closestReservedPostTime', $published, true);
                }
            }
            break;
    }

    $pool->reset("Entries");
    $pool->setAttribute("userid", $entry['userid']);
    $pool->setAttribute("draft", 0);
    $pool->setAttribute("visibility", $entry['visibility']);
    $pool->setAttribute("starred", $entry['starred']);
    $pool->setAttribute("category", $entry['category']);
    $pool->setAttribute("title", $entry['title'], true);
    $pool->setAttribute("slogan", $slogan, true);
    $pool->setAttribute("content", $entry['content'], true);
    $pool->setAttribute("contentformatter", $entry['contentformatter'], true);
    $pool->setAttribute("contenteditor", $entry['contenteditor'], true);
    $pool->setAttribute("location", $entry['location'], true);
    $pool->setAttribute("latitude", $latitude);
    $pool->setAttribute("longitude", $longitude);
    $pool->setAttribute("acceptcomment", $entry['acceptcomment']);
    $pool->setAttribute("accepttrackback", $entry['accepttrackback']);
    $pool->setAttribute("published", $published);
    $pool->setAttribute("modified", Timestamp::getUNIXtime());

    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $entry['id']);
    $pool->setQualifier("draft", "eq", $updateDraft);

    $result = $pool->update();
    if (!$result) {
        return false;
    }

    $pool->reset("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $entry['id']);
    $pool->setQualifier("draft", "eq", 1);
    $pool->delete();

    updateCategoryByEntryId($blogid, $entry['id'], 'update',
        array('category' => array($oldEntry['category'], $entry['category']),
            'visibility' => array($oldEntry['visibility'], $entry['visibility'])
        ));

    CacheControl::flushEntry($entry['id']);
    $gCacheStorage->purge();
    if ($entry['visibility'] == 3) {
        syndicateEntry($entry['id'], 'modify');
    }

    $pool->reset("Attachments");
    $pool->setAttribute("parent", $entry['id']);
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("parent", "eq", 0);
    $pool->update();

    if ($entry['visibility'] >= 2) {
        clearFeed();
    }
    return $result ? $entry['id'] : false;
}

function saveDraftEntry($blogid, $entry) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();

    importlib('model.blog.tag');
    importlib('model.blog.locative');
    importlib('model.blog.attachment');
    importlib('model.blog.category');
    importlib('model.blog.feed');

    if ($entry['id'] == 0) {
        return -11;
    }


    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $entry['id']);
    $pool->setQualifier("draft", "eq", 1);
    $draftCount = $pool->getCount();

    if ($draftCount > 0) { // draft가 없으면 insert를, 있으면 update를.
        $doUpdate = true;
    } else {
        $doUpdate = false;
    }

    // 원 글을 읽어서 몇가지 정보를 보존한다. 원래 글이 없는 경우 draft는 저장될 수 없다.
    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $entry['id']);
    $pool->setQualifier("draft", "eq", 0);
    $origEntry = $pool->getRow("created, comments, trackbacks, pingbacks, password, published");
    if (empty($origEntry)) {
        return -12;
    }

    $created = $origEntry['created'];
    $comments = $origEntry['comments'];
    $trackbacks = $origEntry['trackbacks'];
    $pingbacks = $origEntry['pingbacks'];
    $password = $origEntry['password'];

    if (empty($entry['userid'])) {
        $entry['userid'] = getUserId();
    }
    $entry['title'] = Utils_Unicode::lessenAsEncoding(trim($entry['title']));
    $entry['location'] = Utils_Unicode::lessenAsEncoding(trim($entry['location']));
    $entry['slogan'] = array_key_exists('slogan', $entry) ? trim($entry['slogan']) : '';
    if (empty($entry['slogan'])) {
        $slogan = $slogan0 = getSlogan($entry['title']);
    } else {
        $slogan = $slogan0 = getSlogan($entry['slogan']);
    }
    $slogan = Utils_Unicode::lessenAsEncoding($slogan, 255);
    $title = $entry['title'];

    if ($entry['category'] == -1) {
        if ($entry['visibility'] == 1 || $entry['visibility'] == 3) {
            return false;
        }
        $pool->init("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("id", "neq", $entry['id']);
        $pool->setQualifier("draft", "eq", 0);
        $pool->setQualifier("title", "eq", $entry['title'], true);
        $pool->setQualifier("category", "eq", -1);
        if ($pool->getCount() > 0) {
            return -13;
        }
    }

    if ($entry['category'] < 0) {
        if ($entry['visibility'] == 1) {
            $entry['visibility'] = 0;
        }
        if ($entry['visibility'] == 3) {
            $entry['visibility'] = 2;
        }
    }
    if ($entry['category'] == -4) {
        $entry['visibility'] = 0;
    }


    $slogan = getUniqueSlogan($blogid, $slogan, $entry['id']);

    $tags = getTagsWithEntryString($entry['tag']);
    Tag::modifyTagsWithEntryId($blogid, $entry['id'], $tags);

    $location = $entry['location'];
    $latitude = isset($entry['latitude']) && !is_null($entry['latitude']) ? $entry['latitude'] : null;
    $longitude = isset($entry['longitude']) && !is_null($entry['longitude']) ? $entry['longitude'] : null;
    $content = $entry['content'];
    $contentformatter = $entry['contentformatter'];
    $contenteditor = $entry['contenteditor'];
    switch ($entry['published']) {
        case 0:
            $published = $origEntry['published'];
            break;
        case 1:
            $published = Timestamp::getUNIXtime();
            break;
        default:
            $published = $entry['published'];
            $entry['visibility'] = 0 - $entry['visibility'];
            break;
    }

    if ($doUpdate) {
        $pool->reset("Entries");
        $pool->setAttribute("userid", $entry['userid']);
        $pool->setAttribute("draft", 1);
        $pool->setAttribute("visibility", $entry['visibility']);
        $pool->setAttribute("starred", $entry['starred']);
        $pool->setAttribute("category", $entry['category']);
        $pool->setAttribute("title", $title, true);
        $pool->setAttribute("slogan", $slogan, true);
        $pool->setAttribute("content", $entry['content'], true);
        $pool->setAttribute("contentformatter", $entry['contentformatter'], true);
        $pool->setAttribute("contenteditor", $entry['contenteditor'], true);
        $pool->setAttribute("location", $entry['location'], true);
        $pool->setAttribute("latitude", $latitude);
        $pool->setAttribute("longitude", $longitude);
        $pool->setAttribute("acceptcomment", $entry['acceptcomment']);
        $pool->setAttribute("accepttrackback", $entry['accepttrackback']);
        $pool->setAttribute("published", $published);
        $pool->setAttribute("modified", Timestamp::getUNIXtime());

        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("id", "eq", $entry['id']);
        $pool->setQualifier("draft", "eq", 1);

        $result = $pool->update();
    } else {
        $pool->reset("Entries");
        $pool->setAttribute("blogid", $blogid);
        $pool->setAttribute("userid", $entry['userid']);
        $pool->setAttribute("id", $entry['id']);
        $pool->setAttribute("draft", 1);
        $pool->setAttribute("visibility", $entry['visibility']);
        $pool->setAttribute("starred", $entry['starred']);
        $pool->setAttribute("category", $entry['category']);
        $pool->setAttribute("title", $title, true);
        $pool->setAttribute("slogan", $slogan, true);
        $pool->setAttribute("content", $entry['content'], true);
        $pool->setAttribute("contentformatter", $entry['contentformatter'], true);
        $pool->setAttribute("contenteditor", $entry['contenteditor'], true);
        $pool->setAttribute("location", $entry['location'], true);
        $pool->setAttribute("latitude", $latitude);
        $pool->setAttribute("longitude", $longitude);
        $pool->setAttribute("password", $password, true);
        $pool->setAttribute("acceptcomment", $entry['acceptcomment']);
        $pool->setAttribute("accepttrackback", $entry['accepttrackback']);
        $pool->setAttribute("published", $published);
        $pool->setAttribute("created", $created);
        $pool->setAttribute("modified", Timestamp::getUNIXtime());
        $pool->setAttribute("comments", $comments);
        $pool->setAttribute("trackbacks", $trackbacks);
        $pool->setAttribute("pingbacks", $pingbacks);

        $result = $pool->insert();
    }
    return $result ? $entry['id'] : false;
}

function updateRemoteResponsesOfEntry($blogid, $id) {
    $pool = DBModel::getInstance();
    $pool->reset("RemoteResponses");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("entry", "eq", $id);
    $pool->setQualifier("isfiltered", "eq", 0);
    $pool->setQualifier("responsetype", "eq", 'trackback', true);
    $trackbacks = getCount();

    $pool = DBModel::getInstance();
    $pool->reset("RemoteResponses");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("entry", "eq", $id);
    $pool->setQualifier("isfiltered", "eq", 0);
    $pool->setQualifier("responsetype", "eq", 'pingback', true);
    $pingbacks = getCount();

    if ($trackbacks === null || $pingbacks === null) {
        return false;
    }
    $pool->reset("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $pool->setAttribute("trackbacks", $trackbacks);
    $pool->setAttribute("pingbacks", $pingbacks);
    return $pool->update();
}

function deleteEntry($blogid, $id) {
    $gCacheStorage = globalCacheStorage::getInstance();
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();

    importlib("model.blog.feed");
    importlib("model.blog.category");
    importlib("model.blog.attachment");
    importlib("model.blog.tag");

    $target = getEntry($blogid, $id);
    if (is_null($target)) {
        return false;
    }
    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    if ($pool->getCell("visibility") == 3) {
        syndicateEntry($id, 'delete');
    }
    CacheControl::flushEntry($id);
    CacheControl::flushDBCache('entry');
    CacheControl::flushDBCache('comment');
    CacheControl::flushDBCache('trackback');
    $gCacheStorage->purge();
    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", $blogid)->setQualifier("id", "eq", $id);
    $result = $pool->delete();
    if ($result) {
        $pool->init("Comments")->setQualifier("blogid", "eq", $blogid)->setQualifier("entry", "eq", $id)->delete();
        $pool->init("RemoteResponses")->setQualifier("blogid", "eq", $blogid)->setQualifier("entry", "eq", $id)->delete();
        $pool->init("RemoteResponseLogs")->setQualifier("blogid", "eq", $blogid)->setQualifier("entry", "eq", $id)->delete();
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
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    importlib("model.blog.category");
    importlib("model.blog.feed");

    $targets = array_unique(preg_split('/,/', $entries, -1, PREG_SPLIT_NO_EMPTY));
    $effectedCategories = array();
    if (count($targets) < 1 || !is_numeric($category)) {
        return false;
    }

    if ($category == -1) { // Check Keyword duplication
        foreach ($targets as $entryId) {
            $pool->init("Entries");
            $pool->setQualifier("blogid", "eq", $blogid);
            $pool->setQualifier("id", "eq", $entryId);
            $pool->setQualifier("draft", "eq", 0);
            $title = $pool->getCell("title");
            if (is_null($title)) {
                return false;
            }

            $pool->init("Entries");
            $pool->setQualifier("blogid", "eq", $blogid);
            $pool->setQualifier("id", "neq", $entryId);
            $pool->setQualifier("draft", "eq", 0);
            $pool->setQualifier("title", "eq", $title, true);
            $pool->setQualifier("category", "eq", -1);
            if ($pool->doesExist("id")) {
                return false;
            }
        }
    } else {
        $parent = getParentCategoryId($blogid, $categoryId);
        array_push($effectedCategories, $parent);
    }

    foreach ($targets as $entryId) {
        $pool->init("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("id", "eq", $entryId);
        $pool->setQualifier("draft", "eq", 0);

        list($effectedCategoryId, $oldVisibility) = $pool->getRow("category, visibility");
        $visibility = $oldVisibility;
        if ($category < 0) {
            if ($visibility == 1) {
                $visibility = 0;
            }
            if ($visibility == 3) {
                $visibility = 2;
            }
        }

        if (($oldVisibility == 3) && ($visibility != 3)) {
            syndicateEntry($entryId, 'delete');
        }

        $pool->init("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("id", "eq", $entryId);
        $pool->setAttribute("category", $category);
        $pool->setAttribute("visibility", $visibility);
        $pool->update();

        if (!in_array($effectedCategoryId, $effectedCategories)) {
            array_push($effectedCategories, $effectedCategoryId);
            $parent = getParentCategoryId($blogid, $effectedCategoryId);
            if (!is_null($parent)) {
                array_push($effectedCategories, $parent);
            }
        }
    }
    $effected = false;
    foreach ($effectedCategories as $effectedCategory) {
        updateEntriesOfCategory($blogid, $effectedCategory);
        $effected = true;
    }

    if (updateEntriesOfCategory($blogid, $category)) {
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
    importlib("model.blog.feed");
	$pool = DBModel::getInstance();

    $targets = array_unique(preg_split('/,/', $entries, -1, PREG_SPLIT_NO_EMPTY));
    foreach ($targets as $entryId) {
        $pool->init("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("id", "eq", $entryId);
        $pool->setAttribute("userid", $userid);
        $pool->update();
    }
    clearFeed();
    CacheControl::flushAuthor();
    return true;
}

function setEntryVisibility($id, $visibility) {
    importlib("model.blog.feed");
    importlib("model.blog.category");
    $blogid = getBlogId();
    if (($visibility < 0) || ($visibility > 3)) {
        return false;
    }
	$pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $pool->setQualifier("draft", "eq", 0);

    list($category, $oldVisibility) = $pool->getRow("category, visibility");

    if ($category < 0) {
        if ($visibility == 1) {
            $visibility = 0;
        }
        if ($visibility == 3) {
            $visibility = 2;
        }
    }

    if ($oldVisibility === null) {
        return false;
    }
    if ($visibility == $oldVisibility) {
        return true;
    }

    if ($oldVisibility == 3) {
        syndicateEntry($id, 'delete');
    } else {
        if ($visibility == 3) {
            if (!syndicateEntry($id, 'create')) {
                $pool->init("Entries");
                $pool->setQualifier("blogid", "eq", $blogid);
                $pool->setQualifier("id", "eq", $id);
                $pool->setAttribute("visibility", $oldVisibility);
                $pool->setAttribute("modified", Timestamp::getUNIXtime());
                $pool->update();
                return false;
            }
        }
    }
    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $pool->setAttribute("visibility", $visibility);
    $pool->setAttribute("modified", Timestamp::getUNIXtime());
    $result = $pool->update();
    if (!$result)        // Error.
    {
        return false;
    }
    if ($result == 0)    // Not changed.
    {
        return true;
    }

    if ($category >= 0) {
        if ((($oldVisibility >= 2) && ($visibility < 2)) || (($oldVisibility < 2) && ($visibility >= 2))) {
            clearFeed();
        }
        if ((($oldVisibility == 3) && ($visibility <= 2)) || (($oldVisibility <= 2) && ($visibility == 3))) {
            clearFeed();
        }
        if ($category > 0) {
            updateCategoryByEntryId($blogid, $id, 'update', $parameters = array('visibility' => array($oldVisibility, $visibility)));
        }
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
    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", getBlogId());
    $pool->setQualifier("id", "eq", $id);
    $pool->setQualifier("visibility", "eq", 1);

    $pool->setAttribute("password", $password, true);
    $pool->setAttribute("modified", Timestamp::getUNIXtime());
    $result = $pool->update();

    if ($result) {
        CacheControl::flushEntry($id);
        CacheControl::flushDBCache('entry');
        CacheControl::flushDBCache('comment');
        CacheControl::flushDBCache('trackback');
        return true;
    } else {
        return false;
    }
}

function syndicateEntry($id, $mode) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();

    $pool->reset('XMLRPCPingSettings');
    $pool->setQualifier('blogid', 'equals', $context->getProperty('blog.id'));
    $sites = $pool->getAll('url,pingtype');

    $entry = getEntry($context->getProperty('blog.id'), $id);
    if (is_null($entry)) {
        return false;
    }

    if (!empty($sites)) {
        foreach ($sites as $site) {
            $rpc = new XMLRPC();
            $rpc->url = $site['url'];
            $result[$site['url']] = $rpc->call($context->getProperty('blog.title'), $context->getProperty('uri.default'));
        }
    }
    if ($mode == 'create') {
        fireEvent('CreatePostSyndicate', $id, $entry);
    } else {
        if ($mode == 'modify') {
            fireEvent('ModifyPostSyndicate', $id, $entry);
        } else {
            if ($mode == 'delete') {
                fireEvent('DeletePostSyndicate', $id, $entry);
            }
        }
    }
    return true;
}

function publishEntries() {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $blogid = getBlogId();
    $closestReservedTime = Setting::getBlogSettingGlobal('closestReservedPostTime', INT_MAX);
    if ($closestReservedTime < Timestamp::getUNIXtime()) {
        $pool->init("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("draft", "eq", 0);
        $pool->setQualifier("visibility", "<", 0);
        $pool->setQualifier("published", "<", Timestamp::getUNIXtime());
        $entries = $pool->getAll("id, visibility, category");
        if (count($entries) == 0) {
            return;
        }
        foreach ($entries as $entry) {
            $pool->init("Entries");
            $pool->setQualifier("blogid", "eq", $blogid);
            $pool->setQualifier("draft", "eq", 0);
            $pool->setQualifier("id", "eq", $entry['id']);
            $pool->setAttribute("visibility", 0);
            $result = $pool->update();
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
        $pool->init("Entries");
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("draft", "eq", 0);
        $pool->setQualifier("visibility", "<", 0);
        $pool->setQualifier("published", ">", Timestamp::getUNIXtime());
        $newClosestTime = $pool->getCell("min(published)");
        if (!empty($newClosestTime)) {
            Setting::setBlogSettingGlobal('closestReservedPostTime', $newClosestTime);
        } else {
            Setting::setBlogSettingGlobal('closestReservedPostTime', INT_MAX);
        }
    }
}

function getTagsWithEntryString($entryTag) {
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
        case 3:
        default:
            return _text('발행');
    }
}

function getSloganById($blogid, $id) {
    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $result = $pool->getCell("slogan");
    if (is_null($result)) {
        return false;
    } else {
        return $result;
    }
}

function getEntryIdBySlogan($blogid, $slogan) {
    $pool = DBModel::getInstance();
    $pool->reset("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("slogan", "eq", $slogan, true);
    $result = $pool->getCell("id");
    if (!$result) {
        return false;
    } else {
        return $result;
    }
}

function setEntryStar($entryId, $mark) {
    $pool = DBModel::getInstance();
    $pool->reset("Entries");
    $pool->setAttribute("starred", "eq", $mark);
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $entryId);
    $result = $pool->update();
    if (!$result) {
        return false;
    } else {
        return true;
    }
}

function getEntriesByTagId($blogid, $tagId) {
    $pool = DBModel::getInstance();
    $pool->init("Entries");
    $pool->setAlias("Entries", "e");
    $pool->setAlias("TagRelations", "t");
    $pool->join("TagRelations", "left", array(
        array("e.id", "eq", "t.entry"),
        array("e.blogid", "eq", "t.blogid")
    ));
    $pool->setQualifier("e.blogid", "eq", $blogid);
    $pool->setQualifier("t.tag", "eq", $tagId);
    return $pool->getAll("e.blogid, e.userid, e.id, e.title, e.comments, e.slogan, e.published");
}

?>
