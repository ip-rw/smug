<?php

define("SQUIB_TYPE_INTEGER", 1);
define("SQUIB_TYPE_TEXT", 2);
define("SQUIB_TYPE_DATE", 3);
define("SQUIB_TYPE_BOOL", 4);
define("SQUIB_TYPE_LONG", 5);

// FieldMeta is used to define our DataFields in a DataControl. Should be fairly self-explanatory.

class FieldMeta {

	var $column = null;
	var $name = null;
	var $type = null;
	var $defaultValue = null;
	var $length = 0;
	var $notNull = false;
	var $relatedControl = null;
	var $unique = null;
	
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
	
	public function getColumnMySql($dataControl) {
		$sql = "`" . $this->column . "`";
		$length = ($this->length == -1) ? "MAX" : $this->length;
        $default = '';
		switch ($this->type) {
			case SQUIB_TYPE_INTEGER: 
				$default = ($this->defaultValue != "" && $this->defaultValue != null) ? " DEFAULT " . $this->defaultValue : null;
				$sql .= " INT ";
				break;
			case SQUIB_TYPE_TEXT: 
				$default = ($this->defaultValue != "" && $this->defaultValue != null) ? "DEFAULT '" . $this->defaultValue . "'": null;
				if ($this->length != -1) {
					$sql .= " VARCHAR($length) ";
				} else {
					$sql .= " TEXT ";
				}
				break;
			case SQUIB_TYPE_DATE:
				$default = ($this->defaultValue != null && $this->defaultValue != "") ? " DEFAULT $this->defaultValue " : null;
				$sql .= " TIMESTAMP ";
				break;
			case SQUIB_TYPE_BOOL:
				if ($this->defaultValue !== null) {
					if ($this->defaultValue == true) {
						$default = " DEFAULT 1 ";
					} elseif ($this->defaultValue == false) {
						$default = " DEFAULT 0 ";
					}
				}
				$sql .= " BOOLEAN ";
                break;
            case SQUIB_TYPE_LONG:
                $default = ($this->defaultValue != "" && $this->defaultValue != null) ? " DEFAULT " . $this->defaultValue : null;
                $sql .= " INT UNSIGNED ";
                break;
        }
		
		if ($this->notNull == true) {
			$sql .= " NOT NULL ";
		}
		
		if ($this->column == $dataControl->key) {
			$sql .= " AUTO_INCREMENT PRIMARY KEY ";
		}
		if ($this->unique == true) {
			$sql .= " UNIQUE ";
		}
		$sql .= $default;
		return $sql;
	}
}

?>