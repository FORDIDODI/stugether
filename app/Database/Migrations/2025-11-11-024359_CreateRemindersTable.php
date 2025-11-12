<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRemindersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'reminder_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kanban_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'unique'     => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'waktu' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('reminder_id', true);
        $this->forge->addForeignKey('kanban_id', 'kanbans', 'kanban_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reminders');
        
        // Add default CURRENT_TIMESTAMP for created_at
        $this->db->query('ALTER TABLE reminders MODIFY created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }

    public function down()
    {
        $this->forge->dropTable('reminders');
    }
}
