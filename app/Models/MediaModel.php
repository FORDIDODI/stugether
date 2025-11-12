<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Media;

/**
 * @extends Model<Media>
 */
class MediaModel extends Model
{
	protected $table         = 'media';
	protected $primaryKey    = 'media_id';
	protected $returnType    = Media::class;
	protected $useTimestamps = false;
	protected $allowedFields = [
		'user_id', 'forum_id', 'note_id', 'ref_id', 'file_url',
	];
}


