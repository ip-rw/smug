<?php

// Insert/Update query builder and factory type class.
// Don't expect amazingly useful comments.

class InsertUpdateQueryBuilder {
	var $queryBuilder = null;
	public function __construct($dataControl, $dataEntity) {
		if ($dataEntity->isNew == true) {
			$this->queryBuilder = new InsertQueryBuilder($dataControl, $dataEntity);
		} else {
			$this->queryBuilder = new UpdateQueryBuilder($dataControl, $dataEntity);
		}
	}
	public function getQuery() {
		return $this->queryBuilder->getQuery();
	}
}
class UpdateQueryBuilder {
	var $dataControl;
	var $dataEntity;
	
	public function __construct($dataControl, $dataEntity) {
		$this->dataControl = $dataControl;
		$this->dataEntity = $dataEntity;
	}
	
	public function getQuery() {
		if (!$this->dataEntity->validate()) {
			return false;
		}
		$assign = $this->getAssignments();
		if (strlen($assign) < 1) return false;
		$query = "UPDATE " . $this->dataControl->table . " SET $assign WHERE " . $this->dataControl->key . " = " . $this->dataEntity->get($this->dataControl->key);
		return $query;
	}
	
	// PHP's native array_diff pisses me off, and as far as I can tell, doesn't work properly under certain conditions. Bug report submitted.
	function array_diff_proper($array_a, $array_b) {
		$return = array();
		foreach ($array_a as $key=>$value) {
			if (in_array($key, $array_b) && $array_b[$key] != null) {
				if ($array_b[$key] != $value) {
					$return[$key] = $value;
				}
			} else {
				$return[$key] = $value;
			}
		}
		return $return;
	}
	
	public function getAssignments() {
		$diff = $this->array_diff_proper($this->dataEntity->data, $this->dataEntity->originalData);
		$assignments = "";
		foreach ($this->dataEntity->fieldMeta as $field) {
			if (array_key_exists($field->column, $diff)) {
				$key = $field->column;
				$value = $diff[$field->column];
				if (is_numeric($value) && $field->type == SQUIB_TYPE_INTEGER) {
					$assignments .= "$key = $value, ";
				} else {
					$assignments .= "$key = '" . Database::sanitise($value) . "', ";
				}
			}

		}
		$assignments = rtrim($assignments, ", ");
		return $assignments;
	}
}

class InsertQueryBuilder {
	var $dataControl;
	var $dataEntity;
	
	public function __construct($dataControl, $dataEntity) {
		$this->dataControl = $dataControl;
		$this->dataEntity = $dataEntity;
	}
	
	public function getQuery() {
		if (!$this->dataEntity->validate()) {
			return false;
		}
		$columns = $this->getColumnsSql();
		$values = $this->getValuesSql();
		
		$query = "INSERT INTO " . $this->dataControl->table . " ( " . $columns . " ) VALUES ( " . $values . " )";
		return $query;
	}
	
	public function getColumnsSql() {
		$columns = "";
		foreach ($this->dataEntity->fieldMeta as $field) {
			if ($field->column != $this->dataControl->key) {
				$columns .= "`" . $field->column . "`, ";
			}
		}
		$columns = rtrim($columns, ", ");
		return $columns;
	}
	
	public function getValuesSql() {
		$values = "";
		foreach ($this->dataEntity->fieldMeta as $field) {
			if ($field->column == $this->dataControl->key) continue;
			switch ($field->type) {
				case 1: // Int
					$values .= Database::sanitise($this->dataEntity->get($field->column)) . ", ";
					break;
				case 2: // Text
					$values .= "'" . Database::sanitise($this->dataEntity->get($field->column)) . "', ";
					break;
				case 3: // Date
					if ($this->dataEntity->get($field->column) == "NOW()") {
						$values .= "NOW(), ";					
					} else {
						$values .= "'" . Database::sanitise(date('Y-m-d H:i:s', strtotime($this->dataEntity->get($field->column)))) . "', ";
					}
					break;
				case 4: // Bool
					$values .= ($this->dataEntity->get($field->column) == true) ? '1, ' : '0, ';
					break;
			}
		}
		$values = rtrim($values, ", ");
		return $values;
	}
}
?>
