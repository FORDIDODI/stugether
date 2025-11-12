<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateForumsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'forum_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'admin_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'kode_undangan' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => true,
                'unique'     => true,
            ],
            'jenis_forum' => [
                'type'       => 'ENUM',
                'constraint' => ['akademik', 'proyek', 'komunitas', 'lainnya'],
                'default'    => 'akademik',
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
        $this->forge->addKey('forum_id', true);
        $this->forge->addForeignKey('admin_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('forums');
        
        // Add default CURRENT_TIMESTAMP for created_at and updated_at
        $this->db->query('ALTER TABLE forums MODIFY created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->db->query('ALTER TABLE forums MODIFY updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    public function down()
    {
        $this->forge->dropTable('forums');
    }
}
