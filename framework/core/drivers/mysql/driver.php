<?php
require_once(SQUIB_PATH . "/core/dbms.php");
require_once(SQUIB_PATH . "/core/filter.php");
require_once(SQUIB_PATH . "/core/drivers/mysql/insertupdatebuilder.php");
require_once(SQUIB_PATH . "/core/drivers/mysql/selectbuilder.php");

class DBDriver implements IDBDriver {

    public static function getConnection() {
        static $connection = null;
        try {
            if ($connection == null) {
                $connection = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD); // Constants may be replaced with config at some point.
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Exception mode.
            }
        } catch (PDOException $e) {
            CoreFactory::getErrorControl()->addError("SQL Error: " . $e->getMessage());
        }
        return $connection;
    }

    public static function getOperator($operatorEnum) {
        $operatorMap = array(
            OperatorEnum::Equal                 => '=',
            OperatorEnum::NotEqual              => '!=',
            OperatorEnum::GreaterThan           => '>',
            OperatorEnum::GreaterThanOrEqualTo  => '>=',
            OperatorEnum::LessThan              => '<',
            OperatorEnum::LessThanOrEqualTo     => '<=',
            OperatorEnum::Like                  => 'LIKE',
        );
        return $operatorMap[$operatorEnum];
    }

    public static function getSelectQueryBuilder($filter) {
        return new MySqlSelectBuilder($filter);
    }

    public static function getInsertUpdateQueryBuilder($dataEntity) {
        if ($dataEntity->isNew == true) {
            return new MySqlInsertBuilder($dataEntity);
        } else {
            return new MySqlUpdateBuilder($dataEntity);
        }
    }
}

// This just saved an hour. Result.
//public function createMySql() {
//    $sql = "CREATE TABLE `" . $this->table . "`\n(";
//    foreach ($this->fieldMeta as $field) {
//        $sql .= "\t" . $field->getColumnMySql($this) . ",\n";
//    }
//    $sql = rtrim($sql,",\n") . "\n";
//    $sql .= ");\n\n";
//    return $sql;
//}
