<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Course; 

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = [
            // List of courses extracted from the image
            ['course_code' => 'MNGT 220', 'course_name' => 'Management 220'],
            ['course_code' => 'ANSC 290', 'course_name' => 'Animal Science 290'],
            ['course_code' => 'MENG 207', 'course_name' => 'Mechanical Engineering 207'],
            ['course_code' => 'ANSC 210', 'course_name' => 'Animal Science 210'],
            ['course_code' => 'EMGT 330', 'course_name' => 'Engineering Management 330'],
            ['course_code' => 'CE 210', 'course_name' => 'Civil Engineering 210'],
            ['course_code' => 'BOTA 231', 'course_name' => 'Botany 231'],
            ['course_code' => 'COE 206', 'course_name' => 'Computer Engineering 206'],
            ['course_code' => 'FS 202', 'course_name' => 'Food Science 202'],
            ['course_code' => 'FS 295', 'course_name' => 'Food Science 295'],
            ['course_code' => 'FS 205', 'course_name' => 'Food Science 205'],
            ['course_code' => 'MENG 204', 'course_name' => 'Mechanical Engineering 204'],
            ['course_code' => 'MNGT 303', 'course_name' => 'Management 303'],
            ['course_code' => 'MITS 250', 'course_name' => 'MIT 250'],
            ['course_code' => 'MITC 260', 'course_name' => 'MIT 260'],
            ['course_code' => 'BA 203', 'course_name' => 'Business Administration 203'],
            ['course_code' => 'BA 202', 'course_name' => 'Business Administration 202'],
            ['course_code' => 'EDUC 202', 'course_name' => 'Education 202'],
            ['course_code' => 'COE 208', 'course_name' => 'Computer Engineering 208'],
            // 'BA 203' is repeated, will insert only unique codes
            ['course_code' => 'EDUC 303', 'course_name' => 'Education 303'],
            ['course_code' => 'EMGT 325', 'course_name' => 'Engineering Management 325'],
            ['course_code' => 'MICRO 210', 'course_name' => 'Microbiology 210'],
            // 'CE 206' is repeated, will insert only unique codes
            ['course_code' => 'BA 220', 'course_name' => 'Business Administration 220'],
            ['course_code' => 'PAOM 202', 'course_name' => 'PAOM 202'],
        ];

        // 1. Using DB Facade (More direct and efficient for large simple inserts)
        // If you want to strictly adhere to 'null' for the name, you can change 'course_name' => '' to 'course_name' => null 
        // *IF* your database column is nullable.
        DB::table('courses')->insert(
            collect($courses)->unique('course_code')->toArray()
        );
        
        // OR 2. Using the Eloquent Model (Slower, but fires model events)
        /*
        foreach (collect($courses)->unique('course_code') as $course) {
             Course::firstOrCreate($course, $course);
        }
        */
    }
}