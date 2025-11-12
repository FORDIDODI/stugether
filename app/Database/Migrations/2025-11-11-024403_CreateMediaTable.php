<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMediaTable extends Migration
{
    public function up()
    {
        // Drop table if it exists (in case of previous failed migration)
        if ($this->db->tableExists('media')) {
            $this->forge->dropTable('media', true);
        }
        
        $this->forge->addField([
            'media_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'forum_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'note_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'ref_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('media_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('forum_id', 'forums', 'forum_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('note_id', 'notes', 'note_id', 'SET NULL', 'CASCADE');
        // Composite foreign key for upload permission check
        // Note: CodeIgniter doesn't support composite foreign keys directly,
        // so we'll add individual foreign keys and handle the constraint in application logic
        $this->forge->createTable('media');
        
        // Add default CURRENT_TIMESTAMP for created_at
        $this->db->query('ALTER TABLE media MODIFY created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        
        // Add composite foreign key constraint for upload permission (user must be forum member)
        // This ensures that only members of a forum can upload media to that forum
        // Note: Column order must match the unique key in anggota_forum table (forum_id, user_id)
        // Check if constraint already exists before adding
        $constraints = $this->db->query("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'media' AND CONSTRAINT_NAME = 'fk_media_upload_permission'")->getResult();
        if (empty($constraints)) {
            $this->db->query('ALTER TABLE media ADD CONSTRAINT fk_media_upload_permission FOREIGN KEY (forum_id, user_id) REFERENCES anggota_forum(forum_id, user_id) ON DELETE CASCADE');
        }
    }

    public function down()
    {
        $this->forge->dropTable('media');
    }
}
