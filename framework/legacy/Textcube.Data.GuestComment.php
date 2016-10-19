<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class GuestComment {
    function __construct() {
        $this->reset();
    }

    function reset() {
        $this->error =
        $this->id =
        $this->parent =
        $this->commenter =
        $this->openid =
        $this->name =
        $this->homepage =
        $this->ip =
        $this->password =
        $this->secret =
        $this->content =
        $this->written =
            null;
    }

    function open($filter = '', $fields = '*', $sort = 'id') {
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
        $this->_result = POD::query("SELECT $fields
			FROM {$database['prefix']}Comments
			WHERE blogid = " . getBlogId() . "
				AND entry = 0
				$filter $sort");
        if ($this->_result) {
            if ($this->_count = POD::num_rows($this->_result)) {
                return $this->shift();
            } else {
                POD::free($this->_result);
            }
        }
        unset($this->_result);
        return false;
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
                if ($name == 'blogid') {
                    continue;
                }
                switch ($name) {
                    case 'replier':
                        $name = 'commenter';
                        break;
                    case 'comment':
                        $name = 'content';
                        break;
                }
                $this->$name = $value;
            }
            return true;
        }
        return false;
    }

    function add() {
        global $database;
        if (!isset($this->id)) {
            $this->id = $this->nextId();
        } else {
            $this->id = $this->nextId($this->id);
        }
        if (!isset($this->commenter) && !isset($this->name)) {
            return $this->_error('commenter');
        }
        if (!isset($this->content)) {
            return $this->_error('content');
        }
        if (!isset($this->ip)) {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }

        if (!$query = $this->_buildQuery()) {
            return false;
        }
        if (!$query->hasAttribute('written')) {
            $query->setAttribute('written', 'UNIX_TIMESTAMP()');
        }

        if (!$query->insert()) {
            return $this->_error('insert');
        }
        return true;
    }

    function getCount() {
        return (isset($this->_count) ? $this->_count : 0);
    }

    function getChildren() {
        if (!Validator::number($this->id, 1)) {
            return null;
        }
        $comment = new Comment();
        if ($comment->open('parent = ' . $this->id)) {
            return $comment;
        }
    }

    function nextId($id = 0) {
        global $database;
        $maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}Comments WHERE blogid = " . getBlogId());
        if ($id == 0) {
            return $maxId + 1;
        } else {
            return ($maxId > $id ? $maxId + 1 : $id);
        }
    }

    function _buildQuery() {
        global $database;
        $query = DBModel::getInstance();
        $query->reset('Comments');
        $query->setQualifier('blogid', 'equals', getBlogId());
        $query->setQualifier('entry', 'equals', 0);
        if (isset($this->id)) {
            if (!Validator::number($this->id, 1)) {
                return $this->_error('id');
            }
            $query->setQualifier('id', 'equals', $this->id);
        }
        if (isset($this->parent)) {
            if (!Validator::number($this->parent, 1)) {
                return $this->_error('parent');
            }
        }
        $query->setAttribute('parent', $this->parent);
        if (isset($this->commenter)) {
            if (!Validator::number($this->commenter, 1)) {
                return $this->_error('commenter');
            }
            if (!$this->name = User::getName($this->commenter)) {
                return $this->_error('commenter');
            }
            $query->setAttribute('replier', $this->commenter);
        }
        if (isset($this->name)) {
            $this->name = Utils_Unicode::lessenAsEncoding(trim($this->name), 80);
            if (empty($this->name)) {
                return $this->_error('name');
            }
            $query->setAttribute('name', $this->name, true);
        }
        if (isset($this->openid)) {
            $this->openid = Utils_Unicode::lessenAsEncoding(trim($this->openid), 128);
            if (empty($this->openid)) {
                return $this->_error('openid');
            }
            $query->setAttribute('openid', $this->openid, true);
        }
        if (isset($this->homepage)) {
            $this->homepage = Utils_Unicode::lessenAsEncoding(trim($this->homepage), 80);
            if (empty($this->homepage)) {
                return $this->_error('homepage');
            }
            $query->setAttribute('homepage', $this->homepage, true);
        }
        if (isset($this->ip)) {
            if (!Validator::ip($this->ip)) {
                return $this->_error('ip');
            }
            $query->setAttribute('ip', $this->ip, true);
        }
        if (isset($this->secret)) {
            $query->setAttribute('secret', Validator::getBit($this->secret));
        }
        if (isset($this->content)) {
            $this->content = trim($this->content);
            if (empty($this->content)) {
                return $this->_error('content');
            }
            $query->setAttribute('comment', $this->content, true);
        }
        if (isset($this->written)) {
            if (!Validator::timestamp($this->written)) {
                return $this->_error('written');
            }
            $query->setAttribute('written', $this->written);
        }
        if (isset($this->isfiltered)) {
            $query->setAttribute('isfiltered', Validator::getBit($this->isfiltered));
        }
        if (isset($this->password)) {
            $this->password = Utils_Unicode::lessenAsEncoding($this->password, 32);
            $query->setAttribute('password', $this->password, true);
            $this->password = null;
        }
        return $query;
    }

    function _error($error) {
        $this->error = $error;
        return false;
    }
}

?>
