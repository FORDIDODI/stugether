<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NoteSeeder extends Seeder
{
    public function run()
    {
        // Check if notes already exist
        if ($this->db->table('notes')->countAllResults() > 0) {
            echo "Notes already seeded. Skipping...\n";
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
                'forum_id'    => $forums[0]['forum_id'],
                'user_id'     => $users[0]['user_id'],
                'judul'       => 'Ringkasan Materi Binary Tree',
                'kategori'    => 'Ringkasan',
                'mata_kuliah' => 'Algoritma dan Struktur Data',
                'deskripsi'   => 'Catatan lengkap tentang binary tree, termasuk definisi, jenis-jenis binary tree, dan operasi dasar.',
            ],
            [
                'forum_id'    => $forums[0]['forum_id'],
                'user_id'     => $users[1]['user_id'],
                'judul'       => 'Contoh Soal Sorting Algorithms',
                'kategori'    => 'Latihan',
                'mata_kuliah' => 'Algoritma dan Struktur Data',
                'deskripsi'   => 'Kumpulan contoh soal dan pembahasan tentang berbagai algoritma sorting seperti bubble sort, quick sort, dan merge sort.',
            ],
            [
                'forum_id'    => $forums[3]['forum_id'],
                'user_id'     => $users[2]['user_id'],
                'judul'       => 'Normalisasi Database - Panduan Lengkap',
                'kategori'    => 'Materi',
                'mata_kuliah' => 'Database Management System',
                'deskripsi'   => 'Penjelasan lengkap tentang normalisasi database dari 1NF hingga 3NF beserta contoh kasus.',
            ],
            [
                'forum_id'    => $forums[3]['forum_id'],
                'user_id'     => $users[3]['user_id'],
                'judul'       => 'SQL Queries - Tips dan Trik',
                'kategori'    => 'Tips',
                'mata_kuliah' => 'Database Management System',
                'deskripsi'   => 'Tips dan trik untuk menulis query SQL yang efisien dan optimal.',
            ],
            [
                'forum_id'    => $forums[1]['forum_id'],
                'user_id'     => $users[4]['user_id'],
                'judul'       => 'Best Practices RESTful API',
                'kategori'    => 'Referensi',
                'mata_kuliah' => 'Pemrograman Web',
                'deskripsi'   => 'Best practices dalam merancang dan mengimplementasikan RESTful API.',
            ],
        ];

        $this->db->table('notes')->insertBatch($data);
    }
}
