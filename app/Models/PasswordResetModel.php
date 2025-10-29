<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['email', 'token', 'created_at'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';
    protected $deletedField = '';

    /**
     * Create a new password reset token
     */
    public function createToken($email)
    {
        // Delete any existing tokens for this email
        $this->where('email', $email)->delete();

        // Generate a secure token
        $token = bin2hex(random_bytes(32));

        // Insert new token
        $data = [
            'email' => $email,
            'token' => password_hash($token, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->insert($data);

        return $token; // Return plain token to send via email
    }

    /**
     * Verify if token is valid and not expired
     */
    public function verifyToken($email, $token)
    {
        $reset = $this->where('email', $email)
                      ->orderBy('created_at', 'DESC')
                      ->first();

        if (!$reset) {
            return false;
        }

        // Check if token is expired (1 hour expiration)
        $createdAt = strtotime($reset['created_at']);
        $now = time();
        $expirationTime = 3600; // 1 hour in seconds

        if (($now - $createdAt) > $expirationTime) {
            // Token expired, delete it
            $this->where('email', $email)->delete();
            return false;
        }

        // Verify token
        if (password_verify($token, $reset['token'])) {
            return true;
        }

        return false;
    }

    /**
     * Delete token after password reset
     */
    public function deleteToken($email)
    {
        return $this->where('email', $email)->delete();
    }

    /**
     * Clean up expired tokens (older than 1 hour)
     */
    public function cleanExpiredTokens()
    {
        $expirationTime = date('Y-m-d H:i:s', time() - 3600);
        return $this->where('created_at <', $expirationTime)->delete();
    }
}

