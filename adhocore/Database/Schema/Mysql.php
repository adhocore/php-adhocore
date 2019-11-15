<?php

namespace Adhocore\Database\Schema;

defined('COREPATH') or die('Error 403');

class Mysql extends \Adhocore\Database\Schema
{
    public function __construct()
    {
    }

    public function php_to_db($type)
    {
        $types = [
            'string'  => 'VARCHAR',
            'integer' => 'INT',
            'boolean' => 'TINYINT(1)'
        ];

        return isset($types[$type]) ? strtoupper($types[$type]) : strtoupper($type);
    }

    public function create_table($table, $if_not_exists = false)
    {
        if ($this->fields) {
            $if_not = $if_not_exists ? 'IF NOT EXISTS ' : '';
            $create = "CREATE TABLE {$if_not}{$this->driver()->wrap_table($table)} ("
                    . $this->field_sql(true)
                    . (($attrib = $this->attrib_sql(true)) ? ', ' . $attrib . ');' : ');');

            $this->driver()->query($create);
        }
    }

    public function rename_table($table, $newname)
    {
        return $this->driver()->query("RENAME TABLE {$this->driver()->wrap_table($table)} TO {$this->driver()->wrap_table($newname)}");
    }

    public function update_table($table)
    {
        if ($this->fields) {
            $update = "ALTER TABLE {$this->driver()->wrap_table($table)} ";
            $fields = array_map([$this->driver(), 'wrap'], $this->driver()->list_fields($table));

            // Append field SQL
            $update .= implode(
                ', ',
                array_map(function ($col) use ($fields) {
                    $field = strstr($col, ' ', true);

                    return (in_array($field, $fields) ? "CHANGE COLUMN {$field} " : 'ADD COLUMN ') . $col;
                }, $this->field_sql())
            );

            // Append attrib SQL
            if ($this->attribs) {
                $update .= ', ' . implode(
                    ', ',
                    array_map(function ($col) {
                        is_array($col) and $col = implode(', ADD ', $col);

                        return (str_begins($col, 'PRIMARY KEY')) ? "DROP PRIMARY KEY, ADD {$col}" : "ADD {$col}";
                    }, $this->attrib_sql())
                );
            }

            return $this->driver()->query($update . ';');
        }
    }

    public function drop_table($table)
    {
        return $this->driver()->query("DROP TABLE IF EXISTS {$this->driver()->wrap_table($table)}");
    }

    public function drop_field($table, $field)
    {
        $drop = "ALTER TABLE {$this->driver()->wrap_table($table)} DROP COLUMN "
                . implode(', DROP COLUMN ', array_map([$this->driver(), 'wrap'], (array) $field));

        return $this->driver()->query($drop);
    }

    /*
    private function drop_key($table, $key)
    {
        $drop = "ALTER TABLE {$this->driver()->wrap_table($table)} DROP INDEX {$this->driver()->wrap($key)};";
        return $this->driver()->query($drop);
    }

    public function drop_unique($table, $field, $name = NULL)
    {
        $name or $name = substr(__FUNCTION__, 5).'_'.$field;
        return $this->drop_key($table, $name);
    }

    public function drop_index($table, $field, $name = NULL)
    {
        $name or $name = substr(__FUNCTION__, 5).'_'.$field;
        return $this->drop_key($table, $name);
    }

    public function drop_foreign($table, $name = NULL)
    {
        $drop = "ALTER TABLE {$this->driver()->wrap_table($table)} DROP FOREIGN KEY {$this->driver()->wrap($name)};";
        return $this->driver()->query($drop);
    }
    */

    public function rename_field($field, $newname)
    {
    }

    protected function field_sql($asString = false)
    {
        $coldef = [];

        foreach ($this->fields as $c => $col) {
            $coldef[] = $this->driver()->wrap($col['field']) . ' ' . $this->php_to_db($col['type'])
                    . ($col['type'] === 'string' ? "({$col['length']})" : '')
                    . ($col['type'] === 'decimal' ? "({$col['precision']}, {$col['scale']})" : '')
                    . ((array_key_exists('unsigned', $col) and $col['type'] === 'integer') ? ' UNSIGNED' : '')
                    . ((array_key_exists('nullable', $col) and $col['nullable']) ? ' NULL': ' NOT NULL')
                    . (array_key_exists('default', $col) ? ' DEFAULT ' . ($col['default'] ? "'{$col['default']}'" : 'NULL') : '')
                    . ((array_key_exists('auto', $col) and $col['auto']) ? ' AUTO_INCREMENT' : '')
                    . (array_key_exists('extra', $col) ? ' ' . $col['extra'] . ' ' : '')
                    ;
        }

        return ($asString) ? implode($coldef, ', ') : $coldef;
    }

    protected function attrib_sql($asString = false)
    {
        $attrs = [];

        if ($this->attribs) {
            foreach ($this->attribs as $type => $attrib) {
                if ($type == 'primary') {
                    $attrs[$type] = 'PRIMARY KEY (' . implode(', ', array_map([$this->driver(), 'wrap'], array_unique($attrib))) . ')';
                } elseif ($type == 'foreign') {
                    $attrs[$type] = $this->foreign_attrib($attrib, $asString);
                } else {
                    $attrs[$type] = $this->attrib($attrib, $type, $asString);
                }
            }
        }

        return ($asString) ? implode($attrs, ', ') : $attrs;
    }

    private function foreign_attrib($attrib, $asString = false)
    {
        $return = [];
        foreach ($attrib as $attr) {
            $return[] = "CONSTRAINT {$this->driver()->wrap($attr['name'])} FOREIGN KEY ({$this->driver()->wrap($attr['field'])}) "
                      . "REFERENCES {$this->driver()->wrap_table($attr['ref_table'])} ({$this->driver()->wrap($attr['ref_field'])})"
                      . trim(strtoupper($attr['on_update'] . ' ' . $attr['on_delete']));
        }

        return ($asString) ? implode(', ', $return) : $return;
    }

    private function attrib($attrib, $type, $asString = false)
    {
        $return = [];
        foreach ($attrib as $attr) {
            $return[] = strtoupper($type) . " {$this->driver()->wrap($attr['name'])} ({$this->driver()->wrap($attr['field'])})";
        }

        return ($asString) ? implode(', ', $return) : $return;
    }
}
