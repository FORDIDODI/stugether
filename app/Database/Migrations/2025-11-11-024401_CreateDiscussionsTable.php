<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDiscussionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'discussion_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'forum_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'isi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('discussion_id', true);
        $this->forge->addForeignKey('forum_id', 'forums', 'forum_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('parent_id', 'discussions', 'discussion_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('discussions');
        
        // Add default CURRENT_TIMESTAMP for created_at
        $this->db->query('ALTER TABLE discussions MODIFY created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }

    public function down()
    {
        $this->forge->dropTable('discussions');
    }
}
