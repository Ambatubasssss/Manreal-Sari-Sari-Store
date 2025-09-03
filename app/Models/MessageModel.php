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
        return $this->select('messages.*, 
                              u1.full_name as sender_name, 
                              u1.role as sender_role,
                              u2.full_name as receiver_name, 
                              u2.role as receiver_role')
                    ->join('users u1', 'u1.id = messages.sender_id')
                    ->join('users u2', 'u2.id = messages.receiver_id')
                    ->where("(sender_id = $user1Id AND receiver_id = $user2Id) OR (sender_id = $user2Id AND receiver_id = $user1Id)")
                    ->orderBy('created_at', 'ASC')
                    ->limit($limit, $offset)
                    ->get()
                    ->getResultArray();
    }

    /**
     * Get recent conversations for a user
     */
    public function getRecentConversations($userId, $limit = 20)
    {
        // Use raw SQL query to avoid getCompiledSelect() issue
        $sql = "
            SELECT DISTINCT
                u.id as other_user_id,
                u.full_name as other_user_name,
                u.role as other_user_role,
                m.message as last_message,
                m.created_at as last_message_time,
                m.is_read
            FROM users u
            INNER JOIN (
                SELECT 
                    CASE 
                        WHEN sender_id = ? THEN receiver_id 
                        ELSE sender_id 
                    END as other_user_id,
                    message,
                    created_at,
                    is_read,
                    ROW_NUMBER() OVER (
                        PARTITION BY CASE 
                            WHEN sender_id = ? THEN receiver_id 
                            ELSE sender_id 
                        END 
                        ORDER BY created_at DESC
                    ) as rn
                FROM messages 
                WHERE sender_id = ? OR receiver_id = ?
            ) m ON u.id = m.other_user_id AND m.rn = 1
            WHERE u.id != ?
            ORDER BY m.created_at DESC
            LIMIT ?
        ";
        
        return $this->db->query($sql, [$userId, $userId, $userId, $userId, $userId, $limit])->getResultArray();
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


