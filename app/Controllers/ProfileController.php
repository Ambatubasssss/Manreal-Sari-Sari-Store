<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfileController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    /**
     * Show user profile page
     */
    public function index()
    {
        // Check if user is logged in
        if (!$this->session->get('user_id')) {
            return redirect()->to('/auth');
        }

        // Get current user data
        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            $this->session->setFlashdata('error', 'User not found');
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'My Profile',
            'profile_data' => $user,
        ];

        return $this->renderView('profile/index', $data);
    }

    /**
     * Update user profile
     */
    public function update()
    {
        // Check if user is logged in
        if (!$this->session->get('user_id')) {
            return redirect()->to('/auth');
        }

        $userId = $this->session->get('user_id');
        $user = $this->userModel->find($userId);

        if (!$user) {
            $this->session->setFlashdata('error', 'User not found');
            return redirect()->to('/profile');
        }

        // Get form data
        $fullName = $this->request->getPost('full_name');
        $username = $this->request->getPost('username');
        $email = $this->request->getPost('email');
        $contactNumber = $this->request->getPost('contact_number') ?? '';

        // Validate input
        if (empty($fullName) || empty($username) || empty($email)) {
            $this->session->setFlashdata('error', 'Full name, username, and email are required');
            return redirect()->back()->withInput();
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->setFlashdata('error', 'Please enter a valid email address');
            return redirect()->back()->withInput();
        }

        // Check for uniqueness only if values have changed
        if ($username !== $user['username']) {
            if (!$this->userModel->isUnique('username', $username, $userId)) {
                $this->session->setFlashdata('error', 'Username is already taken by another user');
                return redirect()->back()->withInput();
            }
        }

        if ($email !== $user['email']) {
            if (!$this->userModel->isUnique('email', $email, $userId)) {
                $this->session->setFlashdata('error', 'Email is already registered to another user');
                return redirect()->back()->withInput();
            }
        }

        // Prepare data for update (only include fields that exist in database)
        $updateData = [
            'full_name' => $fullName,
            'username' => $username,
            'email' => $email,
        ];
        
        // Only add contact_number if the field exists in the user data
        if (isset($user['contact_number']) || array_key_exists('contact_number', $user)) {
            $updateData['contact_number'] = $contactNumber;
        }

        // Update user
        if ($this->userModel->update($userId, $updateData)) {
            // Update session data
            $this->session->set([
                'username' => $username,
                'full_name' => $fullName,
            ]);

            $this->session->setFlashdata('success', 'Profile updated successfully!');
        } else {
            // Get validation errors if any
            $errors = $this->userModel->errors();
            if (!empty($errors)) {
                $errorMessage = implode('<br>', $errors);
                $this->session->setFlashdata('error', $errorMessage);
            } else {
                $this->session->setFlashdata('error', 'Failed to update profile. Please try again.');
            }
        }

        return redirect()->to('/profile');
    }
}

