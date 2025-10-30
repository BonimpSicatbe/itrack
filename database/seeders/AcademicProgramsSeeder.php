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
            ['name' => 'Foundation Course', 'description' => 'Basic foundational courses'],
            ['name' => 'Core Course', 'description' => 'Core program requirements'],
            ['name' => 'Major Course', 'description' => 'Major specialization courses'],
            ['name' => 'Specialty Course', 'description' => 'Specialized field courses'],
            ['name' => 'Functional Course', 'description' => 'Functional skill courses'],
            ['name' => 'Elective/Cognate Course', 'description' => 'Elective or cognate courses'],
            ['name' => 'Specialization Course', 'description' => 'Specialization track courses'],
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
            ['name' => 'College of Agriculture, Food, Environment and Natural Resources', 'acronym' => 'CAFENR'],
            ['name' => 'College of Education', 'acronym' => 'CED'],
            ['name' => 'College of Economics, Management, and Development Studies', 'acronym' => 'CEMDS'],
            ['name' => 'College of Arts and Sciences', 'acronym' => 'CAS'],
            ['name' => 'College of Engineering and Information Technology', 'acronym' => 'CEIT'],
        ];

        $collegeModels = [];
        foreach ($colleges as $college) {
            $collegeModels[$college['acronym']] = College::create($college);
        }

        // Programs and Course Data - FIXED: All course codes are now unique
        $programsData = [
            // Doctor of Philosophy in Agriculture
            [
                'program_code' => 'PhD AGRI',
                'program_name' => 'Doctor of Philosophy in Agriculture',
                'college_acronym' => 'CAFENR',
                'courses' => [
                    // Core Course
                    ['AGRI 301', 'Experimental Design II', 'Core Course'],
                    ['AGRI 302', 'Metabolism', 'Core Course'],
                    ['AGRI 303', 'Advanced Biotechnology', 'Core Course'],

                    // Major Course
                    ['ANSC 305', 'Reproductive Physiology', 'Major Course'],
                    ['ANSC 310', 'Digestive Physiology of Livestock and Poultry', 'Major Course'],
                    ['ANSC 315', 'Advanced Animal Breeding', 'Major Course'],
                    ['ANSC 320', 'Animal Genetics and Molecular Biology', 'Major Course'],
                    ['ANSC 390', 'Special Topic', 'Major Course'],
                    ['ANSC 395', 'Special Problem', 'Major Course'],
                    ['ANSC 399', 'Graduate Seminar', 'Graduate Seminar'],

                    ['CRSC 305', 'Soil- Plant Water Relationship', 'Major course'],
                    ['CRSC 310', 'Advanced Plant Breeding', 'Major course'],
                    ['CRSC 315', 'Advanced Plant Nutrient Management', 'Major course'],
                    ['CRSC 320', 'Molecular Plant Biology', 'Major course'],
                    ['CRSC 390', 'Special Topic', 'Major course'],
                    ['CRSC 395', 'Special Problem', 'Major course'],
                    ['CRSC 399', 'Graduate Seminar', 'Major course'],
                    
                    ['AECO 320', 'Economic Analysis and Planning of Agricultural Projects', 'Major Course'],
                    ['FSYS 305', 'Current Issues and Trends in Agriculture', 'Major Course'],
                    ['FSYS 310', 'Climate Adaptation in Agriculture', 'Major Course'],
                    ['FSYS 315', 'International Agriculture and Development', 'Major Course'],
                    ['FSYS 390', 'Special Topic', 'Major Course'],
                    ['FSYS 395', 'Special Problem', 'Major Course'],
                    ['FSYS 399', 'Graduate Seminar', 'Major Course'],

                    // Elective/Cognate Course
                    ['ANSC 321', 'Advanced Poultry Nutrition and Feeding', 'Elective/Cognate Course'],
                    ['ANSC 326', 'Advanced Swine Nutrition and Feeding', 'Elective/Cognate Course'],
                    ['ANSC 331', 'Advanced Ruminant Feeding and Management', 'Elective/Cognate Course'],
                    ['ANSC 336', 'Endocrinology', 'Elective/Cognate Course'],
                    ['ANSC 341', 'Immunology', 'Elective/Cognate Course'],

                    ['CRSC 320', 'Integrated Crop Management', 'Elective/Cognate Course'],
                    ['CRSC 321', 'Advanced Seed Technology', 'Elective/Cognate Course'],
                    ['CRSC 331', 'Environmental Protection', 'Elective/Cognate Course'],
                    ['CRSC 336', 'Applied Precision Crop Production', 'Elective/Cognate Course'],
                    ['CRSC 341', 'Plant Tissue Culture and Embryogenesis', 'Elective/Cognate Course'],
                    ['CRSC 346', 'Reproductive Crop Physiology', 'Elective/Cognate Course'],
                    
                    // Dissertation
                    ['ANSC 400', 'Dissertation Writing', 'Dissertation'],
                ]
            ],

            // Doctor of Philosophy in Education
            [
                'program_code' => 'PhD EDUC',
                'program_name' => 'Doctor of Philosophy in Education',
                'college_acronym' => 'CED',
                'courses' => [
                    // Core Course
                    ['EDUC 301', 'Advance and Contemporary Philosophies, Trends and Issues in Education', 'Core Course'],
                    ['EDUC 302', 'Moral Philosophy', 'Core Course'],
                    ['EDUC 303', 'Comparative Educational Systems and Internationalization of Education', 'Core Course'],
                    ['EDUC 304', 'Advance Educational Statistics', 'Core Course'],
                    ['EDUC 305', 'Methods of Quantitative Research', 'Core Course'],
                    ['EDUC 306', 'Methods of Qualitative Research', 'Core Course'],
                    
                    // Major Course
                    ['EMGT 305', 'Advanced Educational Planning including Planning of Physical Resources', 'Major Course'],
                    ['EMGT 310', 'Strategic Human Resource Management', 'Major Course'],
                    ['EMGT 315', 'Management of Institutional Programs', 'Major Course'],
                    ['EMGT 320', 'Advanced Organizational Behavior and Development', 'Major Course'],
                    ['EMGT 325', 'Legal Aspects in Educational Management Including Ethics', 'Major Course'],
                    ['EMGT 330', 'Organizational Finance and Control', 'Major Course'],
                    ['EMGT 335', 'Educational Technology and Innovative Systems in Administration and Supervision', 'Major Course'],
                    ['EMGT 340', 'Educational Testing and Measurement and Program Evaluation', 'Major Course'],
                    
                    // Dissertation
                    ['EDUC 400', 'Dissertation', 'Dissertation'],
                ]
            ],

            // Doctor of Philosophy in Management
            [
                'program_code' => 'PhD MNGT',
                'program_name' => 'Doctor of Philosophy in Management',
                'college_acronym' => 'CEMDS',
                'courses' => [
                    // Core Course
                    ['MNGT 301', 'Advanced Statistics with Computer Application', 'Core Course'],
                    ['MNGT 302', 'Advanced Business Research', 'Core Course'],
                    ['MNGT 303', 'Advances Good Governance and Corporate Social Responsibility', 'Core Course'],
                    ['MNGT 304', 'Transformational Leadership', 'Core Course'],

                    // Major Course
                    ['MNGT 350', 'Global Marketing Management', 'Major Course'],
                    ['MNGT 355', 'Crisis and Risk Management', 'Major Course'],
                    ['MNGT 360', 'Advanced Operations Management', 'Major Course'],
                    ['MNGT 365', 'Management and Organizational Behavior', 'Major Course'],
                    ['MNGT 370', 'Performance Management', 'Major Course'],
                    ['MNGT 375', 'International Human Resource Management', 'Major Course'],

                    // Dissertation
                    ['MNGT 400', 'Dissertation Writing', 'Dissertation'],
                ]
            ],

            // Master in Business Administration
            [
                'program_code' => 'MBA',
                'program_name' => 'Master in Business Administration',
                'college_acronym' => 'CEMDS',
                'courses' => [
                    // Core Course
                    ['BA 201', 'Social Responsibility and Good Governance', 'Core Course'],
                    ['BA 202', 'Statistics with Computer Application', 'Core Course'],
                    ['BA 203', 'Business Research', 'Core Course'],
                    
                    // Functional Course
                    ['BA 210', 'Human Resource Management', 'Functional Course'],
                    ['BA 215', 'Production and Operations Management', 'Functional Course'],
                    ['BA 220', 'Marketing Management', 'Functional Course'],
                    ['BA 225', 'Financial Management', 'Functional Course'],
                    ['BA 230', 'Organizational Behavior', 'Functional Course'],
                    
                    // Elective/Cognate Course 
                    ['BA 235', 'Philippine Business Environment', 'Elective/Cognate Course'],
                    ['BA 240', 'Agricultural Entrepreneurship', 'Elective/Cognate Course'],
                    ['BA 245', 'Managerial Economics', 'Elective/Cognate Course'],

                    // Specialization Course & Thesis
                    ['BA 290', 'Special Research Project', 'Specialization Course'],
                    ['BA 300', 'Master\'s Thesis', 'Specialization Course'],
                ]
            ],

            // Master of Arts in Education
            [
                'program_code' => 'MAEd',
                'program_name' => 'Master of Arts in Education',
                'college_acronym' => 'CED',
                'courses' => [
                    // Core Course
                    ['EDUC 201', 'Philosophy Foundations of Education', 'Core Course'],
                    ['EDUC 202', 'Methods of Educational Research', 'Core Course'],
                    ['EDUC 203', 'Statistical Methods Applied to Education', 'Core Course'],
                    
                    // Major Course
                    // EDUCATIONAL MANAGEMENT
                    ['EMGT 205', 'Foundation of Management', 'Major Course'],
                    ['EMGT 210', 'Curriculum Development', 'Major Course'],
                    ['EMGT 215', 'Human Resource Management in Organization', 'Major Course'],
                    ['EMGT 220', 'Fiscal Planning and Management', 'Major Course'],
                    ['EMGT 225', 'School Legislation', 'Major Course'],

                    // CURRICULUM AND INSTRUCTION
                    ['CURR 205', 'Theories and Dynamics of Curriculum and Instruction', 'Major Course'],
                    ['CURR 210', 'Methods of Learning and Instruction', 'Major Course'],
                    ['CURR 215', 'Curriculum Development', 'Major Course'],
                    ['CURR 220', 'Instructional Supervision', 'Major Course'],
                    ['CURR 225', 'Curriculum Evaluation Models', 'Major Course'],

                    // GUIDANCE AND COUNSELING
                    ['GUID 205', 'Foundations of Guidance and Counseling', 'Major Course'],
                    ['GUID 210', 'Theories Techniques and Practices of Counseling', 'Major Course'],
                    ['GUID 215', 'Organization, Administration and Supervision of Guidance Program', 'Major Course'],
                    ['GUID 220', 'Advanced Psychometrics', 'Major Course'],
                    ['GUID 225', 'Career Development and Counseling', 'Major Course'],
                    ['GUID 225', 'Group Process', 'Major Course'],

                    // MATHEMATICS
                    ['MATH 205', 'Set Theory and Logic', 'Major Course'],
                    ['MATH 210', 'Number Theory', 'Major Course'],
                    ['MATH 215', 'Linear Algebra', 'Major Course'],
                    ['MATH 220', 'Modern Geometry', 'Major Course'],
                    ['MATH 225', 'Instructional Planning and Procedures for Mathematics', 'Major Course'],

                    // BIOLOGY
                    ['BIOL 205', 'Cell Biology', 'Major Course'],
                    ['BIOL 210', 'Ecology', 'Major Course'],
                    ['BIOL 215', 'Genetics', 'Major Course'],
                    ['BIOL 220', 'Biotechnology', 'Major Course'],
                    ['BIOL 225', 'Microbiology for Teachers', 'Major Course'],

                    // CHEMISTRY
                    ['CHEM 205', 'Advanced Analytical Chemistry', 'Major Course'],
                    ['CHEM 210', 'Advanced Organic Chemistry', 'Major Course'],
                    ['CHEM 215', 'Advanced Biochemistry', 'Major Course'],
                    ['CHEM 220', 'Environmental Chemistry', 'Major Course'],
                    ['CHEM 225', 'Biotechnology', 'Major Course'],

                    // ELEMENTARY EDUCATION
                    ['ELED 205', 'Instructional Procedures for Elementary Education', 'Major Course'],
                    ['ELED 210', 'Selection and Utilization of Instructional Media for Elementary Education', 'Major Course'],
                    ['ELED 215', 'Guidance and Counseling for Elementary Education', 'Major Course'],
                    ['ELED 220', 'Inclusive and Special Needs Education', 'Major Course'],
                    ['ELED 225', 'Child Development and Assessment', 'Major Course'],

                    // SECONDARY EDUCATION
                    ['SEED 205', 'Instructional Procedures for Secondary Education', 'Major Course'],
                    ['SEED 210', 'Selection and Utilization of Instructional Media for Secondary Education', 'Major Course'],
                    ['SEED 215', 'Guidance and Counseling for Secondary Education', 'Major Course'],
                    ['SEED 220', 'Inclusive and Special Needs Education', 'Major Course'],
                    ['SEED 225', 'Curriculum Development', 'Major Course'],
                    
                    // Elective/Cognate Course
                    ['GUID 301', 'Seminar on Behavior Problems', 'Elective/Cognate Course'],
                    ['GUID 316', 'Multicultural Counseling', 'Elective/Cognate Course'],
                    
                    // Master's Thesis
                    ['EDUC 300', 'Thesis Writing', 'Master\'s Thesis'],
                ]
            ],

            // Master of Engineering
            [
                'program_code' => 'MEng',
                'program_name' => 'Master of Engineering',
                'college_acronym' => 'CEIT',
                'courses' => [
                    // Foundation Course
                    ['CoS 23', 'Computer Programming', 'Foundation Course'],
                    ['STAT 101', 'Experimental Design', 'Foundation Course'],

                    // Core Course
                    ['AENG 201', 'Advanced Engineering Mathematics', 'Core Course'],
                    ['MENG 201', 'Numerical Methods', 'Core Course'],
                    ['MENG 202', 'Engineering Production Management', 'Core Course'],
                    ['MENG 203', 'Computer-Aided Design with Finite Element', 'Core Course'],
                    ['MENG 204', 'Environment, Energy and Technology Management', 'Core Course'],
                    ['MENG 205', 'Engineering Materials', 'Core Course'],
                    ['MENG 206', 'Optimization Techniques', 'Core Course'],
                    ['MENG 207', 'Research Methods', 'Core Course'],
                    ['MENG 208', 'Renewable Energy', 'Core Course'],

                    // Major Course
                    // STRUCTURAL ENGINEERING
                    ['CE 201', 'Advanced Structural Analysis', 'Major Course'],
                    ['CE 202', 'Advanced Structural Steel Design', 'Major Course'],
                    ['CE 203', 'Earthquake Engineering', 'Major Course'],
                    ['CE 204', 'Advanced Reinforced Concrete Design', 'Major Course'],
                    ['CE 205', 'Theory of Plates and Shell', 'Major Course'],
                    ['CE 206', 'Plastic Structural Analysis and Design', 'Major Course'],

                    // CONSTRUCTION MANAGEMENT
                    ['CE 207', 'Construction Estimates', 'Major Course'],
                    ['CE 208', 'Construction Methods', 'Major Course'],
                    ['CE 209', 'Project Management', 'Major Course'],
                    ['CE 210', 'Works Engineering', 'Major Course'],
                    ['CE 211', 'Special Topics in Construction Mngl', 'Major Course'],

                    // CIVIL ENGINEERING (GENERAL)
                    ['CE 212', 'Transportation Engineering & Mngl', 'Major Course'],
                    ['CE 213', 'Geotechnical Testing & Instrumentation', 'Major Course'],
                    ['CE 214', 'Hydraulics Engineering', 'Major Course'],
                    ['CE 215', 'Foundation Engineering', 'Major Course'],
                    ['CE 216', 'Regional Development Planning', 'Major Course'],
                    ['CE 217', 'Geographical Information System (GIS) for Engineers', 'Major Course'],

                    // COMPUTER ENGINEERING
                    ['CoE 201', 'Switching Theory', 'Major Course'],
                    ['CoE 202', 'Robotics and Automation', 'Major Course'],
                    ['CoE 203', 'Neural Networks', 'Major Course'],
                    ['CoE 204', 'Computer Architecture', 'Major Course'],
                    ['CoE 205', 'Artificial Intelligence', 'Major Course'],
                    ['CoE 206', 'Microprocessors and Microcomputers', 'Major Course'],
                    ['CoE 207', 'Software Engineering', 'Major Course'],
                    ['CoE 208', 'Digital Feedback Control System', 'Major Course'],
                    ['CoE 209', 'Operating Systems', 'Major Course'],
                    ['CoE 210', 'Advanced Computer Organization', 'Major Course'],

                    // WATER ENGINEERING AND MANAGEMENT
                    ['WEM 201', 'Watershed Hydrology', 'Major Course'],
                    ['WEM 202', 'Water Resources Systems', 'Major Course'],
                    ['WEM 204', 'Irrigation and Drainage Engineering', 'Major Course'],
                    ['WEM 205', 'Irrigation and Drainage Systems Mngt', 'Major Course'],
                    ['WEM 225', 'Coastal Zone Management', 'Major Course'],
                    ['WEM 226', 'Water Supply and Sanitation', 'Major Course'],
                    ['WEM 229', 'Groundwater Development & Mngt', 'Major Course'],
                    ['WEM 230', 'Integrated Water Resources Mngt', 'Major Course'],
                    ['WEM 234', 'EIA and GIS Applications in Water Res', 'Major Course'],

                    // Practicum
                    ['MENG 300', 'Practicum', 'Practicum'],
                ]
            ],

            // Master of Management
            [
                'program_code' => 'MMngt',
                'program_name' => 'Master of Management',
                'college_acronym' => 'CEMDS',
                'courses' => [
                    // Core Course
                    ['MNGT 201', 'Foundation of Management', 'Core Course'],
                    ['MNGT 202', 'Statistics with Computer Application', 'Core Course'],
                    ['MNGT 203', 'Methods of Research', 'Core Course'],
                    
                    // Major Course
                    ['MNGT 205', 'Human Resource Management', 'Major Course'],
                    ['MNGT 210', 'Organizational Theory and Behavior', 'Major Course'],
                    ['MNGT 215', 'Learning and Development of Human Resource', 'Major Course'],
                    ['MNGT 220', 'Ethical Dimensions of Management', 'Major Course'],
                    ['MNGT 225', 'Compensation Administration', 'Major Course'],
                    
                    // Elective/Cognate Course
                    ['MNGT 230', 'Labor Relations', 'Elective/Cognate Course'],
                    ['MNGT 235', 'International Human Resource Management', 'Elective/Cognate Course'],

                    // Master's Thesis
                    ['MNGT 300', 'Thesis Writing', 'Master\'s Thesis'],
                ]
            ],

            // Master in Public Administration
            [
                'program_code' => 'MPA',
                'program_name' => 'Master in Public Administration',
                'college_acronym' => 'CEMDS',
                'courses' => [
                    // Core Course
                    ['PADM 201', 'Principles and Theories of Public Administration', 'Core Course'],
                    ['PADM 202', 'Public Personnel Administration', 'Core Course'],
                    ['PADM 203', 'Public Administration and Political Process', 'Core Course'],
                    ['PADM 204', 'Research Methods in Public Administration', 'Core Course'],

                    // Major Course
                    ['PADM 205', 'Development Administration', 'Major Course'],
                    ['PADM 210', 'Public Policy and Program Administration', 'Major Course'],
                    ['PADM 215', 'Public Financial Administration', 'Major Course'],
                    ['PADM 220', 'Ethics in Public Administration', 'Major Course'],

                    // Master's Thesis
                    ['PADM 300', 'Thesis Writing', 'Master\'s Thesis']
                ]
            ],

            // Master of Science in Agriculture
            [
                'program_code' => 'MS Agri',
                'program_name' => 'Master of Science in Agriculture',
                'college_acronym' => 'CAFENR',
                'courses' => [
                    // Core Course
                    ['AGRI 201', 'Experimental Design I', 'Core Course'],
                    ['AGRI 202', 'Agricultural Biochemistry I', 'Core Course'],
                    ['AGRI 203', 'Research in Agriculture', 'Core Course'],
                    
                    // Major Course
                    ['CRPT 210', 'Advances in Pest Management', 'Major Course'],
                    ['CRPT 215', 'Pathogenesis', 'Major Course'],
                    ['CRPT 220', 'Virology and Nematology', 'Major Course'],
                    ['CRPT 290', 'Special Topic', 'Major Course'],
                    ['CRPT 295', 'Special Problem', 'Major Course'],
                    ['CRPT 299', 'Graduate Seminar', 'Major Course'],

                    ['CRSC 210', 'Plant Nutrition', 'Major Course'],
                    ['CRSC 215', 'Crop Physiology', 'Major Course'],
                    ['CRSC 220', 'Plant Genetics and Breeding', 'Major Course'],
                    ['CRSC 290', 'Special Topic', 'Major Course'],
                    ['CRSC 295', 'Special Problem', 'Major Course'],
                    ['CRSC 299', 'Graduate Seminar', 'Major Course'],

                    ['FSYS 210', 'Foundation Studies in Farming Systems', 'Major Course'],
                    ['FSYS 215', 'Poultry and Swine Production', 'Major Course'],
                    ['FSYS 220', 'Agronomic and Horticultural Crop Production', 'Major Course'],
                    ['FSYS 290', 'Special Topic', 'Major Course'],
                    ['FSYS 295', 'Special Problem', 'Major Course'],
                    ['FSYS 299', 'Graduate Seminar', 'Major Course'],
                    
                    // Elective/Cognate Course
                    ['CRPT 221', 'Advances in Ecology of Pests', 'Elective/Cognate Course'],
                    ['CRPT 226', 'Advances in Biological Control of pests', 'Elective/Cognate Course'],
                    ['CRPT 231', 'Host Plant Resistance', 'Elective/Cognate Course'],
                    ['CRPT 236', 'Taxonomy and Biology of Plant Pathogenic Fungi', 'Elective/Cognate Course'],
                    ['CRPT 241', 'Advances in Plant Virology', 'Elective/Cognate Course'],
                    ['CRPT 246', 'Advances in Weed Control', 'Elective/Cognate Course'],

                    ['CRSC 221', 'Agricultural Chemistry', 'Elective/Cognate Course'],
                    ['CRSC 226', 'Advances in Field Crop Production', 'Elective/Cognate Course'],
                    ['CRSC 231', 'Advances in Vegetable Crop Production', 'Elective/Cognate Course'],
                    ['CRSC 236', 'Advances in Fruit and Plantation Crop Production', 'Elective/Cognate Course'],
                    ['CRSC 241', 'Advances in Ornamental Crop Production', 'Elective/Cognate Course'],
                    ['CRSC 246', 'Advances in Plant Propagation and Nursery Management', 'Elective/Cognate Course'],
                    ['CRSC 251', 'Agricultural Biotechnology', 'Elective/Cognate Course'],

                    ['FSYS 221', 'Multiple Land Use Systems', 'Elective/Cognate Course'],
                    ['FSYS 226', 'Farming Systems Management', 'Elective/Cognate Course'],
                    ['FSYS 231', 'Urban Farming Systems', 'Elective/Cognate Course'],
                    ['FSYS 236', 'Environmental Management', 'Elective/Cognate Course'],
                    ['FSYS 241', 'Social Context of Farming Systems', 'Elective/Cognate Course'],
                    ['FSYS 246', 'Ecological Studies in Farming Systems', 'Elective/Cognate Course'],
                    
                    // Master's Thesis
                    ['CRPT 300', 'Master\'s Thesis', 'Master\'s Thesis'],
                    ['CRSC 300', 'Master\'s Thesis', 'Master\'s Thesis'],
                    ['FSYS 300', 'Master\'s Thesis', 'Master\'s Thesis'],
                ]
            ],

            // Master of Science in Biology
            [
                'program_code' => 'MS Bio',
                'program_name' => 'Master of Science in Biology',
                'college_acronym' => 'CAS',
                'courses' => [
                    // Core Course
                    ['BIOL 203', 'Advanced Physiology', 'Core Course'],
                    ['BIOL 204', 'Advanced Genetics', 'Core Course'],
                    ['BIOL 205', 'Advanced Cell and Molecular Biology', 'Core Course'],
                    ['BIOL 206', 'Advanced Microbiology', 'Core Course'],
                    ['BIOL 207', 'Advanced Ecology', 'Core Course'],
                    ['BIOL 208', 'Advanced Systematics', 'Core Course'],
                    ['BIOL 209', 'Advanced Developmental Biology', 'Core Course'],
                    
                    // Specialty Course
                    ['BIOL 210', 'Bioinformatics', 'Specialty Course'],
                    ['BIOL 215', 'Biotechnology Concepts and Applications', 'Specialty Course'],
                    ['BIOL 220', 'Advanced Immunology', 'Specialty Course'],
                    ['BIOL 225', 'Advanced Parasitology', 'Specialty Course'],
                    ['BIOL 230', 'Biodiversity Conservation and Management', 'Specialty Course'],
                    ['GENE 210', 'Population and Quantitative Genetics', 'Specialty Course'],
                    ['GENE 215', 'Cytogenetics', 'Specialty Course'],
                    ['GENE 220', 'Human Genetics', 'Specialty Course'],
                    ['MICR 210', 'Advanced Bacteriology', 'Specialty Course'],
                    ['MICR 215', 'Advanced Medical Microbiology', 'Specialty Course'],
                    ['MICR 220', 'Advanced Mycology', 'Specialty Course'],
                    ['MICR 225', 'Advanced Microbial Physiology', 'Specialty Course'],
                    ['MICR 230', 'Advanced Microbial Genetics', 'Specialty Course'],
                    ['MICR 235', 'General and Advanced Molecular Virology', 'Specialty Course'],
                    
                    // Elective/Cognate Course
                    ['BIOL 201', 'Biostatistics', 'Elective/Cognate Course'],
                    ['BIOL 202', 'Research Methods in Biology', 'Elective/Cognate Course'],
                    
                    // Graduate Seminar
                    ['BIOL 299', 'Graduate Seminar', 'Graduate Seminar'],

                    // Thesis
                    ['BIOL 300', 'Graduate Thesis', 'Thesis'],
                ]
            ],

            // Master of Science in Food Science
            [
                'program_code' => 'MS FoodSci',
                'program_name' => 'Master of Science in Food Science',
                'college_acronym' => 'CAFENR',
                'courses' => [
                    // Core Course
                    ['FS 201', 'Experimental Design', 'Core Course'],
                    ['FS 202', 'Food Engineering Fundamentals and Processes', 'Core Course'],
                    ['FS 203', 'Food Science Fundamentals', 'Core Course'],
                    ['FS 299', 'Graduate Seminar', 'Core Course'],

                    // Major Course
                    ['FS 205', 'Advanced Food Chemistry', 'Major Course'],
                    ['FS 210', 'Advanced Food Microbiology', 'Major Course'],
                    ['FS 215', 'Advanced Food Sensory Science', 'Major Course'],
                    ['FS 220', 'Advanced Food Analysis', 'Major Course'],
                    ['FS 225', 'Thermal Food Processing', 'Major Course'],
                    ['FS 230', 'Food Dehydration and Freezing', 'Major Course'],
                    ['FS 235', 'Industrial Food Fermentation', 'Major Course'],
                    ['FS 240', 'Tropical Fruits and Vegetable Processing', 'Major Course'],
                    ['FS 245', 'Meat and Dairy Technology', 'Major Course'],
                    ['FS 250', 'Industrial Crop Processing', 'Major Course'],
                    ['FS 255', 'Food Packaging', 'Major Course'],
                    ['FS 290', 'Special Problem', 'Major Course'],

                    // Master's Thesis
                    ['FS 300', 'Master\'s Thesis', 'Master\'s Thesis']
                ]
            ],

            // Master of Science in Hospitality Management
            [
                'program_code' => 'MSHM',
                'program_name' => 'Master of Science in Hospitality Management',
                'college_acronym' => 'CED',
                'courses' => [
                    // Core Course
                    ['MSHM 201', 'Advanced Research and Statistical Methods for Hospitality Industry', 'Core Course'],
                    ['MSHM 202', 'Global Hospitality Marketing', 'Core Course'],
                    ['MSHM 203', 'International Hospitality Perspective', 'Core Course'],
                    
                    // Major Course
                    ['MSHM 205', 'Human Resource in International Hospitality Setting', 'Major Course'],
                    ['MSHM 210', 'Entrepreneurship in International Hospitality Industry', 'Major Course'],
                    ['MSHM 215', 'Ethical, Cultural and Legal Dimensions of Hospitality Management', 'Major Course'],
                    ['MSHM 220', 'Advanced Strategic Management in the Hospitality Industry', 'Major Course'],
                    ['MSHM 225', 'Global Leadership in Hospitality Industry', 'Major Course'],
                    
                    // Final Output
                    ['MSHM 300A', 'Thesis Writing', 'Final Output'],
                    ['MSHM 300B', 'Thesis Writing', 'Final Output'],
                ]
            ],

            // Master in Information Technology
            [
                'program_code' => 'MIT',
                'program_name' => 'Master in Information Technology',
                'college_acronym' => 'CEIT',
                'courses' => [
                    // Core Course
                    ['MSIT 201', 'Advanced Operating System and Networking', 'Core Course'],
                    ['MSIT 202', 'Advanced Database Systems', 'Core Course'],
                    ['MSIT 203', 'Information System and Theory', 'Core Course'],
                    ['MSIT 204', 'Advanced Programming', 'Core Course'],
                    
                    // Major Course
                    ['MSIT 250', 'Technological Trends in Computing with IT Seminar', 'Major Course'],
                    ['MSIT 255', 'IT Service Management', 'Major Course'],
                    ['MSIT 260', 'Risk Management and Business Continuity Plan', 'Major Course'],
                    ['MSIT 265', 'Business Intelligence Analytics', 'Major Course'],
                    ['MSIT 270', 'Methods of Research', 'Major Course'],
                    ['MSIT 275', 'Enterprise Architecture', 'Major Course'],
                    
                    // Thesis
                    ['MSIT 300', 'Graduate\'s Thesis', 'Thesis'],
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