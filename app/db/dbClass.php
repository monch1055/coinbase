<?php

namespace db;

use mysqli;

class dbClass
{
    const CONNECTION_VARS = [
        'host' => 'localhost',
        'user' => 'root',
        'password' => '',
        'database' => 'crypto',
        'port' => 3306,
    ];

    const TABLES = [
        'transactions' => 'transactions',
        'users'  => 'users',
    ];

    /**
     * @var mysqli $connection
     */
    protected mysqli $connection;

    /**
     * @var object $query
     */
    protected object $query;

    /**
     * @var bool $show_errors
     */
    protected bool $show_errors = TRUE;

    /**
     * @var bool $query_closed
     */
    protected bool $query_closed = TRUE;

    /**
     * @var int $query_count
     */
    public int $query_count = 0;

    /**
     * My Database Class Constructor
     *
     * @param string $charset
     */
    public function __construct(string $charset = 'utf8mb4') {
        $this->connection = new mysqli(
            self::CONNECTION_VARS['host'],
            self::CONNECTION_VARS['user'],
            self::CONNECTION_VARS['password'],
            self::CONNECTION_VARS['database'],
            self::CONNECTION_VARS['port'],
        );

        if ($this->connection->connect_error) {
            $this->error('Failed to connect to MySQL - ' . $this->connection->connect_error);
        }

        $this->connection->set_charset($charset);
    }

    /**
     * Database Error
     *
     * @param $error
     * @return void
     */
    public function error($error): void
    {
        if ($this->show_errors) {
            exit($error);
        }
    }

    /**
     * Return variable data type
     *
     * @param $var
     * @return string
     */
    private function _gettype($var): string
    {
        if (is_string($var)) {
            return 's';
        }

        if (is_float($var)) {
            return 'd';
        }

        if (is_int($var)) {
            return 'i';
        }

        return 'b';
    }

    /**
     * Database Query Method
     *
     * @param $query
     * @return $this
     */
    public function query($query): object
    {
        if (! $this->query_closed) {
            $this->query->close();
        }

        if ($this->query = $this->connection->prepare($query)) {
            if (func_num_args() > 1) {
                $x = func_get_args();
                $args = array_slice($x, 1);
                $types = '';
                $args_ref = [];

                foreach ($args as $k => &$arg) {
                    if (is_array($args[$k])) {
                        foreach ($args[$k] as $j => &$a) {
                            $types .= $this->_gettype($args[$k][$j]);
                            $args_ref[] = &$a;
                        }
                    } else {
                        $types .= $this->_gettype($args[$k]);
                        $args_ref[] = &$arg;
                    }
                }

                array_unshift($args_ref, $types);
                call_user_func_array(array($this->query, 'bind_param'), $args_ref);
            }

            $this->query->execute();

            if ($this->query->errno) {
                $this->error('Unable to process MySQL query (check your params) - ' . $this->query->error);
            }

            $this->query_closed = FALSE;
            $this->query_count++;
        } else {
            $this->error('Unable to prepare MySQL statement (check your syntax) - ' . $this->connection->error);
        }

        return $this;
    }

    /**
     * Fetch All Result
     *
     * @param $callback
     * @return array|null
     */
    public function fetchAll($callback = null): ?array {
        $params = [];
        $row    = [];

        $meta = $this->query->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }
        call_user_func_array(array($this->query, 'bind_result'), $params);

        $result = [];
        while ($this->query->fetch()) {
            $r = [];
            foreach ($row as $key => $val) {
                $r[$key] = $val;
            }
            if ($callback != null && is_callable($callback)) {
                $value = call_user_func($callback, $r);
                if ($value == 'break')
                    break;
            } else {
                $result[] = $r;
            }
        }

        $this->query->close();
        $this->query_closed = TRUE;

        return $result;
    }

    /**
     * Fetch Array Result
     *
     * @return array|null
     */
    public function fetchArray(): ?array {
        $params = [];
        $row    = [];

        $meta = $this->query->result_metadata();
        while ($field = $meta->fetch_field()) {
            $params[] = &$row[$field->name];
        }
        call_user_func_array(array($this->query, 'bind_result'), $params);
        $result = array();
        while ($this->query->fetch()) {
            foreach ($row as $key => $val) {
                $result[$key] = $val;
            }
        }
        $this->query->close();
        $this->query_closed = TRUE;

        return $result;
    }

    public function close() {
        return $this->connection->close();
    }

    public function numRows() {
        $this->query->store_result();
        return $this->query->num_rows;
    }

    public function affectedRows() {
        return $this->query->affected_rows;
    }

    public function lastInsertID() {
        return $this->connection->insert_id;
    }
}