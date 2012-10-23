<?php

// CoreFactory is the Factory for the Core classes.
// Notice the two singletons. Don't forgot to assign by reference.

class CoreFactory {
	public static function &getDatabaseConnection() {
		require_once(SQUIB_PATH . "/core/database.php");
		static $instance;
		if (!is_object($instance)) {
			$instance = new Database();
		}
		return $instance;
	}
	
	public static function &getErrorControl() {
		require_once(SQUIB_PATH . "/core/error.php");
		static $instance;
		if (!is_object($instance)) {
			$instance = new ErrorControl();
		}
		return $instance;
	}
	
	public function getInsertUpdateQueryBuilder($dataControl, $dataEntity) {
		require_once(SQUIB_PATH . "/core/insertupdatequerybuilder.php");
		$instance = new InsertUpdateQueryBuilder($dataControl, $dataEntity);
		return $instance;
	}
}
?>