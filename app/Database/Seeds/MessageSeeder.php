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
                'receiver_id' => 2, // Cashier
                'message' => 'Hi! How are sales going today?',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            ],
            [
                'sender_id' => 2, // Cashier
                'receiver_id' => 1, // Admin
                'message' => 'Sales are good! We\'ve sold 15 items so far.',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour 55 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour 55 minutes')),
            ],
            [
                'sender_id' => 1, // Admin
                'receiver_id' => 2, // Cashier
                'message' => 'Great! Any issues with inventory?',
                'is_read' => 1,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour 50 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour 50 minutes')),
            ],
            [
                'sender_id' => 2, // Cashier
                'receiver_id' => 1, // Admin
                'message' => 'We\'re running low on some popular items. Should I create a restock order?',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            ],
            [
                'sender_id' => 1, // Admin
                'receiver_id' => 2, // Cashier
                'message' => 'Yes, please do. Also, how\'s the new POS system working?',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s', strtotime('-25 minutes')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-25 minutes')),
            ],
        ];

        $this->db->table('messages')->insertBatch($data);
    }
}


