<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'admin',
                'email' => 'admin@manrealstore.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'full_name' => 'System Administrator',
                'role' => 'admin',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'cashier1',
                'email' => 'cashier1@manrealstore.com',
                'password' => password_hash('cashier123', PASSWORD_DEFAULT),
                'full_name' => 'John Doe',
                'role' => 'cashier',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'cashier2',
                'email' => 'cashier2@manrealstore.com',
                'password' => password_hash('cashier123', PASSWORD_DEFAULT),
                'full_name' => 'Jane Smith',
                'role' => 'cashier',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
