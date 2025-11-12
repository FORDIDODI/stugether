<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property int      $reminder_id
 * @property int      $kanban_id
 * @property int      $user_id
 * @property string   $title
 * @property string   $waktu
 * @property string   $created_at
 */
class Reminder extends Entity
{
	protected $dates = ['waktu', 'created_at'];
	protected $casts = [
		'reminder_id' => 'integer',
		'kanban_id'   => 'integer',
		'user_id'     => 'integer',
	];
}


