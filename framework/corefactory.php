<?php
class CoreFactory {

    public static function &getErrorControl() {
        require_once(SMUG_PATH . "/core/error.php");
        static $instance;
        if (!is_object($instance)) {
            $instance = new ErrorControl();
        }
        return $instance;
    }
}

?>