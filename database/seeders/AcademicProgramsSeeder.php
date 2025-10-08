<?php
// database/seeders/AcademicProgramsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College;
use App\Models\Program;
use App\Models\Course;
use App\Models\CourseType;

class AcademicProgramsSeeder extends Seeder
{
    public function run(): void
    {
        // Create Course Types
        $courseTypes = [
            ['name' => 'Foundation Courses', 'description' => 'Basic foundational courses'],
            ['name' => 'Core Courses', 'description' => 'Core program requirements'],
            ['name' => 'Major Courses', 'description' => 'Major specialization courses'],
            ['name' => 'Specialty Courses', 'description' => 'Specialized field courses'],
            ['name' => 'Functional Courses', 'description' => 'Functional skill courses'],
            ['name' => 'Elective/Cognate Courses', 'description' => 'Elective or cognate courses'],
            ['name' => 'Specialization Courses', 'description' => 'Specialization track courses'],
            ['name' => 'Graduate Seminar', 'description' => 'Graduate seminar courses'],
            ['name' => 'Final Output', 'description' => 'Final output requirements'],
            ['name' => 'Practicum', 'description' => 'Practical training courses'],
            ['name' => 'Thesis', 'description' => 'Thesis courses'],
            ['name' => 'Master\'s Thesis', 'description' => 'Master\'s thesis courses'],
            ['name' => 'Dissertation', 'description' => 'Doctoral dissertation courses'],
        ];

        foreach ($courseTypes as $type) {
            CourseType::create($type);
        }

        // Create Colleges
        $colleges = [
            ['name' => 'College of Agriculture, Food, and Natural Resources', 'acronym' => 'CAFNER'],
            ['name' => 'College of Education', 'acronym' => 'CED'],
            ['name' => 'College of Economics, Management, and Development Studies', 'acronym' => 'CEMDS'],
            ['name' => 'College of Arts and Sciences', 'acronym' => 'CAS'],
            ['name' => 'College of Engineering and Information Technology', 'acronym' => 'CEIT'],
        ];

        $collegeModels = [];
        foreach ($colleges as $college) {
            $collegeModels[$college['acronym']] = College::create($college);
        }

        // Programs and Courses Data - FIXED: All course codes are now unique
        $programsData = [
            // Doctor of Philosophy in Agriculture - Animal Science
            [
                'program_code' => 'PHD-AGRI-AS',
                'program_name' => 'Doctor of Philosophy in Agriculture - Animal Science',
                'college_acronym' => 'CAFNER',
                'courses' => [
                    // Core Courses
                    ['AGRI 301', 'Experimental Design II', 'Core Courses'],
                    ['AGRI 302', 'Metabolism', 'Core Courses'],
                    ['AGRI 303', 'Advanced Biotechnology', 'Core Courses'],
                    
                    // Major Courses
                    ['ANSC 305', 'Reproductive Physiology', 'Major Courses'],
                    ['ANSC 310', 'Digestive Physiology of Livestock and Poultry', 'Major Courses'],
                    ['ANSC 315', 'Advanced Animal Breeding', 'Major Courses'],
                    ['ANSC 320', 'Animal Genetics and Molecular Biology', 'Major Courses'],
                    ['ANSC 390', 'Special Topic', 'Major Courses'],
                    ['ANSC 395', 'Special Problem', 'Major Courses'],
                    ['ANSC 399', 'Graduate Seminar', 'Graduate Seminar'],
                    
                    // Elective/Cognate Courses
                    ['ANSC 321', 'Advanced Poultry Nutrition and Feeding', 'Elective/Cognate Courses'],
                    ['ANSC 326', 'Advanced Swine Nutrition and Feeding', 'Elective/Cognate Courses'],
                    ['ANSC 331', 'Advanced Ruminant Feeding and Management', 'Elective/Cognate Courses'],
                    ['ANSC 336', 'Endocrinology', 'Elective/Cognate Courses'],
                    ['ANSC 341', 'Immunology', 'Elective/Cognate Courses'],
                    
                    // Dissertation
                    ['ANSC 400', 'Dissertation Writing', 'Dissertation'],
                ]
            ],

            // Doctor of Philosophy in Education - Educational Management
            [
                'program_code' => 'PHD-EDUC-EM',
                'program_name' => 'Doctor of Philosophy in Education - Educational Management',
                'college_acronym' => 'CED',
                'courses' => [
                    // Core Courses
                    ['EDUC 301', 'Advance and Contemporary Philosophies, Trends and Issues in Education', 'Core Courses'],
                    ['EDUC 302', 'Moral Philosophy', 'Core Courses'],
                    ['EDUC 303', 'Comparative Educational Systems and Internationalization of Education', 'Core Courses'],
                    ['EDUC 304', 'Advance Educational Statistics', 'Core Courses'],
                    ['EDUC 305', 'Methods of Quantitative Research', 'Core Courses'],
                    ['EDUC 306', 'Methods of Qualitative Research', 'Core Courses'],
                    
                    // Major Courses
                    ['EMGT 305', 'Advanced Educational Planning including Planning of Physical Resources', 'Major Courses'],
                    ['EMGT 310', 'Strategic Human Resource Management', 'Major Courses'],
                    ['EMGT 315', 'Management of Institutional Programs', 'Major Courses'],
                    ['EMGT 320', 'Advanced Organizational Behavior and Development', 'Major Courses'],
                    ['EMGT 325', 'Legal Aspects in Educational Management Including Ethics', 'Major Courses'],
                    ['EMGT 330', 'Organizational Finance and Control', 'Major Courses'],
                    ['EMGT 335', 'Educational Technology and Innovative Systems in Administration and Supervision', 'Major Courses'],
                    ['EMGT 340', 'Educational Testing and Measurement and Program Evaluation', 'Major Courses'],
                    
                    // Elective/Cognate Courses - FIXED: Unique course codes
                    ['EDUC-EM-ELEC1', 'Elective Course 1 - Educational Management', 'Elective/Cognate Courses'],
                    ['EDUC-EM-ELEC2', 'Elective Course 2 - Educational Management', 'Elective/Cognate Courses'],
                    
                    // Dissertation
                    ['EDUC 400', 'Dissertation', 'Dissertation'],
                ]
            ],

            // Master of Science in Agriculture - Crop Science
            [
                'program_code' => 'MS-AGRI-CS',
                'program_name' => 'Master of Science in Agriculture - Crop Science',
                'college_acronym' => 'CAFNER',
                'courses' => [
                    // Core Courses
                    ['AGRI 201', 'Experimental Design I', 'Core Courses'],
                    ['AGRI 202', 'Agricultural Biochemistry I', 'Core Courses'],
                    ['AGRI 203', 'Research in Agriculture', 'Core Courses'],
                    
                    // Major Courses
                    ['CRSC 210', 'Plant Nutrition', 'Major Courses'],
                    ['CRSC 215', 'Crop Physiology', 'Major Courses'],
                    ['CRSC 220', 'Plant Genetics and Breeding', 'Major Courses'],
                    ['CRSC 290', 'Special Topic', 'Major Courses'],
                    ['CRSC 295', 'Special Problem', 'Major Courses'],
                    ['CRSC 299', 'Graduate Seminar', 'Graduate Seminar'],
                    
                    // Elective/Cognate Courses
                    ['CRSC 221', 'Agricultural Chemistry', 'Elective/Cognate Courses'],
                    ['CRSC 226', 'Advances in Field Crop Production', 'Elective/Cognate Courses'],
                    ['CRSC 231', 'Advances in Vegetable Crop Production', 'Elective/Cognate Courses'],
                    ['CRSC 236', 'Advances in Fruit and Plantation Crop Production', 'Elective/Cognate Courses'],
                    ['CRSC 241', 'Advances in Ornamental Crop Production', 'Elective/Cognate Courses'],
                    ['CRSC 246', 'Advances in Plant Propagation and Nursery Management', 'Elective/Cognate Courses'],
                    ['CRSC 251', 'Agricultural Biotechnology', 'Elective/Cognate Courses'],
                    
                    // Master's Thesis
                    ['CRSC 300', 'Master\'s Thesis', 'Master\'s Thesis'],
                ]
            ],

            // Master of Arts in Education - Educational Management
            [
                'program_code' => 'MA-EDUC-EM',
                'program_name' => 'Master of Arts in Education - Educational Management',
                'college_acronym' => 'CED',
                'courses' => [
                    // Core Courses
                    ['EDUC 201', 'Philosophy Foundations of Education', 'Core Courses'],
                    ['EDUC 202', 'Methods of Educational Research', 'Core Courses'],
                    ['EDUC 203', 'Statistical Methods Applied to Education', 'Core Courses'],
                    
                    // Major Courses
                    ['EMGT 205', 'Foundation of Management', 'Major Courses'],
                    ['EMGT 210', 'Curriculum Development', 'Major Courses'],
                    ['EMGT 215', 'Human Resource Management in Organization', 'Major Courses'],
                    ['EMGT 220', 'Fiscal Planning and Management', 'Major Courses'],
                    ['EMGT 225', 'School Legislation', 'Major Courses'],
                    
                    // Elective/Cognate Courses - FIXED: Unique course codes
                    ['MA-EDUC-ELEC1', 'Elective Course 1 - MA Education', 'Elective/Cognate Courses'],
                    ['MA-EDUC-ELEC2', 'Elective Course 2 - MA Education', 'Elective/Cognate Courses'],
                    
                    // Thesis
                    ['EDUC 300', 'Thesis Writing', 'Thesis'],
                ]
            ],

            // Master of Science in Biology
            [
                'program_code' => 'MS-BIO',
                'program_name' => 'Master of Science in Biology',
                'college_acronym' => 'CAS',
                'courses' => [
                    // Core Courses
                    ['BIOL 203', 'Advanced Physiology', 'Core Courses'],
                    ['BIOL 204', 'Advanced Genetics', 'Core Courses'],
                    ['BIOL 205', 'Advanced Cell and Molecular Biology', 'Core Courses'],
                    ['BIOL 206', 'Advanced Microbiology', 'Core Courses'],
                    ['BIOL 207', 'Advanced Ecology', 'Core Courses'],
                    ['BIOL 208', 'Advanced Systematics', 'Core Courses'],
                    ['BIOL 209', 'Advanced Developmental Biology', 'Core Courses'],
                    
                    // Specialty Courses
                    ['GENE 210', 'Population and Quantitative Genetics', 'Specialty Courses'],
                    ['GENE 215', 'Cytogenetics', 'Specialty Courses'],
                    ['GENE 220', 'Human Genetics', 'Specialty Courses'],
                    ['BIOL 210', 'Bioinformatics', 'Specialty Courses'],
                    ['BIOL 215', 'Biotechnology Concepts and Applications', 'Specialty Courses'],
                    ['BIOL 220', 'Advanced Immunology', 'Specialty Courses'],
                    ['BIOL 225', 'Advanced Parasitology', 'Specialty Courses'],
                    ['BIOL 230', 'Biodiversity Conservation and Management', 'Specialty Courses'],
                    ['MICR 210', 'Advanced Bacteriology', 'Specialty Courses'],
                    ['MICR 215', 'Advanced Medical Microbiology', 'Specialty Courses'],
                    ['MICR 220', 'Advanced Mycology', 'Specialty Courses'],
                    ['MICR 225', 'Advanced Microbial Physiology', 'Specialty Courses'],
                    ['MICR 230', 'Advanced Microbial Genetics', 'Specialty Courses'],
                    ['MICR 235', 'General and Advanced Molecular Virology', 'Specialty Courses'],
                    
                    // Functional Courses
                    ['BIOL 201', 'Biostatistics', 'Functional Courses'],
                    ['BIOL 202', 'Research Methods in Biology', 'Functional Courses'],
                    
                    // Graduate Seminar & Thesis
                    ['BIOL 299', 'Graduate Seminar', 'Graduate Seminar'],
                    ['BIOL 300', 'Graduate Thesis', 'Thesis'],
                ]
            ],

            // Master in Business Administration
            [
                'program_code' => 'MBA',
                'program_name' => 'Master in Business Administration',
                'college_acronym' => 'CEMDS',
                'courses' => [
                    // Core Courses
                    ['BA 201', 'Social Responsibility and Good Governance', 'Core Courses'],
                    ['BA 202', 'Statistics with Computer Application', 'Core Courses'],
                    ['BA 203', 'Business Research', 'Core Courses'],
                    
                    // Functional Courses
                    ['BA 210', 'Human Resource Management', 'Functional Courses'],
                    ['BA 215', 'Production and Operations Management', 'Functional Courses'],
                    ['BA 220', 'Marketing Management', 'Functional Courses'],
                    ['BA 225', 'Financial Management', 'Functional Courses'],
                    ['BA 230', 'Organizational Behavior', 'Functional Courses'],
                    
                    // Elective/Cognate Courses - FIXED: Unique course codes
                    ['MBA-ELEC1', 'Philippine Business Environment', 'Elective/Cognate Courses'],
                    ['MBA-ELEC2', 'Agricultural Entrepreneurship', 'Elective/Cognate Courses'],
                    ['MBA-ELEC3', 'Managerial Economics', 'Elective/Cognate Courses'],
                    
                    // Specialization Courses & Thesis
                    ['BA 290', 'Special Research Project', 'Specialization Courses'],
                    ['BA 300', 'Master\'s Thesis', 'Master\'s Thesis'],
                ]
            ],

            // Master of Science in Information Technology
            [
                'program_code' => 'MS-IT',
                'program_name' => 'Master of Science in Information Technology',
                'college_acronym' => 'CEIT',
                'courses' => [
                    // Core Courses
                    ['MSIT 201', 'Advanced Operating System and Networking', 'Core Courses'],
                    ['MSIT 202', 'Advanced Database Systems', 'Core Courses'],
                    ['MSIT 203', 'Information System and Theory', 'Core Courses'],
                    ['MSIT 204', 'Advanced Programming', 'Core Courses'],
                    
                    // Major Courses
                    ['MSIT 250', 'Technological Trends in Computing with IT Seminar', 'Major Courses'],
                    ['MSIT 255', 'IT Service Management', 'Major Courses'],
                    ['MSIT 260', 'Risk Management and Business Continuity Plan', 'Major Courses'],
                    ['MSIT 265', 'Business Intelligence Analytics', 'Major Courses'],
                    ['MSIT 270', 'Methods of Research', 'Major Courses'],
                    ['MSIT 275', 'Enterprise Architecture', 'Major Courses'],
                    
                    // Thesis
                    ['MSIT 300', 'Graduate\'s Thesis', 'Thesis'],
                ]
            ],

            // Master in Public Administration
            [
                'program_code' => 'MPA',
                'program_name' => 'Master in Public Administration',
                'college_acronym' => 'CEMDS',
                'courses' => [
                    // Core Courses
                    ['PADM 201', 'Principles and Theories of Public Administration', 'Core Courses'],
                    ['PADM 202', 'Public Personnel Administration', 'Core Courses'],
                    ['PADM 203', 'Public Administration and Political Process', 'Core Courses'],
                    ['PADM 204', 'Research Methods in Public Administration', 'Core Courses'],
                    
                    // Major Courses
                    ['PADM 205', 'Development Administration', 'Major Courses'],
                    ['PADM 210', 'Public Policy and Program Administration', 'Major Courses'],
                    ['PADM 215', 'Public Financial Administration', 'Major Courses'],
                    ['PADM 220', 'Ethics in Public Administration', 'Major Courses'],
                    
                    // Elective/Cognate Courses - FIXED: Unique course codes
                    ['MPA-COG1', 'Cognate Course 1 - Public Administration', 'Elective/Cognate Courses'],
                    ['MPA-COG2', 'Cognate Course 2 - Public Administration', 'Elective/Cognate Courses'],
                    
                    // Thesis
                    ['PADM 300', 'Thesis Writing', 'Thesis'],
                ]
            ],

            // Master in Management
            [
                'program_code' => 'MM',
                'program_name' => 'Master in Management',
                'college_acronym' => 'CEMDS',
                'courses' => [
                    // Core Courses
                    ['MNGT 201', 'Foundation of Management', 'Core Courses'],
                    ['MNGT 202', 'Statistics with Computer Application', 'Core Courses'],
                    ['MNGT 203', 'Methods of Research', 'Core Courses'],
                    
                    // Major Courses
                    ['MNGT 205', 'Human Resource Management', 'Major Courses'],
                    ['MNGT 210', 'Organizational Theory and Behavior', 'Major Courses'],
                    ['MNGT 215', 'Learning and Development of Human Resource', 'Major Courses'],
                    ['MNGT 220', 'Ethical Dimensions of Management', 'Major Courses'],
                    ['MNGT 225', 'Compensation Administration', 'Major Courses'],
                    
                    // Elective/Cognate Courses - FIXED: Unique course codes
                    ['MM-ELEC1', 'Labor Relations', 'Elective/Cognate Courses'],
                    ['MM-ELEC2', 'International Human Resource Management', 'Elective/Cognate Courses'],
                    
                    // Thesis
                    ['MNGT 300', 'Thesis Writing', 'Thesis'],
                ]
            ],
        ];

        // Create Programs and Courses
        foreach ($programsData as $programData) {
            $program = Program::create([
                'program_code' => $programData['program_code'],
                'program_name' => $programData['program_name'],
                'description' => $programData['program_name'],
                'college_id' => $collegeModels[$programData['college_acronym']]->id,
            ]);

            foreach ($programData['courses'] as $courseData) {
                $courseType = CourseType::where('name', $courseData[2])->first();
                
                Course::create([
                    'course_code' => $courseData[0],
                    'course_name' => $courseData[1],
                    'description' => $courseData[1],
                    'program_id' => $program->id,
                    'course_type_id' => $courseType->id,
                ]);
            }
        }

        $this->command->info('Academic programs, courses, and course types seeded successfully!');
        $this->command->info('Colleges created: ' . count($collegeModels));
        $this->command->info('Programs created: ' . count($programsData));
        $this->command->info('Course types created: ' . count($courseTypes));
    }
}