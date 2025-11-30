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

        // Get current user info using UserModel
        $currentUser = $this->userModel->find($currentUserId);

        if (!$currentUser) {
            return redirect()->to('/auth')->with('error', 'User not found');
        }

        // Get other active users for chat
        $otherUsers = $this->userModel->where('is_active', true)->findAll();

        // Filter out current user and sort by name
        $otherUsers = array_filter($otherUsers, function($user) use ($currentUserId) {
            return $user['id'] !== $currentUserId;
        });

        usort($otherUsers, function($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });

        $data = [
            'title' => 'Chat',
            'user' => $currentUser,
            'users' => $otherUsers,
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
        log_message('debug', 'fetchMessages called');

        // Set JSON header immediately
        $this->response->setContentType('application/json');

        try {
            // Check authentication manually
            $session = \Config\Services::session();
            if (!$session->get('is_logged_in')) {
                log_message('debug', 'fetchMessages: Authentication failed');
                return $this->response
                    ->setStatusCode(401)
                    ->setJSON(['error' => 'Authentication required']);
            }

            $currentUserId = $session->get('user_id');
            $otherUserId = $this->request->getGet('user_id');

            log_message('debug', "fetchMessages: currentUserId=$currentUserId, otherUserId=$otherUserId");

            if (empty($otherUserId)) {
                log_message('debug', 'fetchMessages: User ID is required');
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON(['error' => 'User ID is required']);
            }

            // Get other user info
            $otherUser = $this->userModel->find($otherUserId);

            if (!$otherUser) {
                log_message('debug', 'fetchMessages: Other user not found');
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON(['error' => 'User not found']);
            }

            // Get messages using MessageModel
            $messages = $this->messageModel->getConversation($currentUserId, $otherUserId);

            log_message('debug', 'fetchMessages: Retrieved ' . count($messages) . ' messages');

            $response = [
                'success' => true,
                'messages' => $messages,
                'other_user' => $otherUser,
            ];

            log_message('debug', 'fetchMessages: Returning success response');

            return $this->response
                ->setStatusCode(200)
                ->setJSON($response);
        } catch (\Exception $e) {
            log_message('error', 'fetchMessages error: ' . $e->getMessage());
            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Internal server error']);
        }
    }

    /**
     * Get users for chat sidebar (AJAX)
     */
    public function users()
    {
        $this->requireAuth();

        $currentUserId = session()->get('user_id');

        // Get active users using UserModel
        $allUsers = $this->userModel->where('is_active', true)->findAll();

        // Filter out current user and sort by name
        $users = array_filter($allUsers, function($user) use ($currentUserId) {
            return $user['id'] !== $currentUserId;
        });

        usort($users, function($a, $b) {
            return strcmp($a['full_name'], $b['full_name']);
        });

        return $this->response->setJSON([
            'success' => true,
            'users' => array_values($users),
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

        // Update last activity using UserModel
        $this->userModel->update($currentUserId, [
            'last_activity' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
        ]);
    }
}
