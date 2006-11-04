<?php
/// Copyright (c) 2004-2006, Tatter & Company / Tatter & Friends.
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)
class BlogSetting {
	function BlogSetting() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->name =
		$this->secondaryDomain =
		$this->defaultDomain =
		$this->title =
		$this->description =
		$this->banner =
		$this->useSlogan =
		$this->entriesOnPage =
		$this->entriesOnList =
		$this->entriesOnFeed =
		$this->publishWholeOnFeed =
		$this->acceptGuestComment =
		$this->acceptCommentOnGuestComment =
		$this->language =
		$this->timezone =
			null;
	}
	
	function load($fields = '*') {
		global $database, $owner;
		$this->reset();
		if ($result = mysql_query("SELECT $fields FROM {$database['prefix']}BlogSettings WHERE owner = $owner")) {
			if ($row = mysql_fetch_assoc($result)) {
				foreach ($row as $name => $value) {
					if ($name == 'owner')
						continue;
					switch ($name) {
						case 'logo':
							$name = 'banner';
							break;
						case 'entriesOnPage':
							$name = 'postsOnPage';
							break;
						case 'entriesOnList':
							$name = 'postsOnList';
							break;
						case 'entriesOnRSS':
							$name = 'postsOnFeed';
							break;
						case 'publishWholeOnRSS':
							$name = 'publishWholeOnFeed';
							break;
						case 'allowWriteOnGuestbook':
							$name = 'acceptGuestComment';
							break;
						case 'allowWriteDoubleCommentOnGuestbook':
							$name = 'acceptCommentOnGuestComment';
							break;
					}
					switch ($name) {
						case 'defaultDomain':
						case 'useSlogan':
						case 'acceptGuestComment':
						case 'acceptCommentOnGuestComment':
							$value = $value ? true : false;
							break;
					}
					$this->$name = $value;
				}
			}
			mysql_free_result($result);
			return true;
		}
		return false;
	}
	
	function save() {
		global $database, $owner;
		
		$query = new TableQuery($database['prefix'] . 'BlogSettings');
		$query->setQualifier('owner', $owner);

		if (isset($this->name)) {
			$this->name = trim($this->name);
			if (!BlogSetting::validateName($this->name))
				return $this->_error('name');
			$query->setAttribute('name', $this->name, true);
		}
		if (isset($this->secondaryDomain)) {
			$this->secondaryDomain = trim($this->secondaryDomain);
			if (!Validator::domain($this->secondaryDomain))
				return $this->_error('secondaryDomain');
			$query->setAttribute('secondaryDomain', $this->secondaryDomain, true);
		}
		if (isset($this->defaultDomain))
			$query->setAttribute('defaultDomain', Validator::getBit($this->defaultDomain));
		if (isset($this->title)) {
			$this->title = trim($this->title);
			$query->setAttribute('title', $this->title, true);
		}
		if (isset($this->description)) {
			$this->description = trim($this->description);
			$query->setAttribute('description', $this->description, true);
		}
		if (isset($this->banner)) {
			if ((strlen($this->banner) != 0) && !Validator::filename($this->banner))
				return $this->_error('banner');
			$query->setAttribute('logo', $this->banner, true);
		}
		if (isset($this->useSlogan))
			$query->setAttribute('useSlogan', Validator::getBit($this->useSlogan));
		if (isset($this->postsOnPage)) {
			if (!Validator::number($this->postsOnPage, 1))
				return $this->_error('postsOnPage');
			$query->setAttribute('entriesOnPage', $this->postsOnPage);
		}
		if (isset($this->postsOnList)) {
			if (!Validator::number($this->postsOnList, 1))
				return $this->_error('postsOnList');
			$query->setAttribute('entriesOnList', $this->postsOnList);
		}
		if (isset($this->postsOnFeed)) {
			if (!Validator::number($this->postsOnFeed, 1))
				return $this->_error('postsOnFeed');
			$query->setAttribute('entriesOnRSS', $this->postsOnFeed);
		}
		if (isset($this->publishWholeOnFeed))
			$query->setAttribute('publishWholeOnRSS', Validator::getBit($this->publishWholeOnFeed));
		if (isset($this->acceptGuestComment))
			$query->setAttribute('allowWriteOnGuestbook', Validator::getBit($this->acceptGuestComment));
		if (isset($this->acceptCommentOnGuestComment))
			$query->setAttribute('allowWriteDoubleCommentOnGuestbook', Validator::getBit($this->acceptCommentOnGuestComment));
		if (isset($this->language)) {
			if (!Validator::language($this->language))
				return $this->_error('language');
			$query->setAttribute('language', $this->language, false);
		}
		if (isset($this->timezone)) {
			if (empty($this->timezone))
				return $this->_error('timezone');
			$query->setAttribute('timezone', $this->timezone, false);
		}
		if (!$query->getQualifiersCount())
			return $this->_error('nothing');
		
		if ($query->doesExist()) {
			if ($query->update())
				return true;
			else
				return $this->_error('update');
		} else if ($query->insert()) {
			return true;
		}
		return $this->_error('insert');
	}
	
	function escape($escape = true) {
		$this->name = Validator::escapeXML(@$this->name, $escape);
		$this->secondaryDomain = Validator::escapeXML(@$this->secondaryDomain, $escape);
		$this->title = Validator::escapeXML(@$this->title, $escape);
		$this->description = Validator::escapeXML(@$this->description, $escape);
	}
	
	/*@static@*/
	function setTimezone($timezone) {
		if (Timezone::set($timezone)) {
			$setting = new BlogSetting();
			$setting->timezone = $timezone;
			return $setting->save();
		}
	}

	/*@static@*/
	function validateName($name) {
		return ereg('^[[:alnum:]]+$', $name);
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>