<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Note;

/**
 * @extends Model<Note>
 */
class NoteModel extends Model
{
	protected $table         = 'notes';
	protected $primaryKey    = 'note_id';
	protected $returnType    = Note::class;
	protected $useTimestamps = false;
	protected $allowedFields = [
		'forum_id', 'user_id', 'judul', 'kategori', 'mata_kuliah', 'deskripsi',
	];
}


