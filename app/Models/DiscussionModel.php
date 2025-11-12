<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Discussion;

/**
 * @extends Model<Discussion>
 */
class DiscussionModel extends Model
{
	protected $table         = 'discussions';
	protected $primaryKey    = 'discussion_id';
	protected $returnType    = Discussion::class;
	protected $useTimestamps = false;
	protected $allowedFields = [
		'forum_id', 'user_id', 'parent_id', 'isi',
	];
}


