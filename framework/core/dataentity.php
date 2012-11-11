<?php

// DataControls produce DataEntities to hold single records.

class DataEntity {
    var $data = null;
    var $originalData = null;
    var $isNew = false;
    var $dataControl = null;
    var $fieldMeta = null;

    function __construct(&$dataControl) {
        $this->dataControl = $dataControl;
        $this->fieldMeta = $this->dataControl->fieldMeta;
        $this->data = $this->dataControl->data;
        $this->originalData = $this->dataControl->data;
    }

    public function get($column) {
        return $this->data[$column];
    }

    // FIXME: This is MySQL specific.
    // $value should be converted to epoc time, and then the DB driver should handle the conversion to it's preferred format.
    public function set($column, $value) {
        foreach ($this->fieldMeta as $field) {
            if ($field->column == $column) {
                switch ($field->type) {
                    case SMUG_TYPE_DATE:
                        $value = date("Y-m-d", strtotime($value));
                        break;
                    default:
                        break;
                }
            }
        }
        $this->data[$column] = $value;
    }

    public function toXml($root = true, $level = 0) {
        $output = "";
        if ($root = true) {
            $output .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        }
        $output .= str_repeat("\t", $level) . "<" . $this->dataControl->table . ">\n";
        $level++;
        foreach ($this->fieldMeta as $fieldMeta) {
            $output .= str_repeat("\t", $level) . "<" . $fieldMeta->column . ">\n";
            $output .= str_repeat("\t", $level) . "<![CDATA[";
            $output .= $this->data[$fieldMeta->column];
            $output .= "]]>\n";
            if ($fieldMeta->relatedControl != null) {
                $output .= $this->toXml(false, $level);
            }
        }
        return $output;
    }

    // With PHP5 we can use properties instead of get/set.
    public function __set($key, $value) {
        $this->set($key, $value);
    }

    public function __get($key) {
        return $this->get($key);
    }

    // FIXME: Data types need to handle their own formatting.
    // Each type is going to need some helper classes, and/or we bring in formatters and types have a default formatter.
    public function getFormatted($key, $format = "d/m/Y G:i:s") {
        foreach ($this->fieldMeta as $field) {
            if ($field->column == $key) {
                switch ($field->type) {
                    case SMUG_TYPE_DATE:
                        return date($format, strtotime($this->get($key)));
                        break;
                    default:
                        return $this->get($key);
                        break;
                }
            }
        }
    }

    // 3 guesses.
    public function save() {
        return $this->dataControl->save($this);
    }

    // Retrieve a related DataEntity.
    public function getRelation($name) {
        foreach ($this->fieldMeta as $field) {
            if ($field->column == $name) {
                $relatedEntity = $field->relatedControl->item($this->get($name));
                return $relatedEntity;
            }
        }
        return false;
    }

    // Validate, will return BOOL but will add errors to errorControl
    public function validate() {
        return $this->dataControl->validate($this);
    }
}

?>
