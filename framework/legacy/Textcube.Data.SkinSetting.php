<?php
/// Copyright (c) 2004-2011, Needlworks  / Tatter Network Foundation
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
		$pool = DBModel::getInstance();
		$pool->reset('SkinSettings');
		$pool->setQualifier('blogid','equals',getBlogId());
		$result = $pool->getAll('name, value');
		if (!empty($result)) {
			foreach ($result as $data) {
				if (!empty($date['name'])) {
					if ($data['name'] == 'tagboxAlign') $data['name'] = 'alignOnTagbox';
					$this->$data['name'] = $data['value'];
				}
			}
			return true;
		}
		return false;
	}
	
	function save() {
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
			Setting::setSkinSetting('skin', $this->skin);
		}
		if (isset($this->entriesOnRecent)) {
			if (!Validator::number($this->entriesOnRecent, 1))
				return $this->_error('entriesOnRecent');
			Setting::setSkinSetting('entriesOnRecent', $this->entriesOnRecent);
		}
		if (isset($this->commentsOnRecent)) {
			if (!Validator::number($this->commentsOnRecent, 1))
				return $this->_error('commentsOnRecent');
			Setting::setSkinSetting('commentsOnRecent', $this->commentsOnRecent);
		}
		if (isset($this->trackbacksOnRecent)) {
			if (!Validator::number($this->trackbacksOnRecent, 1))
				return $this->_error('trackbacksOnRecent');
			Setting::setSkinSetting('trackbacksOnRecent', $this->trackbacksOnRecent);
		}
		if (isset($this->commentsOnGuestbook)) {
			if (!Validator::number($this->commentsOnGuestbook, 1))
				return $this->_error('commentsOnGuestbook');
			Setting::setSkinSetting('commentsOnGuestbook', $this->commentsOnGuestbook);
		}
		if (isset($this->tagsOnTagbox)) {
			if (!Validator::number($this->tagsOnTagbox, 1))
				return $this->_error('tagsOnTagbox');
			Setting::setSkinSetting('tagsOnTagbox', $this->tagsOnTagbox);
		}
		if (isset($this->alignOnTagbox)) {
			if (!Validator::number($this->alignOnTagbox, 1, 3))
				return $this->_error('alignOnTagbox');
			Setting::setSkinSetting('tagboxAlign', $this->alignOnTagbox);
		}
		if (isset($this->expandComment))
			Setting::setSkinSetting('expandComment', Validator::getBit($this->expandComment));
		if (isset($this->expandTrackback))
			Setting::setSkinSetting('expandTrackback', Validator::getBit($this->expandTrackback));
		if (isset($this->recentNoticeLength)) {
			if (!Validator::number($this->recentNoticeLength, 0))
				return $this->_error('recentNoticeLength');
			Setting::setSkinSetting('recentNoticeLength', $this->recentNoticeLength);
		}
		if (isset($this->recentTrackbackLength)) {
			if (!Validator::number($this->recentTrackbackLength, 0))
				return $this->_error('recentTrackbackLength');
			Setting::setSkinSetting('recentTrackbackLength', $this->recentTrackbackLength);
		}
		if (isset($this->linkLength)) {
			if (!Validator::number($this->linkLength, 0))
				return $this->_error('linkLength');
			Setting::setSkinSetting('linkLength', $this->linkLength);
		}
		if (isset($this->showListOnCategory))
			Setting::setSkinSetting('showListOnCategory', Validator::getBit($this->showListOnCategory));
		if (isset($this->showListOnArchive))
			Setting::setSkinSetting('showListOnArchive', Validator::getBit($this->showListOnArchive));
		if (isset($this->tree)) {
			if (!Validator::directory($this->tree) || !file_exists(ROOT . '/skin/tree/' . $this->tree))
				return $this->_error('tree');
			Setting::setSkinSetting('tree', $this->tree);
		}
		if (isset($this->colorOnTree))
			Setting::setSkinSetting('colorOnTree', $this->colorOnTree);
		if (isset($this->bgcolorOnTree))
			Setting::setSkinSetting('bgcolorOnTree', $this->bgcolorOnTree);
		if (isset($this->activecolorOnTree))
			Setting::setSkinSetting('activecolorOnTree', $this->activecolorOnTree);
		if (isset($this->activebgcolorOnTree))
			Setting::setSkinSetting('activebgcolorOnTree', $this->activebgcolorOnTree);
		if (isset($this->labelLengthOnTree)) {
			if (!Validator::number($this->labelLengthOnTree, 0))
				return $this->_error('labelLengthOnTree');
			Setting::setSkinSetting('labelLengthOnTree', $this->labelLengthOnTree);
		}
		if (isset($this->showValueOnTree))
			Setting::setSkinSetting('showValueOnTree', Validator::getBit($this->showValueOnTree));
		return true;
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
