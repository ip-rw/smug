<?php
require_once("selectquerybuilder.php");

//  This is the filter class and it's the best way to programmatically create queries.
//  Create your filter, DataControl->setFilter($filter), DataControl->retrieve() and you're laughing.
//  This is essentially a wrapper for SelectQueryBuilder, check it out for method comments.

class FilterControl {
	var $selectQueryBuilder = null;
	public function __construct($dataControl) {
		$this->selectQueryBuilder = new SelectQueryBuilder();
		$this->selectQueryBuilder->addTable($dataControl->table);
		$this->selectQueryBuilder->addColumn($dataControl->table, "*");
		$this->selectQueryBuilder->setOrderBy($dataControl->table, $dataControl->orderBy, $dataControl->asc);
	}
	public function addConditional($table, $column, $value, $compareOperator = "=", $operator = "AND") {
		$this->selectQueryBuilder->addConditional($table, $column, $value, $compareOperator, $operator);
	}
	public function getConditionalGroup($operator) {
		return $this->selectQueryBuilder->getConditionalGroup($operator);
	}
	public function addJoin($joinType, $table1, $table2, $column1, $column2, $compareOperator = "=") {
		return $this->selectQueryBuilder->addJoin($joinType, $table1, $table2, $column1, $column2, $compareOperator = "=");
	}
	public function addColumn($table, $column = "*") {
		$this->selectQueryBuilder->addColumn($table, $column);
	}
	public function addConditionalGroup($group) {
		$this->selectQueryBuilder->addConditionalGroup($group);
	}
	public function addTable($table) {
		$this->selectQueryBuilder->addTable($table);
	}
	public function setLimit($limit) {
		$this->selectQueryBuilder->setLimit($limit);
	}
	public function setOrderBy($table, $column, $asc) {
		$this->selectQueryBuilder->setOrderBy($table, $column, $asc);
	}
	public function setOffset($offset) {
		$this->selectQueryBuilder->setOffset($offset);
	}
	public function getQuery() {
		return $this->selectQueryBuilder->getQuery();
	}
}