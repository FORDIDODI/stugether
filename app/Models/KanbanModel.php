<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Kanban;

/**
 * @extends Model<Kanban>
 */
class KanbanModel extends Model
{
	protected $table         = 'kanbans';
	protected $primaryKey    = 'kanban_id';
	protected $returnType    = Kanban::class;
	protected $useTimestamps = false;
	protected $allowedFields = [
		'forum_id', 'judul', 'deskripsi', 'tenggat_waktu', 'file_url', 'status', 'created_by',
	];
}


