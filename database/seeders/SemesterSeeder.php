<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Semester;
use Carbon\Carbon;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesters = [
            [
                'name' => 'First Semester | 2025-2026',
                'start_date' => '2025-10-30',
                'end_date' => '2025-11-11',
                'is_active' => 0,
                'created_at' => '2025-10-30 22:08:40',
                'updated_at' => '2025-11-13 21:09:55',
            ],
            [
                'name' => 'Second Semester | 2025-2026',
                'start_date' => '2025-11-12',
                'end_date' => '2025-12-31',
                'is_active' => 1,
                'created_at' => '2025-11-02 20:23:28',
                'updated_at' => '2025-11-13 21:09:55',
            ],
        ];

        foreach ($semesters as $semester) {
            Semester::create($semester);
        }

        $this->command->info('Semesters seeded successfully!');
        $this->command->info('Second Semester | 2025-2026 is set as active semester.');
    }
}