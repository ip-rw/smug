<?php
    // Just a test file. Uses the TestObjects
    // IDE git integration test.
    require_once('framework/common.php');
    header("content-type: text/plain");

    $controls[] = Factory::getTestControl();
    $controls[] = Factory::getTestGroupControl();

    foreach($controls as $control) {
        echo "-- {$control->table}\n";
        echo $control->createMySql();
    }
?>