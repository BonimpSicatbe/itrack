<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Requirement Report - {{ $requirement->name }}</title>

    <style>
        /* Define Green Color Theme Variables */
        :root {
            /* Green Theme */
            --primary-green: #01a73e;     /* Main brand green */
            --dark-green: #006b2f;        /* Darker green shade */
            --light-green: #e8f5e9;       /* Very light green background */
            --accent-green: #00c853;      /* Bright accent green */
            
            /* General Text and Border */
            --dark-text: #1f2937;         /* Darker charcoal text */
            --light-text: #374151;        /* Muted gray text */
            --border-color: #d1d5db;      /* Medium gray border */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            /* Arial Font Only */
            font-family: 'Arial', sans-serif;
            font-size: 12px; /* Increased from 10px */
            color: black;
            margin: 0.4in 1in; /* 0.4 inch top/bottom, 1 inch left/right */
        }

        /* --- Updated Header Styling (Letterhead Style) --- */
        .header {
            width: 100%;
            margin-bottom: 25px; /* Increased from 20px */
            text-align: center;
            font-family: 'Arial', sans-serif;
        }

        .header-content {
            display: table;
            width: 100%;
            margin: 0;
            padding: 0;
            table-layout: fixed;
            font-family: 'Arial', sans-serif;
        }

        .logo-left {
            display: table-cell;
            vertical-align: right;
            width: 25%; 
            text-align: right;
            font-family: 'Arial', sans-serif;
        }

        .header-center {
            display: table-cell;
            vertical-align: middle;
            width: 50%; 
            text-align: center; 
            padding: 0 1px; 
            font-family: 'Arial', sans-serif;
        }

        .logo-right {
            display: table-cell;
            vertical-align: left;
            width: 25%; 
            text-align: left;
            padding-left: 13px; 
            font-family: 'Arial', sans-serif;
        }

        .logo {
            max-height: 100px; 
            padding-top: 5px;
        }

        /* CHANGED: Added text-align: center to university-info to center all text */
        .university-info {
            margin: 0;
            padding: 0;
            line-height: 0.9;
            text-align: center;
            font-family: 'Arial', sans-serif;
        }

        .republic {
            font-size: 12px;
            font-weight: normal;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .university-name {
            font-size: 17px; 
            font-weight: bold;
            margin: 4px 0 0 0; 
            text-transform: uppercase;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .campus-name {
            font-size: 12px; 
            font-weight: bold;
            margin: 5px 0 0 0; 
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .address {
            font-size: 12px; 
            margin: 4px 0 0 0; 
            font-family: 'Arial', sans-serif;
        }

        .contact-info {
            font-size: 12px; 
            margin: 3px 0 0 0; 
            font-family: 'Arial', sans-serif;
        }

        .website {
            font-size: 12px; /* Increased from 9px */
            margin: 3px 0 0 0; /* Increased from 2px */
            font-style: italic;
            font-family: 'Arial', sans-serif;
        }

        .college-name {
            font-size: 16px; /* Same as university-name */
            font-weight: bold;
            margin: 10px 0 0 0; /* Increased from 8px */
            text-transform: uppercase;
            white-space: nowrap;
            font-family: 'Arial', sans-serif;
        }

        .college-divider {
            width: 100%;
            height: 2.5px;
            background-color: black;
            margin: 15px 0 0 0;
            border: none;
        }

        .report-title {
            font-size: 14px; /* Changed from 18px to 14px */
            font-weight: bold;
            margin: 18px 0 0 0; /* Increased from 15px */
            text-transform: uppercase;
            font-family: 'Arial', sans-serif;
        }

        /* Footer Styling */
        .footer {
            width: 100%;
            margin-top: 35px; /* Increased from 30px */
            padding-top: 12px; /* Increased from 10px */
            border-top: 2px solid #1f2937;
            font-family: 'Arial', sans-serif;
        }

        .footer-content {
            display: table;
            width: 100%;
            font-family: 'Arial', sans-serif;
        }

        .footer-left {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
            text-align: left;
            font-family: 'Arial', sans-serif;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
            text-align: right;
            color: var(--light-text);
            font-family: 'Arial', sans-serif;
        }

        .footer-info {
            font-size: 11px; /* Increased from 9px */
            font-family: 'Arial', sans-serif;
        }

        /* --- Section Titles (Minimalist) --- */
        .section-title {
            font-size: 14px; /* Changed from 16px to 14px */
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 30px 0 12px 0; /* Increased from 25px/10px */
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 6px; /* Increased from 5px */
            page-break-after: avoid;
            letter-spacing: 0.8px;
            font-family: 'Arial', sans-serif;
        }

        /* --- Info Boxes (Inline Display) --- */
        .info-container {
            display: table;
            width: 100%;
            margin-bottom: 25px; /* Increased from 20px */
            border-collapse: collapse;
            font-family: 'Arial', sans-serif;
        }

        .info-box {
            display: table-cell;
            width: 50%;
            padding: 12px 0; /* Increased from 10px */
            vertical-align: top;
            font-family: 'Arial', sans-serif;
        }

        .info-box:first-child {
            padding-right: 25px; /* Increased from 20px */
        }

        .info-header {
            font-size: 13px; /* Increased from 11px */
            font-weight: bold;
            color: #000;
            margin-bottom: 6px; /* Increased from 5px */
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 4px; /* Increased from 3px */
            font-family: 'Arial', sans-serif;
        }

        .info-details {
            font-size: 12px; /* Increased from 10px */
            margin-bottom: 3px; /* Increased from 2px */
            font-family: 'Arial', sans-serif;
        }
        
        .info-details strong {
            font-weight: 700;
            font-family: 'Arial', sans-serif;
        }

        /* --- Summary Grid (Inline) --- */
        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 18px; /* Increased from 15px */
            border: 1px solid #16A34A;
            overflow: hidden;
            border-collapse: collapse;
            font-family: 'Arial', sans-serif;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 12px 6px; /* Increased from 10px/5px */
            text-align: center;
            border-right: 1px solid #16A34A;
            border-radius: 4px;
            font-family: 'Arial', sans-serif;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-name {
            font-size: 11px; /* Increased from 9px */
            font-weight: 700;
            color: var(--dark-text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Arial', sans-serif;
        }

        .summary-value {
            font-size: 20px; /* Increased from 18px */
            font-weight: bold;
            margin-top: 4px; /* Increased from 3px */
            color: var(--primary-green);
            font-family: 'Arial', sans-serif;
        }

        /* --- Instructor/Course Table Styling --- */
        .instructor-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            page-break-inside: auto;
            font-family: 'Arial', sans-serif;
        }

        .instructor-header {
            background-color: var(--light-green);
            font-size: 12px;
            font-weight: bold;
            color: var(--primary-green);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        .instructor-header th {
             padding: 10px 12px;
             text-align: left;
             font-family: 'Arial', sans-serif;
        }

        .instructor-row {
            border-bottom: 1px solid var(--border-color);
            font-family: 'Arial', sans-serif;
        }

        .instructor-row td {
            padding: 12px;
            vertical-align: top;
            font-size: 11px;
            font-family: 'Arial', sans-serif;
        }

        .course-row {
            background-color: #ffffff;
        }

        .course-row:nth-child(even) {
            background-color: #f9fafb;
        }

        .course-info {
            font-weight: 600;
            color: black;
            font-family: 'Arial', sans-serif;
        }

        .course-code {
            font-weight: bold;
            color: var(--primary-green);
            font-size: 11px;
            font-family: 'Arial', sans-serif;
        }

        .course-name {
            font-size: 11px;
            color: black;
            margin: 2px 0;
            font-family: 'Arial', sans-serif;
        }

        .program-info {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
            font-family: 'Arial', sans-serif;
        }

        /* --- Status Styling --- */
        .status {
            /* Removed text-transform: uppercase */
            font-size: 10px; /* Increased from 8px */
            padding: 5px 9px; /* Increased from 4px/8px */
            border-radius: 9999px; /* Changed from 3px to 9999px for rounded-full effect */
            display: inline-block;
            font-weight: normal; /* Changed from bold to normal */
            letter-spacing: 0.5px;
            white-space: nowrap;
            font-family: 'Arial', sans-serif;
        }

        /* New Status Colors - Green Theme */
        .status-submitted { background-color: #d1fae5; color: #065f46; }
        .status-no-submission { background-color: #f3f4f6; color: #1f2937; }

        .submission-date {
            font-size: 9px; /* Increased from 7px */
            color: #666;
            margin-top: 5px; /* Increased from 4px */
            display: block;
            font-family: 'Arial', sans-serif;
        }

        .no-data {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 25px; /* Increased from 20px */
            border: 1px dashed var(--border-color);
            background-color: #fafbfc;
            font-family: 'Arial', sans-serif;
        }

        /* NEW: Right-aligned stacked approval sections */
        .approval-sections-right {
            width: 100%;
            margin-top: 40px;
            font-family: 'Arial', sans-serif;
            text-align: right;
        }

        .approval-item {
            margin-bottom: 30px;
            text-align: right;
            width: 100%;
            display: block;
        }

        .approval-item:last-child {
            margin-bottom: 0;
        }

        .approval-label {
            font-size: 11px;
            margin-bottom: 0;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            text-align: right;
            width: 100%;
            display: block;
        }

        .approval-spacing {
            height: 25px; /* Space between label and name */
            width: 100%;
            display: block;
        }

        .approval-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
            font-family: 'Arial', sans-serif;
            text-align: right;
            width: 100%;
            display: block;
        }

        .approval-position {
            font-size: 11px;
            font-family: 'Arial', sans-serif;
            text-align: right;
            width: 100%;
            display: block;
        }

        /* Print Specifics - UPDATED FOR APPROVAL SECTIONS */
        @media print {
            body {
                margin: 0.5in 1in;
                font-family: 'Arial', sans-serif;
            }
            
            .header { border-bottom: none; font-family: 'Arial', sans-serif; }
            .footer { border-top: 2px solid #000; font-family: 'Arial', sans-serif; }
            
            .instructor-header, .instructor-row, .summary-item {
                 -webkit-print-color-adjust: exact;
                 color-adjust: exact;
                 font-family: 'Arial', sans-serif;
            }
            
            .instructor-header {
                 background-color: #cccccc !important;
                 color: #000 !important;
                 border-top: 1px solid #000;
                 border-bottom: 1px solid #000;
                 font-family: 'Arial', sans-serif;
            }

            .course-row:nth-child(even) {
                 background-color: #f0f0f0 !important;
                 font-family: 'Arial', sans-serif;
            }

            .summary-item {
                 border: 1px solid #000;
                 font-family: 'Arial', sans-serif;
            }
            
            .status {
                border: 1px solid #333 !important;
                background-color: #fff !important;
                color: #000 !important;
                font-family: 'Arial', sans-serif;
                border-radius: 9999px; /* Added for print */
            }

            /* Enhanced page break control for approval sections */
            .approval-sections-right {
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
                margin-top: 30px;
                text-align: right;
            }

            /* Force new page if not enough space */
            .prepared-by-new-page {
                page-break-before: always !important;
            }
        }
    </style>
</head>

<body>
    @php
        $formattedPreparedByMiddleName = Auth::user()->middlename ? substr(trim(Auth::user()->middlename), 0, 1) . '.' : '';
    @endphp

    <div class="header">
        <div class="header-content">
            <div class="logo-left">
                <img src="{{ public_path('images/sample.png') }}" alt="CVSU Logo" class="logo">
            </div>
            <div class="header-center">
                <div class="university-info">
                    <div class="republic">Republic of the Philippines</div>
                    <div class="university-name">CAVITE STATE UNIVERSITY</div>
                    <div class="campus-name">Don Severino de las Alas Campus</div>
                    <div class="address">Indang, Cavite</div>
                    <div class="contact-info">(046) 483-9250</div>
                    <div class="website">www.cvsu.edu.ph</div>
                </div>

                <br>
                
                <div class="college-name">GRADUATE SCHOOL AND OPEN LEARNING COLLEGE</div>
            </div>
            <div class="logo-right">
                <img src="{{ public_path('images/1.png') }}" alt="BP Logo" class="logo">
            </div>
        </div>

        <hr class="college-divider">
        
        <div class="report-title">Requirement Report - {{ $requirement->name }}</div>
    </div>

    <div class="info-container">
        <div class="info-box">
            <div class="info-header">Requirement Details</div>
            <div class="info-details"><strong>Requirement: </strong>{{ $requirement->name }}</div>
            <div class="info-details"><strong>Due Date:</strong> {{ $requirement->due->format('F j, Y') }}</div>
        </div>
        
        <div class="info-box">
            <div class="info-header">Semester Details</div>
            <div class="info-details"><strong>Semester: </strong>{{ $semester->name }}</div>
            <div class="info-details"><strong>Start Date:</strong> {{ $semester->start_date->format('F j, Y') }}</div>
            <div class="info-details"><strong>End Date:</strong> {{ $semester->end_date->format('F j, Y') }}</div>
        </div>
    </div>

    @if(isset($submissionFilter) && $submissionFilter !== 'all')
    <div style="margin-bottom: 15px; padding: 8px; background-color: #f0f9ff; border-left: 4px solid #0ea5e9;">
        <strong>Filter Applied:</strong> 
        @if($submissionFilter === 'with_submission')
            Showing Only Courses With Submission
        @elseif($submissionFilter === 'no_submission')
            Showing Only Courses With No Submission
        @endif
    </div>
    @endif

    <div class="section-title">SUBMISSION SUMMARY</div>
    
    @php
        // Calculate statistics based on FILTERED data
        $allInstructors = collect([]);
        $totalCourseAssignments = 0;
        $submittedCount = 0;
        $instructorsWithCourses = $instructorsWithCourses ?? []; // Use the filtered data passed from controller
        
        // Calculate totals from filtered data
        foreach($instructorsWithCourses as $instructorData) {
            $instructor = $instructorData['instructor'];
            $courseSubmissions = $instructorData['courseSubmissions'];
            
            // Add instructor to unique list
            $allInstructors->push($instructor);
            
            foreach($courseSubmissions as $courseData) {
                $totalCourseAssignments++;
                
                if ($courseData['submission']) {
                    $submittedCount++;
                }
            }
        }
        
        $totalInstructors = $allInstructors->unique('id')->count();
        $noSubmissionCount = $totalCourseAssignments - $submittedCount;
        $completionRate = $totalCourseAssignments > 0 ? round(($submittedCount / $totalCourseAssignments) * 100, 1) : 0;
    @endphp
    
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-name">Total Faculty</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $totalInstructors }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Submitted</div>
            <div class="summary-value" style="color: var(--primary-green);">{{ $submittedCount }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">No Submission</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $noSubmissionCount }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Completion Rate</div>
            <div class="summary-value" style="color: var(--primary-green);">
                {{ $completionRate }}%
            </div>
        </div>
    </div>

    <div class="section-title">FACULTY SUBMISSIONS BY COURSE</div>
    
    @if(count($instructorsWithCourses) > 0)
        <table class="instructor-table">
            <thead>
                <tr class="instructor-header">
                    <th style="width: 30%;">Faculty Information</th>
                    <th style="width: 55%;">Course Details</th>
                    <th style="width: 15%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($instructorsWithCourses as $instructorData)
                    @php
                        $instructor = $instructorData['instructor'];
                        $courseSubmissions = $instructorData['courseSubmissions'];
                        $isFirstCourse = true;
                    @endphp
                    
                    @foreach($courseSubmissions as $courseData)
                        @php
                            $course = $courseData['course'];
                            $submission = $courseData['submission'];
                        @endphp
                        
                        <tr class="instructor-row course-row">
                            <td>
                                @if($isFirstCourse)
                                    <strong>{{ $instructor->firstname }} {{ $instructor->middlename ? substr(trim($instructor->middlename), 0, 1) . '.' : '' }} {{ $instructor->lastname }} {{ $instructor->extensionname ?? '' }}</strong>
                                    <div style="font-size: 10px; color: #666; margin-top: 4px;">
                                        {{ $instructor->position ?? 'N/A' }}
                                    </div>
                                    <div style="font-size: 9px; color: #666;">
                                        {{ $instructor->email ?? '' }}
                                    </div>
                                    @php $isFirstCourse = false; @endphp
                                @endif
                            </td>
                            <td class="course-info">
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <div style="font-weight: bold; color: var(--primary-green); font-size: 11px;">
                                        {{ $course->course_code ?? 'N/A' }}
                                        <div style="font-weight: normal; font-size: 11px; color: black;">
                                            {{ $course->course_name ?? 'N/A' }}
                                        </div>
                                        <div style="font-weight: normal; font-size: 10px; color: #666;">
                                            {{ $course->program->program_code ?? 'N/A' }} - {{ $course->program->program_name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if($submission)
                                    <div class="status status-submitted">
                                        Submitted
                                    </div>
                                    @if($submission->submitted_at)
                                        <div class="submission-date">
                                            {{ $submission->submitted_at->format('M j, Y') }}
                                        </div>
                                    @endif
                                @else
                                    <div class="status status-no-submission">
                                        No Submission
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No instructors with course assignments found for this semester.</div>
    @endif

    <div class="footer">
        <div class="footer-content">
            <div class="footer-right">
                <div class="footer-info">Generated On: {{ now()->format('l, F j, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Approval Sections aligned to the right -->
    <div class="approval-sections-right">
        <div class="approval-item">
            <div class="approval-label">Prepared by:</div>
            <div class="approval-spacing"></div>
            <div class="approval-name">{{ Auth::user()->firstname }} {{ $formattedPreparedByMiddleName }} {{ Auth::user()->lastname }} {{ Auth::user()->extensionname }}</div>
            <div class="approval-position">{{ Auth::user()->position ?? 'Administrator' }}</div>
        </div>
        
        <div class="approval-item">
            <div class="approval-label">Noted by:</div>
            <div class="approval-spacing"></div>
            <div class="approval-name">DR. MARIA S. SANTOS</div>
            <div class="approval-position">Dean, Graduate School</div>
        </div>
        
        <div class="approval-item">
            <div class="approval-label">Checked by:</div>
            <div class="approval-spacing"></div>
            <div class="approval-name">DR. JUAN R. CRUZ</div>
            <div class="approval-position">Quality Assurance Director</div>
        </div>
    </div>
</body>
</html>