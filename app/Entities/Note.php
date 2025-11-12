<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int         $note_id
 * @property int         $forum_id
 * @property int         $user_id
 * @property string|null $judul
 * @property string|null $kategori
 * @property string|null $mata_kuliah
 * @property string|null $deskripsi
 * @property string|null $created_at
 */
class Note extends Entity
{
	protected $dates = ['created_at'];
	protected $casts = [
		'note_id'  => 'integer',
		'forum_id' => 'integer',
		'user_id'  => 'integer',
	];
}


