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
        $this->messageModel = new MessageModel();
        $this->userModel = new UserModel();
    }

    /**
     * Show chat interface
     */
    public function index()
    {
        $this->requireAuth();
        
        $currentUserId = session()->get('user_id');
        $currentUser = $this->userModel->find($currentUserId);
        
        // Get recent conversations
        $conversations = $this->messageModel->getRecentConversations($currentUserId);
        
        // Get online users
        $onlineUsers = $this->messageModel->getOnlineUsers($currentUserId);
        
        // Get unread count
        $unreadCount = $this->messageModel->getUnreadCount($currentUserId);
        
        $data = [
            'title' => 'Chat',
            'current_user' => $currentUser,
            'conversations' => $conversations,
            'online_users' => $onlineUsers,
            'unread_count' => $unreadCount,
        ];
        
        return $this->renderView('chat/index', $data);
    }

    /**
     * Send a message (AJAX)
     */
    public function sendMessage()
    {
        $this->requireAuth();
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }
        
        $senderId = session()->get('user_id');
        $receiverId = $this->request->getPost('receiver_id');
        $message = trim($this->request->getPost('message'));
        
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
            // Get the inserted message with sender info
            $insertedMessage = $this->messageModel->select('messages.*, users.full_name as sender_name, users.role as sender_role')
                                                ->join('users', 'users.id = messages.sender_id')
                                                ->find($this->messageModel->insertID);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => $insertedMessage,
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to send message',
                'validation_errors' => $this->messageModel->errors(),
            ]);
        }
    }

    /**
     * Fetch messages for a conversation (AJAX)
     */
    public function fetchMessages()
    {
        $this->requireAuth();
        
        // Log the request for debugging
        log_message('debug', 'Fetch messages request: ' . json_encode([
            'is_ajax' => $this->request->isAJAX(),
            'method' => $this->request->getMethod(),
            'headers' => $this->request->getHeaders(),
            'user_id' => $this->request->getGet('user_id')
        ]));
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request - not AJAX']);
        }
        
        $currentUserId = session()->get('user_id');
        $otherUserId = $this->request->getGet('user_id');
        
        if (empty($otherUserId)) {
            return $this->response->setJSON(['error' => 'User ID is required']);
        }
        
        // Validate other user exists
        $otherUser = $this->userModel->find($otherUserId);
        if (!$otherUser) {
            return $this->response->setJSON(['error' => 'User not found']);
        }
        
        // Get messages
        $messages = $this->messageModel->getConversation($currentUserId, $otherUserId);
        
        // Mark messages as read
        $this->messageModel->markAsRead($otherUserId, $currentUserId);
        
        return $this->response->setJSON([
            'success' => true,
            'messages' => $messages,
            'other_user' => $otherUser,
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
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }
        
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
        
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }
        
        $currentUserId = session()->get('user_id');
        
        // Update last activity
        $this->userModel->update($currentUserId, [
            'last_activity' => date('Y-m-d H:i:s')
        ]);
        
        return $this->response->setJSON([
            'success' => true,
        ]);
    }
}


