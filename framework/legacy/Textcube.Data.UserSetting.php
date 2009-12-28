<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class UserSetting {
	function UserSetting() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->name =
		$this->value =
			null;
	}
	
	function open($name = '', $fields = '*', $sort = 'name') {
		global $database;
		if (!empty($name))
			$name = 'AND name = \'' . $name . '\'';
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = POD::query("SELECT $fields FROM {$database['prefix']}UserSettings WHERE userid = ".getUserId()." $name $sort");
		if ($this->_result)
			$this->_count = POD::num_rows($this->_result);
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
				if ($name == 'user')
					continue;
				switch ($name) {
					case 'value':
						$name = 'value';
						break;
				}
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}

	function add() {
		if (!$query = $this->_buildQuery())
			return false;
		return $query->insert();
	}
	
	function update() {
		if (!$query = $this->_buildQuery())
			return false;
		if (!$query->getAttributeCount())
			return $this->_error('nothing');
		return $query->update();
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}

	function _buildQuery() {
		$query = DBModel::getInstance();
		$query->reset('UserSettings');
		$query->setQualifier('userid', 'equals', getUserId());
		$query->setQualifier('name', 'equals', $this->name, false);
		if (isset($this->value))
			$query->setAttribute('value', $this->value, true);
		return $query;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
