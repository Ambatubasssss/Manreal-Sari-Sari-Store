<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = \Config\Services::session();
    }

    /**
     * Show login form
     */
    public function index()
    {
        // Check if user is already logged in
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    /**
     * Handle login form submission
     */
    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Validate input
        if (empty($username) || empty($password)) {
            $this->session->setFlashdata('error', 'Username and password are required');
            return redirect()->back()->withInput();
        }

        // Authenticate user
        $user = $this->userModel->authenticate($username, $password);

        if ($user) {
            // Set session data
            $this->session->set([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'is_logged_in' => true
            ]);

            // Redirect based on role
            if ($user['role'] === 'admin') {
                return redirect()->to('/dashboard');
            } else {
                return redirect()->to('/pos');
            }
        } else {
            $this->session->setFlashdata('error', 'Invalid username or password');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        // Clear session
        $this->session->destroy();
        
        // Redirect to login
        return redirect()->to('/auth')->with('message', 'You have been logged out successfully');
    }

    /**
     * Show change password form
     */
    public function changePassword()
    {
        // Check if user is logged in
        if (!$this->session->get('user_id')) {
            return redirect()->to('/auth');
        }

        return view('auth/change_password');
    }

    /**
     * Handle change password form submission
     */
    public function updatePassword()
    {
        // Check if user is logged in
        if (!$this->session->get('user_id')) {
            return redirect()->to('/auth');
        }

        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->session->setFlashdata('error', 'All fields are required');
            return redirect()->back()->withInput();
        }

        if ($newPassword !== $confirmPassword) {
            $this->session->setFlashdata('error', 'New passwords do not match');
            return redirect()->back()->withInput();
        }

        if (strlen($newPassword) < 6) {
            $this->session->setFlashdata('error', 'New password must be at least 6 characters long');
            return redirect()->back()->withInput();
        }

        // Verify current password
        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        if (!password_verify($currentPassword, $user['password'])) {
            $this->session->setFlashdata('error', 'Current password is incorrect');
            return redirect()->back()->withInput();
        }

        // Update password
        $this->userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        $this->session->setFlashdata('success', 'Password updated successfully');
        return redirect()->to('/dashboard');
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated()
    {
        $session = \Config\Services::session();
        return $session->get('is_logged_in') === true;
    }

    /**
     * Check if user has admin role
     */
    public static function isAdmin()
    {
        $session = \Config\Services::session();
        return $session->get('role') === 'admin';
    }

    /**
     * Check if user has cashier role
     */
    public static function isCashier()
    {
        $session = \Config\Services::session();
        return $session->get('role') === 'cashier';
    }

    /**
     * Require authentication for protected routes
     */
    protected function requireAuth()
    {
        if (!self::isAuthenticated()) {
            return redirect()->to('/auth');
        }
    }

    /**
     * Require admin role for protected routes
     */
    public function requireAdmin()
    {
        $this->requireAuth();   // ✅ use $this instead of self
        
        if (!$this->isAdmin()) {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Admin privileges required.');
        }
    }
}
