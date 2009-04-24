<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class Model_BlogStatistics {
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
		if ($result = Data_IAdapter::query("SELECT visits FROM {$database['prefix']}BlogStatistics WHERE blogid = ".getBlogId())) {
			if ($row = Data_IAdapter::fetch($result)) {
				foreach ($row as $name => $value) {
					if ($name == 'owner')
						continue;
					$this->$name = $value;
				}
				Data_IAdapter::free($result);
				return true;
			}
			Data_IAdapter::free($result);
		}
		return false;
	}
	
	function add() {
		if (!isset($this->visits))
			$this->visits = 1;
		
		if (!$query = $this->_buildQuery())
			return false;

		if ($query->doesExist()) {
			$query->setAttribute('visits', "visits + {$this->visits}");
			if (!$query->update())
				return $this->_error('update');
		} else if (!$query->insert()) {
			return $this->_error('insert');
		}
		return true;
	}
	
	function update() {
		if (!isset($this->visits))
			$this->visits = 1;
		
		if (!$query = $this->_buildQuery())
			return false;

		if ($query->doesExist()) {
			if (!$query->update())
				return $this->_error('update');
		} else if (!$query->insert()) {
			return $this->_error('insert');
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
		global $database;
		$query = new Data_Table($database['prefix'] . 'BlogStatistics');
		$query->setQualifier('blogid', getBlogId());
		if (isset($this->visits)) {
			if (!Validator::number($this->visits, 0))
				return $this->_error('visits');
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
