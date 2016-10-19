<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class Post {
    function __construct() {
        $this->reset();
    }

    function reset() {
        $this->error =
        $this->blogid =
        $this->userid =
        $this->id =
        $this->visibility =
        $this->starred =
        $this->title =
        $this->slogan =
        $this->content =
        $this->contentformatter =
        $this->contenteditor =
        $this->category =
        $this->tags =
        $this->location =
        $this->password =
        $this->acceptcomment =
        $this->accepttrackback =
        $this->published =
        $this->longitude =
        $this->latitude =
        $this->created =
        $this->modified =
        $this->comments =
        $this->trackbacks =
        $this->pingbacks =
            null;
    }

    function init() {
        if (!isset($this->blogid) || $this->blogid === null) {
            $this->blogid = getBlogId();
        }
    }

    /*@polymorphous(numeric $id, $fields, $sort)@*/
    function open($filter = '', $fields = '*', $sort = 'published DESC') {
        global $database;
        $this->close();
        $this->init();
        if (is_numeric($filter)) {
            $filter = 'AND id = ' . $filter;
        } else {
            if (!empty($filter)) {
                $filter = 'AND ' . $filter;
            }
        }
        if (!empty($sort)) {
            $sort = 'ORDER BY ' . $sort;
        }
        $this->_result = POD::query("SELECT $fields FROM {$database['prefix']}Entries WHERE blogid = {$this->blogid} AND draft = 0 AND category >= 0 $filter $sort");
        if ($this->_result) {
            $this->_count = POD::num_rows($this->_result);
        }
        return $this->shift();
    }

    function close() {
        if (isset($this->_result)) {
            POD::free($this->_result);
            unset($this->_result);
        }
        $this->_count = 0;
        $this->reset();
    }

    function shift() {
        $this->reset();
        if ($this->_result && ($row = POD::fetch($this->_result))) {
            foreach ($row as $name => $value) {
//				if ($name == 'blogid')
//					continue;
				switch ($name) {
					case 'visibility':
						if ($value == -2)
							$value = 'appointed';
						else if ($value == 0)
							$value = 'private';
						else if ($value == 1)
							$value = 'protected';
						else if ($value == 2)
							$value = 'public';
						else
							$value = 'syndicated';
						break;
					case 'category': /*@backward-compatibility@*/
						if (empty($value))
							$value = null;
						break;
					case 'acceptcomment':
					case 'accepttrackback':
						$value = $value ? true : false;
						break;
				}
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}

	function add($userid = null) {
		global $database;
		$this->init();
		if (isset($this->id) && !Validator::number($this->id, 1))
			return $this->_error('id');
		if (isset($this->category) && !Validator::number($this->category, 0))
			return $this->_error('category');
		$this->title = trim($this->title);
		if (empty($this->title))
			return $this->_error('title');
		if (empty($this->content))
			return $this->_error('content');
		if (!$query = $this->_buildQuery())
			return false;
		if (!isset($this->id) || $query->doesExist() || $this->doesExist($this->id)) {
			$this->id = $this->nextEntryId(); // Added (#300)
		}
		$query->setQualifier('id', 'equals', $this->id);

		if (empty($this->starred))
			$this->starred = 0;
//		if (!$query = $this->_buildQuery())
		if (!isset($this->published))
			$query->setAttribute('published', 'UNIX_TIMESTAMP()');
		if (!isset($this->created))
			$query->setAttribute('created', 'UNIX_TIMESTAMP()');
		if (!isset($this->modified))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');
		$query->setAttribute('blogid',$this->blogid);
		if (!isset($this->userid)){
			$this->userid = getUserId();
			$query->setAttribute('userid',$this->userid);
		}
		if (!$query->insert())
			return $this->_error('insert');

		if (isset($this->category)) {
			$target = ($parentCategory = Category::getParent($this->category)) ? '(id = ' . $this->category . ' OR id = ' . $parentCategory . ')' : 'id = ' . $this->category;
			if (isset($this->visibility) && ($this->visibility != 'private'))
				@POD::query("UPDATE {$database['prefix']}Categories SET entries = entries + 1, entriesinlogin = entriesinlogin + 1 WHERE blogid = ".getBlogId()." AND " . $target);
			else
				@POD::query("UPDATE {$database['prefix']}Categories SET entriesinlogin = entriesinlogin + 1 WHERE blogid = ".$this->blogid." AND " . $target);
		}
		$this->saveSlogan();
		$this->addTags();
		if (($this->visibility == 'public') || ($this->visibility == 'syndicated')) {
			RSS::refresh();
			ATOM::refresh();
		}
		if ($this->visibility == 'syndicated') {
			if (!Utils_Syndication::join($this->getLink())) {
				$query->resetAttributes();
				$query->setAttribute('visibility', 2);
				$this->visibility = 'public';
				$query->update();
			}
		}
		return true;
	}

	function remove($id = null) { // attachment & category is own your risk!
		global $database;
		$gCacheStorage = globalCacheStorage::getInstance();
		$this->init();
		if(!empty($id)) $this->id = $id;
		// step 0. Get Information
		if (!isset($this->id) || !Validator::number($this->id, 1))
			return $this->_error('id');

		if (!$query = $this->_buildQuery())
			return false;

		if (!$entry = $query->getRow('category, visibility'))
			return $this->_error('id');

		// step 1. Check Syndication
		if ($entry['visibility'] == 3) {
			Utils_Syndication::leave($this->getLink());
		}

		CacheControl::flushEntry($this->id);
		CacheControl::flushDBCache('entry');
		CacheControl::flushDBCache('comment');
		CacheControl::flushDBCache('trackback');
		$gCacheStorage->purge();

		// step 2. Delete Entry
		$sql = "DELETE FROM ".$database['prefix']."Entries WHERE blogid = ".$this->blogid." AND id = ".$this->id;
		if (POD::queryCount($sql)) {
		// step 3. Delete Comment
			POD::execute("DELETE FROM {$database['prefix']}Comments WHERE blogid = ".$this->blogid." AND entry = ".$this->id);

		// step 4. Delete Trackback
			POD::execute("DELETE FROM {$database['prefix']}RemoteResponses WHERE blogid = ".$this->blogid." AND entry = ".$this->id);

		// step 5. Delete Trackback Logs
			POD::execute("DELETE FROM {$database['prefix']}RemoteResponseLogs WHERE blogid = ".$this->blogid." AND entry = ".$this->id);

		// step 6. update Category
			if (isset($entry['category'])) {
				$target = ($parentCategory = Category::getParent($entry['category'])) ? '(id = ' . $entry['category'] . ' OR id = ' . $parentCategory . ')' : 'id = ' . $entry['category'];

				if (isset($entry['visibility']) && ($entry['visibility'] != 1))
					POD::query("UPDATE {$database['prefix']}Categories SET entries = entries - 1, entriesinlogin = entriesinlogin - 1 WHERE blogid = ".$this->blogid." AND " . $target);
				else
					POD::query("UPDATE {$database['prefix']}Categories SET entriesinlogin = entriesinlogin - 1 WHERE blogid = ".$this->blogid." AND " . $target);
			}

		// step 7. Delete Attachment
			$attachNames = POD::queryColumn("SELECT name FROM {$database['prefix']}Attachments
				WHERE blogid = ".getBlogId()." AND parent = ".$this->id);
			if (POD::execute("DELETE FROM {$database['prefix']}Attachments WHERE blogid = ".getBlogId()." AND parent = ".$this->id)) {
				foreach($attachNames as $attachName) {
					if( file_exists( __TEXTCUBE_ATTACH_DIR__."/".getBlogId()."/$attachName") ) {
						@unlink(__TEXTCUBE_ATTACH_DIR__."/".getBlogId()."/$attachName");
					}
				}
			}

		// step 8. Delete Tags
			$this->deleteTags();

		// step 9. Clear RSS
			RSS::refresh();
			ATOM::refresh();

			return true;
		}
		return false;
	}

	function update() { // attachment & category is own your risk!
		if (!isset($this->id) || !Validator::number($this->id, 1))
			return $this->_error('id');

		if (!$query = $this->_buildQuery())
			return false;
		if (!$old = $query->getRow('category, visibility'))
			return $this->_error('id');

		$bChangedCategory = ($old['category'] != $this->category);

		if ($old['visibility'] == 3) {
			Utils_Syndication::leave($this->getLink());
		}
		if (!isset($this->modified))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');

		if (!$query->update())
			return $this->_error('update');

//		if ($bChangedCategory) {
//			// TODO : Recalculate Category
//		}

        if (isset($this->slogan)) {
            $this->saveSlogan();
        }

        $this->updateTags();

        if ($this->visibility == 'syndicated') {
            if (!Utils_Syndication::join($this->getLink())) {
                $query->resetAttributes();
                $query->setAttribute('visibility', 2);
                $this->visibility = 'public';
                $query->update();
            }
        }
        RSS::refresh();

        return true;
    }

    function getCount() {
        return (isset($this->_count) ? $this->_count : 0);
    }

    function getLink() {
        global $defaultURL;
        if (!Validator::number($this->id, 1)) {
            return null;
        }
        return "$defaultURL/sync/{$this->id}";
    }

    function getAttachments() {
        if (!Validator::number($this->id, 1)) {
            return null;
        }
        $attachment = new Attachment();
        if ($attachment->open('parent = ' . $this->id)) {
            return $attachment;
        }
    }

    function saveSlogan($slogan = null) {
        global $database;
        $this->init();
        if (!Validator::number($this->id, 1)) {
            return $this->_error('id');
        }
        if (!Validator::number($this->userid, 1)) {
            return $this->_error('userid');
        }
        if (isset($slogan)) {
            $this->slogan = $slogan;
        }

        $query = DBModel::getInstance();
        $query->reset('Entries');
        $query->setQualifier('blogid', 'equals', $this->blogid);
        if (isset($this->userid)) {
            $query->setQualifier('userid', 'equals', $this->userid);
        }
        $query->setQualifier('id', 'equals', $this->id);
        if (!$query->doesExist()) {
            return $this->_error('id');
        }

        if (isset($this->slogan) && $this->validateSlogan($this->slogan)) {
            $slogan0 = $this->slogan;
        } else {
            $slogan0 = $this->slogan = $this->makeSlogan($this->title);
        }

        $slogan0 = Utils_Unicode::lessenAsEncoding($slogan0, 255);

        for ($i = 1; $i < 1000; $i++) {
//			$checkSlogan = POD::escapeString($this->slogan);
            $checkSlogan = $this->slogan;
            $query->setAttribute('slogan', $checkSlogan, true);
            if (!POD::queryExistence(
                "SELECT id FROM {$database['prefix']}Entries "
                . "WHERE blogid = " . $this->blogid . " AND id <> {$this->id} AND slogan ='".POD::escapeString($checkSlogan)."'")
            ) {
                if (!$query->update()) {
                    return $this->_error('update');
                }
                return true;
            }
            $this->slogan = Utils_Unicode::lessenAsEncoding($slogan0, 245) . '-' . $i;
        }
        // if try saveSlogan again, slogan string has more $i
        return $this->_error('limit');
    }

    function loadTags() {
        global $database;
        $this->init();
        if (!Validator::number($this->id, 1)) {
            return $this->_error('id');
        }
        $this->tags = array();
        if ($result = POD::queryColumn("SELECT name FROM {$database['prefix']}TagRelations
			LEFT JOIN {$database['prefix']}Tags ON id = tag
			WHERE blogid = " . $this->blogid . " AND entry = {$this->id}
			ORDER BY name")
        ) {
            $this->tags = $result;
            return true;
        }
        return false;
    }

    /*@static, protected@*/
    function getTagsWithEntryString($entryTag) {
        global $database;
        if (empty($entryTag)) {
            return array();
        }

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

    /*@protected@*/
    function addTags() {
        // Don't call outside of object!
        $this->init();
        if (!Validator::number($this->id, 1)) {
            return $this->_error('id');
        }
        if (!is_array($this->tags)) {
            $this->tags = $this->getTagsWithEntryString($this->tags);
        }
        if (empty($this->tags)) {
            return true;
        }
        Tag::addTagsWithEntryId($this->blogid, $this->id, $this->tags);
        return true;
    }

    /*@protected@*/
    function updateTags() {
        // Don't call outside of object!
        if (!Validator::number($this->id, 1)) {
            return $this->_error('id');
        }
        if (!is_array($this->tags)) {
            $this->tags = $this->getTagsWithEntryString($this->tags);

        }
        Tag::modifyTagsWithEntryId(getBlogId(), $this->id, $this->tags);
        return true;
    }

    /*@protected@*/
    function deleteTags() {
        // Don't call outside of object!
        $this->init();
        if (!Validator::number($this->id, 1)) {
            return $this->_error('id');
        }
        Tag::deleteTagsWithEntryId($this->blogid, $this->id);
        return true;
    }

    function getComments() {
        if (!Validator::number($this->id, 1)) {
            return $this->_error('id');
        }
        $comment = new Comment();
        if ($comment->open('entry = ' . $this->id . ' AND parent IS NULL')) {
            return $comment;
        }
    }

    function getTrackbacks() {
        if (!Validator::number($this->id, 1)) {
            return $this->_error('id');
        }
        $trackback = new Trackback();
        if ($trackback->open('entry = ' . $this->id)) {
            return $trackback;
        }
    }

    function getTrackbackLogs() {
        if (!Validator::number($this->id, 1)) {
            return $this->_error('id');
        }
        $log = new TrackbackLog();
        if ($log->open('entry = ' . $this->id)) {
            return $log;
        }
    }

    function doesExist($id) {
        global $database;
        $this->init();
        if (!Validator::number($id, 1)) {
            return false;
        }
        return POD::queryExistence("SELECT id FROM {$database['prefix']}Entries WHERE blogid = " . $this->blogid . " AND id = $id AND category >= 0 AND draft = 0");
    }

    function doesAcceptTrackback($id) {
        global $database;
        $this->init();
        if (!Validator::number($id, 1)) {
            return false;
        }
        return POD::queryExistence("SELECT id
			FROM {$database['prefix']}Entries
			WHERE blogid = " . $this->blogid . " AND id = $id AND draft = 0 AND visibility > 0 AND category >= 0 AND accepttrackback = 1");
    }

    function updateComments($id = null) {
        global $database;
        $this->init();
        if (!is_null($id) && !is_numeric($id)) {
            return false;
        }

        $posts = (is_null($id) ? POD::queryColumn("SELECT id FROM {$database['prefix']}Entries WHERE blogid = " . $this->blogid . " AND category >= 0 AND draft = 0") : array($id));
        if (!is_array($posts)) {
            return false;
        }
        $succeeded = true;
        foreach ($posts as $id) {
            $comments = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE blogid = " . $this->blogid . " AND entry = $id AND isfiltered = 0");
            if (!is_null($comments)) {
                if (POD::execute("UPDATE {$database['prefix']}Entries SET comments = $comments WHERE blogid = " . $this->blogid . " AND id = $id")) {
                    continue;
                }
            }
            $succeeded = false;
        }
        return $succeeded;
    }

    function updateRemoteResponses($id = null) {
        global $database;
        $this->init();

        if (!is_null($id) && !is_numeric($id)) {
            return false;
        }

        $posts = (is_null($id) ? POD::queryColumn("SELECT id FROM {$database['prefix']}Entries WHERE blogid = " . $this->blogid . " AND category >= 0 AND draft = 0") : array($id));
        if (!is_array($posts)) {
            return false;
        }
        $succeeded = true;
        foreach ($posts as $id) {
            $trackbacks = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}RemoteResponses WHERE blogid = " . $this->blogid . " AND entry = $id AND isfiltered = 0 AND responsetype = 'trackback'");
            if (!is_null($trackbacks)) {
                if (!POD::execute("UPDATE {$database['prefix']}Entries SET trackbacks = $trackbacks
					WHERE blogid = " . $this->blogid . " AND id = $id")
                ) {
                    $succeeded = false;
                }
            }
            $pingbacks = POD::queryCell("SELECT COUNT(*) FROM {$database['prefix']}RemoteResponses WHERE blogid = " . $this->blogid . " AND entry = $id AND isFiltered = 0 AND responsetype = 'pingback'");
            if (!is_null($pingbacks)) {
                if (!POD::execute("UPDATE {$database['prefix']}Entries SET pingbacks = $pingbacks
					WHERE blogid = " . $this->blogid . " AND id = $id")
                ) {
                    $succeeded = false;
                }
            }
        }
        return $succeeded;
				}

    function makeSlogan($title) {
        $slogan = preg_replace('/-+/', ' ', $title);
        $slogan = preg_replace('/[!-\/:-@\[-\^`{-~]+/', '', $slogan);
        $slogan = preg_replace('/\s+/', '-', $slogan);
        $slogan = trim($slogan, '-');
        return strlen($slogan) > 0 ? $slogan : 'XFile';
    }

    function validateSlogan($slogan) {
        return preg_match('/^[^!-,.\/:-@\[-\^`{-~\s]+$/', $slogan);
    }

    function makePassword($plain = null) {
        return $plain ? md5($plain) : md5(microtime());
    }

    function nextEntryId($id = 0) {
        global $database;
        $this->init();
        $maxId = POD::queryCell("SELECT MAX(id) FROM {$database['prefix']}Entries WHERE blogid = " . $this->blogid);
        if (!$maxId) {
            /* Oddly, database connection is dropped frequently in this point */
            $maxId = POD::queryCell("SELECT MAX(id) FROM {$database['prefix']}Entries WHERE blogid = " . $this->blogid);
        }
        if ($id == 0) {
            return $maxId + 1;
        } else {
            return ($maxId > $id ? $maxId : $id);
        }
    }

    function _buildQuery() {
        global $database;
        $this->init();
        $query = DBModel::getInstance();
        $query->reset('Entries');
        $query->setQualifier('blogid', 'equals', $this->blogid);
        if (isset($this->id)) {
            if (!Validator::number($this->id, 1)) {
                return $this->_error('id');
            }
            $query->setQualifier('id', 'equals', $this->id);
        }
        if (isset($this->userid)) {
            if (!Validator::number($this->userid, 1)) {
                return $this->_error('userid');
            }
            $query->setQualifier('userid', 'equals', $this->userid);
        }
        if (isset($this->title)) {
            $query->setAttribute('title', Utils_Unicode::lessenAsEncoding($this->title, 255), true);
        }
        if (isset($this->content)) {
            $query->setAttribute('content', $this->content, true);
            $query->setAttribute('contentformatter', $this->contentformatter, true);
            $query->setAttribute('contenteditor', $this->contenteditor, true);
        }
        if (isset($this->visibility)) {
            switch ($this->visibility) {
                case 'appointed':
                    $query->setAttribute('visibility', -2);
                    break;
                case 'private':
                    $query->setAttribute('visibility', 0);
                    break;
                case 'protected':
                    $query->setAttribute('visibility', 1);
                    if (empty($this->password)) {
                        $this->password = $this->makePassword();
                    }
                    break;
                case 'public':
                    $query->setAttribute('visibility', 2);
                    break;
                case 'syndicated':
                    $query->setAttribute('visibility', 3);
                    break;
                default:
                    return $this->_error('visibility');
            }
        }
        if (isset($this->starred)) {
            $query->setAttribute('starred', $this->starred);
        } else {
            $query->setAttribute('starred', 0);
        }
        if (isset($this->category)) {
            if (!Category::doesExist($this->category)) {
                return $this->_error('category');
            }
            $query->setAttribute('category', $this->category);
        }
        if (isset($this->location)) {
            $query->setAttribute('location', Utils_Unicode::lessenAsEncoding($this->location, 255), true);
        }
        if (isset($this->password)) {
            $query->setAttribute('password', $this->password, true);
        }
        if (isset($this->acceptcomment)) {
            $query->setAttribute('acceptcomment', Validator::getBit($this->acceptcomment));
        }
        if (isset($this->accepttrackback)) {
            $query->setAttribute('accepttrackback', Validator::getBit($this->accepttrackback));
        }
        if (isset($this->published)) {
            if (!Validator::number($this->published, 0)) {
                return $this->_error('published');
            }
            $query->setAttribute('published', $this->published);
        }
        if (isset($this->longitude) && Validator::number($this->longitude)) {
            $query->setAttribute('longitude', $this->longitude);
        }
        if (isset($this->latitude) && Validator::number($this->latitude)) {
            $query->setAttribute('latitude', $this->latitude);
        }
        if (isset($this->created)) {
            if (!Validator::number($this->created, 0)) {
                return $this->_error('created');
            }
            $query->setAttribute('created', $this->created);
        }
        if (isset($this->modified)) {
            if (!Validator::number($this->modified, 0)) {
                return $this->_error('modified');
            }
            $query->setAttribute('modified', $this->modified);
        }
        return $query;
    }

    /*@static@*/
    function correctTagsAll() {
        global $database;
        $targetresult = POD::query("SELECT * FROM {$database['prefix']}TagRelations");
        if ($targetresult != false) {
            while ($target = POD::fetch($targetresult)) {
                $oldtag = POD::queryRow("SELECT id, name FROM {$database['prefix']}Tags WHERE id = {$target['tag']}");
                if (!is_null($oldtag)) {
                    $tagid = POD::queryCell("SELECT id FROM {$database['prefix']}Tags WHERE name = '" . POD::escapeString($oldtag['name']) . "' LIMIT 1 ");
                    if (is_null($tagid)) {
                        POD::execute("DELETE FROM {$database['prefix']}TagRelations WHERE blogid = {$target['blogid']} AND tag = {$target['tag']} AND entry = {$target['entry']}");
                    } else {
                        if ($tagid == $oldtag['id']) {
                            continue;
                        }
                        if (POD::execute("UPDATE {$database['prefix']}TagRelations SET tag = $tagid WHERE blogid = {$target['blogid']} AND tag = {$target['tag']} AND entry = {$target['entry']}") == false) { // maybe duplicated tag
                            POD::execute("DELETE FROM {$database['prefix']}TagRelations WHERE blogid = {$target['blogid']} AND tag = {$target['tag']} AND entry = {$target['entry']}");
                        }
                    }
                } else { // Ooops!
                    POD::execute("DELETE FROM {$database['prefix']}TagRelations WHERE blogid = {$target['blogid']} AND tag = {$target['tag']} AND entry = {$target['entry']}");
                }
            }
            POD::free($targetresult);
        }

        $targetresult = POD::query("SELECT id FROM {$database['prefix']}Tags LEFT JOIN {$database['prefix']}TagRelations ON id = tag WHERE tag IS NULL");
        if ($targetresult != false) {
            while ($target = POD::fetch($targetresult)) {
                $tag = $target['id'];
                POD::execute("DELETE FROM {$database['prefix']}Tags WHERE id = $tag ");
            }
            POD::free($targetresult);
        }
    }

    function _error($error) {
        $this->error = $error;
        return false;
    }
}

?>
