<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Check if users already exist
        if ($this->db->table('users')->countAllResults() > 0) {
            echo "Users already seeded. Skipping...\n";
            return;
        }

        $data = [
            [
                'nim'      => '2021001',
                'nama'     => 'Ahmad Rizki',
                'kelas'    => 'TI-21-A',
                'semester' => 5,
                'email'    => 'ahmad.rizki@student.ac.id',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
            ],
            [
                'nim'      => '2021002',
                'nama'     => 'Siti Nurhaliza',
                'kelas'    => 'TI-21-A',
                'semester' => 5,
                'email'    => 'siti.nurhaliza@student.ac.id',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
            ],
            [
                'nim'      => '2021003',
                'nama'     => 'Budi Santoso',
                'kelas'    => 'TI-21-B',
                'semester' => 5,
                'email'    => 'budi.santoso@student.ac.id',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
            ],
            [
                'nim'      => '2021004',
                'nama'     => 'Dewi Lestari',
                'kelas'    => 'TI-21-B',
                'semester' => 5,
                'email'    => 'dewi.lestari@student.ac.id',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
            ],
            [
                'nim'      => '2021005',
                'nama'     => 'Eko Prasetyo',
                'kelas'    => 'TI-20-A',
                'semester' => 7,
                'email'    => 'eko.prasetyo@student.ac.id',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
