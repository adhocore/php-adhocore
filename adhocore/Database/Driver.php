<?php

namespace Adhocore\Database;

defined('COREPATH') or die('Error 403');

abstract class Driver
{
    protected $name;

    protected $wrapper;

    /**
     * @var \PDO
     */
    protected $connection;

    abstract public function connect();

    abstract public function list_fields($table);

    abstract public function list_tables();

    public function is_conected()
    {
        return (is_null($this->connection) === false);
    }

    public function query($sql, $bind="", $fetch_style = null)
    {
        $bind = (is_array($bind)) ? $bind : (empty($bind) ? [] : [$bind]);
        $sql  = trim($sql);

        try {
            $pdoStatement = $this->connection->prepare($sql);

            if ($pdoStatement->execute($bind) !== false) {
                ahc()->database()->queries([
                    'sql' 	 => $sql,
                    'bind' 	=> ($bind)?json_encode($bind):''
                ]);

                if (preg_match("/^(" . implode("|", ["select", "describe", "pragma", "show"]) . ") /i", $sql)) {
                    if (is_null($fetch_style)) {
                        $fetch_style = ahc()->database()->config('fetch_style', 2);
                    }

                    return $pdoStatement->fetchAll($fetch_style);
                }

                if (preg_match("/^(" . implode("|", ["delete", "insert", "update"]) . ") /i", $sql)) {
                    return $pdoStatement->rowCount();
                }
            }
        } catch (\PDOException $e) {
            ahc()->database()->errors([
                'sql' 	 => $sql,
                'bind' 	=> ($bind)?json_encode($bind):'',
                'error' => $e->getMessage()
            ]);

            throw $e;

            return false;
        }
    }

    public function wrapper()
    {
        return $this->wrapper;
    }

    public function insert_id()
    {
        return $this->connection->lastInsertId();
    }

    public function wrap($field)
    {
        if (is_array($field)) {
            return array_map([$this, 'wrap'], $field);
        }

        $field = trim($field);

        if (stripos($field, ' as ') !== false) {
            list($column, , $as_column) = explode(' ', $field, 3);

            return $this->wrap($column) . ' AS ' . $this->wrap($as_column);
        }

        if (stripos($field, '.') !== false) {
            list($alias, $column) = explode('.', $field);

            return $this->wrap($alias) . '.' . $this->wrap($column);
        }

        return ($field === '*') ? $field : sprintf($this->wrapper(), $field);
    }

    public function wrap_table($table)
    {
        return $this->wrap(ahc()->database()->config('table_prefix') . $table);
    }

    public function __toString()
    {
        return $this->name;
    }
}
