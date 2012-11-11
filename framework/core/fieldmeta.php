<?php

define("SMUG_TYPE_INTEGER", 1);
define("SMUG_TYPE_TEXT", 2);
define("SMUG_TYPE_DATE", 3);
define("SMUG_TYPE_BOOL", 4);

// FieldMeta is used to define our DataFields in a DataControl. Should be fairly self-explanatory.

class FieldMeta {

    var $column         = null;
    var $name           = null;
    var $type           = null;
    var $defaultValue   = null;
    var $length         = 0;
    var $notNull        = false;
    var $relatedControl = null;
    var $unique         = null;

    public function __construct($column, $name, $type, $default = null, $length = -1, $notNull = false, $unique = false) {
        $this->column = $column;
        $this->name = $name;
        $this->type = $type;
        $this->defaultValue = $default;
        $this->length = $length;
        $this->notNull = $notNull;
        $this->unique = $unique;
    }

    public function setRelation($relatedControl) {
        $this->relatedControl = $relatedControl;
    }
}

?>