<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnggotaForumSeeder extends Seeder
{
    public function run()
    {
        // Check if anggota_forum already exist
        if ($this->db->table('anggota_forum')->countAllResults() > 0) {
            echo "Anggota forum already seeded. Skipping...\n";
            return;
        }

        // Get user and forum IDs
        $users = $this->db->table('users')->select('user_id')->get()->getResultArray();
        $forums = $this->db->table('forums')->select('forum_id, admin_id')->get()->getResultArray();
        
        if (empty($users) || empty($forums)) {
            return; // Users and forums must be seeded first
        }

        $data = [];
        
        // Add all users to first forum (academic forum)
        foreach ($users as $user) {
            $data[] = [
                'forum_id'      => $forums[0]['forum_id'],
                'user_id'       => $user['user_id'],
                'allowed_upload' => true,
            ];
        }
        
        // Add first 3 users to second forum (project forum)
        for ($i = 0; $i < min(3, count($users)); $i++) {
            $data[] = [
                'forum_id'      => $forums[1]['forum_id'],
                'user_id'       => $users[$i]['user_id'],
                'allowed_upload' => true,
            ];
        }
        
        // Add first 4 users to third forum (community forum)
        for ($i = 0; $i < min(4, count($users)); $i++) {
            $data[] = [
                'forum_id'      => $forums[2]['forum_id'],
                'user_id'       => $users[$i]['user_id'],
                'allowed_upload' => true,
            ];
        }
        
        // Add all users to fourth forum
        foreach ($users as $user) {
            $data[] = [
                'forum_id'      => $forums[3]['forum_id'],
                'user_id'       => $user['user_id'],
                'allowed_upload' => true,
            ];
        }

        $this->db->table('anggota_forum')->insertBatch($data);
    }
}
