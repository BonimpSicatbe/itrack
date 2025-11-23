<?php
// database/seeders/CourseAssignmentSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseAssignment;
use App\Models\Course;
use App\Models\User;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class CourseAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Get the active semester
        $semester = Semester::where('is_active', true)->first();
        
        if (!$semester) {
            $this->command->error('No active semester found. Please run SemesterSeeder first.');
            return;
        }

        $assignments = [
            // ALARAS, NANCY C.
            ['professor' => 'Nancy Alaras', 'course_code' => 'EMGT 215', 'program' => 'MAED'],
            
            // ALIMBOYOGUEN, AGNES B.
            ['professor' => 'Agnes Alimboyoguen', 'course_code' => 'AGRI 202', 'program' => 'MS Agri'],
            
            // ANUADA, MA. CECILLE B.
            ['professor' => 'Ma Cecille Anuada', 'course_code' => 'ANSC 235', 'program' => 'MS Agri'],
            
            // AUTRIZ, MARRIEDEL L.
            ['professor' => 'Mariedel Autriz', 'course_code' => 'ANSC 236', 'program' => 'MS Agri'],
            
            // BAGO, CENE M.
            ['professor' => 'Cene Bago', 'course_code' => 'CE 207', 'program' => 'MEng'],
            
            // BAHIA, REZIN C.
            ['professor' => 'Rezin Bahia', 'course_code' => 'EMGT 330', 'program' => 'PhD EDUC'],
            
            // BALINADO, LLOYD O.
            ['professor' => 'Lloyd Balinado', 'course_code' => 'BIOL 220', 'program' => 'MS Bio'],
            
            // BALTAZAR, MIRIAM D.
            ['professor' => 'Miriam Baltazar', 'course_code' => 'BIOL 204', 'program' => 'MS Bio'],
            
            // BARRIENTOS, JENNIFFER E.
            ['professor' => 'Jenniffer Barrientos', 'course_code' => 'MSHM 215', 'program' => 'MSHM'],
            
            // BATILES, CARMEN A.
            ['professor' => 'Carmen Batiles', 'course_code' => 'MNGT 220', 'program' => 'MMngt'],
            
            // BONO, MICHELE T.
            ['professor' => 'Michele Bono', 'course_code' => 'BIOL 225', 'program' => 'MS Bio'],
            
            // BUCLATIN, WILLIE C.
            ['professor' => 'Willie Buclatin', 'course_code' => 'MENG 204', 'program' => 'MEng'],
            
            // BUENA, MA. CORAZON A.
            ['professor' => 'Ma Corazon Buena', 'course_code' => 'BA 230', 'program' => 'MBA'],
            
            // BUHAY, PAULYN JAYZIEL S.
            ['professor' => 'Paulyn Jayziel Buhay', 'course_code' => 'MSHM 201', 'program' => 'MSHM'],
            
            // CAJIGAL, RONAN M.
            ['professor' => 'Ronan Cajigal', 'course_code' => 'EDUC 201', 'program' => 'MAED'],
            ['professor' => 'Ronan Cajigal', 'course_code' => 'EMGT 220', 'program' => 'MAED'],
            
            // CARANDANG, CHARLOTTE B.
            ['professor' => 'Charlotte Carandang', 'course_code' => 'MITC 206', 'program' => 'MIT'],
            
            // CATACUTAN, ZANDRO M.
            ['professor' => 'Zandro Catacutan', 'course_code' => 'BA 202', 'program' => 'MBA'],
            ['professor' => 'Zandro Catacutan', 'course_code' => 'MNGT 301', 'program' => 'PhD MNGT'],
            
            // CERO, DAVID L.
            ['professor' => 'David Cero', 'course_code' => 'WEM 202', 'program' => 'MEng'],
            
            // CINTO, ANTONIO V.
            ['professor' => 'Antonio Cinto', 'course_code' => 'BA 202', 'program' => 'MBA'],
            ['professor' => 'Antonio Cinto', 'course_code' => 'MNGT 202', 'program' => 'MMngt'],
            
            // CORPUZ, JAYSI T.
            ['professor' => 'Jaysi Corpuz', 'course_code' => 'BA 201', 'program' => 'MBA'],
            
            // COSTA, LIZA C.
            ['professor' => 'Liza Costa', 'course_code' => 'EDUC 302', 'program' => 'PhD EDUC'],
            ['professor' => 'Liza Costa', 'course_code' => 'EDUC 201', 'program' => 'MAED'],
            
            // COSTA, MICHAEL T.
            ['professor' => 'Michael Costa', 'course_code' => 'COE 208', 'program' => 'MEng'],
            
            // CRIZALDO, RHODORA S.
            ['professor' => 'Rhodora Crizaldo', 'course_code' => 'EMGT 325', 'program' => 'PhD EDUC'],
            
            // CUBILLO, DANIKKA A.
            ['professor' => 'Danikka Cubillo', 'course_code' => 'BA 225', 'program' => 'MBA'],
            ['professor' => 'Danikka Cubillo', 'course_code' => 'MNGT 235', 'program' => 'MMngt'],
            
            // CUENO, GENER T.
            ['professor' => 'Gener Cueno', 'course_code' => 'BA 240', 'program' => 'MBA'],
            
            // DEL MUNDO, EVELYN M.
            ['professor' => 'Evelyn Del Mundo', 'course_code' => 'MNGT 220', 'program' => 'MMngt'],
            ['professor' => 'Evelyn Del Mundo', 'course_code' => 'EMGT 225', 'program' => 'MAED'],
            
            // DELA CRUZ, MA. CYNTHIA R.
            ['professor' => 'Ma Cynthia Dela Cruz', 'course_code' => 'BIOL 220', 'program' => 'MS Bio'],
            
            // DELOS REYES, ORLANDO B.
            ['professor' => 'Orlando Delos Reyes', 'course_code' => 'CE 208', 'program' => 'MEng'],
            
            // DESENGANIO, GUILLERMO P.
            ['professor' => 'Guillermo Desenganio', 'course_code' => 'BA 240', 'program' => 'MBA'],
            
            // DESEPIDA, MA. CRISTINA L.
            ['professor' => 'Ma Cristina Desepida', 'course_code' => 'BA 203', 'program' => 'MBA'],
            ['professor' => 'Ma Cristina Desepida', 'course_code' => 'MNGT 302', 'program' => 'PhD MNGT'],
            
            // DESINGAÑO, ARMI GRACE B.
            ['professor' => 'Armi Grace Desingaño', 'course_code' => 'EMGT 215', 'program' => 'MAED'],
            
            // DICO, ANALYN T.
            ['professor' => 'Analyn Dico', 'course_code' => 'EDUC 203', 'program' => 'MAED'],
            
            // DIGMA, JONATHAN R.
            ['professor' => 'Jonathan Digma', 'course_code' => 'BIOL 202', 'program' => 'MS Bio'],
            
            // DIMERO, DICKSON N.
            ['professor' => 'Dickson Dimero', 'course_code' => 'BIOL 299', 'program' => 'MS Bio'],
            
            // DIZON, MARIVIC G.
            ['professor' => 'Marivic Dizon', 'course_code' => 'STAT 101', 'program' => 'MEng'],
            
            // ELUMBA, MONINA DYAN R.
            ['professor' => 'Monina Dyan Elumba', 'course_code' => 'CRSC 231', 'program' => 'MS Agri'],
            
            // ESPINELI, JENNY BEB F.
            ['professor' => 'Jenny Beb Espineli', 'course_code' => 'PADM 215', 'program' => 'MPA'],
            
            // FERANIL, BERNARD S.
            ['professor' => 'Bernard Feranil', 'course_code' => 'EDUC 306', 'program' => 'PhD EDUC'],
            
            // FERRER, AMMIE P.
            ['professor' => 'Ammie Ferrer', 'course_code' => 'EMGT 335', 'program' => 'PhD EDUC'],
            
            // FERRERA, AL OWEN ROY A.
            ['professor' => 'Al Owen Roy Ferrera', 'course_code' => 'WEM 229', 'program' => 'MEng'],
            
            // FRANCISCO, AGNES C.
            ['professor' => 'Agnes Francisco', 'course_code' => 'CURR 220', 'program' => 'MAED'],
            ['professor' => 'Agnes Francisco', 'course_code' => 'EDUC 306', 'program' => 'PhD EDUC'],
            
            // GAMUYAO, ZANNIE I.
            ['professor' => 'Zannie Gamuyao', 'course_code' => 'EDUC 203', 'program' => 'MAED'],
            ['professor' => 'Zannie Gamuyao', 'course_code' => 'BA 202', 'program' => 'MBA'],
            
            // GARCIA, HENRY O.
            ['professor' => 'Henry Garcia', 'course_code' => 'EMGT 330', 'program' => 'PhD EDUC'],
            
            // GONZALES, EDGARDO O.
            ['professor' => 'Edgardo Gonzales', 'course_code' => 'FSYS 225', 'program' => 'MS Agri'],
            
            // GRUESO, EVELYN F.
            ['professor' => 'Evelyn Grueso', 'course_code' => 'EDUC 201', 'program' => 'MAED'],
            
            // GUEVARA, JULIE S.
            ['professor' => 'Julie Guevara', 'course_code' => 'CURR 215', 'program' => 'MAED'],
            ['professor' => 'Julie Guevara', 'course_code' => 'CURR 225', 'program' => 'MAED'],
            
            // GUEVARRA, EMELINE C.
            ['professor' => 'Emeline Guevarra', 'course_code' => 'COS 23', 'program' => 'MEng'],
            
            // GUMBAN, ROSARIO B.
            ['professor' => 'Rosario Gumban', 'course_code' => 'BA 235', 'program' => 'MBA'],
            
            // ILAGAN, FLORINDO C.
            ['professor' => 'Florindo Ilagan', 'course_code' => 'MNGT 210', 'program' => 'MMngt'],
            ['professor' => 'Florindo Ilagan', 'course_code' => 'MNGT 205', 'program' => 'MMngt'],
            
            // ILAGAN, BETTINA JOYCE P.
            ['professor' => 'Bettina Joyce Ilagan', 'course_code' => 'MNGT 201', 'program' => 'MMngt'],
            
            // ILANO, PATRICK GLENN C.
            ['professor' => 'Patrick Glenn Ilano', 'course_code' => 'MSHM 220', 'program' => 'MSHM'],
            ['professor' => 'Patrick Glenn Ilano', 'course_code' => 'MSHM 225', 'program' => 'MSHM'],
            
            // LEGASPI, GEMMA S.
            ['professor' => 'Gemma Legaspi', 'course_code' => 'MATH 215', 'program' => 'MAED'],
            ['professor' => 'Gemma Legaspi', 'course_code' => 'MATH 220', 'program' => 'MAED'],
            
            // LEWIS, KHENELYN P.
            ['professor' => 'Khenelyn Lewis', 'course_code' => 'MITC 265', 'program' => 'MIT'],
            
            // LISING, MA. SOLEDAD M.
            ['professor' => 'Ma Soledad Lising', 'course_code' => 'PADM 220', 'program' => 'MPA'],
            
            // LOPEZ, TITA C.
            ['professor' => 'Tita Lopez', 'course_code' => 'MNGT 370', 'program' => 'PhD MNGT'],
            
            // LUBIGAN, MAGDALENO R.
            ['professor' => 'Magdaleno Lubigan', 'course_code' => 'EMGT 340', 'program' => 'PhD EDUC'],
            
            // LUCERO, PIA RHODA P.
            ['professor' => 'Pia Rhoda Lucero', 'course_code' => 'MSHM 205', 'program' => 'MSHM'],
            
            // MAGCAWAS, ALMIRA G.
            ['professor' => 'Almira Magcawas', 'course_code' => 'MNGT 303', 'program' => 'PhD MNGT'],
            
            // MANIACOP, JASON R.
            ['professor' => 'Jason Maniacop', 'course_code' => 'EMGT 225', 'program' => 'MAED'],
            
            // MANUEL JR., ADOLFO C.
            ['professor' => 'Adolfo Manuel Jr', 'course_code' => 'AGRI 270', 'program' => 'MS Agri'],
            
            // MATEL, HOSEA DL
            ['professor' => 'Hosea Matel', 'course_code' => 'AGRI 203', 'program' => 'MS Agri'],
            
            // MELO, TANIA MARIE P.
            ['professor' => 'Tania Marie Melo', 'course_code' => 'BA 220', 'program' => 'MBA'],
            
            // MOJICA, MARLON A.
            ['professor' => 'Marlon Mojica', 'course_code' => 'BA 210', 'program' => 'MBA'],
            
            // MOJICA, RUEL M.
            ['professor' => 'Ruel Mojica', 'course_code' => 'AGRI 201', 'program' => 'MS Agri'],
            
            // MOJICA, EDISON E.
            ['professor' => 'Edison Mojica', 'course_code' => 'AENG 201', 'program' => 'MEng'],
            
            // NEPOMUCENO, JOHN XAVIER B.
            ['professor' => 'John Xavier Nepomuceno', 'course_code' => 'EMGT 220', 'program' => 'MAED'],
            
            // NOCEDA, ROWENA R.
            ['professor' => 'Rowena Noceda', 'course_code' => 'MNGT 230', 'program' => 'MMngt'],
            ['professor' => 'Rowena Noceda', 'course_code' => 'MNGT 360', 'program' => 'PhD MNGT'],
            
            // NUESTRO, MA. AGNES P.
            ['professor' => 'Ma Agnes Nuestro', 'course_code' => 'EMGT 340', 'program' => 'PhD EDUC'],
            
            // NUESTRO, JO ANNE C.
            ['professor' => 'Jo Anne Nuestro', 'course_code' => 'MNGT 220', 'program' => 'MMngt'],
            
            // OLO, CRISTINA F.
            ['professor' => 'Cristina Olo', 'course_code' => 'ANSC 295', 'program' => 'MS Agri'],
            ['professor' => 'Cristina Olo', 'course_code' => 'ANSC 299', 'program' => 'MS Agri'],
            
            // OQUENDO, ALMON R.
            ['professor' => 'Almon Oquendo', 'course_code' => 'BA 201', 'program' => 'MBA'],
            
            // PANALIGAN, ARLEEN C.
            ['professor' => 'Arleen Panaligan', 'course_code' => 'BIOL 215', 'program' => 'MS Bio'],
            
            // PEÑA, GLENDA S.
            ['professor' => 'Glenda Peña', 'course_code' => 'MNGT 205', 'program' => 'MMngt'],
            
            // PEÑA, RONALD P.
            ['professor' => 'Ronald Peña', 'course_code' => 'EE 203', 'program' => 'MEng'],
            
            // PEÑAFLORIDA, MA. VERONICA P.
            ['professor' => 'Ma Veronica Peñaflorida', 'course_code' => 'BIOL 203', 'program' => 'MS Bio'],
            
            // PEREA, ROSSIAN V.
            ['professor' => 'Rossian Perea', 'course_code' => 'MITS 275', 'program' => 'MIT'],
            
            // PEREN, KATHERINE DG.
            ['professor' => 'Katherine Peren', 'course_code' => 'MSHM 203', 'program' => 'MSHM'],
            
            // PEREÑA, MARLON R.
            ['professor' => 'Marlon Pereña', 'course_code' => 'MITS 270', 'program' => 'MIT'],
            ['professor' => 'Marlon Pereña', 'course_code' => 'EMGT 325', 'program' => 'PhD EDUC'],
            
            // PLETE, ADORA JOY T.
            ['professor' => 'Adora Joy Plete', 'course_code' => 'MNGT 201', 'program' => 'MMngt'],
            
            // RAS JR., SIXTO N.
            ['professor' => 'Sixto Ras Jr', 'course_code' => 'BA 225', 'program' => 'MBA'],
            
            // REYES, JOCELYN L.
            ['professor' => 'Jocelyn Reyes', 'course_code' => 'MNGT 210', 'program' => 'MMngt'],
            
            // ROBLES, HERNANDO D.
            ['professor' => 'Hernando Robles', 'course_code' => 'MNGT 303', 'program' => 'PhD MNGT'],
            
            // ROCELA, LARRY E.
            ['professor' => 'Larry Rocela', 'course_code' => 'CE 202', 'program' => 'MEng'],
            ['professor' => 'Larry Rocela', 'course_code' => 'CE 204', 'program' => 'MEng'],
            
            // ROCILLO, EFREN R.
            ['professor' => 'Efren Rocillo', 'course_code' => 'EE 202', 'program' => 'MEng'],
            ['professor' => 'Efren Rocillo', 'course_code' => 'MENG 208', 'program' => 'MEng'],
            
            // RUPIDO, RODERICK M.
            ['professor' => 'Roderick Rupido', 'course_code' => 'BA 235', 'program' => 'MBA'],
            
            // SALVA, WYLYN S.
            ['professor' => 'Wylyn Salva', 'course_code' => 'BA 240', 'program' => 'MBA'],
            
            // SAPINOSO, BERNADETTE A.
            ['professor' => 'Bernadette Sapinoso', 'course_code' => 'MNGT 210', 'program' => 'MMngt'],
            
            // SAZ, VENUS O.
            ['professor' => 'Venus Saz', 'course_code' => 'CRSC 236', 'program' => 'MS Agri'],
            
            // SEBASTIAN, MILDRED A.
            ['professor' => 'Mildred Sebastian', 'course_code' => 'EDUC 203', 'program' => 'MAED'],
            ['professor' => 'Mildred Sebastian', 'course_code' => 'MATH 225', 'program' => 'MAED'],
            
            // SIDOCON, ANDREW J.
            ['professor' => 'Andrew Sidocon', 'course_code' => 'EDUC 203', 'program' => 'MAED'],
            
            // SIGNO, CRISTINA M.
            ['professor' => 'Cristina Signo', 'course_code' => 'MNGT 210', 'program' => 'MMngt'],
            
            // SOLINA, ALFE M.
            ['professor' => 'Alfe Solina', 'course_code' => 'BA 201', 'program' => 'MBA'],
            ['professor' => 'Alfe Solina', 'course_code' => 'BA 230', 'program' => 'MBA'],
            
            // TALACTAC, MELBOURNE R.
            ['professor' => 'Melbourne Talactac', 'course_code' => 'BIOL 205', 'program' => 'MS Bio'],
            
            // TAYAG, JOANA MARIE M.
            ['professor' => 'Joana Marie Tayag', 'course_code' => 'MSHM 202', 'program' => 'MSHM'],
            
            // TEPORA, MARY JANE T.
            ['professor' => 'Mary Jane Tepora', 'course_code' => 'EMGT 310', 'program' => 'PhD EDUC'],
            ['professor' => 'Mary Jane Tepora', 'course_code' => 'MNGT 365', 'program' => 'PhD MNGT'],
            
            // TEPORA, TEDDY F.
            ['professor' => 'Teddy Tepora', 'course_code' => 'CRSC 295', 'program' => 'MS Agri'],
            ['professor' => 'Teddy Tepora', 'course_code' => 'CRSC 299', 'program' => 'MS Agri'],
            
            // TRIA, MA. LISA FE O.
            ['professor' => 'Ma Lisa Fe Tria', 'course_code' => 'HORTI 299', 'program' => 'MS Agri'],
            ['professor' => 'Ma Lisa Fe Tria', 'course_code' => 'HORTI 290', 'program' => 'MS Agri'],
            
            // ULANDAY, MA. LEAH P.
            ['professor' => 'Ma Leah Ulanday', 'course_code' => 'EDUC 305', 'program' => 'PhD EDUC'],
            
            // VECINA JR., FREDELINO E.
            ['professor' => 'Fredelino Vecina Jr', 'course_code' => 'AGRI 260', 'program' => 'MS Agri'],
            
            // VENZON, ALFRED A.
            ['professor' => 'Alfred Venzon', 'course_code' => 'EDUC 201', 'program' => 'MAED'],
            
            // VIDA, POINSETTIA A.
            ['professor' => 'Poinsettia Vida', 'course_code' => 'COE 201', 'program' => 'MEng'],
            
            // VIRAY, REIZEL G.
            ['professor' => 'Reizel Viray', 'course_code' => 'MSHM 210', 'program' => 'MSHM'],
            
            // YMANA, ROSELYN A.
            ['professor' => 'Roselyn Ymana', 'course_code' => 'EMGT 305', 'program' => 'PhD EDUC'],
        ];

        $assignmentCount = 0;

        foreach ($assignments as $assignmentData) {
            // Find professor
            $professor = User::where('firstname', 'like', $assignmentData['professor'] . '%')
                ->orWhere('lastname', 'like', '%' . $assignmentData['professor'] . '%')
                ->first();

            // Find course
            $course = Course::where('course_code', $assignmentData['course_code'])
                ->whereHas('program', function($query) use ($assignmentData) {
                    $query->where('program_code', $assignmentData['program']);
                })
                ->first();

            if ($professor && $course) {
                CourseAssignment::create([
                    'course_id' => $course->id,
                    'professor_id' => $professor->id,
                    'semester_id' => $semester->id,
                    'assignment_date' => now(),
                ]);
                $assignmentCount++;
            } else {
                $this->command->warn("Could not find professor or course for: {$assignmentData['professor']} - {$assignmentData['course_code']}");
            }
        }

        $this->command->info("Course assignments seeded successfully! Created {$assignmentCount} assignments.");
    }
}