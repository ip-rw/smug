<?php

interface IDatabase {
    public function connect();
    public function query($sql);
    public function getLastInstertedId();
    public function getCount($result);
    public static function sanitise($value);
}

class MySQLDatabase implements IDatabase {

    var $connection = null;
    var $errorControl = null;

    function __construct() {
        $this->errorControl = &CoreFactory::getErrorControl();
        $this->connect();
    }

    public function connect() {
        $this->connection = mysql_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);
        mysql_select_db(DB_DATABASE);
    }

    public function query($sql) {
        $result = mysql_query($sql, $this->connection);
        if (mysql_error($this->connection)) {
            $this->errorControl->addError("SQL Error: " . mysql_error($this->connection) . " ( $sql )");
        }
        return $result;
    }

    public function getLastInstertedId() {
        return mysql_insert_id($this->connection);
    }

    public function getCount($result) {
        return mysql_num_rows($result);
    }
    public static function sanitise($value) {
        return mysql_real_escape_string($value);
    }
}

class SqliteDatabase implements IDatabase {

    var $connection = null;
    var $errorControl = null;

    function __construct() {
        $this->errorControl = &CoreFactory::getErrorControl();
        $this->connect();
    }

    public function connect() {
        $this->connection = sqlite_open(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD);
    }

    public function query($sql) {
        $result = sqlite_query($sql, $this->connection);
        if (sqlite_last_error($this->connection)) {
            $errorCode = sqlite_last_error($this->connection);
            $this->errorControl->addError("SQL Error: " . sqlite_error_string($errorCode) . " ( $sql )");
        }
        return $result;
    }

    public function getLastInstertedId() {
        return sqlite_last_insert_rowid($this->connection);
    }

    public function getCount($result) {
        return sqlite_num_rows($result);
    }
    public static function sanitise($value) {
        return sqlite_escape_string($value);
    }
}
?>
