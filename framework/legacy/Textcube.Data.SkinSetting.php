<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)
class SkinSetting {
	function SkinSetting() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->skin =
		$this->entriesOnRecent =
		$this->commentsOnRecent =
		$this->trackbacksOnRecent =
		$this->commentsOnGuestbook =
		$this->tagsOnTagbox =
		$this->alignOnTagbox =
		$this->expandComment =
		$this->expandTrackback =
		$this->recentNoticeLength =
		$this->recentEntryLength =
		$this->recentTrackbackLength =
		$this->linkLength =
		$this->showListOnCategory =
		$this->showListOnArchive =
		$this->tree =
		$this->colorOnTree =
		$this->bgcolorOnTree =
		$this->activecolorOnTree =
		$this->activebgcolorOnTree =
		$this->labelLengthOnTree =
		$this->showValueOnTree =
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
						case 'tagboxAlign':
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
		
		$query = new DBModel($database['prefix'] . 'SkinSettings');
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
		if (isset($this->entriesOnRecent)) {
			if (!Validator::number($this->entriesOnRecent, 1))
				return $this->_error('entriesOnRecent');
			$query->setAttribute('entriesOnRecent', $this->entriesOnRecent);
		}
		if (isset($this->commentsOnRecent)) {
			if (!Validator::number($this->commentsOnRecent, 1))
				return $this->_error('commentsOnRecent');
			$query->setAttribute('commentsOnRecent', $this->commentsOnRecent);
		}
		if (isset($this->trackbacksOnRecent)) {
			if (!Validator::number($this->trackbacksOnRecent, 1))
				return $this->_error('trackbacksOnRecent');
			$query->setAttribute('trackbacksOnRecent', $this->trackbacksOnRecent);
		}
		if (isset($this->commentsOnGuestbook)) {
			if (!Validator::number($this->commentsOnGuestbook, 1))
				return $this->_error('commentsOnGuestbook');
			$query->setAttribute('commentsOnGuestbook', $this->commentsOnGuestbook);
		}
		if (isset($this->tagsOnTagbox)) {
			if (!Validator::number($this->tagsOnTagbox, 1))
				return $this->_error('tagsOnTagbox');
			$query->setAttribute('tagsOnTagbox', $this->tagsOnTagbox);
		}
		if (isset($this->alignOnTagbox)) {
			if (!Validator::number($this->alignOnTagbox, 1, 3))
				return $this->_error('alignOnTagbox');
			$query->setAttribute('tagboxAlign', $this->alignOnTagbox);
		}
		if (isset($this->expandComment))
			$query->setAttribute('expandComment', Validator::getBit($this->expandComment));
		if (isset($this->expandTrackback))
			$query->setAttribute('expandTrackback', Validator::getBit($this->expandTrackback));
		if (isset($this->recentNoticeLength)) {
			if (!Validator::number($this->recentNoticeLength, 0))
				return $this->_error('recentNoticeLength');
			$query->setAttribute('recentNoticeLength', $this->recentNoticeLength);
		}
		if (isset($this->recentTrackbackLength)) {
			if (!Validator::number($this->recentTrackbackLength, 0))
				return $this->_error('recentTrackbackLength');
			$query->setAttribute('recentTrackbackLength', $this->recentTrackbackLength);
		}
		if (isset($this->linkLength)) {
			if (!Validator::number($this->linkLength, 0))
				return $this->_error('linkLength');
			$query->setAttribute('linkLength', $this->linkLength);
		}
		if (isset($this->showListOnCategory))
			$query->setAttribute('showListOnCategory', Validator::getBit($this->showListOnCategory));
		if (isset($this->showListOnArchive))
			$query->setAttribute('showListOnArchive', Validator::getBit($this->showListOnArchive));
		if (isset($this->tree)) {
			if (!Validator::directory($this->tree) || !file_exists(ROOT . '/skin/tree/' . $this->tree))
				return $this->_error('tree');
			$query->setAttribute('tree', $this->tree, false);
		}
		if (isset($this->colorOnTree))
			$query->setAttribute('colorOnTree', $this->colorOnTree, true);
		if (isset($this->bgcolorOnTree))
			$query->setAttribute('bgcolorOnTree', $this->bgcolorOnTree, true);
		if (isset($this->activecolorOnTree))
			$query->setAttribute('activecolorOnTree', $this->activecolorOnTree, true);
		if (isset($this->activebgcolorOnTree))
			$query->setAttribute('activebgcolorOnTree', $this->activebgcolorOnTree, true);
		if (isset($this->labelLengthOnTree)) {
			if (!Validator::number($this->labelLengthOnTree, 0))
				return $this->_error('labelLengthOnTree');
			$query->setAttribute('labelLengthOnTree', $this->labelLengthOnTree);
		}
		if (isset($this->showValueOnTree))
			$query->setAttribute('showValueOnTree', Validator::getBit($this->showValueOnTree));
			
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
