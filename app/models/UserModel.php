<?php

namespace Jeffrey\Educore\Models;

use Jeffrey\Educore\Core\Model;
use PDO; 

class UserModel extends Model
{
    private $table = 'users';

    /**
     * Finds a user record by a specific column.
     *
     * @param string $column The column name to search (e.g., 'phone_number').
     * @param string $value The value to match.
     * @return array|false The user record as an associative array, or false if not found.
     */
    public function findBy(string $column, string $value): array|false
    {
        return $this->fetch($this->table, [$column => $value]);
    }


    /**
     * Finds a user record by phone number and includes their assigned role.
     *
     * @param string $phoneNumber The user's phone number.
     * @return array|false The user record with role name, or false if not found.
     */
    public function findByPhoneNumberWithRole(string $phoneNumber): array|false
    {
        $sql = "SELECT 
                    u.*, r.name as role_name 
                FROM {$this->table} u
                JOIN user_roles ur ON u.id = ur.user_id
                JOIN roles r ON ur.role_id = r.id
                WHERE u.phone_number = :phone_number
                LIMIT 1";

        $stmt = $this->query($sql, ['phone_number' => $phoneNumber]);
        if ($stmt) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }



    /**
     * Creates a new user record.
     *
     * @param array $data Associative array of user data.
     * @return bool True on success, false on failure.
     */
    public function create(array $data): bool
    {
        return $this->insert($this->table, $data);
    }


    /**
     * Updates a user record.
     *
     * @param int $id The user's ID.
     * @param array $data The associative array of data to update.
     * @return bool True on success, false on failure.
     */
    public function updateById(int $id, array $data): bool
    {
        return parent::update($this->table, $id, $data);
    }

}