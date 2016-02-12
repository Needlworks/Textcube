<?php

class SubscriptionStatistics {
    function __construct() {
        $this->reset();
    }

    function reset() {
        $this->error =
        $this->ip =
        $this->host =
        $this->useragent =
        $this->subscribed =
        $this->referred =
            null;
    }

    function open($filter = '', $fields = '*', $sort = 'subscribed DESC') {
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
        $this->_result = POD::query("SELECT $fields FROM {$database['prefix']}SubscriptionStatistics WHERE blogid = " . getBlogId() . " $filter $sort");
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
                $this->$name = $value;
            }
            return true;
        }
        return false;
    }

    function add() {
        if (!$query->hasAttribute('referred')) {
            $query->setAttribute('referred', 'UNIX_TIMESTAMP()');
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

    function update() {
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
        $instance = new SubscriptionStatistics();
        $instance->host = $host;
        $instance->count = 1;
        return $instance->update();
    }

    function _buildQuery() {
        global $database;
        $this->host = trim($this->host);
        if (empty($this->host)) {
            return $this->_error('host');
        }
        $query = DBModel::getInstance();
        $query->reset('SubscriptionStatistics');
        $query->setQualifier('blogid', 'equals', getBlogId());
        if (isset($this->ip)) {
            if (!Validator::ip($this->ip)) {
                return $this->_error('ip');
            }
            $query->setAttribute('ip', $this->ip, true);
        }
        if (isset($this->host)) {
            $query->setAttribute('host', $this->host, true);
        }
        if (isset($this->useragent)) {
            $query->setAttribute('useragent', $this->useragent, true);
        }
        if (isset($this->subscribed)) {
            if (!Validator::number($this->subscribed, 1)) {
                return $this->_error('subscribed');
            }
            $query->setAttribute('subscribed', $this->subscribed);
        }
        if (isset($this->referred)) {
            if (!Validator::number($this->referred, 1)) {
                return $this->_error('referred');
            }
            $query->setAttribute('referred', $this->referred);
        }
        return $query;
    }

    function _error($error) {
        $this->error = $error;
        return false;
    }
}

?>
