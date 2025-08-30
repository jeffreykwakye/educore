<?php

namespace Jeffrey\Educore\Models;

use Jeffrey\Educore\Core\Model;

class RoleModel extends Model
{
    protected string $table = 'roles';

    
    public function __construct()
    {
        parent::__construct();
        $this->table = 'roles';
    }

    
    /**
     * Finds a role by its name.
     *
     * @param string $name The name of the role.
     * @return array|false The role record as an associative array, or false if not found.
     */
    public function findByName(string $name): array|false
    {
        return $this->fetch($this->table, ['name' => $name]);
    }
    
    /**
     * Gets all roles from the database.
     *
     * @return array An array of all role records.
     */
    public function getAllRoles(): array
    {
        return $this->fetchAll($this->table);
    }
}