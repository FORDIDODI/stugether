<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class AlterStugetherAddMissing extends Migration
{
	public function up()
	{
		$db = Database::connect();

		// forums.is_public
		if ($db->tableExists('forums')) {
			$fields = array_map(static fn($f) => $f->name, $db->getFieldData('forums'));
			if (! in_array('is_public', $fields, true)) {
				$this->forge->addColumn('forums', [
					'is_public' => [
						'type'       => 'TINYINT',
						'constraint' => 1,
						'null'       => false,
						'default'    => 0,
						'after'      => 'jenis_forum',
					],
				]);
			}
		}

		// kanbans.status
		if ($db->tableExists('kanbans')) {
			$fields = array_map(static fn($f) => $f->name, $db->getFieldData('kanbans'));
			if (! in_array('status', $fields, true)) {
				$this->forge->addColumn('kanbans', [
					'status' => [
						'type'       => 'ENUM',
						'constraint' => ['todo', 'doing', 'done'],
						'default'    => 'todo',
						'after'      => 'file_url',
					],
				]);
			}
		}

		// media.file_url
		if ($db->tableExists('media')) {
			$fields = array_map(static fn($f) => $f->name, $db->getFieldData('media'));
			if (! in_array('file_url', $fields, true)) {
				$this->forge->addColumn('media', [
					'file_url' => [
						'type'       => 'VARCHAR',
						'constraint' => 255,
						'null'       => true,
						'after'      => 'ref_id',
					],
				]);
			}
		}

		// user_forum_seen table
		if (! $db->tableExists('user_forum_seen')) {
			$this->forge->addField([
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
				'last_seen_at' => [
					'type' => 'DATETIME',
					'null' => true,
				],
			]);
			$this->forge->addKey(['user_id', 'forum_id'], true);
			$this->forge->createTable('user_forum_seen', true);
		}
	}

	public function down()
	{
		// No-op: keep columns; optionally drop user_forum_seen
		if (Database::connect()->tableExists('user_forum_seen')) {
			$this->forge->dropTable('user_forum_seen', true);
		}
	}
}


