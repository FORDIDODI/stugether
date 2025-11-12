<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DiscussionSeeder extends Seeder
{
    public function run()
    {
        // Check if discussions already exist
        if ($this->db->table('discussions')->countAllResults() > 0) {
            echo "Discussions already seeded. Skipping...\n";
            return;
        }

        // Get forum and user IDs
        $forums = $this->db->table('forums')->select('forum_id')->get()->getResultArray();
        $users = $this->db->table('users')->select('user_id')->get()->getResultArray();
        
        if (empty($forums) || empty($users)) {
            return; // Forums and users must be seeded first
        }

        // First, insert top-level discussions
        $topLevelData = [
            [
                'forum_id' => $forums[0]['forum_id'],
                'user_id'  => $users[0]['user_id'],
                'parent_id' => null,
                'isi'      => 'Bagaimana cara menentukan kompleksitas waktu untuk algoritma rekursif? Ada yang bisa jelaskan dengan contoh?',
            ],
            [
                'forum_id' => $forums[0]['forum_id'],
                'user_id'  => $users[1]['user_id'],
                'parent_id' => null,
                'isi'      => 'Untuk tugas BST, apakah kita perlu implementasi semua operasi atau hanya insert dan search saja?',
            ],
            [
                'forum_id' => $forums[1]['forum_id'],
                'user_id'  => $users[2]['user_id'],
                'parent_id' => null,
                'isi'      => 'Framework apa yang akan kita gunakan untuk proyek ini? Saya sarankan menggunakan CodeIgniter 4.',
            ],
            [
                'forum_id' => $forums[2]['forum_id'],
                'user_id'  => $users[3]['user_id'],
                'parent_id' => null,
                'isi'      => 'Ada yang punya referensi bagus untuk belajar design patterns?',
            ],
        ];

        $this->db->table('discussions')->insertBatch($topLevelData);
        
        // Get the inserted discussion IDs
        $insertedDiscussions = $this->db->table('discussions')
            ->select('discussion_id')
            ->where('parent_id', null)
            ->orderBy('discussion_id', 'ASC')
            ->get()
            ->getResultArray();
        
        if (count($insertedDiscussions) >= 3) {
            // Now insert replies
            $replyData = [
                [
                    'forum_id' => $forums[0]['forum_id'],
                    'user_id'  => $users[2]['user_id'],
                    'parent_id' => $insertedDiscussions[0]['discussion_id'], // Reply to first discussion
                    'isi'      => 'Untuk algoritma rekursif, kompleksitasnya bisa ditentukan dengan Master Theorem atau dengan menghitung jumlah pemanggilan rekursif dikali kompleksitas setiap pemanggilan.',
                ],
                [
                    'forum_id' => $forums[0]['forum_id'],
                    'user_id'  => $users[3]['user_id'],
                    'parent_id' => $insertedDiscussions[0]['discussion_id'], // Reply to first discussion
                    'isi'      => 'Contohnya seperti Fibonacci rekursif yang memiliki kompleksitas O(2^n) karena setiap pemanggilan menghasilkan 2 pemanggilan baru.',
                ],
                [
                    'forum_id' => $forums[1]['forum_id'],
                    'user_id'  => $users[0]['user_id'],
                    'parent_id' => $insertedDiscussions[2]['discussion_id'], // Reply to third discussion
                    'isi'      => 'Setuju! CodeIgniter 4 bagus untuk proyek ini karena mudah dipelajari dan dokumentasinya lengkap.',
                ],
            ];
            
            $this->db->table('discussions')->insertBatch($replyData);
        }
    }
}
