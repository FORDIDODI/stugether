<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Reminder;

/**
 * @extends Model<Reminder>
 */
class ReminderModel extends Model
{
	protected $table         = 'reminders';
	protected $primaryKey    = 'reminder_id';
	protected $returnType    = Reminder::class;
	protected $useTimestamps = false;
	protected $allowedFields = [
		'kanban_id', 'user_id', 'title', 'waktu',
	];
}


