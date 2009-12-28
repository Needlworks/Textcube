<?php
class SubscriptionLog {
	function SubscriptionLog() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->ip =
		$this->host =
		$this->useragent =
		$this->referred =
			null;
	}
	
	function open($filter = '', $fields = '*', $sort = 'referred DESC') {
		global $database;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields 
			FROM {$database['prefix']}SubscriptionLogs 
			WHERE blogid = ".getBlogId()." $filter $sort");
		if ($this->_result) {
			if ($this->_count = POD::num_rows($this->_result))
				return $this->shift();
			else
				POD::free($this->_result);
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
				if ($name == 'blogid')
					continue;
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add($compile = true) {
		if (!isset($this->url))
			return $this->_error('url');
		$this->host = null;
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->hasAttribute('referred'))
			$query->setAttribute('referred', 'UNIX_TIMESTAMP()');

		if (!$query->insert())
			return $this->_error('insert');
		
		if ($compile) {
			SubscriptionStatistics::compile($this->host);
		}
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}

	function _buildQuery() {
		$query = DBModel::getInstance();
		$query->reset('SubscriptionLogs');
		$query->setQualifier('blogid', 'equals', getBlogId());
		if (isset($this->ip)) {
			if (!Validator::ip($this->ip))
				return $this->_error('ip');
			$query->setAttribute('ip', $this->ip, true);
		}
		if (isset($this->host))
			$query->setAttribute('host', $this->host, true);
		if (isset($this->useragent))
			$query->setAttribute('useragent', $this->useragent, true);
		if (isset($this->referred)) {
			if (!Validator::number($this->referred, 1))
				return $this->_error('referred');
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
