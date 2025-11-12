<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int      $anggota_id
 * @property int      $forum_id
 * @property int      $user_id
 * @property int      $allowed_upload
 * @property string   $joined_at
 */
class AnggotaForum extends Entity
{
	protected $dates = ['joined_at'];
	protected $casts = [
		'anggota_id'     => 'integer',
		'forum_id'       => 'integer',
		'user_id'        => 'integer',
		'allowed_upload' => 'integer',
	];
}


