<?php

namespace App\Controllers;

use App\Models\MessageModel;
use App\Models\UserModel;

class ChatController extends BaseController
{
    protected $messageModel;
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->messageModel = new MessageModel();
            $this->userModel = new UserModel();
        } catch (\Exception $e) {
            log_message('error', 'ChatController constructor error: ' . $e->getMessage());
        }
    }

    /**
     * Show chat interface
     */
    public function index()
    {
        $this->requireAuth();
        
        // Get current user and other users
        $currentUserId = session()->get('user_id');
        $db = \Config\Database::connect();
        
        // Get current user info for the layout
        $currentUserQuery = $db->query("SELECT id, full_name, role FROM users WHERE id = ?", [$currentUserId]);
        $currentUser = $currentUserQuery->getRowArray();
        
        // Get other users for chat
        $usersQuery = $db->query("SELECT id, full_name, role FROM users WHERE id != ? AND is_active = 1 ORDER BY full_name", [$currentUserId]);
        $users = $usersQuery->getResultArray();
        
        $data = [
            'title' => 'Chat',
            'user' => $currentUser,
            'users' => $users,
            'current_user_id' => $currentUserId,
            'current_url' => current_url(),
            'is_admin' => $currentUser['role'] === 'admin'
        ];
        
        return view('chat/index', $data);
    }

    /**
     * Send a message (AJAX)
     */
    public function sendMessage()
    {
        // Set JSON header immediately
        $this->response->setContentType('application/json');
        
        try {
            // Check authentication manually
            $session = \Config\Services::session();
            if (!$session->get('is_logged_in')) {
                return $this->response->setJSON(['error' => 'Authentication required'])->setStatusCode(401);
            }
            
            if (!$this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'Invalid request']);
            }
            
            $senderId = $session->get('user_id');
            $receiverId = $this->request->getPost('receiver_id');
            $message = trim($this->request->getPost('message'));
            
            log_message('info', 'SendMessage - Sender: ' . $senderId . ', Receiver: ' . $receiverId . ', Message: ' . $message);
            
            if (empty($message)) {
                return $this->response->setJSON(['error' => 'Message cannot be empty']);
            }
            
            if (empty($receiverId)) {
                return $this->response->setJSON(['error' => 'Receiver ID is required']);
            }
            
            // Validate receiver exists
            $receiver = $this->userModel->find($receiverId);
            if (!$receiver) {
                return $this->response->setJSON(['error' => 'Receiver not found']);
            }
            
            $messageData = [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $message,
                'is_read' => 0,
            ];
            
            if ($this->messageModel->insert($messageData)) {
                $insertId = $this->messageModel->getInsertID();
                log_message('info', 'Message inserted successfully with ID: ' . $insertId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Message sent successfully'
                ])->setStatusCode(200);
            } else {
                $errors = $this->messageModel->errors();
                log_message('error', 'Message insert failed: ' . json_encode($errors));
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Failed to send message'
                ])->setStatusCode(400);
            }
        } catch (\Exception $e) {
            log_message('error', 'Chat sendMessage error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Failed to send message: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Fetch messages for a conversation (AJAX)
     */
    public function fetchMessages()
    {
        // Set JSON header immediately
        $this->response->setContentType('application/json');
        
        // Check authentication manually
        $session = \Config\Services::session();
        if (!$session->get('is_logged_in')) {
            return $this->response
                ->setStatusCode(401)
                ->setJSON(['error' => 'Authentication required']);
        }
        
        $currentUserId = $session->get('user_id');
        $otherUserId = $this->request->getGet('user_id');
        
        if (empty($otherUserId)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'User ID is required']);
        }
        
        // Get messages from database using raw SQL to avoid any query builder issues
        $db = \Config\Database::connect();
        $sql = "SELECT m.*, 
                       u1.full_name as sender_name, 
                       u1.role as sender_role,
                       u2.full_name as receiver_name, 
                       u2.role as receiver_role
                FROM messages m
                INNER JOIN users u1 ON u1.id = m.sender_id
                INNER JOIN users u2 ON u2.id = m.receiver_id
                WHERE (m.sender_id = ? AND m.receiver_id = ?) 
                   OR (m.sender_id = ? AND m.receiver_id = ?)
                ORDER BY m.created_at ASC";
        
        $messages = $db->query($sql, [$currentUserId, $otherUserId, $otherUserId, $currentUserId])->getResultArray();
        
        // Get other user info
        $otherUserQuery = $db->query("SELECT id, full_name, role FROM users WHERE id = ?", [$otherUserId]);
        $otherUser = $otherUserQuery->getRowArray();
        
        if (!$otherUser) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'User not found']);
        }
        
        $response = [
            'success' => true,
            'messages' => $messages,
            'other_user' => $otherUser,
        ];
        
        return $this->response
            ->setStatusCode(200)
            ->setJSON($response);
    }

    /**
     * Get users for chat sidebar (AJAX)
     */
    public function users()
    {
        $this->requireAuth();
        
        $currentUserId = session()->get('user_id');
        $db = \Config\Database::connect();
        $usersQuery = $db->query("SELECT id, full_name, role FROM users WHERE id != ? AND is_active = 1 ORDER BY full_name", [$currentUserId]);
        $users = $usersQuery->getResultArray();
        
        return $this->response->setJSON([
            'success' => true,
            'users' => $users,
        ]);
    }

    /**
     * Search users for chat (AJAX)
     */
    public function searchUsers()
    {
        $this->requireAuth();
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }
        
        $searchTerm = trim($this->request->getGet('search'));
        $currentUserId = session()->get('user_id');
        
        if (empty($searchTerm)) {
            return $this->response->setJSON(['error' => 'Search term is required']);
        }
        
        $users = $this->messageModel->searchUsers($searchTerm, $currentUserId);
        
        return $this->response->setJSON([
            'success' => true,
            'users' => $users,
        ]);
    }

    /**
     * Get unread message count (AJAX)
     */
    public function getUnreadCount()
    {
        $this->requireAuth();
        
        $currentUserId = session()->get('user_id');
        $unreadCount = $this->messageModel->getUnreadCount($currentUserId);
        
        return $this->response->setJSON([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get online users (AJAX)
     */
    public function getOnlineUsers()
    {
        $this->requireAuth();
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }
        
        $currentUserId = session()->get('user_id');
        $onlineUsers = $this->messageModel->getOnlineUsers($currentUserId);
        
        return $this->response->setJSON([
            'success' => true,
            'online_users' => $onlineUsers,
        ]);
    }

    /**
     * Update user's last activity (AJAX)
     */
    public function updateActivity()
    {
        $this->requireAuth();
        
        $currentUserId = session()->get('user_id');
        
        // Check if last_activity column exists before updating
        $db = \Config\Database::connect();
        $fields = $db->getFieldNames('users');
        $hasLastActivity = in_array('last_activity', $fields);
        
        if ($hasLastActivity) {
            // Update last activity
            $this->userModel->update($currentUserId, [
                'last_activity' => date('Y-m-d H:i:s')
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
        ]);
    }
}


