<?php

namespace App\Models;

use App\Libraries\MongoDB;
use MongoDB\BSON\ObjectId;

class UserModel
{
    protected MongoDB $mongodb;
    protected string $collection = 'users';
    protected array $allowedFields = [
        'username', 'email', 'password', 'full_name', 'contact_number', 'role',
        'is_active', 'last_login', 'last_activity', 'created_at', 'updated_at'
    ];

    public function __construct()
    {
        $this->mongodb = new MongoDB();
    }

    /**
     * Find a single document by ID or conditions
     */
    public function find($id = null)
    {
        if ($id !== null) {
            if (is_string($id) && strlen($id) === 24) {
                $id = new ObjectId($id);
            }
            $result = $this->mongodb->findOne($this->collection, ['_id' => $id]);
        } else {
            $result = $this->mongodb->findOne($this->collection, $this->whereConditions ?? []);
        }

        return $result ? $this->convertDocumentToArray($result) : null;
    }

    /**
     * Find all documents matching conditions
     */
    public function findAll(int $limit = 0, int $offset = 0)
    {
        $options = [];
        if ($limit > 0) {
            $options['limit'] = $limit;
        }
        if ($offset > 0) {
            $options['skip'] = $offset;
        }

        $cursor = $this->mongodb->find($this->collection, $this->whereConditions ?? [], $options);
        $results = [];

        foreach ($cursor as $document) {
            $results[] = $this->convertDocumentToArray($document);
        }

        return $results;
    }

    /**
     * Insert a new document
     */
    public function insert($data, bool $returnID = true)
    {
        $data = $this->filterAllowedFields($data);

        // Add timestamps if not present
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $data['is_active'] = $data['is_active'] ?? true;

        $result = $this->mongodb->insert($this->collection, $data);

        return $returnID ? (string) $result : ($result !== null);
    }

    /**
     * Update a document
     */
    public function update($id = null, $data = null)
    {
        if ($id !== null && $data !== null) {
            if (is_string($id) && strlen($id) === 24) {
                $id = new ObjectId($id);
            }

            $data = $this->filterAllowedFields($data);
            $data['updated_at'] = date('Y-m-d H:i:s');

            $result = $this->mongodb->updateOne(
                $this->collection,
                ['_id' => $id],
                ['$set' => $data]
            );

            return $result->getModifiedCount() > 0;
        }

        return false;
    }

    /**
     * Delete a document
     */
    public function delete($id = null, bool $purge = false)
    {
        if ($id !== null) {
            if (is_string($id) && strlen($id) === 24) {
                $id = new ObjectId($id);
            }

            if ($purge) {
                $result = $this->mongodb->deleteOne($this->collection, ['_id' => $id]);
                return $result->getDeletedCount() > 0;
            } else {
                // Soft delete - update is_active to false
                $result = $this->mongodb->updateOne(
                    $this->collection,
                    ['_id' => $id],
                    ['$set' => ['is_active' => false, 'updated_at' => date('Y-m-d H:i:s')]]
                );
                return $result->getModifiedCount() > 0;
            }
        }

        return false;
    }

    /**
     * Add WHERE condition
     */
    public function where($key, $value = null)
    {
        if (!isset($this->whereConditions)) {
            $this->whereConditions = [];
        }

        if (is_array($key)) {
            $this->whereConditions = array_merge($this->whereConditions, $key);
        } else {
            $this->whereConditions[$key] = $value;
        }

        return $this;
    }

    /**
     * Convert MongoDB document to array
     */
    private function convertDocumentToArray($document): array
    {
        $array = (array) $document;
        $array['id'] = (string) $array['_id'];
        unset($array['_id']);

        return $array;
    }

    /**
     * Filter data to only allowed fields
     */
    private function filterAllowedFields(array $data): array
    {
        return array_intersect_key($data, array_flip($this->allowedFields));
    }

    // Validation
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]',
        'email' => 'required|valid_email',
        'password' => 'required|min_length[6]', // Hash should be longer than 6 chars anyway
        'full_name' => 'required|min_length[2]|max_length[100]',
        'contact_number' => 'required|max_length[20]|regex_match[/^[0-9+\-\s]+$/]',
        'role' => 'required|in_list[admin,cashier]',
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
            'required' => 'Contact number is required',
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
        $this->where('username', $username);
        $this->where('is_active', true);
        $user = $this->find();

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

    /**
     * Count documents - alias for MongoDB
     */
    public function countAllResults(): int
    {
        return $this->mongodb->count($this->collection, $this->whereConditions ?? []);
    }

    /**
     * Alias for countAllResults
     */
    public function countAll(): int
    {
        return $this->countAllResults();
    }

    /**
     * Validate data using CodeIgniter validation service
     */
    public function validate($data): bool
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->validationRules, $this->validationMessages);

        // Run validation
        if (!$validation->run($data)) {
            return false;
        }

        // Check for uniqueness constraints
        if (!$this->checkUniqueness($data)) {
            return false;
        }

        return true;
    }

    /**
     * Get validation errors
     */
    public function errors(): array
    {
        $validation = \Config\Services::validation();
        return $validation->getErrors();
    }

    /**
     * Check uniqueness constraints (username and email)
     */
    private function checkUniqueness(array $data): bool
    {
        $validation = \Config\Services::validation();

        // Check username uniqueness
        if (isset($data['username'])) {
            $existingUser = $this->where('username', $data['username'])->find();
            if ($existingUser) {
                $validation->setError('username', 'Username already exists');
                return false;
            }
        }

        // Check email uniqueness
        if (isset($data['email'])) {
            $existingUser = $this->where('email', $data['email'])->find();
            if ($existingUser) {
                $validation->setError('email', 'Email already exists');
                return false;
            }
        }

        return true;
    }
}
