<?php

namespace phpMyFAQ\Database;

use PDO;
use PDOStatement;
use phpMyFAQ\Exception;

class PdoMysql implements DatabaseDriver
{
    /** @var array */
    public $tableNames = [];

    /** @var PDO */
    private $conn = false;

    /** @var string */
    private $sqllog = '';

    /**
     * Creates a PDO instance representing a connection to a database.
     *
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     * @param int    $port
     * @return bool|void
     */
    public function connect($host, $user, $password, $database = '', $port = 3306)
    {
        $this->conn = new PDO('mysql:host=' . $host . ';dbname=' . $database, $user, $password);
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object.
     *
     * @param string $query
     * @param int    $offset
     * @param int    $rowcount
     * @return false|mixed|PDOStatement
     */
    public function query($query, $offset = 0, $rowcount = 0)
    {
        try {
            return $this->conn->query($query);
        } catch (\PDOException $exception) {
            echo $exception->getMessage();
        }
    }

    /**
     * Quotes a string for use in a query
     *
     * @param string
     * @return string
     */
    public function escape($string)
    {
        return $this->conn->quote($string);
    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param mixed $result
     * @return mixed
     * @throws Exception
     */
    public function fetchObject($result)
    {
        if ($result instanceof PDOStatement) {
            return $result->fetchObject();
        }

        throw new Exception($this->error());
    }

    /**
     * @param mixed $result
     * @return array|void
     */
    public function fetchArray($result)
    {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param $result
     * @return false|mixed
     */
    public function fetchRow($result)
    {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param resource $result
     * @return array
     * @throws Exception
     */
    public function fetchAll($result)
    {
        $ret = [];
        if (false === $result) {
            throw new Exception('Error while fetching result: ' . $this->error());
        }

        while ($row = $this->fetchObject($result)) {
            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * Returns the number of rows affected by the last SQL statement.
     *
     * @param mixed $result
     * @return int
     */
    public function numRows($result)
    {
        return !is_null($result) ? $result->rowCount() : 0;
    }

    /**
     * Logs the queries.
     *
     * @return string
     */
    public function log()
    {
        return $this->sqllog;
    }

    /**
     * This function returns the table status.
     *
     * @param string $prefix Table prefix
     *
     * @return array
     */
    public function getTableStatus($prefix = '')
    {
        $status = [];
        foreach ($this->getTableNames($prefix) as $table) {
            $status[$table] = $this->getOne('SELECT count(*) FROM ' . $table);
        }

        return $status;
    }

    /**
     * This function is a replacement for MySQL's auto-increment so that
     * we don't need it anymore.
     *
     * @param string $table The name of the table
     * @param string $id    The name of the ID column
     *
     * @return int
     */
    public function nextId($table, $id)
    {
        $select = sprintf(
            '
           SELECT
               MAX(%s) AS current_id
           FROM
               %s',
            $id,
            $table
        );

        $result = $this->query($select);

        if ($result instanceof PDOStatement) {
            $current = $result->fetch(PDO::FETCH_ASSOC);
        } else {
            $current['current_id'] = 0;
        }

        return $current['current_id'] + 1;
    }

    public function error()
    {
        // TODO: Implement error() method.
    }

    public function clientVersion()
    {
        // TODO: Implement clientVersion() method.
    }

    public function serverVersion()
    {
        // TODO: Implement serverVersion() method.
    }

    public function getTableNames($prefix = '')
    {
        // TODO: Implement getTableNames() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function now()
    {
        // TODO: Implement now() method.
    }

    /**
     * Returns just one row.
     *
     * @param string $query
     *
     * @return string
     */
    private function getOne($query)
    {
        $row = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);

        return $row[0];
    }
}
