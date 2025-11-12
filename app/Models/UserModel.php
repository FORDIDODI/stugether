<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\User;

/**
 * @extends Model<User>
 */
class UserModel extends Model
{
	protected $table            = 'users';
	protected $primaryKey       = 'user_id';
	protected $returnType       = User::class;
	protected $useSoftDeletes   = false;
	protected $useTimestamps    = false;
	protected $allowedFields    = [
		'nim', 'nama', 'kelas', 'semester', 'email', 'password',
	];
	protected $validationRules  = [];
	protected $validationMessages = [];
}


