<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\MongoDB;

class MongoDBSeed extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'mongodb:seed';
    protected $description = 'Insert default data into MongoDB collections for Manreal store';

    public function run(array $params)
    {
        $mongodb = new MongoDB();

        CLI::write('Seeding MongoDB with default data...', 'green');

        // Seed default users
        $this->seedUsers($mongodb);

        // Seed default products
        $this->seedProducts($mongodb);

        CLI::write('MongoDB seeding completed successfully!', 'green');
    }

    private function seedUsers(MongoDB $mongodb)
    {
        CLI::write('Seeding default users...', 'yellow');

        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@manrealstore.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'full_name' => 'System Administrator',
                'contact_number' => '+1234567890',
                'role' => 'admin',
                'is_active' => true,
                'last_login' => null,
                'last_activity' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'cashier',
                'email' => 'cashier@manrealstore.com',
                'password' => password_hash('cashier123', PASSWORD_DEFAULT),
                'full_name' => 'Justine Nanbunturan',
                'contact_number' => '+1234567891',
                'role' => 'cashier',
                'is_active' => true,
                'last_login' => null,
                'last_activity' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $insertedIds = $mongodb->insertMany('users', $users);

        CLI::write('✓ Default users created:', 'green');
        CLI::write('  Admin user: admin (password: admin123)', 'cyan');
        CLI::write('  Cashier user: cashier (password: cashier123)', 'cyan');
    }

    private function seedProducts(MongoDB $mongodb)
    {
        CLI::write('Seeding sample products...', 'yellow');

        $products = [
            [
                'product_code' => 'CIG001',
                'name' => 'Marlboro Blast',
                'description' => 'Premium cigarette brand',
                'category' => 'Cigarettes',
                'price' => 15.00,
                'cost_price' => 12.00,
                'quantity' => 50,
                'min_stock' => 10,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_code' => 'CIG002',
                'name' => 'Camel Blue',
                'description' => 'Smooth taste cigarette',
                'category' => 'Cigarettes',
                'price' => 14.00,
                'cost_price' => 11.00,
                'quantity' => 45,
                'min_stock' => 10,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_code' => 'DRK001',
                'name' => 'Coca Cola 500ml',
                'description' => 'Refreshing cola drink',
                'category' => 'Beverages',
                'price' => 25.00,
                'cost_price' => 18.00,
                'quantity' => 100,
                'min_stock' => 20,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_code' => 'DRK002',
                'name' => 'Sprite 500ml',
                'description' => 'Lemon-lime soda',
                'category' => 'Beverages',
                'price' => 25.00,
                'cost_price' => 18.00,
                'quantity' => 80,
                'min_stock' => 20,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_code' => 'SNK001',
                'name' => 'Pringles Original',
                'description' => 'Stackable potato chips',
                'category' => 'Snacks',
                'price' => 85.00,
                'cost_price' => 65.00,
                'quantity' => 30,
                'min_stock' => 5,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_code' => 'SNK002',
                'name' => 'Lay\'s Classic',
                'description' => 'Classic potato chips',
                'category' => 'Snacks',
                'price' => 45.00,
                'cost_price' => 35.00,
                'quantity' => 60,
                'min_stock' => 10,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_code' => 'CAN001',
                'name' => 'Century Tuna Flakes',
                'description' => 'Premium tuna in oil',
                'category' => 'Canned Goods',
                'price' => 35.00,
                'cost_price' => 28.00,
                'quantity' => 40,
                'min_stock' => 8,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_code' => 'CAN002',
                'name' => 'Del Monte Spaghetti Sauce',
                'description' => 'Italian style pasta sauce',
                'category' => 'Canned Goods',
                'price' => 55.00,
                'cost_price' => 42.00,
                'quantity' => 25,
                'min_stock' => 5,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_code' => 'HTH001',
                'name' => 'Paracetamol 500mg',
                'description' => 'Pain reliever and fever reducer',
                'category' => 'Health & Beauty',
                'price' => 8.00,
                'cost_price' => 5.00,
                'quantity' => 200,
                'min_stock' => 50,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'product_code' => 'HTH002',
                'name' => 'Band Aid Strips',
                'description' => 'Adhesive bandages',
                'category' => 'Health & Beauty',
                'price' => 25.00,
                'cost_price' => 18.00,
                'quantity' => 150,
                'min_stock' => 30,
                'image' => null,
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $insertedIds = $mongodb->insertMany('products', $products);

        CLI::write('✓ Sample products created:', 'green');
        CLI::write('  Cigarettes: 2 (₱' . $products[0]['price'] . ' - ₱' . $products[1]['price'] . ')', 'cyan');
        CLI::write('  Beverages: 2 (₱' . $products[2]['price'] . ' - ₱' . $products[3]['price'] . ')', 'cyan');
        CLI::write('  Snacks: 2 (₱' . $products[4]['price'] . ' - ₱' . $products[5]['price'] . ')', 'cyan');
        CLI::write('  Canned Goods: 2 (₱' . $products[6]['price'] . ' - ₱' . $products[7]['price'] . ')', 'cyan');
        CLI::write('  Health & Beauty: 2 (₱' . $products[8]['price'] . ' - ₱' . $products[9]['price'] . ')', 'cyan');
        CLI::write('  Total products seeded: ' . count($products), 'yellow');
    }
}
