<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
/*@protected, static@*/
function Tag_removeEmptyTagHelper($var) {
    return (strlen($var) > 0);
}

class Tag {
    function doesExist($tag) {
        global $database;
        return POD::queryCount("SELECT id FROM {$database['prefix']}Tags WHERE name = '" . $tag . "' LIMIT 1");
    }

    /*@static@*/
    function addTagsWithEntryId($blogid, $entry, /*string array*/
                                $taglist) {
        global $database;

        if ($taglist == null) {
            return;
        }

        $tmptaglist = array_filter($taglist, 'Tag_removeEmptyTagHelper');

        if (count($tmptaglist) == 0) {
            return;
        }

        $taglist = array();
        foreach ($tmptaglist as $tag) {
            $tag = POD::escapeString(Utils_Unicode::lessenAsEncoding(trim($tag), 255));
            array_push($taglist, $tag);
        }
        // step 1. Insert Tags
        foreach ($taglist as $tg) {
            if (!Tag::doesExist($tg)) {
                @POD::execute("INSERT INTO {$database['prefix']}Tags (id, name) VALUES (" . (Tag::_getMaxId() + 1) . ",'" . $tg . "')");
            }
        }
//		$tagliststr = '(\'' . implode('\') , (\'', $taglist) . '\')';
//		POD::execute("INSERT IGNORE INTO {$database['prefix']}Tags (name) VALUES $tagliststr ");

        // the point of Race condition
        // if other entry is deleted, some missing tags can be exist so they are not related with this entry.

        // step 2. Insert Relations
        $tagliststr = '\'' . implode('\' , \'', $taglist) . '\'';
        /*
        POD::execute("INSERT INTO {$database['prefix']}TagRelations
            (SELECT $blogid, t.id, $entry FROM {$database['prefix']}Tags as t
                WHERE
                name in ( $tagliststr ) AND
                t.id NOT IN
                (tag = t.id) AND (entry = $entry) AND (blogid = $blogid)
                )
            )");
        */
        // For MySQL 3, Simple Query Version
        $tagIDs = POD::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
        $tagrelations = array();
        foreach ($tagIDs as $tagid) {
            array_push($tagrelations, " ($blogid, $tagid, $entry) ");
            CacheControl::flushTag($tagid);
        }
        foreach ($tagrelations as $tr) {
            @POD::execute("INSERT INTO {$database['prefix']}TagRelations VALUES $tr");
        }
        //$tagRelationStr = implode(', ', $tagrelations);
        //POD::execute("INSERT IGNORE INTO {$database['prefix']}TagRelations VALUES $tagRelationStr");
    }

    /*@static@*/
    function modifyTagsWithEntryId($blogid, $entry, /*string array*/
                                   $taglist) {
        global $database;

        if (empty($taglist)) {
            $taglist = array();
        }

        $tmptaglist = array_filter($taglist, 'Tag_removeEmptyTagHelper');
        $taglist = array();
        foreach ($tmptaglist as $tag) {
            $tag = POD::escapeString(trim($tag));
            array_push($taglist, $tag);
        }

        // step 1. Get deleted Tag
        $tmpoldtaglist = POD::queryColumn("SELECT name FROM {$database['prefix']}Tags
			LEFT JOIN {$database['prefix']}TagRelations ON tag = id 
			WHERE blogid = $blogid AND entry = $entry");
        if ($tmpoldtaglist === null) {
            $tmpoldtaglist = array();
        }
        $oldtaglist = array();
        foreach ($tmpoldtaglist as $tag) {
            $tag = POD::escapeString(Utils_Unicode::lessenAsEncoding(trim($tag), 255));
            array_push($oldtaglist, $tag);
        }
        $deletedTagList = array_diff($oldtaglist, $taglist);
        $insertedTagList = array_diff($taglist, $oldtaglist);

        // step 2. Insert Tag
        if (count($insertedTagList) > 0) {
            foreach ($insertedTagList as $tg) {
                if (!Tag::doesExist($tg)) {
                    @POD::execute("INSERT INTO {$database['prefix']}Tags (id, name) VALUES (" . (Tag::_getMaxId() + 1) . ",'" . $tg . "')");
                }
            }
//			$tagliststr = '(\'' . implode('\') , (\'', $insertedTagList) . '\')';
//			POD::execute("INSERT IGNORE INTO {$database['prefix']}Tags (name) VALUES $tagliststr ");

            // step 3. Insert Relation
            $tagliststr = '\'' . implode('\' , \'', $insertedTagList) . '\'';
            /*
            POD::execute("INSERT INTO {$database['prefix']}TagRelations
                (SELECT $blogid, t.id, $entry FROM {$database['prefix']}Tags as t
                    WHERE
                    name in ( $tagliststr ) AND
                    t.id NOT IN
                        ( SELECT tag FROM {$database['prefix']}TagRelations WHERE
                            (tag = t.id) AND (entry = $entry) AND (blogid = $blogid)
                        )
                    )");
            */
            // For MySQL 3, Simple Query Version
            $tagIDs = POD::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
            $tagrelations = array();
            foreach ($tagIDs as $tagid) {
                array_push($tagrelations, " ($blogid, $tagid, $entry) ");
            }
            foreach ($tagrelations as $tr) {
                @POD::execute("INSERT INTO {$database['prefix']}TagRelations VALUES $tr");
            }
            //$tagRelationStr = implode(', ', $tagrelations);
            //POD::execute("INSERT IGNORE INTO {$database['prefix']}TagRelations VALUES $tagRelationStr");
        }

        // step 4. Delete Tag
        if (count($deletedTagList) > 0) {
            // small step, get tag id list
            $tagliststr = '\'' . implode('\' , \'', $deletedTagList) . '\'';
            $t1list = POD::queryColumn("SELECT id FROM {$database['prefix']}Tags WHERE name in ( $tagliststr )");
            if (is_null($t1list)) {
                return;
            } // What?

            // Flushing pageCache
            foreach ($t1list as $tagids) {
                CacheControl::flushTag($tagids);
            }
            // Make string
            $t1liststr = implode(', ', $t1list);
            $taglist = POD::queryColumn(
                "SELECT tag FROM {$database['prefix']}TagRelations
						WHERE blogid = $blogid AND entry = $entry AND tag in ( $t1liststr )");
            if (is_null($taglist)) {
                return;
            } // What?

            // now delete tag
            $tagliststr = implode(', ', $taglist);

            // step 5. Delete Relation
            POD::execute("DELETE FROM {$database['prefix']}TagRelations WHERE blogid = $blogid AND entry = $entry AND tag in ( $tagliststr )");

            // step 6. Delete Tag
            $nottargets = POD::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE tag in ( $tagliststr )");
            if (count($nottargets) > 0) {
                $nottargetstr = implode(', ', $nottargets);
                POD::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) AND id NOT IN ( $nottargetstr )");
            } else {
                POD::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr )");
            }

        }
    }

    /*@static@*/
    function deleteTagsWithEntryId($blogid, $entry) {
        global $database;
        $taglist = POD::queryColumn("SELECT tag FROM {$database['prefix']}TagRelations WHERE blogid = $blogid AND entry = $entry");
        if (!is_null($taglist)) {
            $tagliststr = implode(',', $taglist);
            foreach ($taglist as $tagid) {
                CacheControl::flushTag($tagid);
            }

            POD::execute("DELETE FROM {$database['prefix']}TagRelations
				WHERE blogid = $blogid AND entry = $entry");
            $nottargets = POD::queryColumn("SELECT DISTINCT tag FROM {$database['prefix']}TagRelations WHERE tag in ( $tagliststr )");
            if (count($nottargets) > 0) {
                $nottargetstr = implode(', ', $nottargets);
                POD::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr ) AND id NOT IN ( $nottargetstr )");
            } else {
                POD::execute("DELETE FROM {$database['prefix']}Tags WHERE id IN ( $tagliststr )");
            }
        }
    }

    function getTagsWithEntryId($blogid, $entry) {
        global $database;
        $tags = array();
        foreach (POD::queryAll("SELECT DISTINCT name FROM {$database['prefix']}Tags, {$database['prefix']}TagRelations WHERE id = tag AND blogid = $blogid AND entry = {$entry['id']} ORDER BY name") as $tag) {
            array_push($tags, $tag['name']);
        }
        return $tags;
    }

    function _getMaxId() {
        global $database;
        $maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}Tags");
        if ($maxId) {
            return $maxId;
        } else {
            return 0;
        }
    }
}

?>
