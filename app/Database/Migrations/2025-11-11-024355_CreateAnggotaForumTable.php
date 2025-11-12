<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnggotaForumTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'anggota_id' => [
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
            'allowed_upload' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
            ],
            'joined_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('anggota_id', true);
        $this->forge->addForeignKey('forum_id', 'forums', 'forum_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['forum_id', 'user_id']);
        $this->forge->createTable('anggota_forum');
        
        // Add default CURRENT_TIMESTAMP for joined_at
        $this->db->query('ALTER TABLE anggota_forum MODIFY joined_at DATETIME DEFAULT CURRENT_TIMESTAMP');
    }

    public function down()
    {
        $this->forge->dropTable('anggota_forum');
    }
}
