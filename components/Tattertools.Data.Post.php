<?php
class Post {
	function Post() {
		$this->reset();
	}

	function reset() {
		$this->error =
		$this->id =
		$this->visibility =
		$this->title =
		$this->slogan =
		$this->content =
		$this->category =
		$this->tags =
		$this->location =
		$this->password =
		$this->acceptComment =
		$this->acceptTrackback =
		$this->published =
		$this->created =
		$this->modified =
		$this->comments =
		$this->trackbacks =
			null;
	}

	/*@polymorphous(numeric $id, $fields, $sort)@*/
	function open($filter = '', $fields = '*', $sort = 'published DESC') {
		global $database, $owner;
		if (is_numeric($filter))
			$filter = 'AND id = ' . $filter;
		else if (!empty($filter))
			$filter = 'AND ' . $filter;
		if (!empty($sort))
			$sort = 'ORDER BY ' . $sort;
		$this->close();
		$this->_result = mysql_query("SELECT $fields FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 AND category >= 0 $filter $sort");
		if ($this->_result)
			$this->_count = mysql_num_rows($this->_result);
		return $this->shift();
	}
	
	function close() {
		if (isset($this->_result)) {
			mysql_free_result($this->_result);
			unset($this->_result);
		}
		$this->_count = 0;
		$this->reset();
	}
	
	function shift() {
		$this->reset();
		if ($this->_result && ($row = mysql_fetch_assoc($this->_result))) {
			foreach ($row as $name => $value) {
				if ($name == 'owner')
					continue;
				switch ($name) {
					case 'visibility':
						if ($value <= 0)
							$value = 'private';
						else if ($value == 1)
							$value = 'protected';
						else if ($value == 2)
							$value = 'public';
						else
							$value = 'syndicated';
						break;
					case 'category': /*@backward-compatibility@*/
						if (empty($value))
							$value = null;
						break;
					case 'acceptComment':
					case 'acceptTrackback':
						$value = $value ? true : false;
						break;
				}
				$this->$name = $value;
			}
			return true;
		}
		return false;
	}
	
	function add() {
		global $database, $owner;
		if (isset($this->id) && !Validator::number($this->id, 1))
			return $this->_error('id');
		if (isset($this->category) && !Validator::number($this->category, 1))
			return $this->_error('category');
		$this->title = trim($this->title);
		if (empty($this->title))
			return $this->_error('title');
		if (empty($this->content))
			return $this->_error('content');

		if (!$query = $this->_buildQuery())
			return false;
		if (isset($this->id)) {
			if ($query->doesExist()) {
				$this->id = null;
				$query->setQualifier('id', null);
			}
		}
		if (!isset($this->published))
			$query->setAttribute('published', 'UNIX_TIMESTAMP()');
		if (!isset($this->created))
			$query->setAttribute('created', 'UNIX_TIMESTAMP()');
		if (!isset($this->modified))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');

		if (!$query->insert())
			return $this->_error('insert');
		$this->id = $query->id;
		
		if (isset($this->category)) {
			$target = ($parentCategory = Category::getParent($this->category)) ? '(id = ' . $this->category . ' OR id = ' . $parentCategory . ')' : 'id = ' . $this->category;
			if (isset($this->visibility) && ($this->visibility != 'private'))
				mysql_query("UPDATE {$database['prefix']}Categories SET entries = entries + 1, entriesInLogin = entriesInLogin + 1 WHERE owner = $owner AND " . $target);
			else
				mysql_query("UPDATE {$database['prefix']}Categories SET entriesInLogin = entriesInLogin + 1 WHERE owner = $owner AND " . $target);
		}
		$this->saveSlogan();
		$this->addTags();
		if (($this->visibility == 'public') || ($this->visibility == 'syndicated')) {
			requireComponent('Tattertools.Control.RSS');
			RSS::refresh();
		}
		if ($this->visibility == 'syndicated') {
			requireComponent('Eolin.API.Syndication');
			if (!Syndication::join($this->getLink())) {
				$query->resetAttributes();
				$query->setAttribute('visibility', 2);
				$this->visibility = 'public';
				$query->update();
			}
		}
		return true;
	}
	
	function remove($id) { // attachment & category is own your risk!
		global $database, $owner;
		// step 0. Get Information
		if (!isset($this->id) || !Validator::number($this->id, 1))
			return $this->_error('id');

		if (!$query = $this->_buildQuery())
			return false;
			
		if (!$entry = $query->getRow('category, visibility'))
			return $this->_error('id');
			
		// step 1. Check Syndication
		if ($old['visibility'] == 3) {
			requireComponent('Eolin.API.Syndication');
			Syndication::leave($this->getLink());
		}
		
		// step 2. Delete Entry
		$result = DBQuery::execute("DELETE FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $this->id");
		if (mysql_affected_rows() > 0) {
		// step 3. Delete Comment
			DBQuery::execute("DELETE FROM {$database['prefix']}Comments WHERE owner = $owner AND entry = $this->id");
		
		// step 4. Delete Trackback
			DBQuery::execute("DELETE FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND entry = $this->id");
		
		// step 5. Delete Trackback Logs
			DBQuery::execute("DELETE FROM {$database['prefix']}TrackbackLogs WHERE owner = $owner AND entry = $this->id");
		
		// step 6. update Category
			// TODO : Update Category
		
		// step 7. Delete Attachment
			// TODO : Delete Attachment
		
		// step 8. Delete Tags
			$this->deleteTags();
		
		// step 9. Clear RSS
			requireComponent('Tattertools.Control.RSS');
			RSS::refresh();
		
			return true;
		}
		return false;
	}
	
	function update() { // attachment & category is own your risk!
		if (!isset($this->id) || !Validator::number($this->id, 1))
			return $this->_error('id');

		if (!$query = $this->_buildQuery())
			return false;
		if (!$old = $query->getRow('category, visibility'))
			return $this->_error('id');
			
		$bChangedCategory = ($old['category'] != $this->category);
		
		if ($old['visibility'] == 3) {
			requireComponent('Eolin.API.Syndication');
			Syndication::leave($this->getLink());
		}
		if (!isset($this->modified))
			$query->setAttribute('modified', 'UNIX_TIMESTAMP()');
		
		if (!$query->update())
			return $this->_error('update');
			
		if ($bChangedCategory) {
			// TODO : Recalculate Category
		}

		if (isset($this->slogan))
			$this->saveSlogan();

		$this->updateTags();

		if ($this->visibility == 'syndicated') {
			requireComponent('Eolin.API.Syndication');
			if (!Syndication::join($this->getLink())) {
				$query->resetAttributes();
				$query->setAttribute('visibility', 2);
				$this->visibility = 'public';
				$query->update();
			}
		}
		requireComponent('Tattertools.Control.RSS');
		RSS::refresh();
		
		return true;
	}
	
	function getCount() {
		return (isset($this->_count) ? $this->_count : 0);
	}
	
	function getLink() {
		global $defaultURL;
		if (!Validator::number($this->id, 1))
			return null;
		return "$defaultURL/sync/{$this->id}";
	}
	
	function getAttachments() {
		if (!Validator::number($this->id, 1))
			return null;
		requireComponent('Tattertools.Data.Attachment');
		$attachment = new Attachment();
		if ($attachment->open('parent = ' . $this->id))
			return $attachment;
	}
	
	function saveSlogan($slogan = null) {
		global $database, $owner;
		if (!Validator::number($this->id, 1))
			return $this->_error('id');
		if (isset($slogan))
			$this->slogan = $slogan;

		$query = new TableQuery($database['prefix'] . 'Entries');
		$query->setQualifier('owner', $owner);
		$query->setQualifier('id', $this->id);
		if (!$query->doesExist())
			return $this->_error('id');

		if (isset($this->slogan) && Post::validateSlogan($this->slogan))
			$slogan0 = $this->slogan;
		else
			$slogan0 = $this->slogan = Post::makeSlogan($this->title);
			
		$slogan0 = mysql_lessen($slogan0, 255);

		for ($i = 1; $i < 1000; $i++) {
			$checkSlogan = mysql_tt_escape_string($this->slogan);
			$query->setAttribute('slogan', $checkSlogan, false);
			if (!DBQuery::queryExistence(
				"SELECT id FROM {$database['prefix']}Entries " 
				. "WHERE owner = $owner AND slogan ='{$checkSlogan}'")
				) 
			{
				if (!$query->update())
					return $this->_error('update');
				return true;
			}
			$this->slogan = mysql_lessen($slogan0, 245) . '-' . $i;
		}
		// if try saveSlogan again, slogan string has more $i
		return $this->_error('limit');
	}
	
	function loadTags() {
		global $database, $owner;
		if (!Validator::number($this->id, 1))
			return $this->_error('id');
		$this->tags = array();
		if ($result = mysql_query("SELECT name FROM {$database['prefix']}TagRelations LEFT JOIN {$database['prefix']}Tags ON id = tag WHERE owner = $owner AND entry = {$this->id} ORDER BY name")) {
			while ($row = mysql_fetch_row($result))
				array_push($this->tags, $row[0]);
			mysql_free_result($result);
			return true;
		}
		return false;
	}
	
	/*@static, protected@*/
	function getTagsWithEntryString($entryTag) 
	{
		global $database;
		if ($entryTag == null) 
			return array();
		
		$tags = explode(',', $entryTag);
		
		$ret = array();
		
		foreach ($tags as $tag) {
			$tag = mysql_lessen($tag, 255, '');
			$tag = str_replace('&quot;', '"', $tag);
			$tag = str_replace('&#39;', '\'', $tag);
			$tag = preg_replace('/ +/', ' ', $tag);
			$tag = preg_replace('/[\x00-\x1f]|[\x7f]/', '', $tag);
			$tag = preg_replace('/^(-|\s)+/', '', $tag);
			$tag = preg_replace('/(-|\s)+$/', '', $tag);
			$tag = trim($tag);
			
			array_push($ret, $tag);
		}
		
		return $ret;
	}	

	/*@protected@*/
	function addTags() {
		// Don't call outside of object!
		global $database, $owner;
		if (!Validator::number($this->id, 1))
			return $this->_error('id');
		if (!is_array($this->tags)) {
			$this->tags = Post::getTagsWithEntryString($this->tags);
		}
		if (empty($this->tags))
			return;
		
		requireComponent('Tattertools.Data.Tag');
		Tag::addTagsWithEntryId($owner, $this->id, $this->tags);

		return true;
	}
	
	/*@protected@*/
	function updateTags() {
		// Don't call outside of object!
		global $database, $owner;
		if (!Validator::number($this->id, 1))
			return $this->_error('id');
		if (!is_array($this->tags)) {
			$this->tags = Post::getTagsWithEntryString($this->tags);

		}
		
		requireComponent('Tattertools.Data.Tag');
		Tag::modifyTagsWithEntryId($owner, $this->id, $this->tags);
		
		return true;
	}

	/*@protected@*/
	function deleteTags() {
		// Don't call outside of object!
		global $database, $owner;
		if (!Validator::number($this->id, 1))
			return $this->_error('id');
		
		requireComponent('Tattertools.Data.Tag');
		Tag::deleteTagsWithEntryId($owner, $this->id);
		
		return true;
	}
	
	function getComments() {
		if (!Validator::number($this->id, 1))
			return $this->_error('id');
		requireComponent('Tattertools.Data.Comment');
		$comment = new Comment();
		if ($comment->open('entry = ' . $this->id . ' AND parent IS NULL'))
			return $comment;
	}
	
	function getTrackbacks() {
		if (!Validator::number($this->id, 1))
			return $this->_error('id');
		requireComponent('Tattertools.Data.Trackback');
		$trackback = new Trackback();
		if ($trackback->open('entry = ' . $this->id))
			return $trackback;
	}
	
	function getTrackbackLogs() {
		if (!Validator::number($this->id, 1))
			return $this->_error('id');
		requireComponent('Tattertools.Data.TrackbackLog');
		$log = new TrackbackLog();
		if ($log->open('entry = ' . $this->id))
			return $log;
	}
	
	/*@static@*/
	function doesExist($id) {
		global $database, $owner;
		if (!Validator::number($id, 1))
			return false;
		return DBQuery::queryExistence("SELECT id FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND category >= 0 AND draft = 0");
	}
	
	/*@static@*/
	function doesAcceptTrackback($id) {
		global $database, $owner;
		if (!Validator::number($id, 1))
			return false;
		return DBQuery::queryExistence("SELECT id FROM {$database['prefix']}Entries WHERE owner = $owner AND id = $id AND draft = 0 AND visibility > 0 AND category >= 0 AND acceptTrackback = 1");
	}
	
	/*@static@*/
	function updateComments($id = null) {
		global $database, $owner;

		if (($id !== null) && !is_numeric($id)) {
			return false;
		}

		$posts = ($id === null ? DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Entries WHERE owner = $owner AND category >= 0 AND draft = 0") : array($id));
		if (!is_array($posts))
			return false;
		$succeeded = true;
		foreach ($posts as $id) {
			$comments = DBQuery::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Comments WHERE owner = $owner AND entry = $id AND isFiltered = 0");
			if ($comments !== null) {
				if (DBQuery::execute("UPDATE {$database['prefix']}Entries SET comments = $comments WHERE owner = $owner AND id = $id"))
					continue;
			}
			$succeeded = false;
		}
		return $succeeded;
	}
	
	/*@static@*/
	function updateTrackbacks($id = null) {
		global $database, $owner;

		if (($id !== null) && !is_numeric($id)) {
			return false;
		}

		$posts = ($id === null ? DBQuery::queryColumn("SELECT id FROM {$database['prefix']}Entries WHERE owner = $owner AND category >= 0 AND draft = 0") : array($id));
		if (!is_array($posts))
			return false; 
		$succeeded = true;
		foreach ($posts as $id) {
			$trackbacks = DBQuery::queryCell("SELECT COUNT(*) FROM {$database['prefix']}Trackbacks WHERE owner = $owner AND entry = $id AND isFiltered = 0");
			if ($trackbacks !== null) { 
				if (DBQuery::execute("UPDATE {$database['prefix']}Entries SET trackbacks = $trackbacks WHERE owner = $owner AND id = $id"))
					continue;
			}
			$succeeded = false;
		}
		return $succeeded;	
	}
	
	/*@static@*/
	function makeSlogan($title) {
		$slogan = preg_replace('/-+/', ' ', $title);
		$slogan = preg_replace('/[!-\/:-@[-`{-~]+/', '', $slogan);
		$slogan = preg_replace('/\s+/', '-', $slogan);
		$slogan = trim($slogan, '-');
		return strlen($slogan) > 0 ? $slogan : 'X';
	}
	
	/*@static@*/
	function validateSlogan($slogan) {
		return preg_match('/^[^!-,.\/:-@[-`{-~\s]+$/', $slogan);
	}
	
	/*@static@*/
	function makePassword($plain = null) {
		return $plain ? md5($plain) : md5(microtime());
	}
	
	function _buildQuery() {
		global $database, $owner;
		$query = new TableQuery($database['prefix'] . 'Entries');
		$query->setQualifier('owner', $owner);
		if (isset($this->id)) {
			if (!Validator::number($this->id, 1))
				return $this->_error('id');
			$query->setQualifier('id', $this->id);
		}
		if (isset($this->title))
			$query->setAttribute('title', mysql_lessen($this->title, 255), true);
		if (isset($this->content))
			$query->setAttribute('content', $this->content, true);
		if (isset($this->visibility)) {
			switch ($this->visibility) {
				case 'private':
					$query->setAttribute('visibility', 0);
					break;
				case 'protected':
					$query->setAttribute('visibility', 1);
					if (empty($this->password))
						$this->password = $this->makePassword();
					break;
				case 'public':
					$query->setAttribute('visibility', 2);
					break;
				case 'syndicated':
					$query->setAttribute('visibility', 3);
					break;
				default:
					return $this->_error('visibility');
			}
		}
		if (isset($this->category)) {
			requireComponent('Tattertools.Data.Category');
			if (!Category::doesExist($this->category))
				return $this->_error('category');
			$query->setAttribute('category', $this->category);
		}
		if (isset($this->location))
			$query->setAttribute('location', mysql_lessen($this->location, 255), true);
		if (isset($this->password))
			$query->setAttribute('password', $this->password, true);
		if (isset($this->acceptComment))
			$query->setAttribute('acceptComment', Validator::getBit($this->acceptComment));
		if (isset($this->acceptTrackback))
			$query->setAttribute('acceptTrackback', Validator::getBit($this->acceptTrackback));
		if (isset($this->published)) {
			if (!Validator::number($this->published, 1))
				return $this->_error('published');
			$query->setAttribute('published', $this->published);
		}
		if (isset($this->created)) {
			if (!Validator::number($this->created, 1))
				return $this->_error('created');
			$query->setAttribute('created', $this->created);
		}
		if (isset($this->modified)) {
			if (!Validator::number($this->modified, 1))
				return $this->_error('modified');
			$query->setAttribute('modified', $this->modified);
		}
		return $query;
	}

	/*@static@*/
	function correctTagsAll() {
		global $database;
		$targetresult = mysql_query("SELECT * FROM {$database['prefix']}TagRelations");
		if ($targetresult != false) {
			while ($target = mysql_fetch_array($targetresult)) {
				$oldtag = DBQuery::queryRow("SELECT id, name FROM {$database['prefix']}Tags WHERE id = {$target['tag']}");
				if ($oldtag != null) {		
					$tagid = DBQuery::queryCell("SELECT id FROM {$database['prefix']}Tags WHERE name = '" . mysql_tt_escape_string($oldtag['name']) . "' LIMIT 1 ");
					if ($tagid == null) { 
						DBQuery::execute("DELETE FROM {$database['prefix']}TagRelations WHERE owner = {$target['owner']} AND tag = {$target['tag']} AND entry = {$target['entry']}");
					} else {
						if ($tagid == $oldtag['id']) continue;
						if (DBQuery::execute("UPDATE {$database['prefix']}TagRelations SET tag = $tagid WHERE owner = {$target['owner']} AND tag = {$target['tag']} AND entry = {$target['entry']}") == false) { // maybe duplicated tag
							DBQuery::execute("DELETE FROM {$database['prefix']}TagRelations WHERE owner = {$target['owner']} AND tag = {$target['tag']} AND entry = {$target['entry']}");
						}
					}
				} else { // Ooops!
					DBQuery::execute("DELETE FROM {$database['prefix']}TagRelations WHERE owner = {$target['owner']} AND tag = {$target['tag']} AND entry = {$target['entry']}");
				}
			}
			mysql_free_result($targetresult);
		}
		
		$targetresult = mysql_query("SELECT id FROM {$database['prefix']}Tags LEFT JOIN {$database['prefix']}TagRelations ON id = tag WHERE tag IS NULL");
		if ($targetresult != false) {
			while ($target = mysql_fetch_array($targetresult)) {
				$tag = $target['id'];
				DBQuery::execute("DELETE FROM {$database['prefix']}Tags WHERE id = $tag ");
			}
			mysql_free_result($targetresult);
		}
	}

	function _error($error) {
		$this->error = $error;
		return false;
	}
}
?>
