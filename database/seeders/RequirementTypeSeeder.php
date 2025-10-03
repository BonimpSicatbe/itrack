<?php

namespace Database\Seeders;

use App\Models\RequirementType;
use Illuminate\Database\Seeder;

class RequirementTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Standalone Requirements
        RequirementType::create(['name' => 'Syllabus Acceptance Form', 'is_folder' => false]);
        RequirementType::create(['name' => 'Record', 'is_folder' => false]);
        RequirementType::create(['name' => 'Student Output', 'is_folder' => false]);
        RequirementType::create(['name' => 'Class Record', 'is_folder' => false]);
        $portfolio = RequirementType::create(['name' => 'Portfolio', 'is_folder' => false]);

        // TOS Folder
        $tos = RequirementType::create(['name' => 'TOS', 'is_folder' => true]);
        RequirementType::create(['name' => 'Midterm', 'parent_id' => $tos->id, 'is_folder' => false]);
        RequirementType::create(['name' => 'Finals', 'parent_id' => $tos->id, 'is_folder' => false]);

        // Rubrics Folder
        $rubrics = RequirementType::create(['name' => 'Rubrics', 'is_folder' => true]);
        RequirementType::create(['name' => 'Midterm', 'parent_id' => $rubrics->id, 'is_folder' => false]);
        RequirementType::create(['name' => 'Finals', 'parent_id' => $rubrics->id, 'is_folder' => false]);

        // Examinations Folder
        $exams = RequirementType::create(['name' => 'Examinations', 'is_folder' => true]);
        RequirementType::create(['name' => 'Midterm', 'parent_id' => $exams->id, 'is_folder' => false]);
        RequirementType::create(['name' => 'Finals', 'parent_id' => $exams->id, 'is_folder' => false]);
    }
}
