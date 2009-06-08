<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class SkinSetting {
	function SkinSetting() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->skin =
		$this->entriesonrecent =
		$this->commentsonrecent =
		$this->trackbacksonrecent =
		$this->commentsonguestbook =
		$this->tagsontagbox =
		$this->alignOnTagbox =
		$this->expandcomment =
		$this->expandtrackback =
		$this->recentnoticelength =
		$this->recententrylength =
		$this->recenttrackbacklength =
		$this->linklength =
		$this->showlistoncategory =
		$this->showlistonarchive =
		$this->tree =
		$this->colorontree =
		$this->bgcolorontree =
		$this->activecolorontree =
		$this->activebgcolorontree =
		$this->labellengthontree =
		$this->showvalueontree =
			null;
	}
	
	function load($fields = '*') {
		global $database;
		$this->reset();
		if ($result = POD::query("SELECT $fields FROM {$database['prefix']}SkinSettings WHERE blogid = ".getBlogId())) {
			if ($row = POD::fetch($result)) {
				foreach ($row as $name => $value) {
					if ($name == 'blogid')
						continue;
					switch ($name) {
						case 'tagboxalign':
							$name = 'alignOnTagbox';
							break;
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
	
	function save() {
		global $database;
		
		$query = new TableQuery($database['prefix'] . 'SkinSettings');
		$query->setQualifier('blogid', 'equals', getBlogId());
		if (isset($this->skin)) {
			if (strncmp($this->skin, 'customize/', 10) == 0) {
				if (strcmp($this->skin, "customize/".getBlogId()) != 0)
					return $this->_error('skin');
			} else {
				if (!Validator::filename($this->skin))
					return $this->_error('skin');
			}
			if (!Validator::path($this->skin) || !file_exists(ROOT . '/skin/' . $this->skin))
				return $this->_error('skin');
			$query->setAttribute('skin', $this->skin, false);
		}
		if (isset($this->entriesonrecent)) {
			if (!Validator::number($this->entriesonrecent, 1))
				return $this->_error('entriesonrecent');
			$query->setAttribute('entriesonrecent', $this->entriesonrecent);
		}
		if (isset($this->commentsonrecent)) {
			if (!Validator::number($this->commentsonrecent, 1))
				return $this->_error('commentsonrecent');
			$query->setAttribute('commentsonrecent', $this->commentsonrecent);
		}
		if (isset($this->trackbacksonrecent)) {
			if (!Validator::number($this->trackbacksonrecent, 1))
				return $this->_error('trackbacksonrecent');
			$query->setAttribute('trackbacksonrecent', $this->trackbacksonrecent);
		}
		if (isset($this->commentsonguestbook)) {
			if (!Validator::number($this->commentsonguestbook, 1))
				return $this->_error('commentsonguestbook');
			$query->setAttribute('commentsonguestbook', $this->commentsonguestbook);
		}
		if (isset($this->tagsontagbox)) {
			if (!Validator::number($this->tagsontagbox, 1))
				return $this->_error('tagsontagbox');
			$query->setAttribute('tagsontagbox', $this->tagsontagbox);
		}
		if (isset($this->alignOnTagbox)) {
			if (!Validator::number($this->alignOnTagbox, 1, 3))
				return $this->_error('alignOnTagbox');
			$query->setAttribute('tagboxalign', $this->alignOnTagbox);
		}
		if (isset($this->expandcomment))
			$query->setAttribute('expandcomment', Validator::getBit($this->expandcomment));
		if (isset($this->expandtrackback))
			$query->setAttribute('expandtrackback', Validator::getBit($this->expandtrackback));
		if (isset($this->recentnoticelength)) {
			if (!Validator::number($this->recentnoticelength, 0))
				return $this->_error('recentnoticelength');
			$query->setAttribute('recentnoticelength', $this->recentnoticelength);
		}
		if (isset($this->recenttrackbacklength)) {
			if (!Validator::number($this->recenttrackbacklength, 0))
				return $this->_error('recenttrackbacklength');
			$query->setAttribute('recenttrackbacklength', $this->recenttrackbacklength);
		}
		if (isset($this->linklength)) {
			if (!Validator::number($this->linklength, 0))
				return $this->_error('linklength');
			$query->setAttribute('linklength', $this->linklength);
		}
		if (isset($this->showlistoncategory))
			$query->setAttribute('showlistoncategory', Validator::getBit($this->showlistoncategory));
		if (isset($this->showlistonarchive))
			$query->setAttribute('showlistonarchive', Validator::getBit($this->showlistonarchive));
		if (isset($this->tree)) {
			if (!Validator::directory($this->tree) || !file_exists(ROOT . '/image/tree/' . $this->tree))
				return $this->_error('tree');
			$query->setAttribute('tree', $this->tree, false);
		}
		if (isset($this->colorontree))
			$query->setAttribute('colorontree', $this->colorontree, true);
		if (isset($this->bgcolorontree))
			$query->setAttribute('bgcolorontree', $this->bgcolorontree, true);
		if (isset($this->activecolorontree))
			$query->setAttribute('activecolorontree', $this->activecolorontree, true);
		if (isset($this->activebgcolorontree))
			$query->setAttribute('activebgcolorontree', $this->activebgcolorontree, true);
		if (isset($this->labellengthontree)) {
			if (!Validator::number($this->labellengthontree, 0))
				return $this->_error('labellengthontree');
			$query->setAttribute('labellengthontree', $this->labellengthontree);
		}
		if (isset($this->showvalueontree))
			$query->setAttribute('showvalueontree', Validator::getBit($this->showvalueontree));
			
		if ($query->update())
			return true;
		return $query->insert();
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
