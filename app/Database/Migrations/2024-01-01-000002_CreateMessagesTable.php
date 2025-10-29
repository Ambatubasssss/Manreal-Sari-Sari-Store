<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMessagesTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('messages')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sender_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'receiver_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'is_read' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('sender_id');
        $this->forge->addKey('receiver_id');
        $this->forge->addKey('created_at');
        
        $this->forge->createTable('messages');
    }

    public function down()
    {
        $this->forge->dropTable('messages');
    }
}


