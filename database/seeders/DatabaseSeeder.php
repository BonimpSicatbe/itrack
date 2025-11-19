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
            ['firstname' => 'NANCY', 'middlename' => 'C', 'lastname' => 'ALARAS', 'position' => 'Associate Professor'],
            ['firstname' => 'AGNES', 'middlename' => 'B', 'lastname' => 'ALIMBOYOGUEN', 'position' => 'Professor'],
            ['firstname' => 'MA CECILLE', 'middlename' => 'B', 'lastname' => 'ANUADA', 'position' => 'Assistant Professor'],
            ['firstname' => 'MARIEDEL', 'middlename' => 'L', 'lastname' => 'AUTRIZ', 'position' => 'Associate Professor'],
            ['firstname' => 'CENE', 'middlename' => 'M', 'lastname' => 'BAGO', 'position' => 'Associate Professor'],
            ['firstname' => 'REZIN', 'middlename' => 'C', 'lastname' => 'BAHIA', 'position' => 'Associate Professor'],
            ['firstname' => 'LLOYD', 'middlename' => 'O', 'lastname' => 'BALINADO', 'position' => 'Associate Professor'],
            ['firstname' => 'MIRIAM', 'middlename' => 'D', 'lastname' => 'BALTAZAR', 'position' => 'Professor'],
            ['firstname' => 'JENNIFFER', 'middlename' => 'E', 'lastname' => 'BARRIENTOS', 'position' => 'Assistant Professor'],
            ['firstname' => 'CARMEN', 'middlename' => 'A', 'lastname' => 'BATILES', 'position' => 'Associate Professor'],
            ['firstname' => 'MICHELE', 'middlename' => 'T', 'lastname' => 'BONO', 'position' => 'Associate Professor'],
            ['firstname' => 'WILLIE', 'middlename' => 'C', 'lastname' => 'BUCLATIN', 'position' => 'Associate Professor'],
            ['firstname' => 'MA CORAZON', 'middlename' => 'A', 'lastname' => 'BUENA', 'position' => 'Associate Professor'],
            ['firstname' => 'PAULYN JAYZIEL', 'middlename' => 'S', 'lastname' => 'BUHAY', 'position' => 'Instructor'],
            ['firstname' => 'RONAN', 'middlename' => 'M', 'lastname' => 'CAJIGAL', 'position' => 'Associate Professor'],
            ['firstname' => 'CHARLOTTE', 'middlename' => 'B', 'lastname' => 'CARANDANG', 'position' => 'Associate Professor'],
            ['firstname' => 'ZANDRO', 'middlename' => 'M', 'lastname' => 'CATACUTAN', 'position' => 'Associate Professor'],
            ['firstname' => 'DAVID', 'middlename' => 'L', 'lastname' => 'CERO', 'position' => ''],
            ['firstname' => 'ANTONIO', 'middlename' => 'V', 'lastname' => 'CINTO', 'position' => 'Associate Professor'],
            ['firstname' => 'JAYSI', 'middlename' => 'T', 'lastname' => 'CORPUZ', 'position' => ''],
            ['firstname' => 'LIZA', 'middlename' => 'C', 'lastname' => 'COSTA', 'position' => 'Associate Professor'],
            ['firstname' => 'MICHAEL', 'middlename' => 'T', 'lastname' => 'COSTA', 'position' => 'Associate Professor'],
            ['firstname' => 'RHODORA', 'middlename' => 'S', 'lastname' => 'CRIZALDO', 'position' => 'Associate Professor'],
            ['firstname' => 'DANIKKA', 'middlename' => 'A', 'lastname' => 'CUBILLO', 'position' => 'Associate Professor'],
            ['firstname' => 'GENER', 'middlename' => 'T', 'lastname' => 'CUENO', 'position' => 'Assistant Professor'],
            ['firstname' => 'EVELYN', 'middlename' => 'M', 'lastname' => 'DEL MUNDO', 'position' => 'Associate Professor'],
            ['firstname' => 'MA CYNTHIA', 'middlename' => 'R', 'lastname' => 'DELA CRUZ', 'position' => 'Professor'],
            ['firstname' => 'ORLANDO', 'middlename' => 'B', 'lastname' => 'DELOS REYES', 'position' => 'Associate Professor'],
            ['firstname' => 'GUILLERMO', 'middlename' => 'P', 'lastname' => 'DESENGANIO', 'position' => 'Assistant Professor'],
            ['firstname' => 'MA CRISTINA', 'middlename' => 'L', 'lastname' => 'DESEPIDA', 'position' => 'Associate Professor'],
            ['firstname' => 'ARMI GRACE', 'middlename' => 'B', 'lastname' => 'DESINGAÑO', 'position' => 'Assistant Professor'],
            ['firstname' => 'ANALYN', 'middlename' => 'T', 'lastname' => 'DICO', 'position' => 'Associate Professor'],
            ['firstname' => 'JONATHAN', 'middlename' => 'R', 'lastname' => 'DIGMA', 'position' => 'Associate Professor'],
            ['firstname' => 'DICKSON', 'middlename' => 'N', 'lastname' => 'DIMERO', 'position' => 'Associate Professor'],
            ['firstname' => 'MARIVIC', 'middlename' => 'G', 'lastname' => 'DIZON', 'position' => 'Associate Professor'],
            ['firstname' => 'MONINA DYAN', 'middlename' => 'R', 'lastname' => 'ELUMBA', 'position' => 'Instructor'],
            ['firstname' => 'JENNY BEB', 'middlename' => 'F', 'lastname' => 'ESPINELI', 'position' => 'Associate Professor'],
            ['firstname' => 'BERNARD', 'middlename' => 'S', 'lastname' => 'FERANIL', 'position' => 'Associate Professor'],
            ['firstname' => 'AMMIE', 'middlename' => 'P', 'lastname' => 'FERRER', 'position' => 'Associate Professor'],
            ['firstname' => 'AL OWEN ROY', 'middlename' => 'A', 'lastname' => 'FERRERA', 'position' => 'Assistant Professor'],
            ['firstname' => 'AGNES', 'middlename' => 'C', 'lastname' => 'FRANCISCO', 'position' => 'Associate Professor'],
            ['firstname' => 'ZANNIE', 'middlename' => 'I', 'lastname' => 'GAMUYAO', 'position' => 'Associate Professor'],
            ['firstname' => 'HENRY', 'middlename' => 'O', 'lastname' => 'GARCIA', 'position' => ''],
            ['firstname' => 'EDGARDO', 'middlename' => 'O', 'lastname' => 'GONZALES', 'position' => 'Associate Professor'],
            ['firstname' => 'EVELYN', 'middlename' => 'F', 'lastname' => 'GRUESO', 'position' => 'Assistant Professor'],
            ['firstname' => 'JULIE', 'middlename' => 'S', 'lastname' => 'GUEVARA', 'position' => 'Associate Professor'],
            ['firstname' => 'EMELINE', 'middlename' => 'C', 'lastname' => 'GUEVARRA', 'position' => 'IT Officer'],
            ['firstname' => 'ROSARIO', 'middlename' => 'B', 'lastname' => 'GUMBAN', 'position' => 'Assistant Professor'],
            ['firstname' => 'FLORINDO', 'middlename' => 'C', 'lastname' => 'ILAGAN', 'position' => 'Associate Professor'],
            ['firstname' => 'BETTINA JOYCE', 'middlename' => 'P', 'lastname' => 'ILAGAN', 'position' => 'Associate Professor'],
            ['firstname' => 'PATRICK GLENN', 'middlename' => 'C', 'lastname' => 'ILANO', 'position' => 'Associate Professor'],
            ['firstname' => 'GEMMA', 'middlename' => 'S', 'lastname' => 'LEGASPI', 'position' => 'Associate Professor'],
            ['firstname' => 'KHENELYN', 'middlename' => 'P', 'lastname' => 'LEWIS', 'position' => 'Associate Professor'],
            ['firstname' => 'MA SOLEDAD', 'middlename' => 'M', 'lastname' => 'LISING', 'position' => 'Associate Professor'],
            ['firstname' => 'TITA', 'middlename' => 'C', 'lastname' => 'LOPEZ', 'position' => 'Associate Professor'],
            ['firstname' => 'MAGDALENO', 'middlename' => 'R', 'lastname' => 'LUBIGAN', 'position' => 'Professor'],
            ['firstname' => 'PIA RHODA', 'middlename' => 'P', 'lastname' => 'LUCERO', 'position' => 'Associate Professor'],
            ['firstname' => 'ALMIRA', 'middlename' => 'G', 'lastname' => 'MAGCAWAS', 'position' => 'Associate Professor'],
            ['firstname' => 'JASON', 'middlename' => 'R', 'lastname' => 'MANIACOP', 'position' => 'Associate Professor'],
            ['firstname' => 'ADOLFO', 'middlename' => 'C', 'lastname' => 'MANUEL JR', 'position' => 'Professor'],
            ['firstname' => 'HOSEA', 'middlename' => 'DL', 'lastname' => 'MATEL', 'position' => 'Associate Professor'],
            ['firstname' => 'TANIA MARIE', 'middlename' => 'P', 'lastname' => 'MELO', 'position' => 'Assistant Professor'],
            ['firstname' => 'MARLON', 'middlename' => 'A', 'lastname' => 'MOJICA', 'position' => 'Associate Professor'],
            ['firstname' => 'RUEL', 'middlename' => 'M', 'lastname' => 'MOJICA', 'position' => 'Professor'],
            ['firstname' => 'EDISON', 'middlename' => 'E', 'lastname' => 'MOJICA', 'position' => 'Associate Professor'],
            ['firstname' => 'JOHN XAVIER', 'middlename' => 'B', 'lastname' => 'NEPOMUCENO', 'position' => ''],
            ['firstname' => 'ROWENA', 'middlename' => 'R', 'lastname' => 'NOCEDA', 'position' => 'Associate Professor'],
            ['firstname' => 'MA AGNES', 'middlename' => 'P', 'lastname' => 'NUESTRO', 'position' => 'Professor'],
            ['firstname' => 'JO ANNE', 'middlename' => 'C', 'lastname' => 'NUESTRO', 'position' => 'Associate Professor'],
            ['firstname' => 'CRISTINA', 'middlename' => 'F', 'lastname' => 'OLO', 'position' => 'Professor'],
            ['firstname' => 'ALMON', 'middlename' => 'R', 'lastname' => 'OQUENDO', 'position' => ''],
            ['firstname' => 'ARLEEN', 'middlename' => 'C', 'lastname' => 'PANALIGAN', 'position' => 'Associate Professor'],
            ['firstname' => 'GLENDA', 'middlename' => 'S', 'lastname' => 'PEÑA', 'position' => 'Associate Professor'],
            ['firstname' => 'RONALD', 'middlename' => 'P', 'lastname' => 'PEÑA', 'position' => 'Associate Professor'],
            ['firstname' => 'MA VERONICA', 'middlename' => 'P', 'lastname' => 'PEÑAFLORIDA', 'position' => 'Associate Professor'],
            ['firstname' => 'ROSSIAN', 'middlename' => 'V', 'lastname' => 'PEREA', 'position' => 'Associate Professor'],
            ['firstname' => 'KATHERINE', 'middlename' => 'DG', 'lastname' => 'PEREN', 'position' => 'Instructor'],
            ['firstname' => 'MARLON', 'middlename' => 'R', 'lastname' => 'PEREÑA', 'position' => 'Associate Professor'],
            ['firstname' => 'ADORA JOY', 'middlename' => 'T', 'lastname' => 'PLETE', 'position' => 'Professor'],
            ['firstname' => 'SIXTO', 'middlename' => 'N', 'lastname' => 'RAS JR', 'position' => 'Assistant Professor'],
            ['firstname' => 'JOCELYN', 'middlename' => 'L', 'lastname' => 'REYES', 'position' => 'Associate Professor'],
            ['firstname' => 'HERNANDO', 'middlename' => 'D', 'lastname' => 'ROBLES', 'position' => ''],
            ['firstname' => 'LARRY', 'middlename' => 'E', 'lastname' => 'ROCELA', 'position' => 'Assistant Professor'],
            ['firstname' => 'EFREN', 'middlename' => 'R', 'lastname' => 'ROCILLO', 'position' => 'Associate Professor'],
            ['firstname' => 'RODERICK', 'middlename' => 'M', 'lastname' => 'RUPIDO', 'position' => 'Associate Professor'],
            ['firstname' => 'WYLYN', 'middlename' => 'S', 'lastname' => 'SALVA', 'position' => 'Assistant Professor'],
            ['firstname' => 'BERNADETTE', 'middlename' => 'A', 'lastname' => 'SAPINOSO', 'position' => 'Assistant Professor'],
            ['firstname' => 'VENUS', 'middlename' => 'O', 'lastname' => 'SAZ', 'position' => 'Associate Professor'],
            ['firstname' => 'MILDRED', 'middlename' => 'A', 'lastname' => 'SEBASTIAN', 'position' => 'Associate Professor'],
            ['firstname' => 'ANDREW', 'middlename' => 'J', 'lastname' => 'SIDUCON', 'position' => 'Assistant Professor'],
            ['firstname' => 'CRISTINA', 'middlename' => 'M', 'lastname' => 'SIGNO', 'position' => 'Associate Professor'],
            ['firstname' => 'ALFE', 'middlename' => 'M', 'lastname' => 'SOLINA', 'position' => 'Associate Professor'],
            ['firstname' => 'MELBOURNE', 'middlename' => 'R', 'lastname' => 'TALACTAC', 'position' => 'Professor'],
            ['firstname' => 'JOANA MARIE', 'middlename' => 'M', 'lastname' => 'TAYAG', 'position' => 'Assistant Professor'],
            ['firstname' => 'MARY JANE', 'middlename' => 'T', 'lastname' => 'TEPORA', 'position' => 'Associate Professor'],
            ['firstname' => 'TEDDY', 'middlename' => 'F', 'lastname' => 'TEPORA', 'position' => 'Professor'],
            ['firstname' => 'MA LISA FE', 'middlename' => 'O', 'lastname' => 'TRIA', 'position' => 'Assistant Professor'],
            ['firstname' => 'MA LEAH', 'middlename' => 'P', 'lastname' => 'ULANDAY', 'position' => ''],
            ['firstname' => 'FREDELINO', 'middlename' => 'E', 'lastname' => 'VECINA JR', 'position' => 'Instructor'],
            ['firstname' => 'ALFRED', 'middlename' => 'A', 'lastname' => 'VENZON', 'position' => 'Associate Professor'],
            ['firstname' => 'POINSETTIA', 'middlename' => 'A', 'lastname' => 'VIDA', 'position' => 'Associate Professor'],
            ['firstname' => 'REIZEL', 'middlename' => 'G', 'lastname' => 'VIRAY', 'position' => 'Associate Professor'],
            ['firstname' => 'ROSELYN', 'middlename' => 'A', 'lastname' => 'YMANA', 'position' => 'Associate Professor'],
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