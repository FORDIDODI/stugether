<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Run seeders in correct order (respecting foreign key dependencies)
        $this->call('UserSeeder');
        $this->call('ForumSeeder');
        $this->call('AnggotaForumSeeder');
        $this->call('KanbanSeeder');
        $this->call('ReminderSeeder');
        $this->call('DiscussionSeeder');
        $this->call('NoteSeeder');
        $this->call('MediaSeeder');
    }
}
