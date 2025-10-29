<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PasswordResetModel;
use App\Libraries\SmsService;
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
     * Show register form
     */
    public function register()
    {
        // Check if user is already logged in
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/register');
    }

    /**
     * Handle register form submission
     */
    public function processRegistration()
    {
        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'contact_number' => $this->request->getPost('contact_number'),
            'password' => $this->request->getPost('password'),
            'confirm_password' => $this->request->getPost('confirm_password'),
            'otp' => $this->request->getPost('otp'),
            'role' => 'cashier', // Default role
        ];

        // Validate input
        if (empty($data['full_name']) || empty($data['username']) || empty($data['email']) || empty($data['contact_number']) || empty($data['password'])) {
            $this->session->setFlashdata('error', 'All fields are required, including contact number for SMS verification');
            return redirect()->back()->withInput();
        }

        if ($data['password'] !== $data['confirm_password']) {
            $this->session->setFlashdata('error', 'Passwords do not match');
            return redirect()->back()->withInput();
        }

        // Check if verification step
        $expectedOTP = $this->session->get('registration_otp');
        if ($expectedOTP !== null) {
            // Verify OTP
            if (empty($data['otp']) || $data['otp'] != $expectedOTP) {
                $this->session->setFlashdata('error', 'Invalid OTP verification code');
                return redirect()->back()->withInput();
            }
        }

        // Prepare data for insertion
        $insertData = [
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
            'is_active' => true,
        ];

        // Validate using model
        if (!$this->userModel->validate($insertData)) {
            // Get validation errors
            $errors = $this->userModel->errors();
            $errorMessage = implode('<br>', $errors);
            $this->session->setFlashdata('error', $errorMessage);
            return redirect()->back()->withInput();
        }

        if ($expectedOTP !== null) {
            // Insert user after OTP verification
            if ($this->userModel->insert($insertData)) {
                // Clear session
                $this->session->remove('registration_data');
                $this->session->remove('registration_otp');
                $this->session->setFlashdata('success', 'Registration successful! Please login.');
                return redirect()->to('/auth');
            } else {
                $this->session->setFlashdata('error', 'Registration failed. Please try again.');
                return redirect()->back()->withInput();
            }
        } else {
            // Clear any existing registration session data before starting new registration
            $this->session->remove('registration_data');
            $this->session->remove('registration_otp');

            // Generate NEW OTP and send SMS since contact is required
            $otp = mt_rand(100000, 999999);
            log_message('info', 'Generated NEW OTP: ' . $otp . ' for phone: ' . $data['contact_number']);

            // Send actual SMS OTP
            $smsService = new SmsService();
            $smsSent = $smsService->sendOTP($data['contact_number'], $otp);

            log_message('info', 'Registration attempt: SMS sent result = ' . ($smsSent ? 'SUCCESS' : 'FAILED') . ' for phone ' . $data['contact_number']);

            if ($smsSent) {
                $this->session->set('registration_data', $insertData);
                $this->session->set('registration_otp', $otp);

                // Only show SMS sent message - OTP must come from SMS
                $this->session->setFlashdata('success', 'Verification code sent to your contact number. Please check your SMS and enter the 6-digit code below to complete registration.');
            } else {
                log_message('error', 'SMS sending failed for ' . $data['contact_number']);
                $this->session->setFlashdata('error', 'SMS service unavailable. Your account registration is pending API verification. Please contact support or try again later.');
                return redirect()->back()->withInput();
            }

            return redirect()->back()->withInput();
        }
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

    /**
     * Show forgot password form
     */
    public function forgotPassword()
    {
        // Check if user is already logged in
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/forgot_password');
    }

    /**
     * Handle forgot password form submission
     */
    public function processForgotPassword()
    {
        $email = $this->request->getPost('email');

        // Validate input
        if (empty($email)) {
            $this->session->setFlashdata('error', 'Email address is required');
            return redirect()->back()->withInput();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->setFlashdata('error', 'Please enter a valid email address');
            return redirect()->back()->withInput();
        }

        // Check if email exists
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            // Don't reveal if email exists or not for security
            $this->session->setFlashdata('success', 'If your email is registered, you will receive a password reset link shortly.');
            return redirect()->back();
        }

        // Check if user is active
        if (!$user['is_active']) {
            $this->session->setFlashdata('error', 'Your account is inactive. Please contact support.');
            return redirect()->back();
        }

        // Generate reset token
        $passwordResetModel = new PasswordResetModel();
        $token = $passwordResetModel->createToken($email);

        // Send reset email
        $resetLink = base_url("auth/reset-password?token={$token}&email=" . urlencode($email));
        
        $emailService = \Config\Services::email();
        $emailService->setTo($email);
        $emailService->setSubject('Password Reset Request - Manrealstore');
        
        $message = view('emails/password_reset', [
            'user' => $user,
            'reset_link' => $resetLink,
        ]);
        
        $emailService->setMessage($message);

        if ($emailService->send()) {
            log_message('info', 'Password reset email sent to: ' . $email);
            $this->session->setFlashdata('success', 'Password reset link has been sent to your email address. Please check your inbox.');
        } else {
            log_message('error', 'Failed to send password reset email to: ' . $email);
            log_message('error', 'Email error: ' . $emailService->printDebugger(['headers']));
            $this->session->setFlashdata('error', 'Failed to send reset email. Please try again later or contact support.');
        }

        return redirect()->back();
    }

    /**
     * Show reset password form
     */
    public function resetPassword()
    {
        // Check if user is already logged in
        if ($this->session->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        $token = $this->request->getGet('token');
        $email = $this->request->getGet('email');

        if (empty($token) || empty($email)) {
            $this->session->setFlashdata('error', 'Invalid password reset link');
            return redirect()->to('/auth');
        }

        // Verify token
        $passwordResetModel = new PasswordResetModel();
        if (!$passwordResetModel->verifyToken($email, $token)) {
            $this->session->setFlashdata('error', 'Invalid or expired password reset link. Please request a new one.');
            return redirect()->to('/auth/forgot-password');
        }

        return view('auth/reset_password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Handle reset password form submission
     */
    public function processResetPassword()
    {
        $token = $this->request->getPost('token');
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Validate input
        if (empty($token) || empty($email) || empty($password) || empty($confirmPassword)) {
            $this->session->setFlashdata('error', 'All fields are required');
            return redirect()->back()->withInput();
        }

        if ($password !== $confirmPassword) {
            $this->session->setFlashdata('error', 'Passwords do not match');
            return redirect()->back()->withInput();
        }

        if (strlen($password) < 6) {
            $this->session->setFlashdata('error', 'Password must be at least 6 characters long');
            return redirect()->back()->withInput();
        }

        // Verify token again
        $passwordResetModel = new PasswordResetModel();
        if (!$passwordResetModel->verifyToken($email, $token)) {
            $this->session->setFlashdata('error', 'Invalid or expired password reset link. Please request a new one.');
            return redirect()->to('/auth/forgot-password');
        }

        // Update password
        $user = $this->userModel->where('email', $email)->first();
        if (!$user) {
            $this->session->setFlashdata('error', 'User not found');
            return redirect()->to('/auth/forgot-password');
        }

        $this->userModel->update($user['id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        // Delete reset token
        $passwordResetModel->deleteToken($email);

        $this->session->setFlashdata('success', 'Password reset successful! You can now login with your new password.');
        return redirect()->to('/auth');
    }
}
