<?php
/// Copyright (c) 2004-2008, Needlworks / Tatter Network Foundation
/// All rights reserved. Licensed under the GPL.
/// See the GNU General Public License for more details. (/doc/LICENSE, /doc/COPYRIGHT)

interface IModel
{
	static function _createTable();
	static function _dropTable();
	static function _dumpTable();

	public function setField($fieldName, $value);
	public function __set($fieldName, $value);
	public function getField($fieldName);
	public function __get($fieldName);

	public static function findAll(array $condition);
	public static function deleteAll(array $condition);
	public static function get($id);
	public static function create(array $fields);

	public function save(array $options);
	public function delete();
}
?>
