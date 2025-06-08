<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollegesAndDepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'CAFENR' => [
                'name' => 'College of Agriculture, Food, Environment and Natural Resources',
                'departments' => [
                    'Agriculture',
                    'Environmental Science',
                    'Food Technology',
                    'Agricultural Entrepreneurship',
                ],
            ],
            'CAS' => [
                'name' => 'College of Arts and Sciences',
                'departments' => [
                    'English Language Studies',
                    'Journalism',
                    'Political Science',
                    'Applied Mathematics',
                    'Biology',
                    'Psychology',
                    'Social Work',
                ],
            ],
            'CCJ' => [
                'name' => 'College of Criminal Justice',
                'departments' => [
                    'Criminology',
                    'Industrial Security Management',
                ],
            ],
            'CEd' => [
                'name' => 'College of Education',
                'departments' => [
                    'Early Childhood Education',
                    'Elementary Education',
                    'Secondary Education',
                    'Special Needs Education',
                    'Technology and Livelihood Education',
                    'Hospitality Management',
                    'Tourism Management',
                ],
            ],
            'CEMDS' => [
                'name' => 'College of Economics, Management, and Development Studies',
                'departments' => [
                    'Accountancy',
                    'Business Management',
                    'Development Management',
                    'Economics',
                    'International Studies',
                    'Office Administration',
                ],
            ],
            'CEIT' => [
                'name' => 'College of Engineering and Information Technology',
                'departments' => [
                    'Agricultural and Biosystems Engineering',
                    'Architecture',
                    'Civil Engineering',
                    'Computer Engineering',
                    'Electrical Engineering',
                    'Electronics Engineering',
                    'Industrial Engineering',
                    'Computer Science',
                    'Information Technology',
                ],
            ],
            'CON' => [
                'name' => 'College of Nursing',
                'departments' => ['Nursing'],
            ],
            'CSPEAR' => [
                'name' => 'College of Sports, Physical Education and Recreation',
                'departments' => [
                    'Exercise and Sports Sciences',
                    'Physical Education',
                ],
            ],
            'CVMBS' => [
                'name' => 'College of Veterinary Medicine and Biomedical Sciences',
                'departments' => ['Veterinary Medicine'],
            ],
            'COM' => [
                'name' => 'College of Medicine',
                'departments' => ['Medicine'],
            ],
            'CTHM' => [
                'name' => 'College of Tourism and Hospitality Management',
                'departments' => [
                    'Hospitality Management',
                    'Tourism Management',
                ],
            ],
            'GSOLC' => [
                'name' => 'Graduate School and Open Learning College',
                'departments' => [
                    'Agriculture',
                    'Education',
                    'Management',
                    'Business Administration',
                    'Food Science',
                    'Information Technology',
                ],
            ],
        ];

        foreach ($data as $acronym => $college) {
            $collegeModel = College::create([
                'name' => $college['name'],
                'acronym' => $acronym,
            ]);

            foreach ($college['departments'] as $dept) {
                Department::create([
                    'college_id' => $collegeModel->id,
                    'name' => $dept,
                ]);
            }
        }
    }
}
