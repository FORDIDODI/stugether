<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKanbansTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'kanban_id' => [
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
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'tenggat_waktu' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'file_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('kanban_id', true);
        $this->forge->addForeignKey('forum_id', 'forums', 'forum_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kanbans');
        
        // Add default CURRENT_TIMESTAMP for created_at and updated_at
        $this->db->query('ALTER TABLE kanbans MODIFY created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->db->query('ALTER TABLE kanbans MODIFY updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    public function down()
    {
        $this->forge->dropTable('kanbans');
    }
}
