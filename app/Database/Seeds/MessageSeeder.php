<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'sender_id' => 1, // Admin
                'receiver_id' => 2, // Cashier (John Doe)
                'message' => 'Hi! How are sales going today?',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
            [
                'sender_id' => 2, // Cashier (John Doe)
                'receiver_id' => 1, // Admin
                'message' => 'Sales are good! We\'ve sold 15 items so far.',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour 55 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour 55 minutes')),
            ],
            [
                'sender_id' => 1, // Admin
                'receiver_id' => 2, // Cashier (John Doe)
                'message' => 'Great! Any issues with inventory?',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour 50 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour 50 minutes')),
            ],
            [
                'sender_id' => 2, // Cashier (John Doe)
                'receiver_id' => 1, // Admin
                'message' => 'We\'re running low on some popular items. Should I create a restock order?',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            ],
            [
                'sender_id' => 1, // Admin
                'receiver_id' => 2, // Cashier (John Doe)
                'message' => 'Yes, please do. Also, how\'s the new POS system working?',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s', strtotime('-25 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-25 minutes')),
            ],
            // Messages with user 3 (Jane Smith)
            [
                'sender_id' => 1, // Admin
                'receiver_id' => 3, // Cashier (Jane Smith)
                'message' => 'Hi Jane! How are things going on your shift?',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour 30 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour 30 minutes')),
            ],
            [
                'sender_id' => 3, // Cashier (Jane Smith)
                'receiver_id' => 1, // Admin
                'message' => 'Everything is going well! The new POS system is working great.',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour 25 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour 25 minutes')),
            ],
            [
                'sender_id' => 1, // Admin
                'receiver_id' => 3, // Cashier (Jane Smith)
                'message' => 'That\'s great to hear! Any customer feedback?',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s', strtotime('-20 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-20 minutes')),
            ],
        ];

        $this->db->table('messages')->insertBatch($data);
    }
}


