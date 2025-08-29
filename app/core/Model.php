<?php

namespace Jeffrey\Educore\Core;

use PDO;
use PDOException;
use PDOStatement;

abstract class Model
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Executes a raw SQL query with optional parameters.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params An array of parameters for the prepared statement.
     * @return PDOStatement|false The PDOStatement object on success, false on failure.
     */
    protected function query(string $sql, array $params = []): PDOStatement|false
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            AppLogger::getInstance()->getLogger()->error("Query failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserts a record into the specified table.
     *
     * @param string $table The table name.
     * @param array $data Associative array of column names and values.
     * @return bool True on success, false on failure.
     */
    protected function insert(string $table, array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            AppLogger::getInstance()->getLogger()->error("Insert failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches a single record based on a condition.
     *
     * @param string $table The table name.
     * @param array $conditions Associative array of column names and values for the WHERE clause.
     * @return array|false The fetched record or false if not found.
     */
    protected function fetch(string $table, array $conditions): array|false
    {
        $whereClause = '';
        foreach ($conditions as $key => $value) {
            $whereClause .= "{$key} = :{$key} AND ";
        }
        $whereClause = rtrim($whereClause, ' AND ');

        $sql = "SELECT * FROM {$table} WHERE {$whereClause} LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            AppLogger::getInstance()->getLogger()->error("Fetch failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetches all records from a table that match a condition.
     *
     * @param string $table The table name.
     * @param array $conditions Associative array of column names and values for the WHERE clause.
     * @return array|false The fetched records or false on failure.
     */
    protected function fetchAll(string $table, array $conditions = []): array|false
    {
        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = ' WHERE ';
            foreach ($conditions as $key => $value) {
                $whereClause .= "{$key} = :{$key} AND ";
            }
            $whereClause = rtrim($whereClause, ' AND ');
        }

        $sql = "SELECT * FROM {$table} {$whereClause}";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($conditions);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            AppLogger::getInstance()->getLogger()->error("FetchAll failed: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Deletes records from a table based on conditions.
     *
     * @param string $table The table name.
     * @param array $conditions Associative array of column names and values for the WHERE clause.
     * @return bool True on success, false on failure.
     */
    protected function delete(string $table, array $conditions): bool
    {
        $whereClause = '';
        foreach ($conditions as $key => $value) {
            $whereClause .= "{$key} = :{$key} AND ";
        }
        $whereClause = rtrim($whereClause, ' AND ');

        $sql = "DELETE FROM {$table} WHERE {$whereClause}";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($conditions);
        } catch (PDOException $e) {
            AppLogger::getInstance()->getLogger()->error("Delete failed: " . $e->getMessage());
            return false;
        }
    }

}