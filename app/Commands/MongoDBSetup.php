<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\MongoDB;

class MongoDBSetup extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'mongodb:setup';
    protected $description = 'Set up MongoDB collections and indexes for Manreal store';

    public function run(array $params)
    {
        $mongodb = new MongoDB();

        CLI::write('Setting up MongoDB collections and indexes...', 'green');

        // Create collections based on migration schemas
        $this->createUsersCollection($mongodb);
        $this->createMessagesCollection($mongodb);
        $this->createProductsCollection($mongodb);
        $this->createSalesCollection($mongodb);
        $this->createSaleItemsCollection($mongodb);
        $this->createInventoryLogsCollection($mongodb);
        $this->createPasswordResetsCollection($mongodb);

        CLI::write('MongoDB setup completed successfully!', 'green');
    }

    private function createUsersCollection(MongoDB $mongodb)
    {
        CLI::write('Creating users collection...', 'yellow');

        $indexes = [
            [
                'key' => ['username' => 1],
                'unique' => true,
                'name' => 'username_unique'
            ],
            [
                'key' => ['email' => 1],
                'unique' => true,
                'name' => 'email_unique'
            ],
            [
                'key' => ['role' => 1],
                'name' => 'role_index'
            ],
            [
                'key' => ['is_active' => 1],
                'name' => 'is_active_index'
            ],
            [
                'key' => ['created_at' => 1],
                'name' => 'created_at_index'
            ]
        ];

        $mongodb->createIndexes('users', $indexes);
        CLI::write('✓ Users collection indexes created', 'green');
    }

    private function createMessagesCollection(MongoDB $mongodb)
    {
        CLI::write('Creating messages collection...', 'yellow');

        $indexes = [
            [
                'key' => ['sender_id' => 1],
                'name' => 'sender_id_index'
            ],
            [
                'key' => ['receiver_id' => 1],
                'name' => 'receiver_id_index'
            ],
            [
                'key' => ['created_at' => 1],
                'name' => 'created_at_index'
            ]
        ];

        $mongodb->createIndexes('messages', $indexes);
        CLI::write('✓ Messages collection indexes created', 'green');
    }

    private function createProductsCollection(MongoDB $mongodb)
    {
        CLI::write('Creating products collection...', 'yellow');

        $indexes = [
            [
                'key' => ['product_code' => 1],
                'unique' => true,
                'name' => 'product_code_unique'
            ],
            [
                'key' => ['category' => 1],
                'name' => 'category_index'
            ],
            [
                'key' => ['is_active' => 1],
                'name' => 'is_active_index'
            ],
            [
                'key' => ['created_at' => 1],
                'name' => 'created_at_index'
            ]
        ];

        $mongodb->createIndexes('products', $indexes);
        CLI::write('✓ Products collection indexes created', 'green');
    }

    private function createSalesCollection(MongoDB $mongodb)
    {
        CLI::write('Creating sales collection...', 'yellow');

        $indexes = [
            [
                'key' => ['sale_number' => 1],
                'unique' => true,
                'name' => 'sale_number_unique'
            ],
            [
                'key' => ['user_id' => 1],
                'name' => 'user_id_index'
            ],
            [
                'key' => ['created_at' => 1],
                'name' => 'created_at_index'
            ]
        ];

        $mongodb->createIndexes('sales', $indexes);
        CLI::write('✓ Sales collection indexes created', 'green');
    }

    private function createSaleItemsCollection(MongoDB $mongodb)
    {
        CLI::write('Creating sale_items collection...', 'yellow');

        $indexes = [
            [
                'key' => ['sale_id' => 1],
                'name' => 'sale_id_index'
            ],
            [
                'key' => ['product_id' => 1],
                'name' => 'product_id_index'
            ]
        ];

        $mongodb->createIndexes('sale_items', $indexes);
        CLI::write('✓ Sale items collection indexes created', 'green');
    }

    private function createInventoryLogsCollection(MongoDB $mongodb)
    {
        CLI::write('Creating inventory_logs collection...', 'yellow');

        $indexes = [
            [
                'key' => ['product_id' => 1],
                'name' => 'product_id_index'
            ],
            [
                'key' => ['user_id' => 1],
                'name' => 'user_id_index'
            ],
            [
                'key' => ['action_type' => 1],
                'name' => 'action_type_index'
            ],
            [
                'key' => ['created_at' => 1],
                'name' => 'created_at_index'
            ]
        ];

        $mongodb->createIndexes('inventory_logs', $indexes);
        CLI::write('✓ Inventory logs collection indexes created', 'green');
    }

    private function createPasswordResetsCollection(MongoDB $mongodb)
    {
        CLI::write('Creating password_resets collection...', 'yellow');

        $indexes = [
            [
                'key' => ['email' => 1],
                'name' => 'email_index'
            ],
            [
                'key' => ['token' => 1],
                'unique' => true,
                'name' => 'token_unique'
            ],
            [
                'key' => ['created_at' => 1],
                'name' => 'created_at_index'
            ]
        ];

        $mongodb->createIndexes('password_resets', $indexes);
        CLI::write('✓ Password resets collection indexes created', 'green');
    }
}
