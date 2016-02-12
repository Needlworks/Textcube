<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

function getTagId($blogid, $name) {
    global $database;
    $query = DBModel::getInstance();
    $query->reset('Tags');
    $query->setQualifier('name', 'equals', $name, true);
    return $query->getCell('id');
}

function getTagById($blogid, $id) {
    $query = DBModel::getInstance();
    $query->reset('Tags');
    $query->setQualifier('id', 'equals', $id);
    return $query->getCell('name');
}

function getTags($blogid, $entry) {
    $tags = array();
    $pool = DBModel::getInstance();
    $pool->init("Tags");
    $pool->setAlias("Tags", "t");
    $pool->setAlias("TagRelations", "r");
    $pool->join("TagRelations", "inner", array(
        array("r.blogid", "eq", $blogid),
        array("r.entry", "eq", $entry),
        array("r.tag", "eq", "t.id")
    ));
    if (!doesHaveOwnership()) {
        $pool->setAlias("Entries", "e");
        $pool->join("Entries", "inner", array(
            array("e.id", "eq", "r.entry"),
            array("e.visibility", ">", 0)
        ));
    }
    $pool->setGroup("r.tag", "t.id", "t.name");
    $pool->setOrder("t.name", "desc");
    $result = $pool->getAll("t.*");

    if (!empty($result)) {
        $tags = $result;
    }
    return $tags;
}

function getRandomTags($blogid) {
    $context = Model_Context::getInstance();
    $pool = DBModel::getInstance();
    $pool->init("Tags");
    $pool->setAlias("Tags", "t");
    $pool->setAlias("TagRelations", "r");
    $pool->setAlias("Entries", "e");
    $pool->setGroup("r.tag", "t.name", "t.id");

    if ($context->getProperty('skin.tagsOnTagbox', -1) != -1) {
        $pool->setLimit($context->getProperty('skin.tagsOnTagbox'));
    }
    if ($context->getProperty('skin.tagboxAlign') == 1) { // order by count
        $pool->setOrder("cnt", "desc");
        if (doesHaveOwnership()) {
            $pool->join("TagRelations", "inner", array(
                array("r.blogid", "eq", $blogid),
                array("r.tag", "eq", "t.id")
            ));
        } else {
            $pool->join("TagRelations", "inner", array(
                array("r.blogid", "eq", $blogid),
                array("r.tag", "eq", "t.id")
            ));
            $pool->join("Entries", "inner", array(
                array("r.entry", "eq", "e.id"),
                array("e.visibility", ">", 0),
                array("e.blogid", "eq", $blogid)
            ));
        }
    } else {
        if ($context->getProperty('skin.tagboxAlign') == 2) {  // order by name
            $pool->setOrder("t.name", "desc");
            if (doesHaveOwnership()) {
                $pool->join("TagRelations", "inner", array(
                    array("r.blogid", "eq", $blogid),
                    array("r.tag", "eq", "t.id")
                ));
                $pool->setOption(array('filter' => 'DISTINCT'));
            } else {
                $pool->join("TagRelations", "inner", array(
                    array("r.blogid", "eq", $blogid),
                    array("r.tag", "eq", "t.id")
                ));
                $pool->join("Entries", "inner", array(
                    array("r.entry", "eq", "e.id"),
                    array("e.visibility", ">", 0),
                    array("e.blogid", "eq", $blogid)
                ));
                $pool->setOption(array('filter' => 'DISTINCT'));
            }
        } else { // random
            $pool->setOrder("RAND()", "desc");

            if (doesHaveOwnership()) {
                $pool->join("TagRelations", "inner", array(
                    array("r.blogid", "eq", $blogid),
                    array("r.tag", "eq", "t.id")
                ));
            } else {
                $pool->join("TagRelations", "inner", array(
                    array("r.blogid", "eq", $blogid),
                    array("r.tag", "eq", "t.id")
                ));
                $pool->join("Entries", "inner", array(
                    array("r.entry", "eq", "e.id"),
                    array("e.visibility", ">", 0),
                    array("e.blogid", "eq", $blogid)
                ));
            }
        }
    }
    return $pool->getAll("t.name, count(*) as cnt, t.id");
}

function getSiteTags($blogid) {
    $pool = DBModel::getInstance();
    $pool->init("Tags");
    $pool->setAlias("Tags", "t");
    $pool->setAlias("TagRelations", "r");
    $pool->join("TagRelations", "inner", array(
        array("r.blogid", "eq", $blogid),
        array("r.tag", "eq", "t.id")
    ));

    if (!doesHaveOwnership()) {
        $pool->setAlias("Entries", "e");
        $pool->join("Entries", "inner", array(
            array("e.id", "eq", "r.entry"),
            array("e.visibility", ">", 0)
        ));
    }
    $pool->setGroup("r.tag", "t.id", "t.name");
    $pool->setOrder("t.name", "asc");
    $pool->setLimit(2000);
    $names = $pool->getAll("t.id, t.name");
    if (!empty($names)) {
        return $names;
    } else {
        $names = array();
    }
    return $names;
}

function getTagFrequencyRange() {
    $blogid = getBlogId();
    $max = $min = 0;
    $pool = DBModel::getInstance();
    $pool->init("TagRelations");
    $pool->setAlias("TagRelations", "r");
    $pool->setQualifier("r.blogid", "eq", $blogid);
    $pool->setGroup("r.tag");
    $pool->setOrder("cnt", "desc");

    if (!doesHaveOwnership()) {
        $pool->setAlias("Entries", "e");
        $pool->join("Entries", "inner", array(
            array("r.blogid", "eq", "e.blogid"),
            array("e.visibility", ">", 0),
            array("r.entry", "eq", "e.id")
        ));
    }
    $max = $pool->getCell("r.entry AS cnt");
    $max = ($max === null ? 0 : $max);
    $min = 1;
    return array($max, $min);
}

function getTagFrequency($tag, $max, $min) {
    $pool = DBModel::getInstance();

    $blogid = getBlogId();
    if (is_array($tag) && array_key_exists('cnt', $tag)) {
        $count = $tag['cnt'];
    } else {
        if (!is_array($tag)) {
            $tag = array('name' => $tag);
        }

        $pool->init("Tags");
        $pool->setAlias("Tags", "t");
        $pool->setAlias("TagRelations", "r");
        $pool->join("TagRelations", "inner", array(
            array("r.tag", "eq", "t.id"),
            array("r.blogid", "eq", $blogid)
        ));
        $pool->setQualifier("t.name", "eq", $tag['name'], true);

        if (!doesHaveOwnership()) {
            $pool->setAlias("Entries", "e");
            $pool->join("Entries", "inner", array(
                array("e.blogid", "eq", "r.blogid"),
                array("e.id", "eq", "r.entry"),
                array("e.visibility", ">", 0)
            ));
        }
        $count = $pool->getCount();
    }

    $dist = $max / 3;
    if ($count == $min) {
        return 5;
    } else {
        if ($count == $max) {
            return 1;
        } else {
            if ($count >= $min + ($dist * 2)) {
                return 2;
            } else {
                if ($count >= $min + $dist) {
                    return 3;
                } else {
                    return 4;
                }
            }
        }
    }
}

function suggestLocalTags($blogid, $filter) {
	$tags =	array();
	$pool = DBModel::getInstance();
    $pool->init("Tags");
    $pool->setAlias("Tags", "t");
    $pool->setAlias("TagRelations", "r");
    $pool->join("TagRelations", "inner", array(
        array("t.id", "eq", "r.tag"),
        array("r.blogid", "eq", $blogid),

    ));
    $pool->setGroup("tag", "t.name");
    $pool->setOrder("cnt", "desc");
    $pool->setLimit(10);
    if (!is_null($filter)) {
        $pool->setQualifier($filter[0], $filter[1], $filter[2], true);
    }
    $result = $pool->getAll("t.name, count(*) AS cnt", array("filter" => "distinct"));
    if ($result) {
        foreach ($result as $tag)
            array_push($tags, $tag[0]);
    }
    return $tags;
}

function deleteTagById($blogid, $id) {
    $pool = DBModel::getInstance();

    $pool->init("TagRelations");
    $pool->setQualifier("blogid", "eq", $blogid);
    $pool->setQualifier("tag", "eq", $id);

    /// delete relation
    $result = $pool->delete();

    if (!$result) {
        return false;
    }
    $pool->unsetQualifier("blogid");
    $count = $pool->getCount();
    if (intval($count) == 0) {
        $pool->init("Tags");
        $pool->setQualifier("id", "eq", $id);
        $pool->delete();
    }

    return true;
}


function addTag($blogid, $name) {
    $tagId = getTagId($blogid, $name);
    if (empty($tagId)) {
        $query = DBModel::getInstance();
        $query->reset("Tags");
        $insertId = Tag::_getMaxId() + 1;
        $query->setAttribute('id', $insertId);
        $query->setAttribute('name', $name, true);
        if ($query->insert()) {
            return $insertId;
        } else {
            return false;
        }
    } else {
        return $tagId;
    }
}

function renameTag($blogid, $id, $name) {
    // 1. If tag with new name already exists, skip the tag creation process.
    // 2. If tag with new name does not exist in this service, create new tag.
    // 3. Modify the tag relation information
    // 4. If older tag is not used anymore, drop the tag.
    $oldTagId = $id;
    $newTagId = addTag($blogid, $name);
    $query = DBModel::getInstance();
    $query->reset("TagRelations");
    $query->setAttribute('tag', $newTagId);
    $query->setQualifier('blogid', 'equals', $blogid);
    $query->setQualifier('tag', 'equals', $oldTagId);
    $query->update();
    deleteTagById($blogid, $oldTagId);
    return $newTagId;
}

?>
