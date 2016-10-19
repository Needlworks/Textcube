<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class BlogStatistics {
    function __construct() {
        $this->reset();
    }

    function reset() {
        $this->error =
        $this->visits =
            null;
    }

    function load() {
        global $database;
        $this->reset();
        if ($result = POD::query("SELECT visits FROM {$database['prefix']}BlogStatistics WHERE blogid = " . getBlogId())) {
            if ($row = POD::fetch($result)) {
                foreach ($row as $name => $value) {
                    if ($name == 'owner') {
                        continue;
                    }
                    $this->$name = $value;
                }
                POD::free($result);
                return true;
            }
            POD::free($result);
        }
        return false;
    }

    function add() {
        if (!isset($this->visits)) {
            $this->visits = 1;
        }

        if (!$query = $this->_buildQuery()) {
            return false;
        }

        if ($query->doesExist()) {
            $currentVisit = $query->getCell('visits');
            $query->setAttribute('visits', $currentVisit + $this->visits);
            if (!$query->update()) {
                return $this->_error('update');
            }
        } else {
            if (!$query->insert()) {
                return $this->_error('insert');
            }
        }
        return true;
    }

    function update() {
        if (!isset($this->visits)) {
            $this->visits = 1;
        }

        if (!$query = $this->_buildQuery()) {
            return false;
        }

        if ($query->doesExist()) {
            if (!$query->update()) {
                return $this->_error('update');
            }
        } else {
            if (!$query->insert()) {
                return $this->_error('insert');
            }
        }
        return true;
    }

    /*@static@*/
    function compile($host) {
        $instance = new BlogStatistics();
        $instance->host = $host;
        $instance->visits = 1;
        return $instance->update();
    }

    function _buildQuery() {
        $query = DBModel::getInstance();
        $query->reset('BlogStatistics');
        $query->setQualifier('blogid', 'equals', getBlogId());
        if (isset($this->visits)) {
            if (!Validator::number($this->visits, 0)) {
                return $this->_error('visits');
            }
            $query->setAttribute('visits', $this->visits);
        }
        return $query;
    }

    function _error($error) {
        $this->error = $error;
        return false;
    }
}

?>
