<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getTrashTrackbackWithPagingForOwner($blogid, $category, $site, $url, $ip, $search, $page, $count) {
    return getRemoteResponsesWithPagingForOwner($blogid, $category, $site, $url, $ip, $search, $page, $count, 'trackback', 0);
}

function getTrashCommentsWithPagingForOwner($blogid, $category, $name, $ip, $search, $page, $count) {
    return getCommentsWithPagingForOwner($blogid, $category, $name, $ip, $search, $page, $count, true, 0);
}

function getTrackbackTrash($entry) {
    $pool = DBModel::getInstance();
    $pool->init("RemoteResponses");
    $pool->setQualifier("blogid", "eq", getBlogId());
    $pool->setQualifier("entry", "eq", $entry);
    $pool->setOrder("written", "desc");
    $result = $pool->getAll();

    if (!empty($result)) {
        return $result;
    } else {
        return array();
    }
}

function getRecentTrackbackTrash($blogid) {
    $trackbacks = array();
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->init("RemoteResponses");
    if (doesHaveOwnership()) {
        $pool->setQualifier("blogid", "eq", $blogid);
        $pool->setOrder("written", "desc");
        $pool->setLimit($context->getProperty('skin.trackbacksOnRecent'));
        $result = $pool->getAll();
    } else {
        $pool->setAlias("RemoteResponses", "t");
        $pool->setAlias("Entries", "e");
        $pool->join("Entries", "left", array(
            array("t.blogid", "eq", "e.blogid"),
            array("t.entry", "eq", "e.id")
        ));
        $pool->setQualifier("t.blogid", "eq", $blogid);
        $pool->setQualifier("t.responsetype", "eq", 'trackback', true);
        $pool->setQualifier("e.draft", "eq", 0);
        $pool->setQualifier("e.visibility", ">=", 2);
        $pool->setOrder("t.written", "desc");
        $pool->setLimit($context->getProperty('skin.trackbacksOnRecent'));
        $result = $pool->getAll("t.*");
    }
    if ($result && !empty($result)) {
        $trackbacks = $result;
    }
    return $trackbacks;
}

function deleteTrackbackTrash($blogid, $id) {
    global $database;
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->init("RemoteResponses");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("id", "eq", $id);
    $entry = $pool->getCell("entry");

    if ($entry === null) {
        return false;
    }
    if (!$pool->delete()) {
        return false;
    }
    if (updateTrackbacksOfEntry($blogid, $entry)) {
        return $entry;
    }
    return false;
}

function restoreTrackbackTrash($blogid, $id) {
    $pool = DBModel::getInstance();
    $pool->reset('RemoteResponses');
    $pool->setQualifier('blogid', 'eq', $blogid);
    $pool->setQualifier('id', 'eq', $id);
    $entry = $pool->getCell('entry');
    if ($entry === null) {
        return false;
    }
    $pool->setAttribute('isfiltered', 0);
    if (!$pool->update()) {
        return false;
    }
    if (updateTrackbacksOfEntry($blogid, $entry)) {
        return $entry;
    }
    return false;
}

function trashVan() {
    $context = Model_Context::getInstance();
    if (Timestamp::getUNIXtime() - Setting::getServiceSetting('lastTrashSweep', 0, true) > 43200) {
        $pool = DBModel::getInstance();
        $pool->reset('Comments');
        $pool->setQualifier('isfiltered', 's', Timestamp::getUNIXtime() - $context->getProperty('service.trashtimelimit', 302400));
        $pool->setQualifier('isfiltered', 'b', 0);
        $pool->delete();
        $pool->reset('RemoteResponses');
        $pool->setQualifier('isfiltered', 's', Timestamp::getUNIXtime() - $context->getProperty('service.trashtimelimit', 302400));
        $pool->setQualifier('isfiltered', 'b', 0);
        $pool->delete();
        $pool->reset('RefererLogs');
        $pool->setQualifier('referred', 's', Timestamp::getUNIXtime() - 604800);
        $pool->delete();
        Setting::setServiceSetting('lastTrashSweep', Timestamp::getUNIXtime(), true);
    }
    if (Timestamp::getUNIXtime() - Setting::getServiceSetting('lastNoticeRead', 0, true) > 43200) {
        Setting::removeServiceSetting('TextcubeNotice', true);
        Setting::setServiceSetting('lastNoticeRead', Timestamp::getUNIXtime(), true);
    }
}

function emptyTrash($comment = true, $blogid = null) {
    $pool = DBModel::getInstance();
    if (is_null($blogid)) {
        $blogid = getBlogId();
    }
    if ($comment == true) {
        $pool->reset('Comments');
        $pool->setQualifier('blogid', 'eq', $blogid);
        $pool->setQualifier('isfiltered', 'b', 0);
        $pool->delete();
    } else {
        $pool->reset('RemoteResponses');
        $pool->setQualifier('blogid', 'eq', $blogid);
        $pool->setQualifier('isfiltered', 'b', 0);
        $pool->delete();
    }
}

?>
