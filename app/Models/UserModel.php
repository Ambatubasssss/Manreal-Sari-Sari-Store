<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'username', 'email', 'password', 'full_name', 'contact_number', 'role',
        'is_active', 'last_login', 'last_activity', 'created_at', 'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]',
        'email' => 'required|valid_email',
        'password' => 'permit_empty|min_length[6]', // Allow empty for profile updates
        'full_name' => 'required|min_length[2]|max_length[100]',
        'contact_number' => 'permit_empty|max_length[20]|regex_match[/^[0-9+\-\s]+$/]',
        'role' => 'permit_empty|in_list[admin,cashier]', // Allow empty for profile updates
    ];

    protected $validationMessages = [
        'username' => [
            'required' => 'Username is required',
            'min_length' => 'Username must be at least 3 characters long',
            'max_length' => 'Username cannot exceed 50 characters',
            'is_unique' => 'Username already exists',
        ],
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please enter a valid email address',
            'is_unique' => 'Email already exists',
        ],
        'password' => [
            'required' => 'Password is required',
            'min_length' => 'Password must be at least 6 characters long',
        ],
        'full_name' => [
            'required' => 'Full name is required',
            'min_length' => 'Full name must be at least 2 characters long',
            'max_length' => 'Full name cannot exceed 100 characters',
        ],
        'contact_number' => [
            'max_length' => 'Contact number cannot exceed 20 characters',
            'regex_match' => 'Contact number can only contain numbers, spaces, dashes, and plus signs',
        ],
        'role' => [
            'required' => 'Role is required',
            'in_list' => 'Invalid role selected',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    /**
     * Authenticate user login
     */
    public function authenticate($username, $password)
    {
        $user = $this->where('username', $username)
                    ->where('is_active', true)
                    ->first();

        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
            return $user;
        }

        return false;
    }

    /**
     * Get users by role
     */
    public function getByRole($role)
    {
        return $this->where('role', $role)
                   ->where('is_active', true)
                   ->findAll();
    }

    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        return $this->where('is_active', true)->findAll();
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'admin';
    }

    /**
     * Check if user has cashier role
     */
    public function isCashier($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'cashier';
    }
}
