<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class Keyword {
    function __construct() {
        $this->reset();
    }

    function reset() {
        $this->error =
        $this->id =
        $this->visibility =
        $this->starred =
        $this->name =
        $this->description =
        $this->descriptionEditor =
        $this->descriptionFormatter =
        $this->published =
        $this->created =
        $this->modified =
            null;
    }

    /*@polymorphous(numeric $id, $fields, $sort)@*/
    function open($filter = '', $fields = '*', $sort = 'published DESC') {
        global $database;
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
        $this->close();
        $this->_result = POD::query("SELECT $fields FROM {$database['prefix']}Entries WHERE blogid = " . getBlogId() . " AND draft = 0 AND category = -1 $filter $sort");
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
                switch ($name) {
                    case 'blogid':
                    case 'draft':
                    case 'category':
                        unset($name);
                        break;
                    case 'visibility':
                        if ($value <= 0) {
                            $value = 'private';
                        } else {
                            $value = 'public';
                        }
                        break;
                    case 'title':
                        $name = 'name';
                        break;
                    case 'content':
                        $name = 'description';
                        break;
                    case 'contentformatter':
                        $name = 'descriptionFormatter';
                        break;
                    case 'contenteditor':
                        $name = 'descriptionEditor';
                        break;
                }
                if (isset($name)) {
                    $this->$name = $value;
                }
            }
            return true;
        }
        return false;
    }

    function add() {
        global $database;
        if (isset($this->id) && !Validator::number($this->id, 1)) {
            return $this->_error('id');
        }
        $this->name = trim($this->name);
        if (empty($this->name)) {
            return $this->_error('name');
        }
        if (empty($this->description)) {
            return $this->_error('description');
        }

        if (!$query = $this->_buildQuery()) {
            return false;
        }
        if (!isset($this->id) || $query->doesExist() || $this->doesExist($this->id)) {
            $this->id = $this->nextEntryId();
        }
        $query->setQualifier('id', 'equals', $this->id);

        if (empty($this->starred)) {
            $this->starred = 0;
        }
        if (!isset($this->published)) {
            $query->setAttribute('published', 'UNIX_TIMESTAMP()');
        }
        if (!isset($this->created)) {
            $query->setAttribute('created', 'UNIX_TIMESTAMP()');
        }
        if (!isset($this->modified)) {
            $query->setAttribute('modified', 'UNIX_TIMESTAMP()');
        }

        if (!$query->insert()) {
            return $this->_error('insert');
        }

        return true;
    }

    function remove($id) {
        global $database;
        if (!is_numeric($id)) {
            return false;
        }
        $result = POD::query("DELETE FROM {$database['prefix']}Entries WHERE blogid = " . getBlogId() . " AND category = -1 AND id = $id ");
        if ($result && ($this->_count = POD::num_rows($result))) {
            return true;
        }
        return false;
    }

    function update() {
        if (!isset($this->id) || !Validator::number($this->id, 1)) {
            return $this->_error('id');
        }

        if (!$query = $this->_buildQuery()) {
            return false;
        }
        if (!isset($this->modified)) {
            $query->setAttribute('modified', 'UNIX_TIMESTAMP()');
        }

        if (!$query->update()) {
            return $this->_error('update');
        }
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
        return "$defaultURL/keyword/{$this->id}";
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

    /*@static@*/
    function doesExist($id) {
        global $database;
        if (!Validator::number($id, 1)) {
            return false;
        }
        return POD::queryExistence("SELECT id FROM {$database['prefix']}Entries WHERE blogid = " . getBlogId() . " AND id = $id AND category = -1 AND draft = 0");
    }

    function nextEntryId($id = 0) {
        global $database;
        $maxId = POD::queryCell("SELECT MAX(id) FROM {$database['prefix']}Entries WHERE blogid = " . getBlogId());
        if ($id == 0) {
            return $maxId + 1;
        } else {
            return ($maxId > $id ? $maxId : $id);
        }
    }

    function _buildQuery() {
        global $database;
        $query = DBModel::getInstance();
        $query->reset('Entries');
        $query->setQualifier('blogid', 'equals', getBlogId());
        $query->setQualifier('category', 'equals', -1);
        if (isset($this->id)) {
            if (!Validator::number($this->id, 1)) {
                return $this->_error('id');
            }
            $query->setQualifier('id', 'equals', $this->id);
        }
        if (isset($this->name)) {
            $query->setAttribute('title', $this->name, true);
        }
        if (isset($this->description)) {
            $query->setAttribute('content', $this->description, true);
            $query->setAttribute('contentformatter', $this->descriptionFormatter, true);
            $query->setAttribute('contenteditor', $this->descriptionEditor, true);
        }
        if (isset($this->visibility)) {
            switch ($this->visibility) {
                case 'private':
                    $query->setAttribute('visibility', 0);
                    break;
                case 'public':
                    $query->setAttribute('visibility', 2);
                    break;
                default:
                    $query->setAttribute('visibility', 0);
                    break;
            }
        }
        if (isset($this->starred)) {
            $query->setAttribute('starred', $this->starred);
        } else {
            $query->setAttribute('starred', 0);
        }
        if (isset($this->published)) {
            if (!Validator::number($this->published, 1)) {
                return $this->_error('published');
            }
            $query->setAttribute('published', $this->published);
        }
        if (isset($this->created)) {
            if (!Validator::number($this->created, 1)) {
                return $this->_error('created');
            }
            $query->setAttribute('created', $this->created);
        }
        if (isset($this->modified)) {
            if (!Validator::number($this->modified, 1)) {
                return $this->_error('modified');
            }
            $query->setAttribute('modified', $this->modified);
        }
        return $query;
    }

    function _error($error) {
        $this->error = $error;
        return false;
    }
}

?>
