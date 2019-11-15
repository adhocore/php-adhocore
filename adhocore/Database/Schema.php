<?php

namespace Adhocore\Database;

defined('COREPATH') or die('Error 403');

abstract class Schema
{
    protected $fields  = [];

    protected $attribs = [];

    protected $renames = [];

    abstract public function php_to_db($type);

    abstract protected function field_sql($asString);

    abstract protected function attrib_sql($asString);

    abstract public function create_table($table);

    abstract public function rename_table($table, $newname);

    abstract public function update_table($table);

    abstract public function drop_table($table);

    abstract public function drop_field($table, $field);

    public function reset()
    {
        $this->fields  =
        $this->attribs =
            [];
    }

    public function driver()
    {
        return ahc()->database()->driver();
    }

    public function primary($field = null)
    {
        $field or $field               = $this->fields[count($this->fields) - 1]['field'];
        $this->attribs[__FUNCTION__][] = $field;

        return $this;
    }

    public function unique($name = null)
    {
        $field                         = $this->fields[count($this->fields) - 1]['field'];
        $name or $name                 = __FUNCTION__ . '_' . $field;
        $this->attribs[__FUNCTION__][] = compact('name', 'field');

        return $this;
    }

    public function fulltext($name = null)
    {
        $field                         = $this->fields[count($this->fields) - 1]['field'];
        $name or $name                 = __FUNCTION__ . '_' . $field;
        $this->attribs[__FUNCTION__][] = compact('name', 'field');

        return $this;
    }

    public function index($name = null)
    {
        $field                         = $this->fields[count($this->fields) - 1]['field'];
        $name or $name                 = __FUNCTION__ . '_' . $field;
        $this->attribs[__FUNCTION__][] = compact('name', 'field');

        return $this;
    }

    public function foreign($ref_field, $ref_table, $field = null, $name = null, $on_update = null, $on_delete = null)
    {
        $name or $name                 = __FUNCTION__ . '_' . $ref_table . '_' . $ref_field;
        $field or $field               = $this->fields[count($this->fields) - 1]['field'];
        $this->attribs[__FUNCTION__][] = compact('name', 'field', 'ref_field', 'ref_table', 'on_update', 'on_delete');
        $this->integer($field)->nullable();

        return $this;
    }

    public function auto($field)
    {
        $auto = true;
        $type = 'integer';
        $this->primary($field);
        $this->fields[] = compact('type', 'field', 'auto');

        return $this;
    }

    public function boolean($field)
    {
        if (is_array($field)) {
            foreach ($field as $field) {
                $this->boolean($field);
            }
        } else {
            $type           = __FUNCTION__;
            $this->fields[] = compact('type', 'field');
        }

        return $this;
    }

    public function decimal($field, $precision = 10, $scale = 2)
    {
        if (is_array($field)) {
            foreach ($field as $field) {
                $this->decimal($field, $precision, $scale);
            }
        } else {
            $type           = __FUNCTION__;
            $this->fields[] = compact('type', 'field', 'precision', 'scale');
        }

        return $this;
    }

    public function integer($field)
    {
        if (is_array($field)) {
            foreach ($field as $field) {
                $this->integer($field);
            }
        } else {
            $type           = __FUNCTION__;
            $this->fields[] = compact('type', 'field');
        }

        return $this;
    }

    public function float($field)
    {
        if (is_array($field)) {
            foreach ($field as $field) {
                $this->float($field);
            }
        } else {
            $type           = __FUNCTION__;
            $this->fields[] = compact('type', 'field');
        }

        return $this;
    }

    public function string($field, $length = 255)
    {
        if (is_array($field)) {
            foreach ($field as $field) {
                $this->string($field, $length);
            }
        } else {
            $type           = __FUNCTION__;
            $this->fields[] = compact('type', 'field', 'length');
        }

        return $this;
    }

    public function timestamp($field)
    {
        if (is_array($field)) {
            foreach ($field as $field) {
                $this->text($field);
            }
        } else {
            $type           = __FUNCTION__;
            $this->fields[] = compact('type', 'field');
        }

        return $this;
    }

    public function datetime($field)
    {
        if (is_array($field)) {
            foreach ($field as $field) {
                $this->datetime($field);
            }
        } else {
            $type           = __FUNCTION__;
            $this->fields[] = compact('type', 'field');
        }

        return $this;
    }

    public function text($field)
    {
        if (is_array($field)) {
            foreach ($field as $field) {
                $this->text($field);
            }
        } else {
            $type           = __FUNCTION__;
            $this->fields[] = compact('type', 'field');
        }

        return $this;
    }

    public function longtext($field)
    {
        if (is_array($field)) {
            foreach ($field as $field) {
                $this->longtext($field);
            }
        } else {
            $type           = __FUNCTION__;
            $this->fields[] = compact('type', 'field');
        }

        return $this;
    }

    public function nullable()
    {
        $this->fields[count($this->fields) - 1] += ['nullable' => true, 'default' => null];

        return $this;
    }

    public function set_default($default)
    {
        $default = (is_bool($default)) ? intval($default) : strval($default);
        $this->fields[count($this->fields) - 1] += ['nullable' => false, 'default' => $default];

        return $this;
    }

    public function unsigned()
    {
        $this->fields[count($this->fields) - 1]['unsigned'] = true;

        return $this;
    }
}
