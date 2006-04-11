<?
class Personalization {
	function Personalization() {
		$this->reset();
	}

	function reset() {
		$this->rowsPerPage =
		$this->readerPannelVisibility =
		$this->readerPannelHeight =
		$this->lastVisitNotifiedPage =
			null;
	}
	
	function load($fields = '*') {
		global $database, $owner;
		$this->reset();
		if ($result = mysql_query("SELECT $fields FROM {$database['prefix']}Personalization WHERE owner = $owner")) {
			if ($row = mysql_fetch_assoc($result)) {
				foreach ($row as $name => $value) {
					if ($name == 'owner')
						continue;
					$this->$name = $value;
				}
				mysql_free_result($result);
				return true;
			}
			mysql_free_result($result);
		}
		return false;
	}
	
	function save() {
		global $database, $owner;
		$query = new TableQuery($database['prefix'] . 'Personalization');
		$query->setQualifier('owner', $owner);
		if (isset($this->rowsPerPage))
			$query->setAttribute('rowsPerPage', $this->rowsPerPage, false);
		if (isset($this->readerPannelVisibility))
			$query->setAttribute('readerPannelVisibility', $this->readerPannelVisibility);
		if (isset($this->readerPannelHeight))
			$query->setAttribute('readerPannelHeight', $this->readerPannelHeight);
		if (isset($this->lastVisitNotifiedPage))
			$query->setAttribute('lastVisitNotifiedPage', $this->lastVisitNotifiedPage, true);
			
		if ($query->update())
			return true;
		return $query->insert();
	}
}
?>