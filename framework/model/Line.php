<?php
/// Copyright (c) 2004-2016, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

final class Model_Line extends DBModel {
    private $filter = array();

    public function __construct() {
        $this->context = Model_Context::getInstance();
        parent::reset('Lines');
        $this->reset();
    }

    public function reset() {
        parent::reset('Lines');
        $this->id = null;
        $this->blogid = getBlogId();
        $this->category = 'public';
        $this->root = 'default';
        $this->author = '';
        $this->content = '';
        $this->permalink = '';
        $this->created = null;
        $this->filter = array();
        $this->_error = array();
    }

/// Methods for managing	
    public function add() {
        if (is_null($this->created)) {
            $this->created = Timestamp::getUNIXTime();
        }
        if (!$this->validate()) {
            return false;
        }
        $this->setAttribute('id', $this->id);
        $this->setAttribute('blogid', $this->blogid);
        $this->setAttribute('category', $this->category, true);
        $this->setAttribute('root', $this->root, true);
        $this->setAttribute('author', $this->author, true);
        $this->setAttribute('content', $this->content, true);
        $this->setAttribute('permalink', $this->permalink, true);
        $this->setAttribute('created', $this->created);
        return $this->insert();
    }

    public function remove() {
        if (empty($this->filter)) {
            return $this->error('Filter empty');
        }
        foreach ($this->filter as $filter) {
            if (count($filter) == 3) {
                $this->setQualifier($filter[0], $filter[1], $filter[2]);
            } else {
                $this->setQualifier($filter[0], $filter[1], $filter[2], $filter[3]);
            }
        }
        return $this->delete();
    }

/// Methods for querying
    public function get($fields = '*') {
        if (empty($this->filter)) {
            return $this->error('Filter empty');
        }
        foreach ($this->filter as $filter) {
            if (count($filter) == 3) {
                $this->setQualifier($filter[0], $filter[1], $filter[2]);
            } else {
                $this->setQualifier($filter[0], $filter[1], $filter[2], $filter[3]);
            }
        }
        $this->setOrder('created', 'desc');
        return $this->getAll($fields);
    }

    /// @input condition<array> [array(name, condition, value, [need_escaping])]
    public function setFilter($condition) {
        if (!in_array(count($condition), array(3, 4))) {
            return $this->error('wrong filter');
        }
        array_push($this->filter, $condition);
    }

/// Aliases
    /// conditions [array(page=>value<int>, linesforpage=>value<int>)]
    public function getWithConditions($conditions) {
        $count = 10;
        $offset = 0;
        if (isset($conditions['page'])) {
            $page = $conditions['page'];
        }
        if (isset($conditions['linesforpage'])) {
            $count = $conditions['linesforpage'];
            $offset = ($page - 1) * $count;
        }
        if (isset($conditions['category'])) {
            $this->setQualifier('category', 'equals', $conditions['category'], true);
        }
        if (isset($conditions['root'])) {
            $this->setQualifier('root', 'equals', $conditions['root'], true);
        }
        if (isset($conditions['permalink'])) {
            $this->setQualifier('permalink', 'equals', $conditions['permalink'], true);
        }
        if (isset($conditions['blogid'])) {
            $this->setQualifier('blogid', 'equals', $conditions['blogid']);
        } else {
            $this->setQualifier('blogid', 'equals', getBlogId());
        }
        if (isset($conditions['keyword'])) {
            $this->setQualifier('content', 'like', $conditions['keyword'], true);
        }
        $this->setLimit($count, $offset);
        $this->setOrder('created', 'desc');
        return $this->getAll();
    }

/// Methods for specific function.
    /// conditions [array(page=>value<int>, linesforpage=>value<int>)]
    public function getFormattedList($conditions) {
        //data [array(id, blogid, category, content,
        $data = $this->getWithConditions($conditions);
        $view = '';
        foreach ($data as $d) {
            $template = $conditions['template'];
            $d['created'] = Timestamp::getHumanReadable($d['created']);
            if ($d['root'] == 'default') {
                $d['root'] = 'Textcube Line';
            }
            foreach ($conditions['dress'] as $tag => $match) {
                dress($tag, $d[$match], $template);
            }
            $view .= $template;
        }
        return $view;
    }

/// Private members	
    private function validate() {
        if (is_null($this->id)) {
            $this->id = $this->getNextId();
        }
        $this->category = Utils_Unicode::lessenAsByte($this->category, 11);
        $this->content = Utils_Unicode::lessenAsByte($this->content, 512);
        if (empty($this->author)) {
            $this->author = User::getName();
        }
        $this->author = Utils_Unicode::lessenAsByte($this->author, 32);
        if (!Validator::isInteger($this->blogid, 1)) {
            return $this->error('blogid');
        }
        if (!Validator::timestamp($this->created)) {
            return $this->error('created');
        }
        return true;
    }

    private function getNextId() {
        $maxId = $this->getCell('MAX(id)');
        if (!empty($maxId)) {
            return $maxId + 1;
        } else {
            return 1;
        }
    }

    public function showResult($result) {
        echo "<html><head></head><body>";
        echo '<script type="text/javascript">alert("';
        if ($result) {
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

    public $structure = array(
        "id" => array(
            "type" => "integer",
            "isNull" => false
        ),
        "blogid" => array(
            "type" => "integer",
            "isNull" => false,
            "default" => 1
        ),
        "category" => array(
            "type" => "varchar",
            "length" => 11,
            "isNull" => false,
            "default" => "public"
        ),
        "root" => array(
            "type" => "varchar",
            "length" => 11,
            "isNull" => false,
            "default" => "default"
        ),
        "content" => array(
            "type" => "mediumtext",
            "isNull" => false
        ),
        "permalink" => array(
            "type" => "varchar",
            "length" => 255,
            "isNull" => false,
            "default" => "default"
        ),
        "created" => array(
            "type" => "timestamp",
            "isNull" => false,
            "default" => 0
        )
    );

}

?>
