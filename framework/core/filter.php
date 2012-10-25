<?php
class OperatorEnum {
    const Equal                 = 0;
    const NotEqual              = 1;
    const GreaterThan           = 2;
    const GreaterThanOrEqualTo  = 3;
    const LessThan              = 4;
    const LessThanOrEqualTo     = 5;
    const Like                  = 7;
}

class FilterControl {
    var $columns = null;
    var $tables = null;
    var $conditionals = null;
    var $joins = null;
    var $orderBy = null;
    var $asc = false;
    var $limit = null;
    var $offset = null;

    public function __construct($dataControl) {
        $this->addTable($dataControl->table);
        $this->addColumn($dataControl->table, "*");
        $this->setOrderBy($dataControl->table, $dataControl->orderBy, $dataControl->asc);
    }

    public function addTable($table) {
        $this->tables[] = $table;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
    }

    public function addJoin($joinType, $table1, $table2, $column1, $column2, $compareOperator = OperatorEnum::Equal) {
        $this->joins[] = new Join($joinType, $table1, $table2, $column1, $column2, $compareOperator);
    }

    public function addColumn($table, $column = "*") {
        $this->columns[$table] = $column;
    }

    public function addConditional($table, $column, $value, $compareOperator = OperatorEnum::Equal, $operator = "AND") {
        $conditional = new Conditional($table, $column, $value, $compareOperator, $operator);
        if ($this->conditionalExists($conditional) == false) {
            $this->conditionals[] = $conditional;
        }
    }

    public function setOrderBy($table, $column, $asc = false) {
        $asc = ($asc == false) ? " DESC " : " ASC ";
        $this->orderBy = "`$table`.`$column`" . $asc;
    }

    public function conditionalExists($conditional) {
        if ($this->conditionals == null) return false;
        foreach ($this->conditionals as $existing) {
            if ($existing == $conditional) {
                return true;
            }
        }
        return false;
    }

    public function getConditionalGroup($operator) {
        return new ConditionalGroup($operator);
    }

    public function addConditionalGroup($group) {
        $this->conditionals[] = $group;
    }
}

class ConditionalGroup {
    var $conditionals = null;
    var $operator = null;

    public function __construct($operator) {
        $this->operator = $operator;
    }

    public function getConditionalGroup($operator) {
        return new ConditionalGroup($operator);
    }

    public function addConditionalGroup($group) {
        $this->conditionals[] = $group;
    }

    public function addConditional($table, $column, $value, $compareOperator = OperatorEnum::Equal, $operator = "AND") {
        $conditional = new Conditional($table, $column, $value, $compareOperator, $operator);
        $this->conditionals[] = $conditional;
    }
}

class Conditional {
    var $table = null;
    var $column = null;
    var $value = null;
    var $compareOperator = null;
    var $operator = null;

    public function __construct($table, $column, $value, $compareOperator = OperatorEnum::Equal, $operator = "AND") {
        $this->table = $table;
        $this->column = $column;
        $this->value = $value;
        $this->compareOperator = $compareOperator;
        $this->operator = $operator;
    }
}

class Join {
    var $joinType = null;
    var $table1 = null;
    var $table2 = null;
    var $column1 = null;
    var $column2 = null;
    var $value = null;
    var $compareOperator = null;

    public function __construct($joinType, $table1, $table2, $column1, $column2, $compareOperator = OperatorEnum::Equal) {
        $this->joinType = $joinType;
        $this->table1 = $table1;
        $this->table2 = $table2;
        $this->column1 = $column1;
        $this->column2 = $column2;
        $this->compareOperator = $compareOperator;
    }
}
