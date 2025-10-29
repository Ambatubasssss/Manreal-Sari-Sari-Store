<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'sender_id', 'receiver_id', 'message', 'is_read'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'sender_id' => 'required|integer',
        'receiver_id' => 'required|integer',
        'message' => 'required|min_length[1]',
    ];

    protected $validationMessages = [
        'sender_id' => [
            'required' => 'Sender ID is required',
            'integer' => 'Sender ID must be a valid integer',
        ],
        'receiver_id' => [
            'required' => 'Receiver ID is required',
            'integer' => 'Receiver ID must be a valid integer',
        ],
        'message' => [
            'required' => 'Message is required',
            'min_length' => 'Message cannot be empty',
        ],
    ];

    /**
     * Get conversation between two users
     */
    public function getConversation($user1Id, $user2Id, $limit = 50, $offset = 0)
    {
        // FIXED: Use raw SQL to avoid query builder issues
        $db = \Config\Database::connect();
        $sql = "SELECT messages.*, 
                       u1.full_name as sender_name, 
                       u1.role as sender_role,
                       u2.full_name as receiver_name, 
                       u2.role as receiver_role
                FROM messages
                INNER JOIN users u1 ON u1.id = messages.sender_id
                INNER JOIN users u2 ON u2.id = messages.receiver_id
                WHERE (sender_id = ? AND receiver_id = ?) 
                   OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC
                LIMIT ?";
        
        return $db->query($sql, [$user1Id, $user2Id, $user2Id, $user1Id, $limit])->getResultArray();
    }

    /**
     * Get recent conversations for a user
     */
    public function getRecentConversations($userId, $limit = 20)
    {
        try {
            // Simplified query to avoid complex SQL issues
            $sql = "
                SELECT DISTINCT
                    u.id as other_user_id,
                    u.full_name as other_user_name,
                    u.role as other_user_role,
                    m.message as last_message,
                    m.created_at as last_message_time,
                    m.is_read
                FROM users u
                INNER JOIN messages m ON (
                    (m.sender_id = ? AND m.receiver_id = u.id) OR 
                    (m.receiver_id = ? AND m.sender_id = u.id)
                )
                WHERE u.id != ?
                ORDER BY m.created_at DESC
                LIMIT ?
            ";
            
            return $this->db->query($sql, [$userId, $userId, $userId, $limit])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'getRecentConversations error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCount($userId)
    {
        return $this->where('receiver_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($senderId, $receiverId)
    {
        return $this->where('sender_id', $senderId)
                    ->where('receiver_id', $receiverId)
                    ->where('is_read', 0)
                    ->set(['is_read' => 1])
                    ->update();
    }

    /**
     * Get online users (users who have been active in the last 5 minutes)
     */
    public function getOnlineUsers($currentUserId)
    {
        $userModel = new \App\Models\UserModel();
        
        // Check if last_activity column exists
        $columns = $userModel->db->getFieldNames('users');
        $hasLastActivity = in_array('last_activity', $columns);
        
        if ($hasLastActivity) {
            // First try to get users active in the last 5 minutes
            $recentUsers = $userModel->select('id, full_name, role, last_activity')
                            ->where('id !=', $currentUserId)
                            ->where('last_activity >', date('Y-m-d H:i:s', strtotime('-5 minutes')))
                            ->where('is_active', 1)
                            ->orderBy('last_activity', 'DESC')
                            ->get()
                            ->getResultArray();
            
            // If no recent users, show all active users
            if (empty($recentUsers)) {
                $recentUsers = $userModel->select('id, full_name, role, last_activity')
                                ->where('id !=', $currentUserId)
                                ->where('is_active', 1)
                                ->orderBy('full_name', 'ASC')
                                ->get()
                                ->getResultArray();
            }
        } else {
            // Fallback: just get all active users without last_activity
            $recentUsers = $userModel->select('id, full_name, role')
                            ->where('id !=', $currentUserId)
                            ->where('is_active', 1)
                            ->orderBy('full_name', 'ASC')
                            ->get()
                            ->getResultArray();
        }
        
        return $recentUsers;
    }

    /**
     * Search users for chat
     */
    public function searchUsers($searchTerm, $currentUserId, $limit = 10)
    {
        $userModel = new \App\Models\UserModel();
        return $userModel->select('id, full_name, role, email')
                        ->where('id !=', $currentUserId)
                        ->where('is_active', 1)
                        ->groupStart()
                            ->like('full_name', $searchTerm)
                            ->orLike('email', $searchTerm)
                        ->groupEnd()
                        ->limit($limit)
                        ->get()
                        ->getResultArray();
    }
}


