<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int         $media_id
 * @property int         $user_id
 * @property int         $forum_id
 * @property int|null    $note_id
 * @property int|null    $ref_id
 * @property string|null $file_url
 * @property string|null $created_at
 */
class Media extends Entity
{
	protected $dates = ['created_at'];
	protected $casts = [
		'media_id' => 'integer',
		'user_id'  => 'integer',
		'forum_id' => 'integer',
		'note_id'  => 'integer',
		'ref_id'   => 'integer',
	];
}


