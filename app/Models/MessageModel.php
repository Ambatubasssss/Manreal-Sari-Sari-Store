<?php

namespace App\Models;

use App\Libraries\MongoDB;
use MongoDB\BSON\ObjectId;

class MessageModel
{
    protected MongoDB $mongodb;
    protected string $collection = 'messages';
    protected array $allowedFields = [
        'sender_id', 'receiver_id', 'message', 'is_read', 'created_at', 'updated_at'
    ];

    protected $validationRules = [
        'sender_id' => 'required',
        'receiver_id' => 'required',
        'message' => 'required|min_length[1]',
    ];

    protected $validationMessages = [
        'sender_id' => [
            'required' => 'Sender ID is required',
        ],
        'receiver_id' => [
            'required' => 'Receiver ID is required',
        ],
        'message' => [
            'required' => 'Message is required',
            'min_length' => 'Message cannot be empty',
        ],
    ];

    public function __construct()
    {
        $this->mongodb = new MongoDB();
    }

    /**
     * Find a single document by ID
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
     * Insert a new message
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

        $data['is_read'] = $data['is_read'] ?? 0;

        $result = $this->mongodb->insert($this->collection, $data);

        return $returnID ? (string) $result : ($result !== null);
    }

    /**
     * Update a message
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
     * Get messages for a conversation between two users
     */
    public function getConversation($user1Id, $user2Id, $limit = 50, $offset = 0)
    {
        try {
            log_message('debug', "getConversation called with user1Id: $user1Id, user2Id: $user2Id");

            $userModel = new UserModel();

            $pipeline = [
                [
                    '$match' => [
                        '$or' => [
                            ['sender_id' => $user1Id, 'receiver_id' => $user2Id],
                            ['sender_id' => $user2Id, 'receiver_id' => $user1Id]
                        ]
                    ]
                ],
                [
                    '$sort' => ['created_at' => 1]
                ],
                [
                    '$limit' => $limit
                ]
            ];

            if ($offset > 0) {
                $pipeline = array_merge($pipeline, [['$skip' => $offset]]);
            }

            log_message('debug', 'getConversation pipeline: ' . json_encode($pipeline));

            $messages = $this->mongodb->aggregate($this->collection, $pipeline);
            $result = [];

            $messageCount = 0;
            foreach ($messages as $message) {
                $messageCount++;
                $message = $this->convertDocumentToArray($message);

                // Get sender and receiver info
                $sender = $userModel->find($message['sender_id']);
                $receiver = $userModel->find($message['receiver_id']);

                $message['sender_name'] = $sender ? $sender['full_name'] : 'Unknown';
                $message['sender_role'] = $sender ? $sender['role'] : 'unknown';
                $message['receiver_name'] = $receiver ? $receiver['full_name'] : 'Unknown';
                $message['receiver_role'] = $receiver ? $receiver['role'] : 'unknown';

                $result[] = $message;
            }

            log_message('debug', "getConversation returned $messageCount messages");

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'getConversation error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get unread message count for a user
     */
    public function getUnreadCount($userId)
    {
        return $this->mongodb->count($this->collection, [
            'receiver_id' => $userId,
            'is_read' => 0
        ]);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead($senderId, $receiverId)
    {
        $result = $this->mongodb->updateMany(
            $this->collection,
            [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'is_read' => 0
            ],
            ['$set' => ['is_read' => 1, 'updated_at' => date('Y-m-d H:i:s')]]
        );

        return $result->getModifiedCount() > 0;
    }

    /**
     * Get online users (simplified for now - returns all active users except current)
     */
    public function getOnlineUsers($currentUserId)
    {
        try {
            $userModel = new UserModel();

            // Get all active users first for simplicity
            $allUsers = $userModel->where('is_active', true)->findAll();

            log_message('debug', 'getOnlineUsers - Found ' . count($allUsers) . ' active users');

            // Filter out current user
            $otherUsers = array_filter($allUsers, function($user) use ($currentUserId) {
                return $user['id'] !== $currentUserId;
            });

            log_message('debug', 'getOnlineUsers - After filtering current user: ' . count($otherUsers) . ' users');

            // Sort by full_name
            usort($otherUsers, function($a, $b) {
                return strcmp($a['full_name'], $b['full_name']);
            });

            return array_values($otherUsers);
        } catch (\Exception $e) {
            log_message('error', 'getOnlineUsers error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search users for chat
     */
    public function searchUsers($searchTerm, $currentUserId, $limit = 10)
    {
        $userModel = new UserModel();

        $results = $userModel->where('is_active', true)->findAll();

        // Filter out current user and search by name
        $results = array_filter($results, function($user) use ($searchTerm, $currentUserId) {
            return $user['id'] != $currentUserId &&
                   (stripos($user['full_name'], $searchTerm) !== false ||
                    stripos($user['email'], $searchTerm) !== false);
        });

        // Limit results
        return array_slice($results, 0, $limit);
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

    /**
     * Validate data
     */
    public function validate($data): bool
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->validationRules, $this->validationMessages);

        return $validation->run($data);
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
     * Get insert ID (MongoDB ObjectId as string)
     */
    public function getInsertID()
    {
        // This is a simplified version since MongoDB doesn't return the ID from insert operation
        return null;
    }
}
