<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int         $user_id
 * @property string|null $nim
 * @property string|null $nama
 * @property string|null $kelas
 * @property int|null    $semester
 * @property string|null $email
 * @property string|null $password
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * Relations (helpers):
 * - forumsAdmin(): forums where forums.admin_id = user_id
 * - forums(): via anggota_forum
 * - tasksCreated(): kanbans.created_by = user_id
 */
class User extends Entity
{
	protected $dates = ['created_at', 'updated_at'];
	protected $casts = [
		'user_id'  => 'integer',
		'semester' => 'integer',
	];
}


