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

        $this->call(AcademicProgramsSeeder::class);
        $this->call(RequirementTypeSeeder::class);
        

        // Use existing semesters from your database
        $this->checkSemesters();

        // Create admin user - Dan Lloyd P. Rosada
        $admin = User::create([
            'firstname' => 'Dan Lloyd',
            'middlename' => 'P',
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

        // Seed faculty users
        $this->seedFacultyUsers();
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
     * Seed faculty users from the teaching load document
     */
    private function seedFacultyUsers(): void
    {
        $facultyData = [
            ['firstname' => 'Nancy', 'middlename' => 'C', 'lastname' => 'Alaras', 'position' => 'Associate Professor'],
            ['firstname' => 'Agnes', 'middlename' => 'B', 'lastname' => 'Alimboyoguen', 'position' => 'Professor'],
            ['firstname' => 'Ma Cecille', 'middlename' => 'B', 'lastname' => 'Anuada', 'position' => 'Assistant Professor'],
            ['firstname' => 'Mariedel', 'middlename' => 'L', 'lastname' => 'Autriz', 'position' => 'Associate Professor'],
            ['firstname' => 'Cene', 'middlename' => 'M', 'lastname' => 'Bago', 'position' => 'Associate Professor'],
            ['firstname' => 'Rezin', 'middlename' => 'C', 'lastname' => 'Bahia', 'position' => 'Associate Professor'],
            ['firstname' => 'Lloyd', 'middlename' => 'O', 'lastname' => 'Balinado', 'position' => 'Associate Professor'],
            ['firstname' => 'Miriam', 'middlename' => 'D', 'lastname' => 'Baltazar', 'position' => 'Professor'],
            ['firstname' => 'Jenniffer', 'middlename' => 'E', 'lastname' => 'Barrientos', 'position' => 'Assistant Professor'],
            ['firstname' => 'Carmen', 'middlename' => 'A', 'lastname' => 'Batiles', 'position' => 'Associate Professor'],
            ['firstname' => 'Michele', 'middlename' => 'T', 'lastname' => 'Bono', 'position' => 'Associate Professor'],
            ['firstname' => 'Willie', 'middlename' => 'C', 'lastname' => 'Buclatin', 'position' => 'Associate Professor'],
            ['firstname' => 'Ma Corazon', 'middlename' => 'A', 'lastname' => 'Buena', 'position' => 'Associate Professor'],
            ['firstname' => 'Paulyn Jayziel', 'middlename' => 'S', 'lastname' => 'Buhay', 'position' => 'Instructor'],
            ['firstname' => 'Ronan', 'middlename' => 'M', 'lastname' => 'Cajigal', 'position' => 'Associate Professor'],
            ['firstname' => 'Charlotte', 'middlename' => 'B', 'lastname' => 'Carandang', 'position' => 'Associate Professor'],
            ['firstname' => 'Zandro', 'middlename' => 'M', 'lastname' => 'Catacutan', 'position' => 'Associate Professor'],
            ['firstname' => 'David', 'middlename' => 'L', 'lastname' => 'Cero', 'position' => ''],
            ['firstname' => 'Antonio', 'middlename' => 'V', 'lastname' => 'Cinto', 'position' => 'Associate Professor'],
            ['firstname' => 'Jaysi', 'middlename' => 'T', 'lastname' => 'Corpuz', 'position' => ''],
            ['firstname' => 'Liza', 'middlename' => 'C', 'lastname' => 'Costa', 'position' => 'Associate Professor'],
            ['firstname' => 'Michael', 'middlename' => 'T', 'lastname' => 'Costa', 'position' => 'Associate Professor'],
            ['firstname' => 'Rhodora', 'middlename' => 'S', 'lastname' => 'Crizaldo', 'position' => 'Associate Professor'],
            ['firstname' => 'Danikka', 'middlename' => 'A', 'lastname' => 'Cubillo', 'position' => 'Associate Professor'],
            ['firstname' => 'Gener', 'middlename' => 'T', 'lastname' => 'Cueno', 'position' => 'Assistant Professor'],
            ['firstname' => 'Evelyn', 'middlename' => 'M', 'lastname' => 'Del Mundo', 'position' => 'Associate Professor'],
            ['firstname' => 'Ma Cynthia', 'middlename' => 'R', 'lastname' => 'Dela Cruz', 'position' => 'Professor'],
            ['firstname' => 'Orlando', 'middlename' => 'B', 'lastname' => 'Delos Reyes', 'position' => 'Associate Professor'],
            ['firstname' => 'Guillermo', 'middlename' => 'P', 'lastname' => 'Desenganio', 'position' => 'Assistant Professor'],
            ['firstname' => 'Ma Cristina', 'middlename' => 'L', 'lastname' => 'Desepida', 'position' => 'Associate Professor'],
            ['firstname' => 'Armi Grace', 'middlename' => 'B', 'lastname' => 'Desingaño', 'position' => 'Assistant Professor'],
            ['firstname' => 'Analyn', 'middlename' => 'T', 'lastname' => 'Dico', 'position' => 'Associate Professor'],
            ['firstname' => 'Jonathan', 'middlename' => 'R', 'lastname' => 'Digma', 'position' => 'Associate Professor'],
            ['firstname' => 'Dickson', 'middlename' => 'N', 'lastname' => 'Dimero', 'position' => 'Associate Professor'],
            ['firstname' => 'Marivic', 'middlename' => 'G', 'lastname' => 'Dizon', 'position' => 'Associate Professor'],
            ['firstname' => 'Monina Dyan', 'middlename' => 'R', 'lastname' => 'Elumba', 'position' => 'Instructor'],
            ['firstname' => 'Jenny Beb', 'middlename' => 'F', 'lastname' => 'Espineli', 'position' => 'Associate Professor'],
            ['firstname' => 'Bernard', 'middlename' => 'S', 'lastname' => 'Feranil', 'position' => 'Associate Professor'],
            ['firstname' => 'Ammie', 'middlename' => 'P', 'lastname' => 'Ferrer', 'position' => 'Associate Professor'],
            ['firstname' => 'Al Owen Roy', 'middlename' => 'A', 'lastname' => 'Ferrera', 'position' => 'Assistant Professor'],
            ['firstname' => 'Agnes', 'middlename' => 'C', 'lastname' => 'Francisco', 'position' => 'Associate Professor'],
            ['firstname' => 'Zannie', 'middlename' => 'I', 'lastname' => 'Gamuyao', 'position' => 'Associate Professor'],
            ['firstname' => 'Henry', 'middlename' => 'O', 'lastname' => 'Garcia', 'position' => ''],
            ['firstname' => 'Edgardo', 'middlename' => 'O', 'lastname' => 'Gonzales', 'position' => 'Associate Professor'],
            ['firstname' => 'Evelyn', 'middlename' => 'F', 'lastname' => 'Grueso', 'position' => 'Assistant Professor'],
            ['firstname' => 'Julie', 'middlename' => 'S', 'lastname' => 'Guevara', 'position' => 'Associate Professor'],
            ['firstname' => 'Emeline', 'middlename' => 'C', 'lastname' => 'Guevarra', 'position' => 'IT Officer'],
            ['firstname' => 'Rosario', 'middlename' => 'B', 'lastname' => 'Gumban', 'position' => 'Assistant Professor'],
            ['firstname' => 'Florindo', 'middlename' => 'C', 'lastname' => 'Ilagan', 'position' => 'Associate Professor'],
            ['firstname' => 'Bettina Joyce', 'middlename' => 'P', 'lastname' => 'Ilagan', 'position' => 'Associate Professor'],
            ['firstname' => 'Patrick Glenn', 'middlename' => 'C', 'lastname' => 'Ilano', 'position' => 'Associate Professor'],
            ['firstname' => 'Gemma', 'middlename' => 'S', 'lastname' => 'Legaspi', 'position' => 'Associate Professor'],
            ['firstname' => 'Khenelyn', 'middlename' => 'P', 'lastname' => 'Lewis', 'position' => 'Associate Professor'],
            ['firstname' => 'Ma Soledad', 'middlename' => 'M', 'lastname' => 'Lising', 'position' => 'Associate Professor'],
            ['firstname' => 'Tita', 'middlename' => 'C', 'lastname' => 'Lopez', 'position' => 'Associate Professor'],
            ['firstname' => 'Magdaleno', 'middlename' => 'R', 'lastname' => 'Lubigan', 'position' => 'Professor'],
            ['firstname' => 'Pia Rhoda', 'middlename' => 'P', 'lastname' => 'Lucero', 'position' => 'Associate Professor'],
            ['firstname' => 'Almira', 'middlename' => 'G', 'lastname' => 'Magcawas', 'position' => 'Associate Professor'],
            ['firstname' => 'Jason', 'middlename' => 'R', 'lastname' => 'Maniacop', 'position' => 'Associate Professor'],
            ['firstname' => 'Adolfo', 'middlename' => 'C', 'lastname' => 'Manuel Jr', 'position' => 'Professor'],
            ['firstname' => 'Hosea', 'middlename' => 'DL', 'lastname' => 'Matel', 'position' => 'Associate Professor'],
            ['firstname' => 'Tania Marie', 'middlename' => 'P', 'lastname' => 'Melo', 'position' => 'Assistant Professor'],
            ['firstname' => 'Marlon', 'middlename' => 'A', 'lastname' => 'Mojica', 'position' => 'Associate Professor'],
            ['firstname' => 'Ruel', 'middlename' => 'M', 'lastname' => 'Mojica', 'position' => 'Professor'],
            ['firstname' => 'Edison', 'middlename' => 'E', 'lastname' => 'Mojica', 'position' => 'Associate Professor'],
            ['firstname' => 'John Xavier', 'middlename' => 'B', 'lastname' => 'Nepomuceno', 'position' => ''],
            ['firstname' => 'Rowena', 'middlename' => 'R', 'lastname' => 'Noceda', 'position' => 'Associate Professor'],
            ['firstname' => 'Ma Agnes', 'middlename' => 'P', 'lastname' => 'Nuestro', 'position' => 'Professor'],
            ['firstname' => 'Jo Anne', 'middlename' => 'C', 'lastname' => 'Nuestro', 'position' => 'Associate Professor'],
            ['firstname' => 'Cristina', 'middlename' => 'F', 'lastname' => 'Olo', 'position' => 'Professor'],
            ['firstname' => 'Almon', 'middlename' => 'R', 'lastname' => 'Oquendo', 'position' => ''],
            ['firstname' => 'Arleen', 'middlename' => 'C', 'lastname' => 'Panaligan', 'position' => 'Associate Professor'],
            ['firstname' => 'Glenda', 'middlename' => 'S', 'lastname' => 'Peña', 'position' => 'Associate Professor'],
            ['firstname' => 'Ronald', 'middlename' => 'P', 'lastname' => 'Peña', 'position' => 'Associate Professor'],
            ['firstname' => 'Ma Veronica', 'middlename' => 'P', 'lastname' => 'Peñaflorida', 'position' => 'Associate Professor'],
            ['firstname' => 'Rossian', 'middlename' => 'V', 'lastname' => 'Perea', 'position' => 'Associate Professor'],
            ['firstname' => 'Katherine', 'middlename' => 'DG', 'lastname' => 'Peren', 'position' => 'Instructor'],
            ['firstname' => 'Marlon', 'middlename' => 'R', 'lastname' => 'Pereña', 'position' => 'Associate Professor'],
            ['firstname' => 'Adora Joy', 'middlename' => 'T', 'lastname' => 'Plete', 'position' => 'Professor'],
            ['firstname' => 'Sixto', 'middlename' => 'N', 'lastname' => 'Ras Jr', 'position' => 'Assistant Professor'],
            ['firstname' => 'Jocelyn', 'middlename' => 'L', 'lastname' => 'Reyes', 'position' => 'Associate Professor'],
            ['firstname' => 'Hernando', 'middlename' => 'D', 'lastname' => 'Robles', 'position' => ''],
            ['firstname' => 'Larry', 'middlename' => 'E', 'lastname' => 'Rocela', 'position' => 'Assistant Professor'],
            ['firstname' => 'Efren', 'middlename' => 'R', 'lastname' => 'Rocillo', 'position' => 'Associate Professor'],
            ['firstname' => 'Roderick', 'middlename' => 'M', 'lastname' => 'Rupido', 'position' => 'Associate Professor'],
            ['firstname' => 'Wylyn', 'middlename' => 'S', 'lastname' => 'Salva', 'position' => 'Assistant Professor'],
            ['firstname' => 'Bernadette', 'middlename' => 'A', 'lastname' => 'Sapinoso', 'position' => 'Assistant Professor'],
            ['firstname' => 'Venus', 'middlename' => 'O', 'lastname' => 'Saz', 'position' => 'Associate Professor'],
            ['firstname' => 'Mildred', 'middlename' => 'A', 'lastname' => 'Sebastian', 'position' => 'Associate Professor'],
            ['firstname' => 'Andrew', 'middlename' => 'J', 'lastname' => 'Sidocon', 'position' => 'Assistant Professor'],
            ['firstname' => 'Cristina', 'middlename' => 'M', 'lastname' => 'Signo', 'position' => 'Associate Professor'],
            ['firstname' => 'Alfe', 'middlename' => 'M', 'lastname' => 'Solina', 'position' => 'Associate Professor'],
            ['firstname' => 'Melbourne', 'middlename' => 'R', 'lastname' => 'Talactac', 'position' => 'Professor'],
            ['firstname' => 'Joana Marie', 'middlename' => 'M', 'lastname' => 'Tayag', 'position' => 'Assistant Professor'],
            ['firstname' => 'Mary Jane', 'middlename' => 'T', 'lastname' => 'Tepora', 'position' => 'Associate Professor'],
            ['firstname' => 'Teddy', 'middlename' => 'F', 'lastname' => 'Tepora', 'position' => 'Professor'],
            ['firstname' => 'Ma Lisa Fe', 'middlename' => 'O', 'lastname' => 'Tria', 'position' => 'Assistant Professor'],
            ['firstname' => 'Ma Leah', 'middlename' => 'P', 'lastname' => 'Ulanday', 'position' => ''],
            ['firstname' => 'Fredelino', 'middlename' => 'E', 'lastname' => 'Vecina Jr', 'position' => 'Instructor'],
            ['firstname' => 'Alfred', 'middlename' => 'A', 'lastname' => 'Venzon', 'position' => 'Associate Professor'],
            ['firstname' => 'Poinsettia', 'middlename' => 'A', 'lastname' => 'Vida', 'position' => 'Associate Professor'],
            ['firstname' => 'Reizel', 'middlename' => 'G', 'lastname' => 'Viray', 'position' => 'Associate Professor'],
            ['firstname' => 'Roselyn', 'middlename' => 'A', 'lastname' => 'Ymana', 'position' => 'Associate Professor'],
        ];

        foreach ($facultyData as $faculty) {
            // Create email from name
            $email = strtolower($faculty['firstname'] . '.' . $faculty['lastname'] . '@cvsu.edu.ph');
            $email = str_replace(' ', '', $email); // Remove spaces
            $email = str_replace(['.', ' ', 'JR', 'MA'], '', $email); // Clean up common prefixes

            $user = User::create([
                'firstname' => $faculty['firstname'],
                'middlename' => $faculty['middlename'],
                'lastname' => $faculty['lastname'],
                'extensionname' => '',
                'email' => $email,
                'college_id' => 1, // Default to college_id 1
                'position' => $faculty['position'],
                'teaching_started_at' => null,
                'teaching_ended_at' => null,
                'is_active' => 1,
                'deactivated_at' => null,
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]);
            
            $user->assignRole('user');
        }

        $this->command->info('Faculty users seeded successfully.');
    }
}