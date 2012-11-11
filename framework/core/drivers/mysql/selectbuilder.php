<?php
require_once(SMUG_PATH . "/core/dbms.php");
require_once(SMUG_PATH . "/core/drivers/mysql/driver.php");

class MySqlSelectBuilder implements ISelectBuilder {
    var $filter = null;
    var $bindValues = array();

    public function __construct($filter) {
        $this->filter = $filter;
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
        $query = "SELECT ";
        $query .= $this->getColumnSql();
        $query .= " FROM ";
        $query .= $this->getTablesSql();
        $query .= $this->getJoinsSql();
        $query .= $this->getConditionalsSql();
        if ($this->filter->orderBy != null) {
            $query .= " ORDER BY " . $this->filter->orderBy;
        }
        if ($this->filter->limit != null) {
            $query .= " LIMIT " . $this->filter->limit;
        }
        if ($this->filter->offset != null) {
            $query .= " OFFSET " . $this->filter->offset;
        }
        return $query;
    }

    private function getColumnSql() {
        $columns = "";
        foreach ($this->filter->columns as $table => $column) {
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
        if ($this->filter->joins == null) return null;
        foreach ($this->filter->joins as $join) {
            $joins .= $join->joinType . " `" . $join->table1 . "` ON `" . $join->table1 . "`.`" . $join->column1 . "` " . DBDriver::getOperator($join->compareOperator) . " `" . $join->table2 . "`.`" . $join->column2 . "` ";
        }
        return $joins;
    }

    private function getTablesSql() {
        $tables = "";
        foreach ($this->filter->tables as $table) {
            $tables .= "`$table`, ";
        }
        return rtrim($tables, ", ") . " ";
    }

    private function getConditionalsSql() {
        if ($this->filter->conditionals == null) return null;
        $where = "";
        $this->getConditionalSqlFromGroup($this->filter->conditionals, $where);
        $where = " WHERE " . $where;
        return $where;
    }

    private function getConditionalSqlFromGroup($group, &$output, $include = false) {
        if ($group == null) return null;
        foreach ($group as $conditional) {
            $class = get_class($conditional);
            if ($class == "ConditionalGroup") {
                if ($include == true) {
                    $output .= " " . $conditional->operator;
                }
                $output .= " ( ";
                $this->getConditionalSqlFromGroup($conditional->conditionals, $output);
                $output .= ")";
                $include = true;
            } else {
                $table = $conditional->table;
                $column = $conditional->column;
                $value = $conditional->value;
                $compareOperator = DBDriver::getOperator($conditional->compareOperator);
                $operator = $conditional->operator;

                if ($include == false) {
                    $output .= " `$table`.`$column` $compareOperator ? ";
                    $include = true;
                } else {
                    $output .= " $operator `$table`.`$column` $compareOperator ? ";
                }
                $this->bindValues[] = $value;
            }
        }
    }

}
