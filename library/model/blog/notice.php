<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getNoticesWithPaging($blogid, $search, $page, $count) {
    $context = Model_Context::getInstance();
    $pool = getDefaultDBModelOnNotice($blogid);

    if (($search !== true) && $search) {
        $search = escapeSearchString($search);
        $pool->setQualifierSet(array("title", "like", $search, true), "OR", array("content", "like", $search, true));
    }

    return Paging::fetch($pool, $page, $count, $context->getProperty("uri.folder") . "/" . $context->getProperty("suri.value"));
}

function getNotice($blogid, $id) {
    $query = getDefaultDBModelOnNotice($blogid);
    $query->setQualifier('id', 'equals', $id);
    return $query->getAll('id, title, slogan, published, userid');
}

function getNotices($blogid) {
    $query = getDefaultDBModelOnNotice($blogid);
    return $query->getAll('id, title, slogan, published, userid');
}

function getRecentNotices($blogid) {
    $context = Model_Context::getInstance();
    $query = getDefaultDBModelOnNotice($blogid);
    $query->setLimit($context->getProperty('skin.noticesOnRecent'));
    return $query->getAll('id, title, slogan, published, userid');
}

function getDefaultDBModelOnNotice($blogid) {
    $query = DBModel::getInstance();
    $query->reset('Entries');
    $query->setQualifier('blogid', 'equals', $blogid);
    $query->setQualifier('draft', 'equals', 0);
    if (!doesHaveOwnership()) {
        $query->setQualifier('visibility', 'bigger', 1);
    }
    $query->setQualifier('category', 'equals', -2);
    $query->setOrder('published', 'DESC');
    return $query;
}

?>
