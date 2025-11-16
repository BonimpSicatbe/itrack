<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Requirement;
use App\Models\User;
use App\Models\Course;
use App\Models\Semester;
use App\Models\CourseAssignment;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'super-admin']);

        $this->call(SemesterSeeder::class);
        $this->call(AcademicProgramsSeeder::class);
        $this->call(RequirementTypeSeeder::class);
        

        // Use existing semesters from your database
        $this->checkSemesters();

        // Create admin user - Dan Lloyd P. Rosada
        $admin = User::create([
            'firstname' => 'Dan Lloyd',
            'middlename' => 'Panganiban',
            'lastname' => 'Rosada',
            'extensionname' => '',
            'email' => 'danlloyd.rosada@gmail.com',
            'college_id' => '1',
            'position' => 'MIS Officer',
            'teaching_started_at' => '2020-01-15',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        // Create regular user - Doming H. Ricalde
        $user = User::create([
            'firstname' => 'Doming',
            'middlename' => 'Hilapo',
            'lastname' => 'Ricalde',
            'extensionname' => '',
            'email' => 'dominghilaporicalde@gmail.com',
            'college_id' => '1',
            'position' => 'Faculty',
            'teaching_started_at' => '2018-06-01',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('user');

        // Create 10 dummy users
        $dummyUsers = [
            [
                'firstname' => 'Maria',
                'middlename' => 'Santos',
                'lastname' => 'Cruz',
                'email' => 'maria.cruz@gmail.com',
                'position' => 'Assistant Professor',
                'teaching_started_at' => '2019-08-15',
                'college_id' => 1, // CAFENR
            ],
            [
                'firstname' => 'Juan',
                'middlename' => 'Dela',
                'lastname' => 'Cruz',
                'email' => 'juan.delacruz@gmail.com',
                'position' => 'Associate Professor',
                'teaching_started_at' => '2017-03-10',
                'college_id' => 2, // CED
            ],
            [
                'firstname' => 'Ana',
                'middlename' => 'Reyes',
                'lastname' => 'Garcia',
                'email' => 'ana.garcia@gmail.com',
                'position' => 'Instructor',
                'teaching_started_at' => '2021-01-20',
                'college_id' => 3, // CEMDS
            ],
            [
                'firstname' => 'Carlos',
                'middlename' => 'Mendoza',
                'lastname' => 'Lopez',
                'email' => 'carlos.lopez@gmail.com',
                'position' => 'Professor',
                'teaching_started_at' => '2015-11-05',
                'college_id' => 4, // CAS
            ],
            [
                'firstname' => 'Elena',
                'middlename' => 'Torres',
                'lastname' => 'Martinez',
                'email' => 'elena.martinez@gmail.com',
                'position' => 'Assistant Professor',
                'teaching_started_at' => '2020-06-30',
                'college_id' => 5, // CEIT
            ],
            [
                'firstname' => 'Ricardo',
                'middlename' => 'Gonzales',
                'lastname' => 'Santos',
                'email' => 'ricardo.santos@gmail.com',
                'position' => 'Associate Professor',
                'teaching_started_at' => '2016-09-12',
                'college_id' => 1, // CAFENR
            ],
            [
                'firstname' => 'Lourdes',
                'middlename' => 'Villanueva',
                'lastname' => 'Reyes',
                'email' => 'lourdes.reyes@gmail.com',
                'position' => 'Instructor',
                'teaching_started_at' => '2022-03-25',
                'college_id' => 2, // CED
            ],
            [
                'firstname' => 'Fernando',
                'middlename' => 'Castillo',
                'lastname' => 'Ramirez',
                'email' => 'fernando.ramirez@gmail.com',
                'position' => 'Professor',
                'teaching_started_at' => '2014-07-18',
                'college_id' => 3, // CEMDS
            ],
            [
                'firstname' => 'Isabel',
                'middlename' => 'Alvarez',
                'lastname' => 'Flores',
                'email' => 'isabel.flores@gmail.com',
                'position' => 'Assistant Professor',
                'teaching_started_at' => '2019-11-08',
                'college_id' => 4, // CAS
            ],
            [
                'firstname' => 'Antonio',
                'middlename' => 'Rivera',
                'lastname' => 'Gomez',
                'email' => 'antonio.gomez@gmail.com',
                'position' => 'Associate Professor',
                'teaching_started_at' => '2018-04-22',
                'college_id' => 5, // CEIT
            ]
        ];

        $userModels = [];
        foreach ($dummyUsers as $dummyUser) {
            $user = User::create([
                'firstname' => $dummyUser['firstname'],
                'middlename' => $dummyUser['middlename'],
                'lastname' => $dummyUser['lastname'],
                'extensionname' => '',
                'email' => $dummyUser['email'],
                'college_id' => $dummyUser['college_id'],
                'position' => $dummyUser['position'],
                'teaching_started_at' => $dummyUser['teaching_started_at'],
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]);
            $user->assignRole('user');
            $userModels[] = $user;
        }

        // Assign courses to professors
        $this->assignCoursesToProfessors($userModels);
    }

    /**
     * Check if semesters exist and display info
     */
    private function checkSemesters(): void
    {
        $semesterCount = Semester::count();
        $activeSemester = Semester::where('is_active', true)->first();

        if ($semesterCount > 0) {
            $this->command->info("Found {$semesterCount} existing semesters in database.");
            if ($activeSemester) {
                $this->command->info("Active semester: {$activeSemester->name}");
            } else {
                $this->command->warn('No active semester found.');
            }
        } else {
            $this->command->warn('No semesters found in database. Please run SemesterSeeder first.');
        }
    }

    /**
     * Assign courses to professors based on their college
     */
    private function assignCoursesToProfessors(array $professors): void
    {
        $activeSemester = Semester::where('is_active', true)->first();
        
        if (!$activeSemester) {
            $activeSemester = Semester::first();
            $this->command->warn('No active semester found. Using first semester instead.');
        }

        $courseAssignments = [
            // CAFENR professors (college_id 1) - Agriculture courses
            0 => ['AGRI 301', 'AGRI 302'], // Maria Cruz - Experimental Design II, Metabolism
            5 => ['CRSC 305', 'CRSC 310'], // Ricardo Santos - Soil-Plant Water Relationship, Advanced Plant Breeding

            // CED professors (college_id 2) - Education courses
            1 => ['EDUC 301', 'EDUC 302'], // Juan Dela Cruz - Advance Philosophies, Moral Philosophy
            6 => ['EMGT 205', 'EMGT 210'], // Lourdes Reyes - Foundation of Management, Curriculum Development

            // CEMDS professors (college_id 3) - Management courses
            2 => ['MNGT 301', 'MNGT 302'], // Ana Garcia - Advanced Statistics, Advanced Business Research
            7 => ['BA 201', 'BA 210'], // Fernando Ramirez - Social Responsibility, Human Resource Management

            // CAS professors (college_id 4) - Biology courses
            3 => ['BIOL 203', 'BIOL 204'], // Carlos Lopez - Advanced Physiology, Advanced Genetics
            8 => ['BIOL 205', 'BIOL 206'], // Isabel Flores - Advanced Cell and Molecular Biology, Advanced Microbiology

            // CEIT professors (college_id 5) - IT and Engineering courses
            4 => ['MSIT 201', 'MSIT 202'], // Elena Martinez - Advanced Operating System, Advanced Database Systems
            9 => ['MENG 201', 'MENG 202'], // Antonio Gomez - Numerical Methods, Engineering Production Management
        ];

        $assignmentsCount = 0;
        foreach ($courseAssignments as $professorIndex => $courseCodes) {
            $professor = $professors[$professorIndex];
            
            foreach ($courseCodes as $courseCode) {
                $course = Course::where('course_code', $courseCode)->first();
                
                if ($course && $activeSemester) {
                    // Check if assignment already exists to avoid duplicates
                    $existingAssignment = CourseAssignment::where([
                        'course_id' => $course->id,
                        'professor_id' => $professor->id,
                        'semester_id' => $activeSemester->id,
                    ])->first();

                    if (!$existingAssignment) {
                        CourseAssignment::create([
                            'course_id' => $course->id,
                            'professor_id' => $professor->id,
                            'semester_id' => $activeSemester->id,
                            'assignment_date' => now(),
                        ]);
                        $assignmentsCount++;
                    }
                }
            }
        }

        $this->command->info("Successfully assigned {$assignmentsCount} courses to professors.");
        $this->command->info("Using semester: {$activeSemester->name}");
        $this->command->info("Semester period: {$activeSemester->start_date} to {$activeSemester->end_date}");
    }
}