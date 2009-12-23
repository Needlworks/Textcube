<?php
/// Copyright (c) 2004-2010, Needlworks  / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/documents/LICENSE, /documents/COPYRIGHT)

/**
 * Interface for Model
 */
interface IModel {
	public function reset($param = null);
	/// Attribute methods
	public function resetAttributes();
	public function getAttributesCount();
	public function hasAttribute($name);
	public function getAttribute($name);
	public function setAttribute($name, $value, $escape = null);
	public function unsetAttribute($name);
	/// Qualifier methods
	public function resetQualifiers();
	public function getQualifiersCount();
	public function hasQualifier($name);
	public function getQualifier($name);
	public function setQualifier($name, $condition, $value = null, $escape = null);
	public function unsetQualifier($name);
	/// Ordering / Limiting methods
	public function setOrder($standard, $order = 'ASC');
	public function unsetOrder();
	public function setLimit($count, $offset = 0);
	public function unsetLimit();
	/// Querying methods
	public function doesExist();
	public function getCell($field = '*');
	public function getRow($field = '*');
	public function getColumn($field = '*');
	public function getAll($field = '*');
	public function insert();
	public function update();
	public function replace();
	public function delete();
	/// Creating /discarding methods
	public function create();
	public function discard();
}
?>