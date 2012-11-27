<?php
require_once(SMUG_PATH . "/common.php");
require_once(SMUG_PATH . "/core/filter.php");
require_once(SMUG_PATH . "/core/dataentity.php");

// Here we really have the guts of the framework.
// Extend this class to create your DataControllers, see base for examples.

class DataControl {
    var $connection     = null;
    var $table          = null;
    var $fieldMeta      = null;
    var $key            = null;
    var $orderBy        = null;
    var $asc            = false;
    var $statement      = null;
    var $isNewRecord    = true;
    var $data           = null;
    var $dataControl    = null;
    var $filter         = null;
    var $count          = 0;

    public function __construct() {
        $this->connection = &DBDriver::getConnection();
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

    public function query($sql, $values = null) {
        $this->statement = $this->connection->prepare($sql);
        foreach ($values as $key => $value) $this->statement->bindValues($key, $value);
        return $this->execute();
    }

    public function execute() {
        try {
            $result = $this->statement->execute();
            $this->count = $this->statement->rowCount();
            return $result;
        } catch (PDOException $e) {
            $this->statement->debugDumpParams();
            $this->errorControl->addError("SQL Error: " . $e->getMessage());
        }
    }

    // Run query, get result set, and count.
    public function retrieve() {
        if ($this->filter != null) {
            $builder = DBDriver::getSelectQueryBuilder($this->filter);
            $this->statement = $builder->getStatement();
            $this->execute();
        } else {
            $filter = $this->getNewFilter();
            $this->setFilter($filter);
            $this->retrieve();
        }
    }

    // Returns the next DataEntity in the result set. Think: while ($entity = $dataControl->getNext()) {
    public function getNext() {
        if ($this->statement == null) $this->retrieve();
        $result = $this->statement->fetch(PDO::FETCH_BOTH);
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

    // FIXME: Data types should to handle their own validation.
    // Each type is going to need some helper classes, and/or we bring in validators and types have a default validator.
    public function validate($dataEntity) {
        $validates = true;
        foreach ($this->fieldMeta as $field) {
            if ($field->column != $this->key) {
                $value = $dataEntity->data[$field->column];
                if ($field->notNull == true) {
                    if ($value === null || $value === "") {
                        $this->errorControl->addError("'" . $field->name . "' cannot be left empty.");
                        $validates = false;
                    }
                }
                if ($field->type == SMUG_TYPE_INTEGER) {
                    if (!is_numeric($value)) {
                        $this->errorControl->addError("'" . $field->name . "' is not a number.");
                        $validates = false;
                    }
                }
                if ($field->type == SMUG_TYPE_DATE) {
                    if (strtotime($value) === false) {
                        $this->errorControl->addError("'" . $field->name . "' is not a valid timestamp.");
                        $validates = false;
                    }
                }
            }
        }
        return $validates;
    }

    // Map array of key values to a dataEntity. Think $_POST.
    public function map($array) {
        // Allow JOINS.
        if ($array != null) {
            foreach ($array as $key => $value) {
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
        $queryBuilder = DBDriver::getInsertUpdateQueryBuilder($dataEntity);
        $isInsert = $dataEntity->isNew;
        $statement = $queryBuilder->getStatement();
        try {
            $statement->execute();
            if ($isInsert == true) {
                $lastId = $this->connection->lastInsertId();
                $dataEntity->data[$this->key] = $lastId;
            }
        } catch (PDOException $e) {
            $this->errorControl->addError("DB Error: " . strval($e));
        }
        if ($this->errorControl->hasErrors() == false) {
            return (!isset($lastId) || $lastId == null) ? true : $lastId;
        } else {
            return false;
        }
    }
}
