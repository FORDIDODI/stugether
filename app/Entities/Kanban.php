<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int         $kanban_id
 * @property int         $forum_id
 * @property string|null $judul
 * @property string|null $deskripsi
 * @property string|null $tenggat_waktu
 * @property string|null $file_url
 * @property string      $status
 * @property int|null    $created_by
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * Relations:
 * - forum(), creator(), reminder()
 */
class Kanban extends Entity
{
	protected $dates = ['created_at', 'updated_at', 'tenggat_waktu'];
	protected $casts = [
		'kanban_id'  => 'integer',
		'forum_id'   => 'integer',
		'created_by' => 'integer',
	];
}


