<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryLogsTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('inventory_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'action_type' => [
                'type' => 'ENUM',
                'constraint' => ['sale', 'restock', 'adjustment', 'damaged', 'return'],
                'default' => 'adjustment',
            ],
            'quantity_change' => [
                'type' => 'INT',
                'constraint' => 11,
                'comment' => 'Positive for restock, negative for sales',
            ],
            'previous_quantity' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'new_quantity' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'reference_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Sale ID or other reference',
            ],
            'reference_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'sale, restock, adjustment, etc.',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('product_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('action_type');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inventory_logs');
    }

    public function down()
    {
        $this->forge->dropTable('inventory_logs');
    }
}
