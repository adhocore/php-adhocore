<?php

namespace Adhocore;

defined('COREPATH') or die('Error 403');

class Database
{
    private $config;

    private $drivers = [];

    private $driver;

    private $connection;

    private $insert_id;

    private $wrapper;

    private $schema;

    /**
     * @var Adhocore\Database\Table
     */
    private $tables = [];

    public $queries = [];

    public $errors = [];

    private $cache = [
        'fields' => [],
        'tables' => [],
    ];

    public function __construct($config = null)
    {
        $this->config = is_array($config) ? $config : ahc()->config->item('database');
    }

    public function __call($method, $params)
    {
        return call_user_func_array([$this->driver(), $method], $params);
    }

    public function config($item = null, $default = null)
    {
        if (is_null($item)) {
            return $this->config;
        }

        return array_pick($this->config, $item, $default);
    }

    /**
     * @return Adhocore\Database\Driver
     */
    public function driver()
    {
        if (is_null($this->driver)) {
            switch ($this->config('driver')) {
                case 'mysql':
                    $this->driver = new Database\Driver\Mysql();

                    break;

                default:
                    throw new \Exception('Unsupported Database Driver: ' . $driver);
            }
        }

        if ($this->driver->is_conected() === false) {
            $this->driver->connect();
        }

        return $this->driver;
    }

    /**
     * @return Adhocore\Database\Schema
     */
    public function schema()
    {
        if (is_null($this->schema)) {
            switch ($driver = ahc()->database->driver('mysql')) {
                case 'mysql':
                    $this->schema = new Database\Schema\Mysql();

                    break;

                default:
                    throw new \Exception('Unsupported Database Driver: ' . $driver);

            }
        }

        return $this->schema;
    }

    /**
     * @return \PDO
     */
    public function connection()
    {
        return $this->driver()->connect();
    }

    public function version()
    {
        return $this->driver()->connection->getAttribute(\PDO::ATTR_CLIENT_VERSION);
    }

    public function queries($query = [])
    {
        if (isset($query['sql'])) {
            $this->queries[] = $query;
        } else {
            return $this->queries;
        }
    }

    public function errors($error = [])
    {
        if (isset($error['sql'])) {
            $this->errors[] = $error;
        } else {
            return $this->errors;
        }
    }

    private function filter_fields($table, $data)
    {
        return array_values(array_intersect($this->driver()->list_fields($table), array_keys($data)));
    }

    /**
     * Performs Raw SQL Query
     *
     * @param string $sql
     * @param array|string $bind Array for multiple binds, or a string for single
     * @return Array of results for select types or Number of rows affected by the query for other types
     */
    public function query($sql, $bind = "")
    {
        return $this->driver()->query($sql, $bind);
    }

    /**
     * Performs Selection of columns $fields on table $table
     *
     * @param string $table
     * @param string $fields
     * @param string $where The where Clause with placeholder ?
     * @param array|string $bind
     */
    public function select($table, $fields = "*", $where = "", $bind = "")
    {
        $sql = "SELECT {$fields} FROM {$this->wrap_table($table)}"
             . (empty($where) ? ';' : " WHERE {$where};");

        return $this->driver()->query($sql, $bind);
    }

    public function insert($table, $data)
    {
        ksort($data);
        $fields = $this->filter_fields($table, $data);
        asort($fields);

        $sql = "INSERT INTO {$this->wrap($table)} (" . implode(array_map([$this, 'wrap'], $fields), ", ") . ") "
                . "VALUES (" . trim(str_repeat("?, ", count($fields)), ', ') . ");";

        return $this->driver()->query($sql, array_flatten($data));
    }

    public function insert_batch($table, $datas)
    {
        // Sort order of each datas
        foreach ($datas as &$data) {
            ksort($data);
        }

        $fields = $this->filter_fields($table, (array) reset($datas));
        asort($fields);
        $count  = count($fields);

        $sql = "INSERT INTO {$this->wrap_table($table)} (" . implode(array_map([$this, 'wrap'], $fields), ", ") . ") VALUES "
                . str_repeat("(" . trim(str_repeat("?, ", $count), ', ') . "), ", count($datas));

        return $this->driver()->query(trim($sql, ', ') . ';', array_flatten($datas));
    }

    public function update($table, $data, $where, $bind = "")
    {
        ksort($data);
        $fields = $this->filter_fields($table, $data);
        $bind   = (is_array($bind)) ? array_flatten($bind) : (empty($bind) ? [] : [$bind]);

        $sql = "UPDATE {$this->wrap_table($table)} SET "
             . implode('= ? , ', array_map([$this, 'wrap'], $fields)) . ' = ? '
             . "WHERE {$this->wrap_where($where)};";

        return $this->driver()->query($sql, array_merge(array_flatten($data), $bind));
    }

    public function delete($table, $where, $bind="")
    {
        $sql = "DELETE FROM {$this->wrap_table($table)} WHERE {$this->wrap_where($where)};";
        $this->driver()->query($sql, $bind);
    }

    public function insert_id()
    {
        return $this->driver()->insert_id();
    }

    /**
     * @param string $table The name of Table to start fluent query
     * @param string $alias The alias to be used for table in Query
     *
     * @return Adhocore\Database\Table
     */
    public function table($table, $alias = null)
    {
        $table = $this->config('table_prefix') . $table;

        if (empty($this->cache['tables'])) {
            $this->cache['tables'] = $this->driver()->list_tables();
        }

        if (! in_array($table, $this->cache['tables'])) {
            throw new \Exception("Table `{$table}` doesnot exist in Database: `{$this->database()}`.");
        }

        if (! isset($this->tables[$table])) {
            $this->tables[$table] = new Database\Table($table);
        }

        return $this->tables[$table]->reset()->alias($alias);
    }

    public function wrap_where($where)
    {
        return $where;

        $protect = ['where', 'or', 'and', ];
        echo_(preg_split('/(' . implode('|', $protect) . ')/s', $where, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));

        return implode(' ', array_map(
            function ($string) use ($protect) {
                if (in_array(strtolower($string), $protect)) {
                    return strtoupper($string);
                }
                $string = explode(' ', trim($string), 2);

                return ahc()->database->wrap($string[0]) . (isset($string[1]) ? ' ' . $string[1] : '');
            },
            preg_split('/(' . implode('|', $protect) . ')/is', $where, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)
        ));
    }
}
