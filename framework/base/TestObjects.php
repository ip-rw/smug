<?php
require_once(SQUIB_PATH . "/core/datacontrol.php");
require_once(SQUIB_PATH . "/core/fieldmeta.php");

class TestControl extends DataControl {
	function __construct() {
		parent::__construct();
		$this->table = "Tests";
		$this->key = "TestID";
		$this->orderBy = "TestID";

		// $column, $name, $type, $default = null, $length = -1, $notNull = false
		$this->fieldMeta[] = new FieldMeta("TestID", "Test ID", SQUIB_TYPE_INTEGER, null, -1, true);
		$this->fieldMeta[] = new FieldMeta("TestName", "Test Name", SQUIB_TYPE_TEXT, null, 64, true);
	}
}

class TestGroupControl extends DataControl {
    function __construct() {
        parent::__construct();
        $this->table = "TestGroups";
        $this->key = "TestGroupID";
        $this->orderBy = "TestGroupID";

        // $column, $name, $type, $default = null, $length = -1, $notNull = false
        $this->fieldMeta[] = new FieldMeta("TestGroupID", "Test Group ID", SQUIB_TYPE_INTEGER, null, -1, true);

        $testID = new FieldMeta("TestID", "Test ID", SQUIB_TYPE_INTEGER, null, -1, true);
        $testID->setRelation(new TestControl());
        $this->fieldMeta[] = $testID;

        $this->fieldMeta[] = new FieldMeta("TestGroupName", "Test Group Name", SQUIB_TYPE_TEXT, null, 64, true);
        $this->fieldMeta[] = new FieldMeta("DateCreated", "Created Date", SQUIB_TYPE_DATE, "NOW()", -1, true);
    }
}
?>
