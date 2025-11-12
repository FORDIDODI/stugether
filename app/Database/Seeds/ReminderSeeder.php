<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ReminderSeeder extends Seeder
{
    public function run()
    {
        // Check if reminders already exist
        if ($this->db->table('reminders')->countAllResults() > 0) {
            echo "Reminders already seeded. Skipping...\n";
            return;
        }

        // Get kanban and user IDs
        $kanbans = $this->db->table('kanbans')->select('kanban_id')->limit(3)->get()->getResultArray();
        $users = $this->db->table('users')->select('user_id')->get()->getResultArray();
        
        if (empty($kanbans) || empty($users)) {
            return; // Kanbans and users must be seeded first
        }

        $data = [
            [
                'kanban_id' => $kanbans[0]['kanban_id'],
                'user_id'   => $users[0]['user_id'],
                'title'     => 'Deadline Tugas BST',
                'waktu'     => date('Y-m-d H:i:s', strtotime('+2 weeks -1 day')),
            ],
            [
                'kanban_id' => $kanbans[1]['kanban_id'],
                'user_id'   => $users[1]['user_id'],
                'title'     => 'Deadline Analisis Kompleksitas',
                'waktu'     => date('Y-m-d H:i:s', strtotime('+1 week -2 hours')),
            ],
            [
                'kanban_id' => $kanbans[2]['kanban_id'],
                'user_id'   => $users[2]['user_id'],
                'title'     => 'Sprint 1 Deadline',
                'waktu'     => date('Y-m-d H:i:s', strtotime('+3 days -6 hours')),
            ],
        ];

        $this->db->table('reminders')->insertBatch($data);
    }
}
