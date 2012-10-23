<?php
	session_start();
    ini_set('display_errors', 'On');
	error_reporting(E_ALL);
	date_default_timezone_set('Europe/London');

    define('SQUIB_PATH', realpath(dirname(__FILE__)));

	define("DB_HOSTNAME", "localhost");
	define("DB_USERNAME", "root");
	define("DB_PASSWORD", "");
	define("DB_DATABASE", "TestData");

	require_once(SQUIB_PATH . "/corefactory.php");
	require_once(SQUIB_PATH . "/core/database.php");
	require_once(SQUIB_PATH . "/factory.php");

	$errorControl = &CoreFactory::getErrorControl();
?>
