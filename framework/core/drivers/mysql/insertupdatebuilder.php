<?php
require_once(SMUG_PATH . "/core/dbms.php");
require_once(SMUG_PATH . "/core/drivers/mysql/driver.php");

class MySqlUpdateBuilder implements IInsertUpdateBuilder {
    var $dataEntity = null;
    var $bindValues = array();

    public function __construct($dataEntity) {
        $this->dataEntity = $dataEntity;
    }

    public function getStatement() {
        $query = $this->getQuery();
        $db = DBDriver::getConnection();
        $statement = $db->prepare($query);
        for ($i = 0; $i < count($this->bindValues); $i++) {
            $statement->bindValue($i + 1, $this->bindValues[$i]);
        }
        return $statement;
    }

    public function getQuery() {
        if (!$this->dataEntity->validate()) {
            return false;
        }
        $assign = $this->getAssignments();
        if (strlen($assign) < 1) return false;
        $query = "UPDATE " . $this->dataEntity->dataControl->table . " SET $assign WHERE " . $this->dataEntity->dataControl->key . " = " . $this->dataEntity->get($this->dataEntity->dataControl->key);
        return $query;
    }

    // Find changes.
    function array_diff_proper($array_a, $array_b) {
        $return = array();
        foreach ($array_a as $key => $value) {
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
                $assignments .= "$key = ?, ";
                $this->bindValues[] = $value;
            }
        }
        $assignments = rtrim($assignments, ", ");
        return $assignments;
    }
}

class MySqlInsertBuilder implements IInsertUpdateBuilder {
    var $dataEntity;
    var $bindValues = array();

    public function __construct($dataEntity) {
        $this->dataEntity = $dataEntity;
    }

    public function getStatement() {
        $query = $this->getQuery();
        $db = DBDriver::getConnection();
        $statement = $db->prepare($query);
        for ($i = 0; $i < count($this->bindValues); $i++) {
            $statement->bindValue($i + 1, $this->bindValues[$i]);
        }
        return $statement;
    }

    public function getQuery() {
        if (!$this->dataEntity->validate()) {
            return false;
        }
        $columns = $this->getColumnsSql();
        $values = $this->getValuesSql();

        $query = "INSERT INTO " . $this->dataEntity->dataControl->table . " ( " . $columns . " ) VALUES ( " . $values . " )";
        return $query;
    }

    public function getColumnsSql() {
        $columns = "";
        foreach ($this->dataEntity->fieldMeta as $field) {
            if ($field->column != $this->dataEntity->dataControl->key) {
                $columns .= "`" . $field->column . "`, ";
            }
        }
        $columns = rtrim($columns, ", ");
        return $columns;
    }

    public function getValuesSql() {
        $values = "";
        foreach ($this->dataEntity->fieldMeta as $field) {
            if ($field->column != $this->dataEntity->dataControl->key) {
                $values .= '?, ';
                $this->bindValues[] = $this->dataEntity->get($field->column);

            }
        }
        $values = rtrim($values, ", ");
        return $values;
    }
}

?>