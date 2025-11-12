<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KanbanSeeder extends Seeder
{
    public function run()
    {
        // Check if kanbans already exist
        if ($this->db->table('kanbans')->countAllResults() > 0) {
            echo "Kanbans already seeded. Skipping...\n";
            return;
        }

        // Get forum and user IDs
        $forums = $this->db->table('forums')->select('forum_id')->get()->getResultArray();
        $users = $this->db->table('users')->select('user_id')->get()->getResultArray();
        
        if (empty($forums) || empty($users)) {
            return; // Forums and users must be seeded first
        }

        $data = [
            [
                'forum_id'      => $forums[0]['forum_id'],
                'judul'         => 'Tugas Implementasi Binary Search Tree',
                'deskripsi'     => 'Implementasikan operasi insert, delete, dan search pada Binary Search Tree. Deadline: 2 minggu dari sekarang.',
                'tenggat_waktu' => date('Y-m-d H:i:s', strtotime('+2 weeks')),
                'file_url'      => '/uploads/tugas/bst.pdf',
                'created_by'    => $users[0]['user_id'],
            ],
            [
                'forum_id'      => $forums[0]['forum_id'],
                'judul'         => 'Tugas Analisis Kompleksitas Algoritma',
                'deskripsi'     => 'Analisis kompleksitas waktu dan ruang untuk algoritma sorting yang telah dipelajari.',
                'tenggat_waktu' => date('Y-m-d H:i:s', strtotime('+1 week')),
                'file_url'      => '/uploads/tugas/kompleksitas.pdf',
                'created_by'    => $users[0]['user_id'],
            ],
            [
                'forum_id'      => $forums[1]['forum_id'],
                'judul'         => 'Sprint 1: Setup Project',
                'deskripsi'     => 'Setup environment development, install dependencies, dan konfigurasi database.',
                'tenggat_waktu' => date('Y-m-d H:i:s', strtotime('+3 days')),
                'file_url'      => null,
                'created_by'    => $users[1]['user_id'],
            ],
            [
                'forum_id'      => $forums[1]['forum_id'],
                'judul'         => 'Sprint 2: Authentication & Authorization',
                'deskripsi'     => 'Implementasi sistem login, register, dan role-based access control.',
                'tenggat_waktu' => date('Y-m-d H:i:s', strtotime('+1 week')),
                'file_url'      => null,
                'created_by'    => $users[1]['user_id'],
            ],
            [
                'forum_id'      => $forums[3]['forum_id'],
                'judul'         => 'Tugas Desain Database',
                'deskripsi'     => 'Buat ERD dan normalisasi database untuk sistem perpustakaan digital.',
                'tenggat_waktu' => date('Y-m-d H:i:s', strtotime('+10 days')),
                'file_url'      => '/uploads/tugas/erd.pdf',
                'created_by'    => $users[2]['user_id'],
            ],
        ];

        $this->db->table('kanbans')->insertBatch($data);
    }
}
