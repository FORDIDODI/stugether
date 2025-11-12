<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MediaSeeder extends Seeder
{
    public function run()
    {
        // Check if media already exist
        if ($this->db->table('media')->countAllResults() > 0) {
            echo "Media already seeded. Skipping...\n";
            return;
        }

        // Get required IDs
        $users = $this->db->table('users')->select('user_id')->get()->getResultArray();
        $forums = $this->db->table('forums')->select('forum_id')->get()->getResultArray();
        $notes = $this->db->table('notes')->select('note_id')->limit(3)->get()->getResultArray();
        
        // Get anggota_forum to ensure user is member of forum
        $anggotaForum = $this->db->table('anggota_forum')
            ->select('user_id, forum_id')
            ->get()
            ->getResultArray();
        
        if (empty($users) || empty($forums) || empty($anggotaForum)) {
            return; // Required data must be seeded first
        }

        $data = [];
        
        // Create media entries for notes (first 3 notes)
        foreach ($notes as $index => $note) {
            if (isset($anggotaForum[$index])) {
                $data[] = [
                    'user_id' => $anggotaForum[$index]['user_id'],
                    'forum_id' => $anggotaForum[$index]['forum_id'],
                    'note_id' => $note['note_id'],
                    'ref_id' => null,
                ];
            }
        }
        
        // Create additional media entries for forum attachments
        for ($i = 0; $i < 2; $i++) {
            if (isset($anggotaForum[$i])) {
                $data[] = [
                    'user_id' => $anggotaForum[$i]['user_id'],
                    'forum_id' => $anggotaForum[$i]['forum_id'],
                    'note_id' => null,
                    'ref_id' => $i + 1, // Reference ID for other purposes
                ];
            }
        }

        if (!empty($data)) {
            $this->db->table('media')->insertBatch($data);
        }
    }
}
