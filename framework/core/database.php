<?php
class Database {

    var $connection = null;
    var $errorControl = null;

    function __construct() {
        $this->errorControl = &CoreFactory::getErrorControl();
        $this->connect();
    }

    public function connect() {
        try {
            $this->connection = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD); // Constants may be replaced with config at some point.
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Exception mode.
        } catch (PDOException $e) {
            $this->errorControl->addError("SQL Error: " . $e->getMessage());
        }
    }

    public function query($sql, $parameters = null) {
        try {
            $result = $this->connection->prepare($sql);
            $result->execute($parameters);
            return $result;
        } catch (PDOException $e) {
           $this->errorControl->addError("SQL Error: " . $e->getMessage());
        }
    }

    public function getLastInstertedId() {
        try {
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->errorControl->addError("SQL Error: " . $e->getMessage());
        }
    }

    public function getCount($result) {
        return $result->rowCount;
    }

    public function getNext($result) {
        return $result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
    }

//    Should be using parametrized queries.
//    public static function sanitise($value) {
//        return mysql_real_escape_string($value);
//    }
}
?>