<?php
// Just a test file. Uses the TestObjects defined in base.

require_once('framework/common.php');
$testControl = Factory::getTestControl();
$testGroupControl = Factory::getTestGroupControl();

$testGroup = $testGroupControl->makeNew();
$testGroup->TestGroupName = 'Group #' . rand(0, 65535);
$testGroup->DateCreated = time();
$id = $testGroup->save();

for ($i = 0; $i < rand(1, 20); $i++) {
    $test = $testControl->makeNew();
    $test->TestGroupID = $id;
    $test->TestName = 'Test #' . rand(0, 65535);
    $test->save();
}

$testControl->retrieve();
while ($test = $testControl->getNext()) {
    $testGroup = $test->getRelation("TestGroupID");
    echo "{$test->TestName} ({$testGroup->TestGroupName})<br>";
}
$errors = & CoreFactory::getErrorControl();
echo $errors->getErrorHtml(0);
?>