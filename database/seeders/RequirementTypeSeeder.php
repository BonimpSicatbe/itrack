<?php

namespace Database\Seeders;

use App\Models\RequirementType;
use Illuminate\Database\Seeder;

class RequirementTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data to avoid duplicates
        RequirementType::query()->delete();

        // Create all items as folders first
        $syllabi = RequirementType::create(['name' => 'Syllabus/Syllabi', 'is_folder' => true]);
        $syllabusAcceptance = RequirementType::create(['name' => 'Syllabus Acceptance Form', 'is_folder' => true]);
        
        // Midterm folder with sub-folders
        $midterm = RequirementType::create(['name' => 'Midterm', 'is_folder' => true]);
        $midtermTos = RequirementType::create(['name' => 'TOS', 'parent_id' => $midterm->id, 'is_folder' => true]);
        $midtermRubrics = RequirementType::create(['name' => 'Rubrics', 'parent_id' => $midterm->id, 'is_folder' => true]);
        $midtermExams = RequirementType::create(['name' => 'Examinations', 'parent_id' => $midterm->id, 'is_folder' => true]);

        // Finals folder with sub-folders
        $finals = RequirementType::create(['name' => 'Finals', 'is_folder' => true]);
        $finalsTos = RequirementType::create(['name' => 'TOS', 'parent_id' => $finals->id, 'is_folder' => true]);
        $finalsRubrics = RequirementType::create(['name' => 'Rubrics', 'parent_id' => $finals->id, 'is_folder' => true]);
        $finalsExams = RequirementType::create(['name' => 'Examinations', 'parent_id' => $finals->id, 'is_folder' => true]);

        // Other folders
        $gradingSheet = RequirementType::create(['name' => 'Grading Sheet', 'is_folder' => true]);
        $record = RequirementType::create(['name' => 'Record', 'is_folder' => true]);
        $studentOutput = RequirementType::create(['name' => 'Student Output', 'is_folder' => true]);
        $classRecord = RequirementType::create(['name' => 'Class Record', 'is_folder' => true]);
    }
}