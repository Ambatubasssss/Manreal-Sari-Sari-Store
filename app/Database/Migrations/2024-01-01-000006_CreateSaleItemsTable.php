<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaleItemsTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('sale_items')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sale_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'product_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'product_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'product_name' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
            ],
            'unit_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'total_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('sale_id');
        $this->forge->addKey('product_id');
        $this->forge->addForeignKey('sale_id', 'sales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sale_items');
    }

    public function down()
    {
        $this->forge->dropTable('sale_items');
    }
}
