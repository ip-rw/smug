<?php

class SelectQueryBuilder {
	var $columns = null;
	var $tables = null;
	var $conditionals = null;
	var $joins = null;
	var $orderBy = null;
	var $asc = false;
	var $limit = null;
	var $offset = null;
	
	public function addTable($table) {
		$this->tables[] = $table;
	}
	
	public function setLimit($limit) {
		$this->limit = $limit;
	}

	public function setOffset($offset) {
		$this->offset = $offset;
	}
	
	public function addJoin($joinType, $table1, $table2, $column1, $column2, $compareOperator = "=") {
		$this->joins[] = new Join($joinType, $table1, $table2, $column1, $column2, $compareOperator = "=");
	}
	
	public function addColumn($table, $column = "*") {
		$this->columns[$table] = $column;
	}
	
	public function addConditional($table, $column, $value, $compareOperator = "=", $operator = "AND") {
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
	
	public function getQuery() {
		$query = "SELECT ";
		$query .= $this->getColumnSql();
		$query .= " FROM ";
		$query .= $this->getTablesSql();
		$query .= $this->getJoinsSql();
		$query .= $this->getConditionalsSql();		
		if ($this->orderBy != null) {
			$query .= " ORDER BY " . $this->orderBy;
		}
		if ($this->limit != null) {
			$query .= " LIMIT " . $this->limit;
		}
		if ($this->offset != null) {
			$query .= " OFFSET " . $this->offset;
		}
		return $query;
	}
	
	private function getColumnSql() {
		$columns = "";
		foreach ($this->columns as $table=>$column) {
			if ($column == "*") {
				$columns .= "`$table`.$column, ";
			} else {
				$columns .= "`$table`.$column, ";
			}
		}
		return rtrim($columns, ", ");
	}
	
	private function getJoinsSql() {
		$joins = "";
		if ($this->joins == null) return null;
		foreach ($this->joins as $join) {
			$joins .= $join->joinType . " `" . $join->table1 . "` ON `" . $join->table1 . "`.`" . $join->column1 . "` " . $join->compareOperator . " `". $join->table2 . "`.`" . $join->column2 . "` ";
		}
		return $joins;
	}
	
	private function getTablesSql() {
		$tables = "";
		foreach ($this->tables as $table) {
			$tables .= "`$table`, ";
		}
		return rtrim($tables, ", ") . " ";
	}
	
	public function getConditionalGroup($operator) {
		return new ConditionalGroup($operator);
	}
	
	public function addConditionalGroup($group) {
		$this->conditionals[] = $group;
	}
	
	private function getConditionalsSql() {
		if ($this->conditionals == null) return null;
		$where = "";
		$this->getConditionalSqlFromGroup($this->conditionals, $where);
		$where = " WHERE " . $where;
		return $where;
	}
	
	private function getConditionalSqlFromGroup($group, &$output, $include = false) {
		if ($group == null) return null;
		foreach ($group as $conditional) {
			$class = get_class($conditional);
			if ($class == "ConditionalGroup") {
				if ($include == true) {
					$output .=  " " . $conditional->operator;
				}
				$output .= " ( ";
				$this->getConditionalSqlFromGroup($conditional->conditionals, $output);
				$output .=  ")";
				$include = true;
			} else {
				$table = $conditional->table;
				$column = $conditional->column;
				$value = $conditional->value;
				$compareOperator = $conditional->compareOperator;
				$operator = $conditional->operator;
				if (!is_numeric($value) && $value != "null") {
					$value = "'" . Database::sanitise($value) . "'";
				}
				if ($include == false) {
					$output .= " `$table`.`$column` $compareOperator $value ";
					$include = true;
				} else {
					$output .= " $operator `$table`.`$column` $compareOperator $value ";
				}			
			}
		}
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
	
	public function addConditional($table, $column, $value, $compareOperator = "=", $operator = "AND") {
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
	
	public function __construct($table, $column, $value, $compareOperator = "=", $operator = "AND") {
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
	
	public function __construct($joinType, $table1, $table2, $column1, $column2, $compareOperator = "=") {
		$this->joinType = $joinType;
		$this->table1 = $table1;
		$this->table2 = $table2;
		$this->column1 = $column1;
		$this->column2 = $column2;
		$this->compareOperator = $compareOperator;
	}
	
}
