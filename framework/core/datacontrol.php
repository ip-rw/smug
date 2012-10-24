<?php
require_once(SQUIB_PATH . "/common.php");
require_once(SQUIB_PATH . "/core/filter.php");
require_once(SQUIB_PATH . "/core/dataentity.php");

// Here we really have the guts of the framework.
// Extend this class to create your DataControllers, see base for examples.

class DataControl {
	
	var $table = null;
	var $fieldMeta = null;
	var $key = null;
	var $orderBy = null;	
	var $asc = false;
	var $dataResult = null;	
	var $isNewRecord = true;
	var $data = null;
	var $dataControl = null;
	var $databaseControl = null;
	var $filter = null;
	var $count = 0;
	
	public function __construct() {
		$this->errorControl = &CoreFactory::getErrorControl();
	}
	
	// Create a blank dataEntity (with defaults);
	public function makeNew() {
		foreach ($this->fieldMeta as $fieldMeta) {
			$this->data[$fieldMeta->column] = $fieldMeta->defaultValue; 
		}
		$dataEntity = new DataEntity($this);
		$dataEntity->isNew = true;
		return $dataEntity;
	}
	
	// Get a new filter.
	public function getNewFilter() {
		$filterControl = new FilterControl($this);
		return $filterControl;
	}
	
	// Gets any existing filter.
	public function getFilter() {
		if ($this->filter == null) {
			return $this->getNewFilter();
		} else {
			return $this->filter;
		}
	}
	
	// Guess.
	public function setFilter($filter) {
		$this->filter = $filter;
	}
	
	// Select on $this->key
	public function item($id) {
		$filter = $this->getNewFilter();
		$filter->addConditional($this->table, $this->key, $id);
		$this->setFilter($filter);
		$this->retrieve();
		return $this->getNext();
	}
	
	// Guess
	public function setOrder($column, $asc) {
		$filter = $this->getFilter();
		$filter->setOrderBy($this->table, $column, $asc);
		$this->setFilter($filter);
		$this->retrieve();
	}
	public function query($query) {
		$this->dataResult = $this->databaseControl->query($query);
		$this->count = $this->databaseControl->getCount($this->dataResult);
	}

	// Run query, get result set, and count.
	public function retrieve() {
		if ($this->filter != null) {
			$this->query($this->filter->getQuery());
		} else {
			$filter = $this->getNewFilter();
			$this->setFilter($filter);
			$this->retrieve();
		}
	}
	
	// Returns the next DataEntity in the result set. Think: while ($entity = $dataControl->getNext()) {
	public function getNext() {
		if ($this->dataResult == null) $this->retrieve();
		$result = $this->databaseControl($this->dataResult, MYSQL_ASSOC);
		if ($result == null) return false;
		$this->data = $result;
		$dataEntity = $this->getDataEntity();
		$dataEntity->isNew = false;
		return $dataEntity;
	}

	// Used in paging. Gotta love that paging...
	public function retrievePage($pageLength, $pageNumber) {
		$offset = $pageNumber * $pageLength;
		$filter = $this->getFilter();
		$filter->setLimit($pageLength);
		$filter->setOffset($offset);
		$this->setFilter($filter);
		$this->retrieve();
	}
	
	// Internal function to produce an entity.
	public function getDataEntity() {
		$dataEntity = new DataEntity($this);
		return $dataEntity;
	}
	
	// Needs redoing when we bring in validators.
	public function validate($dataEntity) {
		$validates = true;
		foreach ($this->fieldMeta as $field) {
			if ($field->column != $this->key) {
				$value = $dataEntity->data[$field->column];
				if ($field->notNull == true) {
					if ($value === null || $value === "")  {
						$this->errorControl->addError("'" . $field->name . "' cannot be left empty.");
						$validates = false;
					}
				}
				if ($field->type == SQUIB_TYPE_INTEGER || $field->type == SQUIB_TYPE_LONG) {
					if (!is_numeric($value)) {
						$this->errorControl->addError("'" . $field->name . "' is not a number.");
						$validates = false;
					}
				}
				if ($field->type == SQUIB_TYPE_DATE) {
					if ($value != "NOW()") {
						if (strtotime($value) === false) {
							$this->errorControl->addError("'" . $field->name . "' is not a valid date time.");
							$validates = false;
						}
					}
				}
			}
		}
		return $validates;
	}
	
	// This just saved an hour. Result.
	public function createMySql() {
		$sql = "CREATE TABLE `" . $this->table . "`\n(";
		foreach ($this->fieldMeta as $field) {
			$sql .= "\t" . $field->getColumnMySql($this) . ",\n";
		}
		$sql = rtrim($sql,",\n") . "\n";
		$sql .= ");\n\n";
		return $sql;
	}
	
	// Map array of key values to a dataEntity. Think $_POST.
	public function map($array) {
		// Allow JOINS.
		if ($array != null) {
			foreach ($array as $key=>$value) {
				$this->data[$key] = $value;
			}		
		}
		foreach ($this->fieldMeta as $field) {
			if (array_key_exists($field->column, $array)) {
				$this->data[$field->column] = (string)$array[$field->column];
			} else {
				$this->data[$field->column] = $field->defaultValue;
			}
		}
		$dataEntity = $this->getDataEntity();
		$dataEntity->originalData = array();
		if (isset($array[$this->key]) && $array[$this->key] != null) {
			$dataEntity->isNew = false;
		} else {
			$dataEntity->isNew = true;
		}
		return $dataEntity;
	}
	
	// Save. INSERT or UPDATE.
	public function save(&$dataEntity) {
		if ($dataEntity->validate() != true) return false;
		$insertUpdateQueryBuilder = CoreFactory::getInsertUpdateQueryBuilder($this, $dataEntity);
		$isInsert = $dataEntity->isNew;
		$query = $insertUpdateQueryBuilder->getQuery();
		if ($query == false) return true;
		$this->databaseControl->query($query);
		if ($isInsert == true) {
			$lastId = $this->databaseControl->getLastInstertedId();
			$dataEntity->data[$this->key] = $lastId;
		}
		if ($this->errorControl->hasErrors() == false) {
			return (!isset($lastId) || $lastId == null) ? true : $lastId;
		} else {
			return false;
		}
	}
}
