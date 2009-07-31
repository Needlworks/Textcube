<?php
/// Copyright (c) 2004-2009, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

final class Model_Line extends Singleton {
	private $filter = array();
	public function __constructor() {
		$this->reset();
	}

	public static function getInstance() {
		return self::_getInstance(__CLASS__);
	}
		
	public function reset() {
		global $database;		
		$this->id = null;
		$this->blogid = getBlogId();
		$this->category = 'public';
		$this->content = '';
		$this->created = null;
		$this->filter = array();
		$this->_error = array();
		$query = new TableQuery($database['prefix'].'Lines');
	}
/// Methods for managing	
	public function add() {
		global $database;
		if(is_null($this->created)) $this->created = Timestamp::getUNIXTime();
		if(!$this->validate()) return false;
		$query = new TableQuery($database['prefix'].'Lines');
		$query->setAttribute('id',$this->id);
		$query->setAttribute('blogid',$this->blogid);
		$query->setAttribute('category',$this->category,true);
		$query->setAttribute('content',$this->content,true);
		$query->setAttribute('created',$this->created);
		return $query->insert();
	}
	
	public function delete(){
		global $database;
		if(empty($this->filter)) return $this->error('Filter empty');
		$query = new TableQuery($database['prefix'].'Lines');
		foreach($this->filter as $filter) {
			if(count($filter) == 3) {
				$query->setQualifier($filter[0],$filter[1],$filter[2]);
			} else {
				$query->setQualifier($filter[0],$filter[1],$filter[2],$filter[3]);			
			}
		}
		return $query->delete();
	}
/// Methods for querying
	public function get($fields = '*') {
		global $database;
		if(empty($this->filter)) return $this->error('Filter empty');
		$query = new TableQuery($database['prefix'].'Lines');
		foreach($this->filter as $filter) {
			if(count($filter) == 3) {
				$query->setQualifier($filter[0],$filter[1],$filter[2]);
			} else {
				$query->setQualifier($filter[0],$filter[1],$filter[2],$filter[3]);			
			}
		}
		$query->setOrder('created','desc');
		return $query->getAll($fields);		
	}
	
	/// @input condition<array> [array(name, condition, value, [need_escaping])]
	public function setFilter($condition) {
		if(!in_array(count($condition),array(3,4))) return $this->error('wrong filter');
		array_push($this->filter, $condition);
	}

/// Aliases
	/// conditions [array(page=>value<int>, linesforpage=>value<int>)]
	public function getWithConditions($conditions) {
		global $database;
		$count = 10;
		$offset = 0;
		if(isset($conditions['page'])) $page = $conditions['page'];
		if(isset($conditions['linesforpage'])) {
			$count = $conditions['linesforpage'];
			$offset = ($page - 1) * $count;
		}
		$query = new TableQuery($database['prefix'].'Lines');
		if(isset($conditions['category'])) $query->setQualifier('category','equals',$conditions['category'],true);
		if(isset($conditions['blogid'])) $query->setQualifier('blogid','equals',$conditions['blogid']);
		else $query->setQualifier('blogid','equals',getBlogId());
		if(isset($conditions['keyword'])) {
			$query->setQualifier('content','like',$conditions['keyword'],true);
		}
		$query->setLimit($count, $offset);
		$query->setOrder('created','desc');
		return $query->getAll();
	}

/// Methods for specific function.
	/// conditions [array(page=>value<int>, linesforpage=>value<int>)]
	public function getFormattedList($conditions) {
		//data [array(id, blogid, category, content, 
		$data = $this->getWithConditions($conditions);
		$view = '';
		foreach($data as $d) {
			$template = $conditions['template'];
			$d['created'] = Timestamp::getHumanReadable($d['created']);
			foreach($conditions['dress'] as $tag => $match) {
				dress($tag, $d[$match],$template);
			}
			$view .= $template;
		}
		return $view;
	}

/// Private members	
	private function validate() {
		if(is_null($this->id)) $this->id = $this->getNextId();
		$this->category = UTF8::lessenAsByte($this->category, 11);
		$this->content = UTF8::lessenAsByte($this->content, 512);
		if(!Validator::isInteger($this->blogid, 1)) return $this->error('blogid');		
		if(!Validator::timestamp($this->created)) return $this->error('created');
		return true;
	}
	
	private function getNextId() {
		global $database;
		$query = new TableQuery($database['prefix'].'Lines');
		$maxId = $query->getCell('MAX(id)');
		if(!empty($maxId)) return $maxId + 1;
		else return 1;
	}
	public function showResult($result) {
		echo "<html><head></head><body>";
		echo '<script type="text/javascript">alert("';
		if($result) {
			echo _t('Line이 추가되었습니다.');
		} else {
			echo _t('Line 추가에 실패했습니다.');	
		}
		echo '");history.back(-1);</script></body></html>';
	}
	private function error($state) {
		$this->_error['message'] = $state;
		return false;
	}
}
?>
