<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class PluginSetting {
    function __construct() {
        $this->reset();
    }

    function reset() {
        $this->error =
        $this->name =
        $this->setting =
            null;
    }

    function open($name = '', $fields = '*', $sort = 'name') {
        global $database;
        if (!empty($name)) {
            $name = 'AND name = \'' . $name . '\'';
        }
        if (!empty($sort)) {
            $sort = 'ORDER BY ' . $sort;
        }
        $this->close();
        $this->_result = POD::query("SELECT $fields FROM {$database['prefix']}Plugins WHERE blogid = " . getBlogId() . " $name $sort");
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
                if ($name == 'blogid') {
                    continue;
                }
                switch ($name) {
                    case 'settings':
                        $name = 'setting';
                        break;
                }
                $this->$name = $value;
            }
            return true;
        }
        return false;
    }

    function add() {
        if (!$query = $this->_buildQuery()) {
            return false;
        }
        return $query->insert();
    }

    function update() {
        if (!$query = $this->_buildQuery()) {
            return false;
        }
        if (!$query->getAttributeCount()) {
            return $this->_error('nothing');
        }
        return $query->update();
    }

    function getCount() {
        return (isset($this->_count) ? $this->_count : 0);
    }

    function _buildQuery() {
        if (!Validator::directory($this->name)) {
            return $this->_error('name');
        }

        $query = DBModel::getInstance();
        $query->reset('Plugins');
        $query->setQualifier('blogid', 'equals', getBlogId());
        $query->setQualifier('name', 'equals', Utils_Unicode::lessenAsEncoding($this->name, 255), true);
        if (isset($this->setting)) {
            $query->setAttribute('settings', $this->setting, true);
        }
        return $query;
    }

    function _error($error) {
        $this->error = $error;
        return false;
    }
}

?>
