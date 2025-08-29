<?php

namespace Jeffrey\Educore\Models;

use Jeffrey\Educore\Core\Model;

class SchoolModel extends Model
{
    private $table = 'schools';

    /**
     * Finds a school by a unique column (e.g., phone_number).
     *
     * @param string $column The column name.
     * @param string $value The value to search for.
     * @return array|false The fetched school record or false if not found.
     */
    public function findBy(string $column, string $value): array|false
    {
        return $this->fetch($this->table, [$column => $value]);
    }

    /**
     * Creates a new school record.
     *
     * @param array $data The school data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        return $this->insert($this->table, $data);
    }
}