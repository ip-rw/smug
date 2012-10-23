<?php
require_once(SQUIB_PATH . "/common.php");
require_once(SQUIB_PATH . "/core/filter.php");
require_once(SQUIB_PATH . "/core/dataentity.php");

class Database {

    var $connection = null;
    var $errorControl = null;
    function __construct() {
        $this->errorControl = &CoreFactory::getErrorControl();

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
?>