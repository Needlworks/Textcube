<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getLinks($blogid, $sort = "category") {
    $context = Model_Context::getInstance();
    $__gCacheLink = $context->getProperty("cache.links", array());
    if (empty($__gCacheLink)) {
        $pool = DBModel::getInstance();
        $pool->init("Links");
        $pool->setAlias("Links", "l");
        $pool->setAlias("LinkCategories", "lc");
        $pool->join("LinkCategories", "left", array(array("lc.blogid", "eq", "l.blogid"), array("lc.id", "eq", "l.category")));
        $pool->setQualifier("l.blogid", "eq", $blogid);
        $pool->setOrder("lc.name, l.name", "desc");
        if ($result = $pool->getAll("l.*, lc.name AS categoryName")) {
            $__gCacheLink = array();
            foreach ($result as $link) {
                array_push($__gCacheLink, $link);
            }
            $context->setProperty("cache.links", $__gCacheLink);
        }
    }
    return $__gCacheLink;
}

function getLinksWithPagingForOwner($blogid, $page, $count) {
    $pool = DBModel::getInstance();
    $pool->init("Links");
    $pool->setAlias("Links", "l");
    $pool->setAlias("LinkCategories", "lc");
    $pool->join("LinkCategories", "left", array(array("lc.blogid", "eq", "l.blogid"), array("lc.id", "eq", "l.category")));
    $pool->setQualifier("l.blogid", "eq", $blogid);
    $pool->setOrder("l.name", "desc");
    $pool->setProjection("l.*", "lc.name AS categoryName");

    return Paging::fetch($pool, $page, $count);
}

function getLink($blogid, $id) {
    $pool = DBModel::getInstance();
    $pool->init("Links");
    $pool->setAlias("Links", "l");
    $pool->setAlias("LinkCategories", "lc");
    $pool->join("LinkCategories", "left", array(array("lc.blogid", "eq", "l.blogid"), array("lc.id", "eq", "l.category")));
    $pool->setQualifier("l.blogid", "eq", $blogid);
    $pool->setQualifier("l.id", "eq", $id);
    return $pool->getRow("l.*, lc.name AS categoryName");
}

function deleteLink($blogid, $id) {
    $pool = DBModel::getInstance();
    $pool->init("Links");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $result = $pool->delete();
    return ($result) ? true : false;
}

function toggleLinkVisibility($blogid, $id, $visibility) {
    $pool = DBModel::getInstance();
    $pool->init("Links");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $pool->setAttribute("visibility", $visibility);
    $result = $pool->update();
    return array(($result) ? true : false, $visibility);
}

function addLink($blogid, $link) {
    $name = Utils_Unicode::lessenAsEncoding(trim($link['name']), 255);
    $url = Utils_Unicode::lessenAsEncoding(trim($link['url']), 255);

    if (empty($name) || empty($url)) {
        return -1;
    }
    $pool = DBModel::getInstance();

    $category = (isset($link['category'])) ? $link['category'] : 0;
    if (isset($link['newCategory']) && !empty($link['newCategory'])) { // Add new category information
        $newCategoryTitle = Utils_Unicode::lessenAsEncoding(trim($link['newCategory']), 255);
        $newCategoryId = addLinkCategory($blogid, $newCategoryTitle);
        if (!empty($newCategoryId)) {
            $category = $newCategoryId;
        } else {
            return false;
        }
    }

    $id = getMaxIdOfLink() + 1;
    $pid = getMaxPidOfLink() + 1;

    $pool->init("Links");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("url", "eq", $url, true);

    if ($pool->getCell("id")) {
        return 1;
    }

    $rss = isset($link['rss']) ? Utils_Unicode::lessenAsEncoding(trim($link['rss']), 255) : '';

    $pool->init("Links");
    $pool->setAttribute("pid", $pid);
    $pool->setAttribute("blogid", $blogid);
    $pool->setAttribute("id", $id);
    $pool->setAttribute("category", $category);
    $pool->setAttribute("name", $name, true);
    $pool->setAttribute("url", $url, true);
    $pool->setAttribute("rss", $rss, true);
    $pool->setAttribute("written", Timestamp::getUNIXtime());
    if ($pool->insert()) {
        return 0;
    } else {
        return -1;
    }
}

function updateLink($blogid, $link) {
    $id = $link['id'];
    $name = Utils_Unicode::lessenAsEncoding(trim($link['name']), 255);
    $url = Utils_Unicode::lessenAsEncoding(trim($link['url']), 255);
    if (empty($name) || empty($url)) {
        return false;
    }
    $category = (isset($link['category'])) ? $link['category'] : 0;

    if (isset($link['newCategory']) && !empty($link['newCategory'])) { // Add new category information
        $newCategoryTitle = Utils_Unicode::lessenAsEncoding(trim($link['newCategory']), 255);
        $newCategoryId = addLinkCategory($blogid, $newCategoryTitle);
        if (!empty($newCategoryId)) {
            $category = $newCategoryId;
        }
    }

    $rss = isset($link['rss']) ? Utils_Unicode::lessenAsEncoding(trim($link['rss']), 255) : '';

    $pool = DBModel::getInstance();

    $pool->init("Links");
    $pool->setAttribute("category", $category);
    $pool->setAttribute("name", $name, true);
    $pool->setAttribute("url", $url, true);
    $pool->setAttribute("rss", $rss, true);
    $pool->setAttribute("written", Timestamp::getUNIXtime());
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $link['id']);
    $result = $pool->update();

    // Garbage correction
    $pool->init("Links");
    $pool->setQualifier("blogid", "eq", $blogid);
    $existCategories = $pool->getColumn("category", array("filter" => "distinct"));

    $pool->init("LinkCategories");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "hasnoneof", $existCategories);
    $pool->delete();
    return $result;
}

function updateXfn($blogid, $links) {
    $pool = DBModel::getInstance();

    $ids = Array();
    foreach ($links as $k => $v) {
        if (substr($k, 0, 3) == 'xfn') {
            $id = substr($k, 3);
            $xfn = $v;
            $pool->init("Links");
            $pool->setAttribute("xfn", $xfn, true);
            $pool->setAttribute("written", Timestamp::getUNIXtime());
            $pool->setQualifier("blogid", "eq", $blogid);
            $pool->setQualifier("id", "eq", $id);
            $pool->update();
        }
    }
}

function getLinkCategories($blogid) {
    $pool = DBModel::getInstance();
    $pool->init("LinkCategories");
    $pool->setQualifier("blogid", "eq", $blogid);
    return $pool->getAll();
}

function addLinkCategory($blogid, $categoryTitle) {
    $pool = DBModel::getInstance();
    $pool->init("LinkCategories");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("name", "eq", $categoryTitle, true);
    $id = $pool->getCell();
    if (!empty($id)) {
        return $id;
    } else {    // Add new Link Category
        $pid = getMaxPidOfLinkCategory() + 1;
        $id = getMaxIdOfLinkCategory($blogid) + 1;
        $priority = 0;
        $visibility = 2; // Default visibility
        $pool->init("LinkCategories");
        $pool->setAttribute("pid", $pid);
        $pool->setAttribute("blogid", $blogid);
        $pool->setAttribute("id", $id);
        $pool->setAttribute("name", $categoryTitle, true);
        $pool->setAttribute("priority", $priority);
        $pool->setAttribute("visibility", $visibility);
        if ($pool->insert()) {
            return $id;
        } else {
            return false;
        }
    }
}

function updateLinkCategory($blogid, $category) {
    $pool = DBModel::getInstance();

    $pool->init("LinkCategories");
    $pool->setAttribute("name", $category['name'], true);
    $pool->setQualifier("id", $category['id']);
    $pool->setQualifier("blogid", "eq", $blogid);

    if ($pool->update()) {
        return true;
    } else {
        return false;
    }
}

function deleteLinkCategory($blogid, $id) {
    $pool = DBModel::getInstance();

    $pool->init("LinkCategories");
    $pool->setQualifier("id", $category['id']);
    $pool->setQualifier("blogid", "eq", $blogid);
    if ($pool->delete()) {
        $pool->init("Links");
        $pool->setAttribute("category", 0);
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setQualifier("category", "eq", $id);
        $pool->update();
        return true;
    } else {
        return false;
    }
}

function getLinkCategory($blogid, $id) {
    $pool = DBModel::getInstance();
    $pool->reset('LinkCategories');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    return $pool->getRow('*');
}

function getMaxIdOfLink($blogid = null) {
    if (empty($blogid)) {
        $blogid = getBlogId();
    }
    $pool = DBModel::getInstance();
    $pool->reset('Links');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $id = $pool->getCell('max(id)');
    return (empty($id) ? 0 : $id);
}

function getMaxPidOfLink() {
    $pool = DBModel::getInstance();
    $pool->reset('Links');
    $id = $pool->getCell('max(pid)');
    return (empty($id) ? 0 : $id);
}

function getMaxIdOfLinkCategory($blogid = null) {
    if (empty($blogid)) {
        $blogid = getBlogId();
    }
    $pool = DBModel::getInstance();
    $pool->reset('LinkCategories');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $id = $pool->getCell('max(id)');
    return (empty($id) ? 0 : $id);
}

function getMaxPidOfLinkCategory() {
    $pool = DBModel::getInstance();
    $pool->reset('LinkCategories');
    $id = $pool->getCell('max(pid)');
    return (empty($id) ? 0 : $id);
}

?>
