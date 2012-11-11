<?php
session_start();
ini_set('display_errors', 'On');
error_reporting(E_ALL);

define('SMUG_PATH', realpath(dirname(__FILE__)));

define("DB_DRIVER", "MySql");
define("DB_DSN", "mysql:host=localhost;dbname=Test");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "c4ssi3");

require_once(SMUG_PATH . "/corefactory.php");
require_once(SMUG_PATH . "/factory.php");
require_once(SMUG_PATH . "/core/drivers/" . strtolower(DB_DRIVER) . "/driver.php");

// This is something of a hack, but it's marginally better than calling each driver class DBDriver.
class_alias(DB_DRIVER . "Driver", 'DBDriver');

$errorControl = &CoreFactory::getErrorControl();
?>
