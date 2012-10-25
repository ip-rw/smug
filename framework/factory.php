<?php
class Factory {

    public static function getTestControl() {
        require_once(SQUIB_PATH . "/base/TestObjects.php");
        $control = new TestControl();
        return $control;
    }

    public static function getTestGroupControl() {
        require_once(SQUIB_PATH . "/base/TestObjects.php");
        $control = new TestGroupControl();
        return $control;
    }
}

?>