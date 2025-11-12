<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int      $discussion_id
 * @property int      $forum_id
 * @property int      $user_id
 * @property int|null $parent_id
 * @property string   $isi
 * @property string   $created_at
 */
class Discussion extends Entity
{
	protected $dates = ['created_at'];
	protected $casts = [
		'discussion_id' => 'integer',
		'forum_id'      => 'integer',
		'user_id'       => 'integer',
		'parent_id'     => 'integer',
	];
}


