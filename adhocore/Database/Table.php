<?php

namespace Adhocore\Database;

defined('COREPATH') or die('Error 403');

class Table
{
    private $name;

    private $select;

    private $distinct;

    private $alias;

    private $join;

    private $where;

    private $nested;

    private $groupby;

    private $having;

    private $orderby;

    private $limit;

    private $offset;

    private $binds;

    private $format;

    private $rawsql;

    private $aggregates;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __call($method, $params)
    {
        $matched = [];
        preg_match('/(or_where|and_where)_(\w+)/i', $method, $matched);
        if ($matched) {
            return $this->{$matched[1]}((isset($params[2])?$params[2] . '.':'') . $matched[2], $params[0], $params[1]);
        }

        if (in_array($method, ['count', 'min', 'max', 'avg', 'sum'])) {
            $params = $params ? reset($params) : '*';

            return $this->aggr(strtoupper($method), $params);
        }

        throw new \Exception("Unknown Method: " . __CLASS__ . ": {$method}()");
    }

    public function __get($key)
    {
        return $this->$key;
    }

    public function reset()
    {
        $protect = ['name', 'database', 'format'];
        foreach (get_object_vars($this) as $key => $val) {
            if (in_array($key, $protect) === true) {
                continue;
            }
            $this->$key = null;
        }

        return $this;
    }

    public function select()
    {
        $this->select = array_flatten(func_get_args()) ?: ['*'];

        return $this;
    }

    public function distinct()
    {
        if (is_null($this->select)) {
            $this->select = array_flatten(func_get_args()) ?: ['*'];
        }
        $this->distinct = true;

        return $this;
    }

    public function alias($alias)
    {
        $this->alias = $alias;

        return $this;
    }

    public function join($on, $condition)
    {
        $type         = 'INNER';
        $this->join[] = compact('on', 'condition', 'type');

        return $this;
    }

    public function left_join($on, $condition)
    {
        $type         = 'LEFT';
        $this->join[] = compact('on', 'condition', 'type');

        return $this;
    }

    public function right_join($on, $condition)
    {
        $type         = 'RIGHT';
        $this->join[] = compact('on', 'condition', 'type');

        return $this;
    }

    public function where($field, $comparator = '=', $value = '')
    {
        $joiner        = '';
        $nested        = false;
        $this->where[] = compact('field', 'comparator', 'value', 'joiner', 'nested');

        return $this;
    }

    public function or_where($field, $comparator = '=', $value = '')
    {
        $joiner        = 'OR';
        $nested        = false;
        $this->where[] = compact('field', 'comparator', 'value', 'joiner', 'nested');

        return $this;
    }

    public function and_where($field, $comparator = '=', $value = '')
    {
        $joiner        = 'AND';
        $nested        = false;
        $this->where[] = compact('field', 'comparator', 'value', 'joiner', 'nested');

        return $this;
    }

    public function where_in($field, $in = [], $joiner = '')
    {
        $comparator = 'IN';
        $value      = (array) $in;
        $nested     = false;

        $this->where[] = compact('field', 'comparator', 'value', 'joiner', 'nested');

        return $this;
    }

    public function where_not_in($field, $not_in = [], $joiner = '')
    {
        $comparator = 'NOT IN';
        $value      = (array) $not_in;
        $nested     = false;

        $this->where[] = compact('field', 'comparator', 'value', 'joiner', 'nested');

        return $this;
    }

    public function where_between($field, $start, $end, $joiner = '')
    {
        $comparator = 'BETWEEN';
        $value      = [$start, $end];
        $nested     = false;

        $this->where[] = compact('field', 'comparator', 'value', 'joiner', 'nested');

        return $this;
    }

    public function where_not_between($field, $start, $end, $joiner = '')
    {
        $comparator = 'NOT BETWEEN';
        $value      = [$start, $end];
        $nested     = false;

        $this->where[] = compact('field', 'comparator', 'value', 'joiner', 'nested');

        return $this;
    }

    public function nest_where()
    {
        foreach (func_get_args() as $where) {

            // Initialise
            if (! isset($where[0])) {
                continue;
            } // Ignore them Losers
            if (! isset($where[1])) {
                $where[1] = ' = ';
            }
            if (! isset($where[2])) {
                $where[2] = '';
            }
            if (! isset($where[3])) {
                $where[3] = 'and';
            }

            $where[3] = strtoupper($where[3]);
            $where[4] = true;

            $this->where[] = array_combine(
                ['field', 'comparator', 'value', 'joiner', 'nested'],
                $where
            );
        }

        return $this;
    }

    public function group_by()
    {
        $this->groupby = array_flatten(func_get_args()) ?: [];

        return $this;
    }

    public function having($field, $comparator = '=', $value = '')
    {
        $joiner         = '';
        $this->having[] = compact('field', 'comparator', 'value', 'joiner');

        return $this;
    }

    public function or_having($field, $comparator = '=', $value = '')
    {
        $joiner         = 'OR';
        $this->having[] = compact('field', 'comparator', 'value', 'joiner');

        return $this;
    }

    public function and_having($field, $comparator = '=', $value = '')
    {
        $joiner         = 'AND';
        $this->having[] = compact('field', 'comparator', 'value', 'joiner');

        return $this;
    }

    public function order_by($field, $order = 'asc')
    {
        $this->orderby[] = compact('field', 'order');

        return $this;
    }

    public function limits($limit = null, $offset = null)
    {
        if ($limit !== null) {
            $this->limit = $limit;
        }
        if ($offset !== null) {
            $this->offset = $offset;
        }

        return $this;
    }

    public function get()
    {
        if (is_null($this->select)) {
            $this->select = array_flatten(func_get_args()) ?: ['*'];
        }

        return ahc()->database()->query($this->_sql(), $this->binds);
    }

    private function aggr($func, $field)
    {
        $sql = ($this->distinct === true ? "SELECT DISTINCT {$func}(" : "SELECT {$func}(")
             . $this->_field($field) . ') AS ' . $this->_field($func) . $this->_body()
            ;

        $result                          = ahc()->database()->query($sql, $this->binds);
        isset($result[$func]) or $result = reset($result);

        return isset($result[$func]) ? $result[$func] : 0;
    }

    public function raw_sql()
    {
        return $this->_sql(false);
    }

    private function _sql($bind = true)
    {
        return $this->_select() . $this->_body($bind);
    }

    private function _body($bind = true)
    {
        return $this->_from()
             . $this->_join()
             . $this->_conditions('where', $bind)
             . $this->_groupby()
             . $this->_conditions('having', $bind)
             . $this->_orderby()
             . $this->_limits()
             . ';';
    }

    private function _select()
    {
        return ($this->distinct === true ? 'SELECT DISTINCT ' : 'SELECT ')
                . implode(', ', array_map([$this, '_field'], $this->select));
    }

    private function _from()
    {
        return ' FROM ' . $this->_field($this->name) . ($this->alias ? ' ' . $this->_field($this->alias) : '');
    }

    private function _join()
    {
        $sql = '';
        if (! $this->join) {
            return $sql;
        }

        switch (ahc()->database()->driver()) {
            case 'mssql':
            case 'sqlserver':
                $format      = '[$1]';

break;
            default: $format = '`$1`';
        }

        foreach ($this->join as $join) {
            $sql .= ' ' . strtoupper($join['type']) . ' JOIN '
                 . implode(' ', array_map([$this, '_field'], explode(' ', $join['on']))) . ' ON '
                 . preg_replace('/(\w+)/', $format, $join['condition'])
                ;
        }

        return $sql;
    }

    private function _conditions($key = 'where', $bind = true)
    {
        $sql = '';
        if (! $this->{$key}) {
            return $sql;
        }
        $capped = true;
        foreach ($this->{$key} as $w => $clause) {
            $sql .= ($w > 0) ? $clause['joiner'] . ' ' : '';

            if ($key == 'where' and $capped and $clause['nested']) {
                $sql .= '( ';
                $capped = false;
            } elseif ($key == 'where' and ! $capped and ! $clause['nested']) {
                $sql .= ' )';
                $capped = true;
            }

            $sql .= $this->_field($clause['field']) . ' '
                 . (ctype_alpha($clause['comparator']) ? strtoupper($clause['comparator'])
                        : $clause['comparator']);

            if (is_array($clause['value'])) {
                if (str_ends($clause['comparator'], 'BETWEEN')) {
                    $sql .= ($bind !== false ? " ? AND ? "
                            : " {$clause['value'][0]} AND {$clause['value'][1]} ");
                } else {
                    $sql .= ($bind !== false ? " (" . trim(str_repeat("?, ", count($clause['value'])), ', ') . ")"
                            : " '" . implode(', ', $clause['value']) . "' ");
                }

                if ($bind !== false) {
                    foreach ($clause['value'] as $value) {
                        $this->binds[] = $value;
                    }
                }
            } else {
                $sql .= ($bind !== false ? ' ? ': " '" . $clause['value'] . "' ");
                if ($bind !== false) {
                    $this->binds[] = $clause['value'];
                }
            }
        }

        return (' ' . strtoupper($key) . ' ') . trim($sql) . (($key == 'where' and (! $capped and $clause['nested'])) ? ' )' : '');
    }

    private function _groupby()
    {
        return ($this->groupby) ? ' GROUP BY ' . implode(', ', array_map([$this, '_field'], $this->groupby)) : '';
    }

    private function _orderby()
    {
        $sql = '';
        if (is_null($this->orderby)) {
            return $sql;
        }

        foreach ($this->orderby as $order) {
            $sql .= $this->_field($order['field']) . ' ' . strtoupper($order['order']) . ', ';
        }

        return ' ORDER BY ' . trim($sql, ', ');
    }

    private function _limits()
    {
        return ($this->limit ? ' LIMIT ' . $this->limit : '') . ($this->offset ? ' OFFSET ' . $this->offset : '');
    }

    private function _field($field)
    {
        return ahc()->database()->wrap($field);
    }
}
