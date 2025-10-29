<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLastActivityToUsers extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->fieldExists('last_activity', 'users')) {
            return;
        }

        $this->forge->addColumn('users', [
            'last_activity' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'last_activity');
    }
}


