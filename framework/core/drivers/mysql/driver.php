<?php
require_once(SMUG_PATH . "/core/dbms.php");
require_once(SMUG_PATH . "/core/filter.php");
require_once(SMUG_PATH . "/core/drivers/mysql/insertupdatebuilder.php");
require_once(SMUG_PATH . "/core/drivers/mysql/selectbuilder.php");

class MySqlDriver implements IDBDriver {

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

// FIXME: Automatic CREATE TABLE stuff is very useful, we need to work this functionality into the Driver interfaces.
//public function createMySql() {
//    $sql = "CREATE TABLE `" . $this->table . "`\n(";
//    foreach ($this->fieldMeta as $field) {
//        $sql .= "\t" . $field->getColumnMySql($this) . ",\n";
//    }
//    $sql = rtrim($sql,",\n") . "\n";
//    $sql .= ");\n\n";
//    return $sql;
//}

// Taken from fieldmeta.php
//    public function getColumnMySql($dataControl) {
//        $sql = "`" . $this->column . "`";
//        $length = ($this->length == -1) ? "MAX" : $this->length;
//        $default = '';
//        switch ($this->type) {
//            case SMUG_TYPE_INTEGER:
//                $default = ($this->defaultValue != "" && $this->defaultValue != null) ? " DEFAULT " . $this->defaultValue : null;
//                $sql .= " INT ";
//                break;
//            case SMUG_TYPE_TEXT:
//                $default = ($this->defaultValue != "" && $this->defaultValue != null) ? "DEFAULT '" . $this->defaultValue . "'" : null;
//                if ($this->length != -1) {
//                    $sql .= " VARCHAR($length) ";
//                } else {
//                    $sql .= " TEXT ";
//                }
//                break;
//            case SMUG_TYPE_DATE:
//                $default = ($this->defaultValue != null && $this->defaultValue != "") ? " DEFAULT $this->defaultValue " : null;
//                $sql .= " TIMESTAMP ";
//                break;
//            case SMUG_TYPE_BOOL:
//                if ($this->defaultValue !== null) {
//                    if ($this->defaultValue == true) {
//                        $default = " DEFAULT 1 ";
//                    } elseif ($this->defaultValue == false) {
//                        $default = " DEFAULT 0 ";
//                    }
//                }
//                $sql .= " BOOLEAN ";
//                break;
//        }
//
//        if ($this->notNull == true) {
//            $sql .= " NOT NULL ";
//        }
//
//        if ($this->column == $dataControl->key) {
//            $sql .= " AUTO_INCREMENT PRIMARY KEY ";
//        }
//        if ($this->unique == true) {
//            $sql .= " UNIQUE ";
//        }
//        $sql .= $default;
//        return $sql;
//    }
