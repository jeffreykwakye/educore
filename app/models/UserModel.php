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
     * Finds a user record by phone number.
     *
     * @param string $phoneNumber The user's phone number.
     * @return array|false The user record, or false if not found.
     */
    public function findByPhoneNumber(string $phoneNumber): array|false
    {
        return $this->fetch($this->table, ['phone_number' => $phoneNumber]);
    }


    /**
     * Gets all roles for a given user ID.
     *
     * @param int $userId The ID of the user.
     * @return array An array of role names.
     */
    public function getAllRolesForUser(int $userId): array
    {
        $sql = "SELECT r.name 
                FROM user_roles ur
                JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id";

        $stmt = $this->query($sql, ['user_id' => $userId]);
        $roles = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        
        return array_column($roles, 'name');
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
     * Updates a user record by its ID.
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