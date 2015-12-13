<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getLocatives($blogid) {
    return getEntries($blogid, 'id, userid, title, slogan, location', array(array('length(location)', '>', 1), 'AND', array('category', '>', -1)), array('location', 'desc'));
}

function suggestLocatives($blogid, $filter) {
    $pool = DBModel::getInstance();
    $pool->reset("Entries");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("location", "like", $filter . '%', true);
    $pool->setGroup("location");
    $pool->setOrder("cnt", "desc");
    $pool->setLimit(10);
    $result = $pool->getAll("location, COUNT(*) cnt", array("filter" => "distinct"));
    $locatives = array();
    if ($result) {
        foreach ($result as $locative) {
            $locatives[] = $locative[0];
        }
    }
    return $locatives;
}

?>