<?php
	session_start();
    ini_set('display_errors', 'On');
	error_reporting(E_ALL);

    define('SQUIB_PATH', realpath(dirname(__FILE__)));

    define("DB_DRIVER", "mysql");
    define("DB_DSN", "localhost");
	define("DB_USERNAME", "root");
	define("DB_PASSWORD", "");

	require_once(SQUIB_PATH . "/corefactory.php");
	require_once(SQUIB_PATH . "/factory.php");
    require_once(SQUIB_PATH . "/core/drivers/" . DB_DRIVER . "/driver.php");

	$errorControl = &CoreFactory::getErrorControl();
?>
