<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class TrackbackLog {
    function __construct() {
        $this->reset();
    }

    function reset() {
        $this->error =
        $this->id =
        $this->entry =
        $this->url =
        $this->sent =
            null;
    }

    function open($filter = '', $fields = '*', $sort = 'written') {
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
        $this->_result = POD::query("SELECT $fields FROM {$database['prefix']}RemoteResponseLogs WHERE blogid = " . getBlogId() . " AND responsetype = 'trackback' $filter $sort");
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
                    case 'written':
                        $name = 'sent';
                        break;
                }
                $this->$name = $value;
            }
            return true;
        }
        return false;
    }

    function add() {
        if (!isset($this->id)) {
            $this->id = $this->nextId();
        } else {
            $this->id = $this->nextId($this->id);
        }
        if (!isset($this->entry)) {
            return $this->_error('entry');
        }
        if (!isset($this->url)) {
            return $this->_error('url');
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

    function nextId($id = 0) {
        global $database;
        $maxId = POD::queryCell("SELECT max(id) FROM {$database['prefix']}RemoteResponseLogs WHERE blogid = " . getBlogId());
        if ($id == 0) {
            return $maxId + 1;
        } else {
            return ($maxId > $id ? $maxId + 1 : $id);
        }
    }

    function _buildQuery() {
        $query = DBModel::getInstance();
        $query->reset('RemoteResponseLogs');
        $query->setQualifier('blogid', 'equals', getBlogId());
        $query->setQualifier('responsetype', 'equals', 'trackback', true);
        if (isset($this->id)) {
            if (!Validator::number($this->id, 1)) {
                return $this->_error('id');
            }
            $query->setQualifier('id', 'equals', $this->id);
        }
        if (isset($this->entry)) {
            if (!Validator::number($this->entry, 1)) {
                return $this->_error('entry');
            }
            $query->setAttribute('entry', $this->entry);
        }
        if (isset($this->url)) {
            $this->url = Utils_Unicode::lessenAsEncoding(trim($this->url), 255);
            if (empty($this->url)) {
                return $this->_error('url');
            }
            $query->setAttribute('url', $this->url, true);
        }
        if (isset($this->sent)) {
            if (!Validator::timestamp($this->sent)) {
                return $this->_error('sent');
            }
            $query->setAttribute('written', $this->sent);
        }
        return $query;
    }

    function _error($error) {
        $this->error = $error;
        return false;
    }
}

?>
